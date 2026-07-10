<?php

namespace App\Models;

use Database\Factories\ConversationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
{
    /** @use HasFactory<ConversationFactory> */
    use HasFactory;

    protected $guarded = [];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('last_read_at')
            ->withTimestamps();
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function otherUser(User $user): ?User
    {
        return $this->users->first(fn (User $u) => $u->id !== $user->id);
    }

    public function hasUnreadMessagesFor(int $userId): bool
    {
        $pivot = $this->users()->where('user_id', $userId)->first()?->pivot;

        if (! $pivot || ! $pivot->last_read_at) {
            return $this->messages()->exists();
        }

        return $this->messages()
            ->where('created_at', '>', $pivot->last_read_at)
            ->where('user_id', '!=', $userId)
            ->exists();
    }
}
