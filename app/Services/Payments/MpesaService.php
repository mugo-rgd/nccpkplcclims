<?php

namespace App\Services\Payments;

use App\Models\MpesaPayment;
use App\Models\Quote;
use Carbon\Carbon;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class MpesaService
{
    /**
     * Obtain an OAuth access token from Daraja.
     */
    public function accessToken(): string
    {
        $consumerKey = trim(
            (string) config('mpesa.consumer_key')
        );

        $consumerSecret = trim(
            (string) config('mpesa.consumer_secret')
        );

        if (
            $consumerKey === ''
            || $consumerSecret === ''
        ) {
            throw new RuntimeException(
                'M-PESA consumer credentials are not configured.'
            );
        }

        try {
            $response = Http::withBasicAuth(
                $consumerKey,
                $consumerSecret
            )
                ->acceptJson()
                ->timeout(
                    (int) config('mpesa.timeout', 60)
                )
                ->get(
                    $this->baseUrl()
                    . '/oauth/v1/generate',
                    [
                        'grant_type' => 'client_credentials',
                    ]
                );
        } catch (ConnectionException $exception) {
            throw new RuntimeException(
                'The M-PESA authentication service could not be reached.',
                previous: $exception
            );
        }

        if ($response->failed()) {
            throw new RuntimeException(
                'M-PESA authentication failed with HTTP '
                . $response->status()
                . ': '
                . $this->extractErrorMessage($response->json())
            );
        }

        $token = trim(
            (string) $response->json('access_token')
        );

        if ($token === '') {
            throw new RuntimeException(
                'M-PESA did not return an access token.'
            );
        }

        return $token;
    }

    /**
     * Initiate an M-PESA Express/STK Push payment.
     */
    public function initiateStkPush(
        Quote $quote,
        string $phoneNumber,
        float $amount
    ): MpesaPayment {
        $phoneNumber = $this->normalizePhoneNumber(
            $phoneNumber
        );

        $this->validatePaymentAmount(
            $quote,
            $amount
        );

        $timestamp = now()->format('YmdHis');

        $shortcode = trim(
            (string) config('mpesa.shortcode')
        );

        $passkey = trim(
            (string) config('mpesa.passkey')
        );

        $callbackUrl = trim(
            (string) config('mpesa.callback_url')
        );

        if (
            $shortcode === ''
            || $passkey === ''
            || $callbackUrl === ''
        ) {
            throw new RuntimeException(
                'M-PESA shortcode, passkey or callback URL is not configured.'
            );
        }

        $password = base64_encode(
            $shortcode
            . $passkey
            . $timestamp
        );

        $accountReference =
            $this->buildAccountReference($quote);

        $payment = MpesaPayment::query()->create([
            'quote_id' => $quote->id,
            'calibration_id' => $quote->calibration_id,
            'customer_id' => $quote->customer_id,

            'payment_reference' =>
                $this->generatePaymentReference(),

            'phone_number' => $phoneNumber,
            'amount' => $amount,
            'currency' => $quote->currency ?: 'KES',
            'status' => MpesaPayment::STATUS_PENDING,

            'initiated_at' => now(),

            'ip_address' => request()->ip(),

            'user_agent' => Str::limit(
                (string) request()->userAgent(),
                500,
                ''
            ),
        ]);

        $payload = [
            'BusinessShortCode' => $shortcode,
            'Password' => $password,
            'Timestamp' => $timestamp,

            'TransactionType' => config(
                'mpesa.transaction_type',
                'CustomerPayBillOnline'
            ),

            /*
             * Daraja expects a whole-number amount.
             */
            'Amount' => (int) round($amount),

            'PartyA' => $phoneNumber,
            'PartyB' => $shortcode,
            'PhoneNumber' => $phoneNumber,

            'CallBackURL' => $callbackUrl,

            'AccountReference' => $accountReference,

            'TransactionDesc' =>
                'Payment for quote '
                . $quote->quote_number,
        ];

        $payment->update([
            'request_payload' =>
                $this->safeRequestPayload($payload),
        ]);

        try {
            $response = Http::withToken(
                $this->accessToken()
            )
                ->acceptJson()
                ->asJson()
                ->timeout(
                    (int) config('mpesa.timeout', 60)
                )
                ->post(
                    $this->baseUrl()
                    . '/mpesa/stkpush/v1/processrequest',
                    $payload
                );
        } catch (ConnectionException $exception) {
            $payment->update([
                'status' => MpesaPayment::STATUS_FAILED,
                'failed_at' => now(),
                'result_description' =>
                    'Connection to M-PESA failed.',
            ]);

            throw new RuntimeException(
                'The M-PESA payment service could not be reached.',
                previous: $exception
            );
        }

        $responseData = $response->json();

        if (!is_array($responseData)) {
            $responseData = [
                'raw_response' => Str::limit(
                    $response->body(),
                    5000
                ),
            ];
        }

        if ($response->failed()) {
            $payment->update([
                'status' => MpesaPayment::STATUS_FAILED,
                'failed_at' => now(),
                'initial_response' => $responseData,
                'result_description' =>
                    $this->extractErrorMessage(
                        $responseData
                    ),
            ]);

            throw new RuntimeException(
                'M-PESA rejected the payment request: '
                . $this->extractErrorMessage(
                    $responseData
                )
            );
        }

        $responseCode = (string) (
            $responseData['ResponseCode']
            ?? ''
        );

        $payment->update([
            'merchant_request_id' =>
                $responseData['MerchantRequestID']
                ?? null,

            'checkout_request_id' =>
                $responseData['CheckoutRequestID']
                ?? null,

            'response_code' => $responseCode,

            'response_description' =>
                $responseData['ResponseDescription']
                ?? null,

            'customer_message' =>
                $responseData['CustomerMessage']
                ?? null,

            'initial_response' => $responseData,

            'status' => $responseCode === '0'
                ? MpesaPayment::STATUS_PROCESSING
                : MpesaPayment::STATUS_FAILED,

            'failed_at' => $responseCode === '0'
                ? null
                : now(),
        ]);

        if ($responseCode !== '0') {
            throw new RuntimeException(
                $payment->customer_message
                ?: $payment->response_description
                ?: 'M-PESA could not initiate the payment.'
            );
        }

        return $payment->fresh();
    }

    /**
     * Normalize Kenyan M-PESA phone numbers to 2547XXXXXXXX
     * or 2541XXXXXXXX format.
     */
    public function normalizePhoneNumber(
        string $phoneNumber
    ): string {
        $phoneNumber = preg_replace(
            '/\D+/',
            '',
            $phoneNumber
        );

        if (str_starts_with($phoneNumber, '0')) {
            $phoneNumber =
                '254' . substr($phoneNumber, 1);
        } elseif (
            str_starts_with($phoneNumber, '7')
            || str_starts_with($phoneNumber, '1')
        ) {
            $phoneNumber = '254' . $phoneNumber;
        }

        if (
            !preg_match(
                '/^254(?:7|1)\d{8}$/',
                $phoneNumber
            )
        ) {
            throw ValidationException::withMessages([
                'phone_number' =>
                    'Enter a valid Kenyan M-PESA number, for example 0712345678.',
            ]);
        }

        return $phoneNumber;
    }

    /**
     * Confirm the amount does not exceed the outstanding quote balance.
     */
    private function validatePaymentAmount(
        Quote $quote,
        float $amount
    ): void {
        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'amount' =>
                    'The payment amount must be greater than zero.',
            ]);
        }

        $balance = max(
            (float) $quote->balance_due,
            0
        );

        if ($balance <= 0) {
            throw ValidationException::withMessages([
                'amount' =>
                    'This quote has already been fully paid.',
            ]);
        }

        if ($amount > $balance) {
            throw ValidationException::withMessages([
                'amount' =>
                    'The payment cannot exceed the outstanding balance of KES '
                    . number_format($balance, 2)
                    . '.',
            ]);
        }

        /*
         * STK Push uses a whole-number amount.
         */
        if (round($amount, 2) !== round($amount)) {
            throw ValidationException::withMessages([
                'amount' =>
                    'M-PESA payment amounts must be whole Kenyan shillings.',
            ]);
        }
    }

 /**
 * Build the M-PESA account reference.
 *
 * Example:
 * Quote number: KP1/13B.1/QT/3/7-KPLC-00016
 * Account:      MCLAB-QT00016
 */
public function buildAccountReference(
    Quote $quote
): string {
    $account = strtoupper(
        preg_replace(
            '/[^A-Za-z0-9]/',
            '',
            (string) config(
                'mpesa.account_number',
                'MCLAB'
            )
        )
    );

    $sequence = null;

    if (
        preg_match(
            '/(\d+)$/',
            (string) $quote->quote_number,
            $matches
        )
    ) {
        $sequence = str_pad(
            (string) ((int) $matches[1]),
            5,
            '0',
            STR_PAD_LEFT
        );
    }

    if (!$sequence) {
        $sequence = str_pad(
            (string) $quote->id,
            5,
            '0',
            STR_PAD_LEFT
        );
    }

    return $account . '-QT' . $sequence;
}

    private function generatePaymentReference(): string
    {
        return 'MCL-MPESA-'
            . now()->format('YmdHis')
            . '-'
            . strtoupper(Str::random(8));
    }

    private function safeRequestPayload(
        array $payload
    ): array {
        unset($payload['Password']);

        return $payload;
    }

    private function baseUrl(): string
    {
        return rtrim(
            (string) config('mpesa.base_url'),
            '/'
        );
    }

    private function extractErrorMessage(
        mixed $data
    ): string {
        if (!is_array($data)) {
            return 'Unknown M-PESA error.';
        }

        return (string) (
            $data['errorMessage']
            ?? $data['ResponseDescription']
            ?? $data['CustomerMessage']
            ?? $data['ResultDesc']
            ?? $data['message']
            ?? 'Unknown M-PESA error.'
        );
    }
}
