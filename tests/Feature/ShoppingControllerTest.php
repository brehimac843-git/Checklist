<?php

namespace Tests\Feature;

use App\Models\ShoppingItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ShoppingControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_page_is_available_for_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $response = $this->get('/shopping/create');

        $response->assertStatus(200);
    }

    public function test_non_admin_user_cannot_access_create_page(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $this->actingAs($user);

        $response = $this->get('/shopping/create');

        $response->assertForbidden();
    }

    public function test_edit_page_is_available_for_existing_item(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $item = ShoppingItem::create([
            'name' => 'Milk',
            'quantity' => 2,
            'unit' => 'bottle',
            'category' => 'Food',
            'notes' => 'Test item',
        ]);

        $response = $this->get('/shopping/' . $item->id . '/edit');

        $response->assertStatus(200);
    }

    public function test_item_can_be_updated_and_deleted(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $item = ShoppingItem::create([
            'name' => 'Milk',
            'quantity' => 2,
            'unit' => 'bottle',
            'category' => 'Food',
            'notes' => 'Test item',
        ]);

        $this->withoutMiddleware();

        $response = $this->put('/shopping/' . $item->id, [
            'name' => 'Bread',
            'quantity' => 3,
            'unit' => 'loaf',
            'category' => 'Food',
            'notes' => 'Updated item',
        ]);

        $response->assertRedirect('/shopping');

        $this->assertDatabaseHas('shopping_items', [
            'name' => 'Bread',
            'quantity' => 3,
            'unit' => 'loaf',
            'category' => 'Food',
            'notes' => 'Updated item',
        ]);

        $this->withoutMiddleware();

        $this->delete('/shopping/' . $item->id)->assertRedirect('/shopping');

        $this->assertDatabaseMissing('shopping_items', [
            'id' => $item->id,
        ]);
    }

    public function test_item_can_upload_and_store_an_image(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        Storage::fake('public');

        $this->withoutMiddleware();

        $response = $this->post('/shopping', [
            'name' => 'Milk',
            'quantity' => 2,
            'unit' => 'bottle',
            'category' => 'Food',
            'notes' => 'Test item',
            'image' => UploadedFile::fake()->image('milk.jpg'),
        ]);

        $response->assertRedirect('/shopping');

        $item = ShoppingItem::first();

        $this->assertNotNull($item);
        $this->assertNotNull($item->image);
        Storage::disk('public')->assertExists($item->image);
    }

    public function test_user_can_add_item_to_cart_with_selected_quantity(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $this->actingAs($user);

        $item = ShoppingItem::create([
            'name' => 'Milk',
            'quantity' => 2,
            'unit' => 'bottle',
            'category' => 'Food',
            'notes' => 'Test item',
        ]);

        $this->withoutMiddleware();

        $response = $this->post('/cart/add', ['item_id' => $item->id, 'quantity' => 3]);

        $response->assertRedirect();
        $this->assertTrue(session('cart.' . $item->id) === 3);
    }

    public function test_user_can_update_cart_quantity_and_remove_item(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $this->actingAs($user);

        $item = ShoppingItem::create([
            'name' => 'Milk',
            'quantity' => 2,
            'unit' => 'bottle',
            'category' => 'Food',
            'notes' => 'Test item',
        ]);

        $this->withSession(['cart' => [$item->id => 2]]);

        $this->withSession(['_token' => 'test-token']);

        $this->post('/cart/update/' . $item->id, ['action' => 'increment', '_token' => 'test-token'])
            ->assertRedirect();
        $this->assertSame(3, session('cart.' . $item->id));

        $this->post('/cart/update/' . $item->id, ['action' => 'decrement', '_token' => 'test-token'])
            ->assertRedirect();
        $this->assertSame(2, session('cart.' . $item->id));

        $this->post('/cart/update/' . $item->id, ['action' => 'remove', '_token' => 'test-token'])
            ->assertRedirect();
        $this->assertFalse(session()->has('cart.' . $item->id));
    }

    public function test_user_cart_persists_across_logout_and_login_cycle(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $this->actingAs($user);

        $item = ShoppingItem::create([
            'name' => 'Milk',
            'quantity' => 2,
            'unit' => 'bottle',
            'category' => 'Food',
            'notes' => 'Test item',
        ]);

        $this->post('/cart/add', ['item_id' => $item->id, 'quantity' => 2]);

        $this->assertSame(2, session('cart.' . $item->id));

        Auth::logout();
        $this->app['session']->flush();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertSame(2, session('cart.' . $item->id));
        $this->assertSame(2, $user->fresh()->cart[$item->id] ?? null);
    }

    public function test_dashboard_shows_add_to_cart_action_for_regular_user(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $this->actingAs($user);

        ShoppingItem::create([
            'name' => 'Milk',
            'quantity' => 2,
            'unit' => 'bottle',
            'category' => 'Food',
            'notes' => 'Test item',
        ]);

        $response = $this->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Ajouter au panier');
    }

    public function test_dashboard_marks_items_already_in_cart_for_regular_user(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $this->actingAs($user);

        $item = ShoppingItem::create([
            'name' => 'Milk',
            'quantity' => 2,
            'unit' => 'bottle',
            'category' => 'Food',
            'notes' => 'Test item',
        ]);

        $this->withSession(['cart' => [$item->id => 2]]);

        $response = $this->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Déjà dans le panier');
        $response->assertDontSee('Ajouter au panier');
    }

    public function test_cart_page_displays_items_from_session(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $this->actingAs($user);

        $item = ShoppingItem::create([
            'name' => 'Milk',
            'quantity' => 2,
            'unit' => 'bottle',
            'category' => 'Food',
            'notes' => 'Test item',
        ]);

        $this->withSession(['cart' => [$item->id => 2]]);

        $response = $this->get('/cart');

        $response->assertStatus(200);
        $response->assertSee($item->name);
        $response->assertSee('2');
    }

    public function test_search_endpoint_returns_matching_suggestions(): void
    {
        $item = ShoppingItem::create([
            'name' => 'Milk',
            'quantity' => 2,
            'unit' => 'bottle',
            'category' => 'Food',
            'notes' => 'Test item',
        ]);

        $response = $this->getJson('/products/search?query=mil');

        $response->assertOk();
        $response->assertJsonFragment([
            'id' => $item->id,
            'name' => $item->name,
        ]);
    }

    public function test_register_page_is_available(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }
}
