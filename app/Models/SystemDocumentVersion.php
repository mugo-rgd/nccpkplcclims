<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SystemDocumentVersion extends Model
{
    protected $fillable = [
        'system_document_id',
        'version',
        'change_summary',
        'revision_notes',
        'file_path',
        'original_filename',
        'stored_filename',
        'mime_type',
        'file_size',
        'file_extension',
        'status',
        'uploaded_by',
        'approved_by',
        'approved_at',
        'effective_date',
        'review_date',
        'is_current',
    ];

    protected $casts = [
        'approved_at' => 'datetime',

        'effective_date' => 'date',
        'review_date' => 'date',

        'is_current' => 'boolean',

        'file_size' => 'integer',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(
            SystemDocument::class,
            'system_document_id'
        );
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'uploaded_by'
        );
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'approved_by'
        );
    }
}
