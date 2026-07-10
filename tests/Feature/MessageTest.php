<?php

namespace Tests\Feature;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MessageTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_send_message_to_conversation(): void
    {
        $user = User::factory()->create();
        $recipient = User::factory()->create();

        $conversation = Conversation::create();
        $conversation->users()->attach([$user->id, $recipient->id]);

        $response = $this->actingAs($user)->post(route('messages.store', $conversation->id), [
            'content' => 'Hey, how are you?',
        ]);

        $response->assertRedirect(route('conversations.show', $conversation->id));

        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,
            'user_id' => $user->id,
            'content' => 'Hey, how are you?',
        ]);
    }

    public function test_non_participant_cannot_send_message(): void
    {
        $user = User::factory()->create();
        $participant1 = User::factory()->create();
        $participant2 = User::factory()->create();

        $conversation = Conversation::create();
        $conversation->users()->attach([$participant1->id, $participant2->id]);

        $response = $this->actingAs($user)->post(route('messages.store', $conversation->id), [
            'content' => 'Spy message!',
        ]);

        $response->assertStatus(403);
    }

    public function test_sending_message_touches_conversation_updated_at(): void
    {
        $user = User::factory()->create();
        $recipient = User::factory()->create();

        $conversation = Conversation::create();
        $conversation->users()->attach([$user->id, $recipient->id]);

        $originalUpdatedAt = $conversation->updated_at;

        sleep(1);

        $this->actingAs($user)->post(route('messages.store', $conversation->id), [
            'content' => 'New message!',
        ]);

        $this->assertNotEquals(
            $originalUpdatedAt->timestamp,
            $conversation->fresh()->updated_at->timestamp
        );
    }

    public function test_message_requires_content(): void
    {
        $user = User::factory()->create();
        $recipient = User::factory()->create();

        $conversation = Conversation::create();
        $conversation->users()->attach([$user->id, $recipient->id]);

        $response = $this->actingAs($user)->post(route('messages.store', $conversation->id), [
            'content' => '',
        ]);

        $response->assertSessionHasErrors('content');
    }
}
