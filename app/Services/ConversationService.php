<?php

namespace App\Services;

use App\Interfaces\ConversationRepositoryInterface;
use App\Models\Conversation;
use Illuminate\Database\Eloquent\Collection;

class ConversationService
{
    public function __construct(
        private readonly ConversationRepositoryInterface $conversationRepository,
    ) {}

    public function getUserConversations(int $userId): Collection
    {
        return $this->conversationRepository->getUserConversations($userId);
    }

    public function getConversation(int $id): Conversation
    {
        return $this->conversationRepository->findByIdOrFail($id);
    }

    public function markAsRead(Conversation $conversation, int $userId): void
    {
        $this->conversationRepository->markAsRead($conversation, $userId);
    }
}
