<?php

namespace Database\Seeders;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'age' => 28,
            'bio' => 'Looking for interesting conversations and new connections. Love hiking, reading, and good coffee!',
            'gender' => 'female',
        ]);

        $users = User::factory(10)->create();

        User::factory()->create([
            'name' => 'Alice',
            'email' => 'alice@example.com',
            'age' => 25,
            'bio' => 'Artist and photographer. I spend my weekends exploring new places.',
            'gender' => 'female',
        ]);

        User::factory()->create([
            'name' => 'Bob',
            'email' => 'bob@example.com',
            'age' => 32,
            'bio' => 'Software engineer by day, musician by night. Always up for a good chat!',
            'gender' => 'male',
        ]);

        User::factory()->create([
            'name' => 'Charlie',
            'email' => 'charlie@example.com',
            'age' => 29,
            'bio' => 'Foodie and world traveler. 15 countries and counting!',
            'gender' => 'male',
        ]);

        $users->each(function (User $user) {
            $conversation = Conversation::create();
            $conversation->users()->attach([1, $user->id]);

            Message::create([
                'conversation_id' => $conversation->id,
                'user_id' => $user->id,
                'content' => fake()->sentence(),
            ]);
        });
    }
}
