<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ItemApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Category::factory()->create();
    }

    public function test_can_list_items(): void
    {
        Item::factory()->count(3)->create();

        $response = $this->getJson('/api/items');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_can_get_single_item(): void
    {
        $item = Item::factory()->create();

        $response = $this->getJson("/api/items/{$item->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $item->id)
            ->assertJsonPath('data.name', $item->name);
    }

    public function test_can_create_item(): void
    {
        $category = Category::first();

        $data = [
            'name' => 'Test Product',
            'sku' => 'SKU-0001',
            'barcode' => '1234567890123',
            'category_id' => $category->id,
            'cost_price' => 100.00,
            'retail_price' => 150.00,
        ];

        $response = $this->postJson('/api/items', $data);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Test Product');

        $this->assertDatabaseHas('items', [
            'name' => 'Test Product',
            'sku' => 'SKU-0001',
        ]);
    }

    public function test_can_update_item(): void
    {
        $item = Item::factory()->create();

        $response = $this->putJson("/api/items/{$item->id}", [
            'name' => 'Updated Name',
            'retail_price' => 200.00,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Updated Name');

        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'name' => 'Updated Name',
        ]);
    }

    public function test_can_delete_item(): void
    {
        $item = Item::factory()->create();

        $response = $this->deleteJson("/api/items/{$item->id}");

        $response->assertStatus(200);
    }

    public function test_cannot_create_item_with_duplicate_sku(): void
    {
        Item::factory()->create(['sku' => 'DUPLICATE']);

        $response = $this->postJson('/api/items', [
            'name' => 'New Item',
            'sku' => 'DUPLICATE',
            'category_id' => Category::first()->id,
            'cost_price' => 100,
            'retail_price' => 150,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['sku']);
    }

    public function test_returns_404_for_nonexistent_item(): void
    {
        $response = $this->getJson('/api/items/99999');

        $response->assertStatus(404);
    }

    public function test_can_filter_items_by_category(): void
    {
        $category1 = Category::factory()->create();
        $category2 = Category::factory()->create();

        Item::factory()->count(2)->create(['category_id' => $category1->id]);
        Item::factory()->count(3)->create(['category_id' => $category2->id]);

        $response = $this->getJson("/api/items?category_id={$category1->id}");

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }
}
