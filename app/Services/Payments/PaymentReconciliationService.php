<?php

namespace App\Services\Payments;

use App\Models\MpesaPayment;
use App\Models\PaymentTransaction;
use App\Models\Quote;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PaymentReconciliationService
{
    /** Call inside a transaction. All payment writers must lock the quotation first. */
    public function reconcile(int $quoteId): Quote
    {
        $quote = Quote::query()->lockForUpdate()->findOrFail($quoteId);
        $total = $this->cents($quote->total_amount);
        $posted = (int) PaymentTransaction::query()
            ->where('quote_id', $quoteId)
            ->where('status', PaymentTransaction::CONFIRMED)
            ->selectRaw('COALESCE(SUM(ROUND(amount * 100)), 0) AS cents')
            ->value('cents');

        // Do not erase payments recorded before the ledger was installed.
        if ($posted < $this->cents($quote->amount_paid)) {
            throw new RuntimeException("Quote {$quoteId}: historical payments must be audited/backfilled before reconciliation.");
        }
        if ($posted > $total) {
            throw new RuntimeException("Quote {$quoteId}: confirmed payments exceed quotation total.");
        }
        if (!$quote->is_external || in_array($quote->status, [Quote::STATUS_CANCELLED, Quote::STATUS_REJECTED], true)) {
            throw new RuntimeException("Quote {$quoteId}: cancelled/rejected/internal quotation cannot be reconciled automatically.");
        }
        if (!in_array($quote->status, [Quote::STATUS_SENT, Quote::STATUS_PARTIAL_PAID, Quote::STATUS_PAID], true)) {
            throw new RuntimeException("Quote {$quoteId}: quotation status is not eligible for automatic reconciliation.");
        }

        $remaining = $total - $posted;
        $status = $remaining === 0 ? Quote::STATUS_PAID : ($posted > 0 ? Quote::STATUS_PARTIAL_PAID : Quote::STATUS_SENT);
        $last = PaymentTransaction::query()->where('quote_id', $quoteId)
            ->where('status', PaymentTransaction::CONFIRMED)->orderByDesc('id')->first();
        $quote->forceFill([
            'amount_paid' => $posted / 100,
            'balance_due' => $remaining / 100,
            'status' => $status,
           'mpesa_payment_status' => $last?->method === 'mpesa'
    ? ($status === Quote::STATUS_PAID
        ? 'paid'
        : 'partially_paid')
    : 'unpaid',
            'paid_at' => $remaining === 0 ? ($quote->paid_at ?? now()) : null,
            'payment_method' => $last?->method,
            'payment_reference' => $last?->reference,
        ])->save();

        // Sync only known calibration payment fields. Do not overwrite its laboratory workflow status.
      // Synchronize calibration payment details and permitted workflow transition.
if ($quote->calibration_id) {
    $calibration = $quote->calibration()
        ->lockForUpdate()
        ->first();

    if ($calibration) {
        $updates = [
            'amount_due' => $total / 100,
            'amount_paid' => $posted / 100,
            'payment_status' => $remaining === 0
                ? 'paid'
                : ($posted > 0 ? 'partial' : 'pending'),
            'payment_method' => $last?->method,
            'payment_reference' => $last?->reference,
            'payment_received_at' => $last?->confirmed_at,
            'payment_date' => $last?->payment_date,
        ];

        // Only release the payment hold when fully paid.
        // Never reset an already-progressed laboratory workflow.
        if (
            $remaining === 0
            && $calibration->status === 'pending_payment'
        ) {
            $updates['status'] = 'paid';
        }

        $calibration->forceFill($updates)->save();
    }
}
        return $quote->refresh();
    }

    /** Quotation row must be locked before calling this method. */
    public function postCompletedMpesa(MpesaPayment $payment): void
    {
        if ($payment->status !== MpesaPayment::STATUS_COMPLETED || !$payment->mpesa_receipt_number) {
            throw new RuntimeException('M-Pesa payment must be completed and have a receipt.');
        }
        $quote = Quote::query()->lockForUpdate()->findOrFail($payment->quote_id);
        if (!$quote->is_external || !in_array($quote->status, [Quote::STATUS_SENT, Quote::STATUS_PARTIAL_PAID, Quote::STATUS_PAID], true)) {
            throw new RuntimeException('Quotation is not eligible for M-Pesa ledger posting.');
        }
        if ($this->cents($payment->paid_amount) <= 0) {
            throw new RuntimeException('M-Pesa paid amount must be positive.');
        }
        $existing = PaymentTransaction::query()->where('mpesa_payment_id', $payment->id)->first();
        if ($existing) {
            if ($existing->method !== 'mpesa' || $existing->status !== PaymentTransaction::CONFIRMED
                || $existing->reference !== $payment->mpesa_receipt_number
                || $this->cents($existing->amount) !== $this->cents($payment->paid_amount)
                || (int) $existing->quote_id !== (int) $quote->id) {
                throw new RuntimeException('Existing M-Pesa ledger entry does not match the payment.');
            }
        } else {
            if (PaymentTransaction::query()->where('method', 'mpesa')->where('reference', $payment->mpesa_receipt_number)->exists()) {
                throw new RuntimeException('M-Pesa receipt already posted to a different ledger entry.');
            }
            PaymentTransaction::query()->create([
                'quote_id' => $quote->id,
                'mpesa_payment_id' => $payment->id,
                'method' => 'mpesa',
                'status' => PaymentTransaction::CONFIRMED,
                'amount' => $payment->paid_amount,
                'currency' => 'KES',
                'reference' => $payment->mpesa_receipt_number,
                'payment_date' => ($payment->transaction_date ?? $payment->completed_at ?? now())->toDateString(),
                'confirmed_at' => $payment->completed_at ?? now(),
            ]);
        }
        $this->reconcile($quote->id);
    }

    /** For a previously completed M-Pesa payment missing its ledger entry. */
    public function recordMpesa(MpesaPayment $payment): void
    {
        DB::transaction(function () use ($payment): void {
            $original = MpesaPayment::query()->findOrFail($payment->id);
            Quote::query()->lockForUpdate()->findOrFail($original->quote_id);
            $locked = MpesaPayment::query()->lockForUpdate()->findOrFail($payment->id);
            $this->postCompletedMpesa($locked);
        }, 3);
    }

    private function cents(mixed $value): int
    {
        return (int) round((float) $value * 100);
    }
}
