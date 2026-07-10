<?php

namespace App\Interfaces;

use App\Models\Conversation;
use Illuminate\Database\Eloquent\Collection;

interface ConversationRepositoryInterface
{
    public function findById(int $id): ?Conversation;

    public function findByIdOrFail(int $id): Conversation;

    public function findBetweenUsers(int $userId, int $recipientId): ?Conversation;

    public function create(): Conversation;

    public function attachUser(Conversation $conversation, int $userId): void;

    public function getUserConversations(int $userId): Collection;

    public function markAsRead(Conversation $conversation, int $userId): void;
}
