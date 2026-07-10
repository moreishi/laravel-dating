<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_browse_profiles_shows_all_users_except_self(): void
    {
        $user = User::factory()->create();
        $others = User::factory(3)->create();

        $response = $this->actingAs($user)->get(route('profiles.index'));

        $response->assertStatus(200);
        $response->assertSee($others[0]->name);
        $response->assertSee($others[1]->name);
        $response->assertSee($others[2]->name);
    }

    public function test_browse_profiles_requires_authentication(): void
    {
        $response = $this->get(route('profiles.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_show_profile_displays_user_details(): void
    {
        $user = User::factory()->create();
        $profile = User::factory()->create([
            'name' => 'Jane Doe',
            'age' => 28,
            'bio' => 'Looking for meaningful connections.',
            'gender' => 'female',
        ]);

        $response = $this->actingAs($user)->get(route('profiles.show', $profile->id));

        $response->assertStatus(200);
        $response->assertSee('Jane Doe');
        $response->assertSee('28');
        $response->assertSee('Looking for meaningful connections.');
        $response->assertSee('Female');
    }

    public function test_edit_profile_page_displays_current_data(): void
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'age' => 25,
            'bio' => 'My bio',
            'gender' => 'male',
        ]);

        $response = $this->actingAs($user)->get(route('profiles.edit'));

        $response->assertStatus(200);
        $response->assertSee('Test User');
        $response->assertSee('25');
        $response->assertSee('My bio');
    }

    public function test_update_profile_saves_changes(): void
    {
        $user = User::factory()->create([
            'name' => 'Original Name',
            'age' => 20,
        ]);

        $response = $this->actingAs($user)->put(route('profiles.update'), [
            'name' => 'Updated Name',
            'age' => 30,
            'bio' => 'Updated bio',
            'gender' => 'female',
        ]);

        $response->assertRedirect(route('profiles.edit'));

        $user->refresh();

        $this->assertEquals('Updated Name', $user->name);
        $this->assertEquals(30, $user->age);
        $this->assertEquals('Updated bio', $user->bio);
        $this->assertEquals('female', $user->gender);
    }

    public function test_update_profile_requires_valid_age(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->put(route('profiles.update'), [
            'name' => 'Test',
            'age' => 15,
        ]);

        $response->assertSessionHasErrors('age');
    }
}
