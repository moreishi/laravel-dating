<?php

namespace App\Interfaces;

use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Database\Eloquent\Collection;

interface MessageRepositoryInterface
{
    public function findByConversation(Conversation $conversation): Collection;

    public function create(int $conversationId, int $userId, string $content): Message;
}
