<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SystemDocumentAcknowledgement extends Model
{
    protected $fillable = [
        'system_document_id',
        'user_id',
        'document_version',
        'acknowledged_at',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'acknowledged_at' => 'datetime',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(
            SystemDocument::class,
            'system_document_id'
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'user_id'
        );
    }
}
