<?php

namespace App\Actions;

use App\Exceptions\CannotMessageSelfException;
use App\Interfaces\ConversationRepositoryInterface;
use App\Models\Conversation;

class StartConversationAction
{
    public function __construct(
        private readonly ConversationRepositoryInterface $conversationRepository,
    ) {}

    public function execute(int $userId, int $recipientId): Conversation
    {
        if ($userId === $recipientId) {
            throw new CannotMessageSelfException;
        }

        $existing = $this->conversationRepository->findBetweenUsers($userId, $recipientId);

        if ($existing) {
            return $existing;
        }

        $conversation = $this->conversationRepository->create();

        $this->conversationRepository->attachUser($conversation, $userId);
        $this->conversationRepository->attachUser($conversation, $recipientId);

        return $conversation->fresh()->load('users', 'messages.user');
    }
}
