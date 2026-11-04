<?php

namespace App\Http\Controllers\Payments;

use App\Http\Controllers\Controller;
use App\Models\MpesaPayment;
use App\Models\Quote;
use App\Services\Payments\MpesaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class MpesaPaymentController extends Controller
{
    public function __construct(
        private readonly MpesaService $mpesaService
    ) {
    }

    /**
     * Display the public quote payment page.
     *
     * The customer does not need to be logged in.
     */
    public function show(
        Request $request,
        Quote $quote
    ): View {
        $this->validateQuoteToken(
            $request,
            $quote
        );

        $quote->loadMissing([
            'customer',
            'calibration',
        ]);

        $quote->refresh();

        $outstandingBalance = max(
            (float) $quote->balance_due,
            0
        );

        $initiateUrl = URL::temporarySignedRoute(
            'payments.quote.mpesa.initiate',
            now()->addMinutes(30),
            [
                'quote' => $quote->id,
                'token' => $quote->payment_token,
            ]
        );

        return view(
            'payments.mpesa.quote',
            compact(
                'quote',
                'outstandingBalance',
                'initiateUrl'
            )
        );
    }

    /**
     * Initiate the M-PESA STK Push.
     */
    public function initiate(
        Request $request,
        Quote $quote
    ): JsonResponse|RedirectResponse {
        $this->validateQuoteToken(
            $request,
            $quote
        );

        $validated = $request->validate([
            'phone_number' => [
                'required',
                'string',
                'max:20',
            ],

            'amount' => [
                'required',
                'numeric',
                'min:1',
            ],
        ]);

        /*
         * Reload financial values before initiating payment.
         */
        $quote->refresh();

        try {
            $payment =
                $this->mpesaService->initiateStkPush(
                    $quote,
                    $validated['phone_number'],
                    (float) $validated['amount']
                );

            $statusUrl = URL::temporarySignedRoute(
                'payments.quote.mpesa.status',
                now()->addHours(2),
                [
                    'quote' => $quote->id,
                    'payment' => $payment->id,
                    'token' => $quote->payment_token,
                ]
            );

            return response()->json([
                'success' => true,

                'message' =>
                    $payment->customer_message
                    ?: 'Check your phone and enter your M-PESA PIN.',

                'payment' => [
                    'id' => $payment->id,
                    'reference' =>
                        $payment->payment_reference,

                    'status' => $payment->status,

                    'amount' => (float) $payment->amount,
                ],

                'status_url' => $statusUrl,
            ]);
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'success' => false,

                'message' => app()->isLocal()
                    ? $exception->getMessage()
                    : 'The M-PESA payment could not be initiated. Please try again.',
            ], 422);
        }
    }

    /**
     * Poll the current payment status.
     */
    public function status(
        Request $request,
        Quote $quote,
        MpesaPayment $payment
    ): JsonResponse {
        $this->validateQuoteToken(
            $request,
            $quote
        );

        abort_unless(
            (int) $payment->quote_id
            === (int) $quote->id,
            404
        );

        $payment->refresh();
        $quote->refresh();

        return response()->json([
            'success' => true,

            'payment' => [
                'id' => $payment->id,
                'status' => $payment->status,

                'result_code' =>
                    $payment->result_code,

                'message' =>
                    $payment->result_description
                    ?: $payment->customer_message
                    ?: $payment->response_description,

                'receipt_number' =>
                    $payment->mpesa_receipt_number,

                'paid_amount' =>
                    (float) (
                        $payment->paid_amount
                        ?? 0
                    ),

                'completed_at' =>
                    $payment->completed_at
                        ?->toIso8601String(),
            ],

            'quote' => [
                'amount_paid' =>
                    (float) $quote->amount_paid,

                'balance_due' =>
                    (float) $quote->balance_due,

                'mpesa_payment_status' =>
                    $quote->mpesa_payment_status,
            ],
        ]);
    }

    private function validateQuoteToken(
        Request $request,
        Quote $quote
    ): void {
        $storedToken = (string) (
            $quote->payment_token ?? ''
        );

        $providedToken = (string) (
            $request->query('token')
            ?? $request->input('token')
            ?? ''
        );

        abort_unless(
            $storedToken !== ''
            && $providedToken !== ''
            && hash_equals(
                $storedToken,
                $providedToken
            ),
            403,
            'This quote payment link is invalid.'
        );
    }
}
