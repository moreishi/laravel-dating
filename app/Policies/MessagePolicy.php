<?php

namespace App\Policies;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;

class MessagePolicy
{
    public function create(User $user, Conversation $conversation): bool
    {
        return $conversation->users()
            ->where('user_id', $user->id)
            ->exists();
    }

    public function view(User $user, Message $message): bool
    {
        return $message->conversation->users()
            ->where('user_id', $user->id)
            ->exists();
    }
}
