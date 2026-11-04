<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SystemDocumentReview extends Model
{
    protected $fillable = [
        'system_document_id',
        'document_version',
        'review_type',
        'outcome',
        'review_notes',
        'recommended_changes',
        'reviewed_by',
        'reviewed_at',
        'next_review_date',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
        'next_review_date' => 'date',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(
            SystemDocument::class,
            'system_document_id'
        );
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'reviewed_by'
        );
    }
}