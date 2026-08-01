<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_login_redirects_to_admin_dashboard(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $this->get('/admin/login');

        $response = $this->from('/admin/login')->post('/admin/login', [
            'email' => 'admin@example.com',
            'password' => 'password',
            '_token' => csrf_token(),
        ]);

        $response->assertRedirect('/admin/dashboard');
        $this->assertAuthenticatedAs($admin);
    }

    public function test_user_login_redirects_to_user_dashboard(): void
    {
        $user = User::factory()->create([
            'name' => 'Regular User',
            'email' => 'user@example.com',
            'password' => bcrypt('password'),
            'role' => 'user',
        ]);

        $this->get('/login');

        $response = $this->from('/login')->post('/login', [
            'email' => 'user@example.com',
            'password' => 'password',
            '_token' => csrf_token(),
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_inactive_admin_cannot_login(): void
    {
        $admin = User::factory()->create([
            'name' => 'Inactive Admin',
            'email' => 'inactive-admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_active' => false,
        ]);

        $response = $this->from('/admin/login')->post('/admin/login', [
            'email' => 'inactive-admin@example.com',
            'password' => 'password',
            '_token' => csrf_token(),
        ]);

        $response->assertRedirect('/admin/login');
        $this->assertGuest();
    }

    public function test_inactive_user_cannot_login(): void
    {
        User::factory()->create([
            'name' => 'Inactive User',
            'email' => 'inactive-user@example.com',
            'password' => bcrypt('password'),
            'role' => 'user',
            'is_active' => false,
        ]);

        $response = $this->from('/login')->post('/login', [
            'email' => 'inactive-user@example.com',
            'password' => 'password',
            '_token' => csrf_token(),
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors(['email' => 'Votre compte est désactivé.']);
        $this->assertGuest();
    }

    public function test_invalid_user_credentials_show_generic_error(): void
    {
        User::factory()->create([
            'name' => 'Active User',
            'email' => 'active-user@example.com',
            'password' => bcrypt('password'),
            'role' => 'user',
            'is_active' => true,
        ]);

        $response = $this->from('/login')->post('/login', [
            'email' => 'active-user@example.com',
            'password' => 'wrong-password',
            '_token' => csrf_token(),
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors(['email' => 'Email ou mot de passe incorrect.']);
        $this->assertGuest();
    }
}
