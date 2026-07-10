<?php

namespace App\Repositories;

use App\Interfaces\ConversationRepositoryInterface;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ConversationRepository implements ConversationRepositoryInterface
{
    public function findById(int $id): ?Conversation
    {
        return Conversation::with('users', 'messages.user')->find($id);
    }

    public function findByIdOrFail(int $id): Conversation
    {
        return Conversation::with('users', 'messages.user')->findOrFail($id);
    }

    public function findBetweenUsers(int $userId, int $recipientId): ?Conversation
    {
        $conversations = Conversation::whereHas('users', fn ($q) => $q->where('user_id', $userId))
            ->whereHas('users', fn ($q) => $q->where('user_id', $recipientId))
            ->get();

        return $conversations->first();
    }

    public function create(): Conversation
    {
        return Conversation::create();
    }

    public function attachUser(Conversation $conversation, int $userId): void
    {
        $conversation->users()->attach($userId);
    }

    public function getUserConversations(int $userId): Collection
    {
        return User::findOrFail($userId)
            ->conversations()
            ->with(['users', 'messages' => fn ($q) => $q->latest()->limit(1)])
            ->orderBy('updated_at', 'desc')
            ->get();
    }

    public function markAsRead(Conversation $conversation, int $userId): void
    {
        DB::table('conversation_user')
            ->where('conversation_id', $conversation->id)
            ->where('user_id', $userId)
            ->update(['last_read_at' => now()]);
    }
}
