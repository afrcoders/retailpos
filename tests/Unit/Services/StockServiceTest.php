<?php

namespace Tests\Unit\Services;

use App\Models\Item;
use App\Models\Stock;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockServiceTest extends TestCase
{
    use RefreshDatabase;

    protected StockService $stockService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->stockService = new StockService();
    }

    public function test_can_adjust_stock_for_item(): void
    {
        $item = Item::factory()->create();
        Stock::create(['item_id' => $item->id, 'quantity_on_hand' => 10]);

        $stock = $this->stockService->adjustStock($item, 5, 'purchase');

        $this->assertEquals(15, $stock->quantity_on_hand);
    }

    public function test_creates_stock_record_if_not_exists(): void
    {
        $item = Item::factory()->create();

        $stock = $this->stockService->adjustStock($item, 10, 'purchase');

        $this->assertNotNull($stock);
        $this->assertEquals(10, $stock->quantity_on_hand);
    }

    public function test_creates_transaction_record_on_adjustment(): void
    {
        $item = Item::factory()->create();
        Stock::create(['item_id' => $item->id, 'quantity_on_hand' => 10]);

        $this->stockService->adjustStock($item, 5, 'purchase');

        $this->assertDatabaseHas('stock_transactions', [
            'item_id' => $item->id,
            'transaction_type' => 'purchase',
            'quantity' => 5,
            'quantity_before' => 10,
            'quantity_after' => 15,
        ]);
    }

    public function test_can_record_purchase(): void
    {
        $item = Item::factory()->create();
        Stock::create(['item_id' => $item->id, 'quantity_on_hand' => 0]);

        $stock = $this->stockService->recordPurchase($item, 50);

        $this->assertEquals(50, $stock->quantity_on_hand);
    }

    public function test_can_record_sale(): void
    {
        $item = Item::factory()->create();
        $stock = Stock::create(['item_id' => $item->id, 'quantity_on_hand' => 20]);

        $result = $this->stockService->recordSale($item, 5);

        $this->assertEquals(15, $result->quantity_on_hand);
    }

    public function test_sale_throws_exception_on_insufficient_stock(): void
    {
        $item = Item::factory()->create();
        Stock::create(['item_id' => $item->id, 'quantity_on_hand' => 5]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Insufficient stock");

        $this->stockService->recordSale($item, 10);
    }

    public function test_sale_deducts_correct_amount(): void
    {
        $item = Item::factory()->create();
        Stock::create(['item_id' => $item->id, 'quantity_on_hand' => 100]);

        $this->stockService->recordSale($item, 25);

        $this->assertDatabaseHas('stock_transactions', [
            'item_id' => $item->id,
            'quantity' => -25, // Negative for sale
        ]);
    }
}
