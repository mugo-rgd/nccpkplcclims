<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Permission\Models\Role;

class SystemDocumentAudience extends Model
{
    protected $fillable = [
        'system_document_id',
        'role_id',
        'user_id',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(
            SystemDocument::class,
            'system_document_id'
        );
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(
            Role::class,
            'role_id'
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