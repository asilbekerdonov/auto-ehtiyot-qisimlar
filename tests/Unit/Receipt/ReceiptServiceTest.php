<?php

namespace Tests\Unit\Receipt;

use App\Models\Product;
use App\Models\Category;
use App\Models\Warehouse;
use App\Models\Quantity;
use App\Services\ReceiptService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReceiptServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $receiptService;
    protected $category;
    protected $warehouse;

    protected function setUp(): void
    {
        parent::setUp();

        $this->receiptService = app(ReceiptService::class);
        $this->category = Category::create(['title' => 'Кузовные']);
        $this->warehouse = Warehouse::create(['title' => 'Склад №1']);
    }

    public function test_add_stock_creates_new_quantity()
    {
        $product = Product::create([
            'title' => 'Крыло',
            'category_id' => $this->category->id,
            'cost_price' => 200000,
            'markup' => 100000,
        ]);

        $result = $this->receiptService->addStock(
            $product->id,
            $this->warehouse->id,
            10
        );

        $this->assertInstanceOf(Quantity::class, $result);
        $this->assertEquals(10, $result->quantity);
        $this->assertDatabaseHas('quantities', [
            'product_id' => $product->id,
            'warehouse_id' => $this->warehouse->id,
            'quantity' => 10,
        ]);
    }

    public function test_add_stock_updates_existing_quantity()
    {
        $product = Product::create([
            'title' => 'Крыло',
            'category_id' => $this->category->id,
            'cost_price' => 200000,
            'markup' => 100000,
        ]);

        // Создаем начальный остаток
        Quantity::create([
            'product_id' => $product->id,
            'warehouse_id' => $this->warehouse->id,
            'quantity' => 10,
        ]);

        $result = $this->receiptService->addStock(
            $product->id,
            $this->warehouse->id,
            5
        );

        $this->assertEquals(15, $result->quantity);
        $this->assertDatabaseHas('quantities', [
            'product_id' => $product->id,
            'warehouse_id' => $this->warehouse->id,
            'quantity' => 15,
        ]);
    }

    public function test_get_stock_returns_quantity()
    {
        $product = Product::create([
            'title' => 'Крыло',
            'category_id' => $this->category->id,
            'cost_price' => 200000,
            'markup' => 100000,
        ]);

        Quantity::create([
            'product_id' => $product->id,
            'warehouse_id' => $this->warehouse->id,
            'quantity' => 25,
        ]);

        $stock = $this->receiptService->getStock($product->id, $this->warehouse->id);

        $this->assertEquals(25, $stock);
    }

    public function test_get_stock_returns_zero_when_no_stock()
    {
        $product = Product::create([
            'title' => 'Крыло',
            'category_id' => $this->category->id,
            'cost_price' => 200000,
            'markup' => 100000,
        ]);

        $stock = $this->receiptService->getStock($product->id, $this->warehouse->id);

        $this->assertEquals(0, $stock);
    }

    public function test_has_stock_returns_true_when_quantity_exists()
    {
        $product = Product::create([
            'title' => 'Крыло',
            'category_id' => $this->category->id,
            'cost_price' => 200000,
            'markup' => 100000,
        ]);

        Quantity::create([
            'product_id' => $product->id,
            'warehouse_id' => $this->warehouse->id,
            'quantity' => 10,
        ]);

        $hasStock = $this->receiptService->hasStock($product->id, $this->warehouse->id);

        $this->assertTrue($hasStock);
    }

    public function test_has_stock_returns_false_when_no_quantity()
    {
        $product = Product::create([
            'title' => 'Крыло',
            'category_id' => $this->category->id,
            'cost_price' => 200000,
            'markup' => 100000,
        ]);

        $hasStock = $this->receiptService->hasStock($product->id, $this->warehouse->id);

        $this->assertFalse($hasStock);
    }

    public function test_update_stock_sets_new_quantity()
    {
        $product = Product::create([
            'title' => 'Крыло',
            'category_id' => $this->category->id,
            'cost_price' => 200000,
            'markup' => 100000,
        ]);

        $result = $this->receiptService->updateStock(
            $product->id,
            $this->warehouse->id,
            50
        );

        $this->assertEquals(50, $result->quantity);
        $this->assertDatabaseHas('quantities', [
            'product_id' => $product->id,
            'warehouse_id' => $this->warehouse->id,
            'quantity' => 50,
        ]);
    }
}
