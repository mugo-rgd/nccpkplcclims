<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'conversation_id',
        'sender_id',
        'message',
        'type',
        'metadata',
        'is_read',
        'read_at',
          'file_path',
    'file_name',
    'file_type',
    'file_size',
    'is_read',
    ];

    protected $casts = [
        'metadata' => 'array',
        'is_read' => 'boolean',
        'read_at' => 'datetime'
    ];

    // Relationships
    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function readReceipts()
    {
        return $this->hasMany(MessageRead::class);
    }

    public function isReadBy($userId)
    {
        return $this->readReceipts()->where('user_id', $userId)->exists();
    }

    // Scopes
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    // Accessors
    public function getIsMineAttribute()
    {
        return $this->sender_id === auth()->id();
    }

    public function getTimeAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    public function getFormattedTimeAttribute()
    {
        return $this->created_at->format('H:i');
    }

    public function getFullDateTimeAttribute()
    {
        return $this->created_at->format('Y-m-d H:i:s');
    }

    // Helper Methods
    public function markAsRead($userId)
    {
        $this->update([
            'is_read' => true,
            'read_at' => now()
        ]);

        MessageRead::create([
            'message_id' => $this->id,
            'user_id' => $userId,
            'read_at' => now()
        ]);
    }
}
