<?php

namespace App\Policies;

use App\Models\Conversation;
use App\Models\User;

class ConversationPolicy
{
    public function view(User $user, Conversation $conversation): bool
    {
        return $conversation->users->contains($user);
    }

    public function create(User $user): bool
    {
        return true;
    }
}
