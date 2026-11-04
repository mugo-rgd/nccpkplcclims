<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Conversation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'type',
        'slug',
        'created_by',
        'is_active',
        'last_message_at'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_message_at' => 'datetime'
    ];

    // Relationships
    public function participants()
    {
        return $this->hasMany(ConversationParticipant::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class)->orderBy('created_at', 'desc');
    }

    public function latestMessage()
    {
        return $this->hasOne(Message::class)->latest();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePrivate($query)
    {
        return $query->where('type', 'private');
    }

    public function scopeGroup($query)
    {
        return $query->where('type', 'group');
    }

    // Accessors
    public function getDisplayNameAttribute()
    {
        if ($this->type === 'private') {
            $otherParticipant = $this->participants()
                ->where('user_id', '!=', auth()->id())
                ->first();
            return $otherParticipant?->user?->name ?? 'Unknown';
        }
        return $this->name ?? 'Group Chat';
    }

    public function getAvatarAttribute()
    {
        if ($this->type === 'private') {
            $otherParticipant = $this->participants()
                ->where('user_id', '!=', auth()->id())
                ->first();
            return $otherParticipant?->user?->avatar ?? null;
        }
        return null;
    }

    // Helper Methods
    public function isPrivate()
    {
        return $this->type === 'private';
    }

    public function isGroup()
    {
        return $this->type === 'group';
    }

    public static function createPrivateConversation($userId1, $userId2)
    {
        // Check if conversation already exists
        $existing = static::where('type', 'private')
            ->whereHas('participants', function($q) use ($userId1, $userId2) {
                $q->where('user_id', $userId1);
            })
            ->whereHas('participants', function($q) use ($userId1, $userId2) {
                $q->where('user_id', $userId2);
            })
            ->first();

        if ($existing) {
            return $existing;
        }

        $conversation = static::create([
            'type' => 'private',
            'slug' => Str::slug('private-' . $userId1 . '-' . $userId2 . '-' . time()),
            'created_by' => $userId1,
            'is_active' => true
        ]);

        // Add participants
        ConversationParticipant::create([
            'conversation_id' => $conversation->id,
            'user_id' => $userId1,
            'is_admin' => true,
            'joined_at' => now()
        ]);

        ConversationParticipant::create([
            'conversation_id' => $conversation->id,
            'user_id' => $userId2,
            'is_admin' => false,
            'joined_at' => now()
        ]);

        return $conversation;
    }

    public function markAsRead($userId)
    {
        $this->participants()
            ->where('user_id', $userId)
            ->update(['last_read_at' => now()]);
    }

    public function getUnreadCount($userId)
    {
        $participant = $this->participants()->where('user_id', $userId)->first();
        if (!$participant) {
            return 0;
        }

        $lastRead = $participant->last_read_at ?? $this->created_at;

        return $this->messages()
            ->where('sender_id', '!=', $userId)
            ->where('created_at', '>', $lastRead)
            ->where('is_read', false)
            ->count();
    }
}
