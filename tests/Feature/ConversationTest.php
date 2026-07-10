<?php

namespace Tests\Feature;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConversationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_start_conversation_with_another_user(): void
    {
        $user = User::factory()->create();
        $recipient = User::factory()->create();

        $response = $this->actingAs($user)->post(route('conversations.store'), [
            'recipient_id' => $recipient->id,
        ]);

        $this->assertDatabaseHas('conversation_user', [
            'user_id' => $user->id,
        ]);
        $this->assertDatabaseHas('conversation_user', [
            'user_id' => $recipient->id,
        ]);

        $conversation = Conversation::first();
        $response->assertRedirect(route('conversations.show', $conversation));
    }

    public function test_starting_conversation_with_self_fails(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('conversations.store'), [
            'recipient_id' => $user->id,
        ]);

        $response->assertSessionHasErrors('recipient_id');
    }

    public function test_reusing_existing_conversation_does_not_create_duplicate(): void
    {
        $user = User::factory()->create();
        $recipient = User::factory()->create();

        $this->actingAs($user)->post(route('conversations.store'), [
            'recipient_id' => $recipient->id,
        ]);

        $this->actingAs($recipient)->post(route('conversations.store'), [
            'recipient_id' => $user->id,
        ]);

        $this->assertEquals(1, Conversation::count());
    }

    public function test_user_can_view_conversation_list(): void
    {
        $user = User::factory()->create();
        $recipient = User::factory()->create();

        $conversation = Conversation::create();
        $conversation->users()->attach([$user->id, $recipient->id]);

        $response = $this->actingAs($user)->get(route('conversations.index'));

        $response->assertStatus(200);
        $response->assertSee($recipient->name);
    }

    public function test_user_can_view_conversation_thread(): void
    {
        $user = User::factory()->create();
        $recipient = User::factory()->create();

        $conversation = Conversation::create();
        $conversation->users()->attach([$user->id, $recipient->id]);

        $message = $conversation->messages()->create([
            'user_id' => $recipient->id,
            'content' => 'Hello there!',
        ]);

        $response = $this->actingAs($user)->get(route('conversations.show', $conversation->id));

        $response->assertStatus(200);
        $response->assertSee('Hello there!');
    }

    public function test_non_participant_cannot_view_conversation(): void
    {
        $user = User::factory()->create();
        $participant1 = User::factory()->create();
        $participant2 = User::factory()->create();

        $conversation = Conversation::create();
        $conversation->users()->attach([$participant1->id, $participant2->id]);

        $response = $this->actingAs($user)->get(route('conversations.show', $conversation->id));

        $response->assertStatus(403);
    }
}
