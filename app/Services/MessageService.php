<?php

namespace App\Services;

use App\Interfaces\MessageRepositoryInterface;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Database\Eloquent\Collection;

class MessageService
{
    public function __construct(
        private readonly MessageRepositoryInterface $messageRepository,
    ) {}

    public function getMessages(Conversation $conversation): Collection
    {
        return $this->messageRepository->findByConversation($conversation);
    }

    public function sendMessage(int $conversationId, int $userId, string $content): Message
    {
        return $this->messageRepository->create($conversationId, $userId, $content);
    }
}
