<?php

namespace Tests\Unit\Stock;

use App\Models\Category;
use App\Models\Product;
use App\Models\Quantity;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $category;
    protected $warehouse;
    protected $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->category = Category::create(['title' => 'Кузовные']);
        $this->warehouse = Warehouse::create(['title' => 'Склад №1']);

        $user = User::create([
            'username' => 'testuser',
            'password' => bcrypt('password'),
        ]);
        $this->actingAs($user);

        $this->product = Product::create([
            'title' => 'Крыло',
            'category_id' => $this->category->id,
            'cost_price' => 200000,
            'markup' => 100000,
        ]);
    }

    public function test_index_returns_view()
    {
        $response = $this->get(route('stock'));

        $response->assertStatus(200);
        $response->assertViewIs('pages.stock');
        $response->assertViewHas('warehouses');
        $response->assertViewHas('categories');
        $response->assertViewHas('selectedWarehouseId');
        $response->assertViewHas('selectedCategoryId');
        $response->assertViewHas('stockItems');
    }

    public function test_index_defaults_to_first_warehouse_when_none_selected()
    {
        $response = $this->get(route('stock'));

        $response->assertStatus(200);
        $this->assertEquals($this->warehouse->id, $response->viewData('selectedWarehouseId'));
    }

    public function test_index_filters_by_selected_warehouse()
    {
        $warehouse2 = Warehouse::create(['title' => 'Склад №2']);

        Quantity::create([
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'quantity' => 10,
        ]);

        Quantity::create([
            'product_id' => $this->product->id,
            'warehouse_id' => $warehouse2->id,
            'quantity' => 20,
        ]);

        $response = $this->get(route('stock', ['warehouse' => $warehouse2->id]));

        $response->assertStatus(200);
        $stockItems = $response->viewData('stockItems');
        $this->assertCount(1, $stockItems);
        $this->assertEquals(20, $stockItems->first()->quantity);
        $this->assertEquals($warehouse2->id, $stockItems->first()->warehouse_id);
    }

    public function test_index_filters_by_category()
    {
        $category2 = Category::create(['title' => 'Двигатель']);

        $product2 = Product::create([
            'title' => 'Поршень',
            'category_id' => $category2->id,
            'cost_price' => 150000,
            'markup' => 50000,
        ]);

        Quantity::create([
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'quantity' => 10,
        ]);

        Quantity::create([
            'product_id' => $product2->id,
            'warehouse_id' => $this->warehouse->id,
            'quantity' => 5,
        ]);

        $response = $this->get(route('stock', [
            'warehouse' => $this->warehouse->id,
            'category' => $this->category->id,
        ]));

        $response->assertStatus(200);
        $stockItems = $response->viewData('stockItems');
        $this->assertCount(1, $stockItems);
        $this->assertEquals($this->product->id, $stockItems->first()->product_id);
    }

    public function test_index_returns_empty_stock_when_no_warehouses_exist()
    {
        Warehouse::query()->delete();

        $response = $this->get(route('stock'));

        $response->assertStatus(200);
        $this->assertNull($response->viewData('selectedWarehouseId'));
        $this->assertCount(0, $response->viewData('stockItems'));
    }

    public function test_update_changes_quantity()
    {
        $quantity = Quantity::create([
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'quantity' => 10,
        ]);

        $response = $this->put(route('stock.update', $quantity), [
            'quantity' => 25,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Количество обновлено');

        $this->assertDatabaseHas('quantities', [
            'id' => $quantity->id,
            'quantity' => 25,
        ]);
    }

    public function test_destroy_removes_quantity_from_stock()
    {
        $quantity = Quantity::create([
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'quantity' => 10,
        ]);

        $response = $this->delete(route('stock.destroy', $quantity));

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Товар удалён со склада');

        $this->assertDatabaseMissing('quantities', [
            'id' => $quantity->id,
        ]);
    }
}