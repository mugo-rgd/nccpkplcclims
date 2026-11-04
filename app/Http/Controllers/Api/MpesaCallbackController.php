<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Calibration;
use App\Models\MpesaPayment;
use App\Models\Quote;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class MpesaCallbackController extends Controller
{
    /**
     * Receive the M-PESA Express callback.
     */
    public function handle(
        Request $request
    ): JsonResponse {
        $payload = $request->all();

        Log::info('M-PESA STK callback received', [
            'checkout_request_id' => data_get(
                $payload,
                'Body.stkCallback.CheckoutRequestID'
            ),
        ]);

        $callback = data_get(
            $payload,
            'Body.stkCallback'
        );

        if (!is_array($callback)) {
            Log::warning(
                'Invalid M-PESA callback structure.',
                [
                    'payload' => $payload,
                ]
            );

            /*
             * Return HTTP 200 so Safaricom does not repeatedly
             * retry a malformed callback.
             */
            return response()->json([
                'ResultCode' => 0,
                'ResultDesc' =>
                    'Callback received.',
            ]);
        }

        $checkoutRequestId = trim(
            (string) (
                $callback['CheckoutRequestID']
                ?? ''
            )
        );

        if ($checkoutRequestId === '') {
            return response()->json([
                'ResultCode' => 0,
                'ResultDesc' =>
                    'Callback received without checkout ID.',
            ]);
        }

        $payment = MpesaPayment::query()
            ->where(
                'checkout_request_id',
                $checkoutRequestId
            )
            ->first();

        if (!$payment) {
            Log::warning(
                'M-PESA callback payment not found.',
                [
                    'checkout_request_id' =>
                        $checkoutRequestId,
                ]
            );

            return response()->json([
                'ResultCode' => 0,
                'ResultDesc' =>
                    'Callback received.',
            ]);
        }

        /*
         * Idempotency:
         * do not process a successful payment twice.
         */
        if (
            $payment->status
            === MpesaPayment::STATUS_COMPLETED
        ) {
            return response()->json([
                'ResultCode' => 0,
                'ResultDesc' =>
                    'Payment already processed.',
            ]);
        }

        $resultCode = (int) (
            $callback['ResultCode']
            ?? -1
        );

        $resultDescription = (string) (
            $callback['ResultDesc']
            ?? 'No result description supplied.'
        );

        $metadata = $this->metadataToArray(
            data_get(
                $callback,
                'CallbackMetadata.Item',
                []
            )
        );

        try {
            DB::transaction(function () use (
                $payment,
                $payload,
                $resultCode,
                $resultDescription,
                $metadata
            ): void {
                $lockedPayment = MpesaPayment::query()
                    ->lockForUpdate()
                    ->findOrFail($payment->id);

                if (
                    $lockedPayment->status
                    === MpesaPayment::STATUS_COMPLETED
                ) {
                    return;
                }

                if ($resultCode !== 0) {
                    $this->markFailed(
                        $lockedPayment,
                        $payload,
                        $resultCode,
                        $resultDescription
                    );

                    return;
                }

                $receiptNumber = trim(
                    (string) (
                        $metadata['MpesaReceiptNumber']
                        ?? ''
                    )
                );

                $paidAmount = (float) (
                    $metadata['Amount']
                    ?? 0
                );

                $transactionPhone = trim(
                    (string) (
                        $metadata['PhoneNumber']
                        ?? ''
                    )
                );

                $transactionDate =
                    $this->parseTransactionDate(
                        $metadata['TransactionDate']
                        ?? null
                    );

                /*
                 * Ensure receipt numbers remain unique.
                 */
                $duplicateReceipt = MpesaPayment::query()
                    ->where(
                        'mpesa_receipt_number',
                        $receiptNumber
                    )
                    ->whereKeyNot(
                        $lockedPayment->id
                    )
                    ->exists();

                if (
                    $receiptNumber === ''
                    || $duplicateReceipt
                ) {
                    $lockedPayment->update([
                        'status' =>
                            MpesaPayment::STATUS_FAILED,

                        'result_code' => $resultCode,

                        'result_description' =>
                            $receiptNumber === ''
                                ? 'Successful callback did not contain an M-PESA receipt number.'
                                : 'The supplied M-PESA receipt has already been processed.',

                        'callback_payload' => $payload,
                        'failed_at' => now(),
                    ]);

                    return;
                }

                /*
                 * Confirm the callback amount matches the
                 * amount originally requested.
                 */
                if (
                    abs(
                        $paidAmount
                        - (float) $lockedPayment->amount
                    ) > 0.01
                ) {
                    $lockedPayment->update([
                        'status' =>
                            MpesaPayment::STATUS_FAILED,

                        'result_code' => $resultCode,

                        'result_description' =>
                            'The M-PESA callback amount does not match the requested payment amount.',

                        'paid_amount' => $paidAmount,
                        'callback_payload' => $payload,
                        'failed_at' => now(),
                    ]);

                    return;
                }

                $lockedPayment->update([
                    'status' =>
                        MpesaPayment::STATUS_COMPLETED,

                    'result_code' => $resultCode,

                    'result_description' =>
                        $resultDescription,

                    'mpesa_receipt_number' =>
                        $receiptNumber,

                    'paid_amount' => $paidAmount,

                    'transaction_phone' =>
                        $transactionPhone,

                    'transaction_date' =>
                        $transactionDate,

                    'callback_payload' => $payload,

                    'completed_at' => now(),
                    'failed_at' => null,
                ]);

                $this->reconcileQuote(
                    $lockedPayment
                );
            });
        } catch (Throwable $exception) {
            report($exception);

            /*
             * Returning 200 acknowledges receipt, but the error remains
             * in Laravel logs for controlled reconciliation.
             */
            return response()->json([
                'ResultCode' => 0,
                'ResultDesc' =>
                    'Callback received for review.',
            ]);
        }

        return response()->json([
            'ResultCode' => 0,
            'ResultDesc' =>
                'Callback processed successfully.',
        ]);
    }

    /**
     * Convert the Daraja metadata item list into an associative array.
     */
    private function metadataToArray(
        mixed $items
    ): array {
        if (!is_array($items)) {
            return [];
        }

        $metadata = [];

        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $name = $item['Name'] ?? null;

            if (!$name) {
                continue;
            }

            $metadata[$name] =
                $item['Value'] ?? null;
        }

        return $metadata;
    }

    private function markFailed(
        MpesaPayment $payment,
        array $payload,
        int $resultCode,
        string $resultDescription
    ): void {
        $status = match ($resultCode) {
            /*
             * Commonly encountered cancellation/timeout results.
             * The description is also stored unchanged.
             */
            1032 =>
                MpesaPayment::STATUS_CANCELLED,

            1037 =>
                MpesaPayment::STATUS_TIMEOUT,

            default =>
                MpesaPayment::STATUS_FAILED,
        };

        $payment->update([
            'status' => $status,
            'result_code' => $resultCode,

            'result_description' =>
                $resultDescription,

            'callback_payload' => $payload,

            'failed_at' => $status
                === MpesaPayment::STATUS_FAILED
                    ? now()
                    : null,

            'cancelled_at' => $status
                === MpesaPayment::STATUS_CANCELLED
                    ? now()
                    : null,
        ]);
    }

    /**
     * Recalculate quote payment totals from successful M-PESA payments.
     */
    private function reconcileQuote(
        MpesaPayment $payment
    ): void {
        $quote = Quote::query()
            ->lockForUpdate()
            ->find($payment->quote_id);

        if (!$quote) {
            return;
        }

        /*
         * Preserve any payments already recorded outside this
         * M-PESA integration by taking the greater of:
         *
         * 1. Existing amount_paid
         * 2. Total successful M-PESA payments
         *
         * Change this rule if all quote payments will be recorded
         * through mpesa_payments.
         */
        $successfulMpesaTotal =
            (float) MpesaPayment::query()
                ->where('quote_id', $quote->id)
                ->where(
                    'status',
                    MpesaPayment::STATUS_COMPLETED
                )
                ->sum('paid_amount');

        $existingAmountPaid =
            (float) $quote->amount_paid;

        $newAmountPaid = max(
            $successfulMpesaTotal,
            $existingAmountPaid
        );

        $totalAmount = (float) $quote->total_amount;

        $balanceDue = max(
            $totalAmount - $newAmountPaid,
            0
        );

        $paymentStatus = match (true) {
            $balanceDue <= 0.01 => 'paid',
            $newAmountPaid > 0 => 'partially_paid',
            default => 'unpaid',
        };

        $quote->update([
            'amount_paid' => $newAmountPaid,
            'balance_due' => $balanceDue,

            'mpesa_payment_status' =>
                $paymentStatus,

            'paid_at' => $paymentStatus === 'paid'
                ? now()
                : null,

            'payment_method' => 'mpesa',

            'payment_reference' =>
                $payment->mpesa_receipt_number,
        ]);

        /*
         * Optionally synchronize the related calibration.
         *
         * Only enable this block if your calibration payment fields
         * use these exact column names.
         */

        if ($quote->calibration_id) {
           Calibration::query()
            ->whereKey($quote->calibration_id)
            ->update([
                'amount_paid' => $newAmountPaid,
                'payment_method' => 'mpesa',
                'payment_reference' =>
                    $payment->mpesa_receipt_number,
                'payment_status' =>
                    $paymentStatus === 'paid'
                        ? 'paid'
                        : 'partial',
            ]);
        }

    }

    private function parseTransactionDate(
        mixed $value
    ): ?Carbon {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        try {
            return Carbon::createFromFormat(
                'YmdHis',
                $value
            );
        } catch (Throwable) {
            return null;
        }
    }
}
