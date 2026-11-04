<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MpesaPayment extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_INITIATED = 'initiated';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_TIMEOUT = 'timeout';

    protected $fillable = [
    'quote_id',
    'calibration_id',
    'customer_id',
    'payment_reference',
    'phone_number',
    'amount',
    'currency',
    'status',
    'merchant_request_id',
    'checkout_request_id',
    'response_code',
    'response_description',
    'customer_message',
    'result_code',
    'result_description',
    'mpesa_receipt_number',
    'paid_amount',
    'transaction_phone',
    'transaction_date',
    'initiated_at',
    'completed_at',
    'failed_at',
    'cancelled_at',
    'request_payload',
    'initial_response',
    'callback_payload',
    'ip_address',
    'user_agent',
];

    protected $casts = [
    'amount' => 'decimal:2',
    'paid_amount' => 'decimal:2',
    'result_code' => 'integer',
    'transaction_date' => 'datetime',
    'initiated_at' => 'datetime',
    'completed_at' => 'datetime',
    'failed_at' => 'datetime',
    'cancelled_at' => 'datetime',
    'request_payload' => 'array',
    'initial_response' => 'array',
    'callback_payload' => 'array',
];

    public function quote(): BelongsTo
    {
        return $this->belongsTo(
            Quote::class,
            'quote_id'
        );
    }

    public function calibration(): BelongsTo
    {
        return $this->belongsTo(
            Calibration::class,
            'calibration_id'
        );
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(
            Customer::class,
            'customer_id'
        );
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }



public function mpesaPayments(): HasMany
{
    return $this->hasMany(
        MpesaPayment::class,
        'quote_id'
    );
}

public function successfulMpesaPayments(): HasMany
{
    return $this->hasMany(
        MpesaPayment::class,
        'quote_id'
    )->where(
        'status',
        MpesaPayment::STATUS_COMPLETED
    );
}
}
