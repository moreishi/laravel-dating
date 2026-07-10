<?php

namespace App\Repositories;

use App\Interfaces\MessageRepositoryInterface;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Database\Eloquent\Collection;

class MessageRepository implements MessageRepositoryInterface
{
    public function findByConversation(Conversation $conversation): Collection
    {
        return $conversation->messages()->with('user')->oldest()->get();
    }

    public function create(int $conversationId, int $userId, string $content): Message
    {
        return Message::create([
            'conversation_id' => $conversationId,
            'user_id' => $userId,
            'content' => $content,
        ]);
    }
}
