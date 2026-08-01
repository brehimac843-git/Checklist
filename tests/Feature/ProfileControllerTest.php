<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_profile_page(): void
    {
        $user = User::factory()->create(['name' => 'Alice', 'role' => 'user']);
        $this->actingAs($user);

        $response = $this->get('/profile');

        $response->assertStatus(200);
        $response->assertSee('Profil');
        $response->assertSee('Alice');
    }

    public function test_user_can_update_profile_without_search_history(): void
    {
        Storage::fake('public');

        $user = User::factory()->create(['name' => 'Alice', 'role' => 'user']);
        $this->actingAs($user);

        $response = $this->get('/profile');
        $response->assertStatus(200);
        $response->assertDontSee('Historique de recherches');

        $updateResponse = $this->post('/profile/update', [
            'first_name' => 'Alice',
            'last_name' => 'Updated',
            'password' => 'newpassword123',
            'profile_photo' => UploadedFile::fake()->image('avatar.jpg'),
        ]);

        $updateResponse->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('Alice', $user->first_name);
        $this->assertSame('Updated', $user->last_name);
        $this->assertTrue(Hash::check('newpassword123', $user->password));

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'first_name' => 'Alice',
            'last_name' => 'Updated',
        ]);

        $this->getJson('/products/search?query=milk');

        $this->assertDatabaseMissing('user_search_histories', [
            'user_id' => $user->id,
            'query' => 'milk',
        ]);
    }
}
