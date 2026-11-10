<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentTransaction extends Model
{
    public const PENDING = 'pending';
    public const CONFIRMED = 'confirmed';
    public const REJECTED = 'rejected';

    protected $fillable = [
        'quote_id', 'mpesa_payment_id', 'method', 'status', 'amount', 'currency',
        'reference', 'receipt_path', 'clearance_path', 'payment_date', 'notes',
        'rejection_reason', 'recorded_by', 'confirmed_by', 'confirmed_at', 'rejected_at',
    ];
    protected $casts = [
        'amount' => 'decimal:2', 'payment_date' => 'date',
        'confirmed_at' => 'datetime', 'rejected_at' => 'datetime',
    ];
    public function quote(): BelongsTo { return $this->belongsTo(Quote::class); }
    public function mpesaPayment(): BelongsTo { return $this->belongsTo(MpesaPayment::class); }
    public function recorder(): BelongsTo { return $this->belongsTo(User::class, 'recorded_by'); }
    public function confirmer(): BelongsTo { return $this->belongsTo(User::class, 'confirmed_by'); }
}
