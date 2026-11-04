<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserTyping implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $userId;
    public $conversationId;
    public $isTyping;

    public function __construct($userId, $conversationId, $isTyping = true)
    {
        $this->userId = $userId;
        $this->conversationId = $conversationId;
        $this->isTyping = $isTyping;
    }

    public function broadcastOn()
    {
        return [
            new PrivateChannel('conversation.' . $this->conversationId)
        ];
    }

    public function broadcastWith()
    {
        return [
            'user_id' => $this->userId,
            'conversation_id' => $this->conversationId,
            'is_typing' => $this->isTyping
        ];
    }
}
