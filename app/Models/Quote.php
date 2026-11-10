<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Quote extends Model
{
    use SoftDeletes;

    protected $table = 'quotes';

    public const STATUS_DRAFT = 'draft';
    public const STATUS_SENT = 'sent';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_PAID = 'paid';
    public const STATUS_PARTIAL_PAID = 'partial_paid';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
    'calibration_id',
    'customer_id',
    'created_by',
    'approved_by',

    'quote_number',
    'quote_reference',

    'subtotal',
    'tax_amount',
    'tax_rate',
    'discount_amount',
    'discount_percentage',
    'total_amount',

    'items',
    'notes',
    'terms',

    'valid_until',
    'sent_at',
    'approved_at',
    'rejected_at',
    'expired_at',

    'status',
    'approval_notes',
    'rejection_reason',

    'amount_paid',
    'balance_due',
    'payment_due_date',

    'is_external',
    'currency',
    'metadata',

    'payment_token',
    'mpesa_payment_status',
    'paid_at',
    'pdf_path',
    'payment_method',
    'payment_reference',
];

    protected $casts = [
    'subtotal' => 'decimal:2',
    'tax_amount' => 'decimal:2',
    'tax_rate' => 'decimal:2',
    'discount_amount' => 'decimal:2',
    'discount_percentage' => 'decimal:2',
    'total_amount' => 'decimal:2',
    'amount_paid' => 'decimal:2',
    'balance_due' => 'decimal:2',

    'items' => 'array',
    'metadata' => 'array',

    'valid_until' => 'date',
    'sent_at' => 'datetime',
    'approved_at' => 'datetime',
    'rejected_at' => 'datetime',
    'expired_at' => 'datetime',
    'updated_at' => 'datetime',
    'payment_due_date' => 'date',
    'paid_at' => 'datetime',

    'is_external' => 'boolean',
];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function calibration(): BelongsTo
    {
        return $this->belongsTo(Calibration::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /*
    |--------------------------------------------------------------------------
    | Status checks
    |--------------------------------------------------------------------------
    */

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isSent(): bool
    {
        return $this->status === self::STATUS_SENT;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    public function isPartiallyPaid(): bool
    {
        return $this->status === self::STATUS_PARTIAL_PAID;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

   
public function canBeApproved(): bool
{
    return $this->status === self::STATUS_DRAFT
        && !$this->isExpiredByDate();
}

public function canBeSent(): bool
{
    return $this->status === self::STATUS_APPROVED
        && !$this->isExpiredByDate();
}

public function canBeRejected(): bool
{
    return $this->status === self::STATUS_DRAFT;
}


    /**
     * Customer payment is allowed only after an approved quotation
     * has been sent, while a positive balance remains outstanding.
     */
    public function canReceivePayment(): bool
    {
        return (bool) $this->is_external
            && in_array($this->status, [
                self::STATUS_SENT,
                self::STATUS_PARTIAL_PAID,
            ], true)
            && !$this->isExpired()
            && (float) ($this->total_amount ?? 0) > 0
            && max(
                0,
                (float) ($this->total_amount ?? 0)
                    - (float) ($this->amount_paid ?? 0)
            ) > 0;
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    public function getItemsDataAttribute(): array
{
    $items = $this->items;

    /*
     * Correctly stored JSON will already be converted to an array
     * by the Eloquent cast.
     */
    if (is_array($items)) {
        return $items;
    }

    /*
     * Old records may be double JSON-encoded.
     */
    if (is_string($items) && trim($items) !== '') {
        $decoded = json_decode($items, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }
    }

    return [];
}

    // public function getBalanceDueAttribute(): float
    // {
    //     return max(
    //         0,
    //         (float) ($this->total_amount ?? 0)
    //         - (float) ($this->amount_paid ?? 0)
    //     );
    // }

    public function getCurrencyCodeAttribute(): string
    {
        return strtoupper($this->currency ?: 'KES');
    }

    public function getFormattedSubtotalAttribute(): string
    {
        return $this->formatMoney($this->subtotal);
    }

    public function getFormattedTaxAttribute(): string
    {
        return $this->formatMoney($this->tax_amount);
    }

    public function getFormattedDiscountAttribute(): string
    {
        return $this->formatMoney($this->discount_amount);
    }

    public function getFormattedTotalAttribute(): string
    {
        return $this->formatMoney($this->total_amount);
    }

    public function getFormattedAmountPaidAttribute(): string
    {
        return $this->formatMoney($this->amount_paid);
    }

    public function getFormattedBalanceDueAttribute(): string
    {
        return $this->formatMoney($this->balance_due);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_SENT => 'Sent',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_EXPIRED => 'Expired',
            self::STATUS_PAID => 'Paid',
            self::STATUS_PARTIAL_PAID => 'Partially Paid',
            self::STATUS_CANCELLED => 'Cancelled',
            default => ucfirst(str_replace('_', ' ', $this->status ?? 'unknown')),
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'secondary',
            self::STATUS_SENT => 'info',
            self::STATUS_APPROVED => 'success',
            self::STATUS_REJECTED => 'danger',
            self::STATUS_EXPIRED => 'warning',
            self::STATUS_PAID => 'success',
            self::STATUS_PARTIAL_PAID => 'warning',
            self::STATUS_CANCELLED => 'danger',
            default => 'secondary',
        };
    }

    public function getDaysUntilExpiryAttribute(): ?int
    {
        if (!$this->valid_until) {
            return null;
        }

        return now()->startOfDay()->diffInDays(
            $this->valid_until->startOfDay(),
            false
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    public function scopeSent(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_SENT);
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    public function scopePaid(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PAID);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', [
            self::STATUS_DRAFT,
            self::STATUS_SENT,
            self::STATUS_APPROVED,
            self::STATUS_PARTIAL_PAID,
        ]);
    }

    public function scopeByCalibration(
        Builder $query,
        int $calibrationId
    ): Builder {
        return $query->where('calibration_id', $calibrationId);
    }

    public function scopeByCustomer(
        Builder $query,
        int $customerId
    ): Builder {
        return $query->where('customer_id', $customerId);
    }

    public function scopeValid(Builder $query): Builder
    {
        return $query
            ->where(function (Builder $builder): void {
                $builder
                    ->whereNull('valid_until')
                    ->orWhereDate('valid_until', '>=', today());
            })
            ->whereNotIn('status', [
                self::STATUS_EXPIRED,
                self::STATUS_CANCELLED,
                self::STATUS_REJECTED,
            ]);
    }

      /*
    |--------------------------------------------------------------------------
    | Total calculations
    |--------------------------------------------------------------------------
    */




public function calculateTotals(): array
{
    $serviceSubtotal = 0.0;
    $onsiteTotal = 0.0;

    foreach ($this->items_data as $item) {
        $quantity = max(0, (float) ($item['quantity'] ?? 1));

        $unitPrice = max(
            0,
            (float) ($item['unit_price'] ?? $item['price'] ?? 0)
        );

        $lineTotal = round($quantity * $unitPrice, 2);

        $type = (string) ($item['type'] ?? 'calibration');

        if (str_starts_with($type, 'onsite_')) {
            $onsiteTotal += $lineTotal;
        } else {
            $serviceSubtotal += $lineTotal;
        }
    }

    $serviceSubtotal = round($serviceSubtotal, 2);
    $onsiteTotal = round($onsiteTotal, 2);

    // The active quotation form uses percentage discounts.
    $discountPercentage = max(
        0,
        min(100, (float) ($this->discount_percentage ?? 0))
    );

    $discountAmount = round(
        $serviceSubtotal * $discountPercentage / 100,
        2
    );

    $taxableAmount = round(
        max(0, $serviceSubtotal - $discountAmount),
        2
    );

    // Use the approved VAT rate stored on the quotation.
    $taxRate = max(
        0,
        min(100, (float) ($this->tax_rate ?? 0))
    );

    $taxAmount = round(
        $taxableAmount * $taxRate / 100,
        2
    );

    // Onsite costs are added after VAT under the current rule.
    $totalAmount = round(
        $taxableAmount + $taxAmount + $onsiteTotal,
        2
    );

    return [
        'subtotal' => $serviceSubtotal,
        'onsite_total' => $onsiteTotal,
        'percentage_discount' => $discountAmount,
        'fixed_discount' => 0.0,
        'total_discount' => $discountAmount,
        'taxable_amount' => $taxableAmount,
        'tax_amount' => $taxAmount,
        'total' => $totalAmount,
    ];
}



 public function updateTotals(): void
{
    if (!$this->isDraft()) {
        throw new \DomainException(
            'Only draft quotations can have their totals recalculated.'
        );
    }

    $calculated = $this->calculateTotals();

    $total = $calculated['total'];
    $amountPaid = (float) ($this->amount_paid ?? 0);

    $this->forceFill([
        'subtotal' => $calculated['subtotal'],
        'discount_amount' => $calculated['total_discount'],
        'tax_amount' => $calculated['tax_amount'],
        'total_amount' => $total,
        'balance_due' => $this->is_external
            ? max(0, $total - $amountPaid)
            : 0,
    ])->save();
}

    /*
    |--------------------------------------------------------------------------
    | Status actions
    |--------------------------------------------------------------------------
    */

   
public function markAsApproved(
    int $userId,
    ?string $notes = null
): void {
    if (!$this->canBeApproved()) {
        throw new \DomainException(
            'Only valid draft quotations can be approved.'
        );
    }

    $this->update([
        'status' => self::STATUS_APPROVED,
        'approved_by' => $userId,
        'approved_at' => now(),
        'approval_notes' => $notes,
        'rejected_at' => null,
        'rejection_reason' => null,
    ]);
}

public function markAsSent(): void
{
    if (!$this->canBeSent()) {
        throw new \DomainException(
            'Only approved quotations can be sent to customers.'
        );
    }

    $this->update([
        'status' => self::STATUS_SENT,
        'sent_at' => now(),
    ]);
}


    public function markAsRejected(?string $reason = null): void
    {
        if (!$this->canBeRejected()) {
            throw new \DomainException(
                'This quotation cannot be rejected.'
            );
        }

        $this->update([
            'status' => self::STATUS_REJECTED,
            'rejected_at' => now(),
            'rejection_reason' => $reason,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Expiry checks
    |--------------------------------------------------------------------------
    */

    public function isExpiredByDate(): bool
    {
        return $this->valid_until !== null
            && $this->valid_until->endOfDay()->isPast();
    }

    public function isExpired(): bool
    {
        return $this->status === self::STATUS_EXPIRED
            || $this->isExpiredByDate();
    }

    public function isAboutToExpire(): bool
    {
        $days = $this->days_until_expiry;

        return $days !== null
            && $days >= 0
            && $days <= 7;
    }

    /*
    |--------------------------------------------------------------------------
    | Utility methods
    |--------------------------------------------------------------------------
    */

    private function formatMoney(float|int|string|null $amount): string
    {
        return $this->currency_code . ' '
            . number_format((float) ($amount ?? 0), 2);
    }
    /** All M-Pesa attempts associated with this quotation. */
    public function mpesaPayments(): HasMany
    {
        return $this->hasMany(MpesaPayment::class, 'quote_id');
    }

    /** Successfully completed M-Pesa transactions only. */
    public function successfulMpesaPayments(): HasMany
    {
        return $this->mpesaPayments()
            ->where('status', MpesaPayment::STATUS_COMPLETED);
    }

    /**
     * Record a confirmed payment against this quotation.
     *
     * The database row is locked to prevent simultaneous updates from
     * overwriting each other. Call only after the payment has been verified.
     * Do not call this for a transaction already included in amount_paid.
     * Transaction-level deduplication belongs in the payment ledger/service.
     */
    public function recordPayment(float $amount): void
    {
        if (!is_finite($amount) || $amount <= 0 || round($amount, 2) <= 0) {
            throw new \InvalidArgumentException('Payment amount must be greater than zero.');
        }

        if (abs($amount - round($amount, 2)) > 0.000001) {
            throw new \InvalidArgumentException('Payment amount cannot have more than two decimal places.');
        }

        DB::transaction(function () use ($amount): void {
            $quote = self::query()->lockForUpdate()->findOrFail($this->getKey());

            if (!$quote->canReceivePayment()) {
                throw new \DomainException(
                    'Only sent, valid external quotations with an outstanding balance can receive payments.'
                );
            }

            $totalCents = (int) round((float) $quote->total_amount * 100);
            $paidCents = (int) round((float) ($quote->amount_paid ?? 0) * 100);
            $incomingCents = (int) round($amount * 100);
            $remainingCents = max(0, $totalCents - $paidCents);

            if ($incomingCents > $remainingCents) {
                throw new \DomainException(
                    'Payment exceeds the outstanding quotation balance.'
                );
            }

            $newPaidCents = $paidCents + $incomingCents;
            $newBalanceCents = $totalCents - $newPaidCents;
            $fullyPaid = $newBalanceCents === 0;

            $quote->forceFill([
                'amount_paid' => $newPaidCents / 100,
                'balance_due' => $newBalanceCents / 100,
                'status' => $fullyPaid ? self::STATUS_PAID : self::STATUS_PARTIAL_PAID,
                'mpesa_payment_status' => $fullyPaid ? 'paid' : 'partially_paid',
                'paid_at' => $fullyPaid ? now() : null,
            ])->save();
        }, 3);

        $this->refresh();
    }
}
