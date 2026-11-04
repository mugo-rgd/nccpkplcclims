<?php

use Illuminate\Support\Facades\Broadcast;

// Private channels for user-specific broadcasts
Broadcast::channel('user.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

// Private channels for conversation
Broadcast::channel('conversation.{conversationId}', function ($user, $conversationId) {
    $conversation = \App\Models\Conversation::find($conversationId);
    if (!$conversation) {
        return false;
    }
    return $conversation->participants()->where('user_id', $user->id)->exists();
});

// Presence channel for online users (optional)
Broadcast::channel('online-users', function ($user) {
    return ['id' => $user->id, 'name' => $user->name];
});
