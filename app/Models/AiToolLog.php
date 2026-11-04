<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiToolLog extends Model
{
    protected $fillable = [
        'conversation_id',
        'user_id',
        'tool_name',
        'arguments',
        'result',
        'successful',
        'error_message',
        'ip_address',
        'duration_ms',
'result_size_bytes',
    ];

    protected $casts = [
        'arguments' => 'array',
        'result' => 'array',
        'successful' => 'boolean',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(
            AiConversation::class,
            'conversation_id'
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
