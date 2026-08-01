<?php

namespace Tests\Feature;

use App\Models\ShoppingItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShoppingControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_page_is_available(): void
    {
        $response = $this->get('/shopping/create');

        $response->assertStatus(200);
    }

    public function test_edit_page_is_available_for_existing_item(): void
    {
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
        $item = ShoppingItem::create([
            'name' => 'Milk',
            'quantity' => 2,
            'unit' => 'bottle',
            'category' => 'Food',
            'notes' => 'Test item',
        ]);

        $this->put('/shopping/' . $item->id, [
            'name' => 'Bread',
            'quantity' => 3,
            'unit' => 'loaf',
            'category' => 'Food',
            'notes' => 'Updated item',
        ])->assertRedirect('/shopping');

        $this->assertDatabaseHas('shopping_items', [
            'name' => 'Bread',
            'quantity' => 3,
            'unit' => 'loaf',
            'category' => 'Food',
            'notes' => 'Updated item',
        ]);

        $this->delete('/shopping/' . $item->id)->assertRedirect('/shopping');

        $this->assertDatabaseMissing('shopping_items', [
            'id' => $item->id,
        ]);
    }

    public function test_register_page_is_available(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }
}
