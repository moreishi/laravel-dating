<?php

namespace App\Actions;

use App\Interfaces\ConversationRepositoryInterface;
use App\Interfaces\MessageRepositoryInterface;
use App\Models\Conversation;
use App\Models\Message;

class SendMessageAction
{
    public function __construct(
        private readonly MessageRepositoryInterface $messageRepository,
        private readonly ConversationRepositoryInterface $conversationRepository,
    ) {}

    public function execute(int $conversationId, int $userId, string $content): Message
    {
        $conversation = $this->conversationRepository->findByIdOrFail($conversationId);

        return $this->messageRepository->create($conversation->id, $userId, $content);
    }
}
