<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ConversationBlockUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $conversationId;
    public int $blockerId;
    public bool $isBlocked;

    /**
     * Create a new event instance.
     */
    public function __construct(string $conversationId, int $blockerId, bool $isBlocked)
    {
        $this->conversationId = $conversationId;
        $this->blockerId = $blockerId;
        $this->isBlocked = $isBlocked;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("conversation.{$this->conversationId}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'ConversationBlockUpdated';
    }
}
