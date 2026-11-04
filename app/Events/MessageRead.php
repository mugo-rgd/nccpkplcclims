<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageRead implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
    public $userId;
    public $conversationId;

    public function __construct(Message $message, $userId, $conversationId)
    {
        $this->message = $message;
        $this->userId = $userId;
        $this->conversationId = $conversationId;
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
            'message_id' => $this->message->id,
            'user_id' => $this->userId,
            'conversation_id' => $this->conversationId
        ];
    }
}
