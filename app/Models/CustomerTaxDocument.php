<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CustomerTaxDocument extends Model
{
    use HasFactory;

    protected $table = 'customer_tax_documents';

    protected $fillable = [
        'customer_id',
        'document_type',
        'file_path',
        'original_filename',
        'mime_type',
        'file_size',
        'verification_status',
        'uploaded_by',
        'verified_by',
        'verified_at',
        'verification_notes',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'verified_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function uploadedBy()
    {
        return $this->belongsTo(
            User::class,
            'uploaded_by'
        );
    }

    public function verifiedBy()
    {
        return $this->belongsTo(
            User::class,
            'verified_by'
        );
    }

    public function scopePending($query)
    {
        return $query->where(
            'verification_status',
            'pending'
        );
    }

    public function scopeApproved($query)
    {
        return $query->where(
            'verification_status',
            'approved'
        );
    }
}
