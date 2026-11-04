<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AiConversation extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'status',
        'openai_response_id',
    ];

    protected $casts = [
        'user_id' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'user_id'
        );
    }

    public function messages(): HasMany
    {
        return $this->hasMany(
            AiMessage::class,
            'conversation_id'
        );
    }

    public function toolLogs(): HasMany
    {
        return $this->hasMany(
            AiToolLog::class,
            'conversation_id'
        );
    }

    public function scopeOwnedBy(
        Builder $query,
        int $userId
    ): Builder {
        return $query->where(
            'user_id',
            $userId
        );
    }

    public function scopeActive(
        Builder $query
    ): Builder {
        return $query->where(
            'status',
            'active'
        );
    }

    public function belongsToUser(
        User $user
    ): bool {
        return (int) $this->user_id
            === (int) $user->id;
    }
}
