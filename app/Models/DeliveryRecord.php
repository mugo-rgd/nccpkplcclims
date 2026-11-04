<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryRecord extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'customer_request_id',
        'job_card_id',
        'delivery_number',
        'type',
        'method',
        'from_address',
        'to_address',
        'contact_person',
        'contact_phone',
        'scheduled_at',
        'dispatched_at',
        'delivered_at',
        'collected_at',
        'items',
        'condition_notes',
        'handling_instructions',
        'sender_signature_path',
        'receiver_signature_path',
        'sender_name',
        'receiver_name',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'items' => 'array',
        'scheduled_at' => 'datetime',
        'dispatched_at' => 'datetime',
        'delivered_at' => 'datetime',
        'collected_at' => 'datetime',
    ];

    public function customerRequest(): BelongsTo
    {
        return $this->belongsTo(CustomerRequest::class);
    }

    public function jobCard(): BelongsTo
    {
        return $this->belongsTo(JobCard::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function generateDeliveryNumber(): string
    {
        $sequence = Sequence::where('name', 'delivery')->first();
        if (!$sequence) {
            Sequence::create([
                'name' => 'delivery',
                'last_number' => 0,
                'prefix' => 'DEL',
                'padding' => 5,
            ]);
            $sequence = Sequence::where('name', 'delivery')->first();
        }
        $next = $sequence->last_number + 1;
        $sequence->update(['last_number' => $next]);

        return $sequence->prefix . str_pad($next, $sequence->padding, '0', STR_PAD_LEFT);
    }
}
