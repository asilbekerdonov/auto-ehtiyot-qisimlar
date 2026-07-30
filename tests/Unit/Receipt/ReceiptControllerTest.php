<?php

namespace Tests\Unit\Receipt;

use App\Models\Car;
use App\Models\Category;
use App\Models\Product;
use App\Models\Quantity;
use App\Models\Warehouse;
use App\Services\ReceiptService;
use App\Http\Requests\Receipt\AddStockRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReceiptControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $receiptService;
    protected $category;
    protected $car;
    protected $warehouse;

    protected function setUp(): void
    {
        parent::setUp();

        $this->receiptService = app(ReceiptService::class);
        $this->category = Category::create(['title' => 'Кузовные']);
        $this->car = Car::create(['title' => 'Cobalt']);
        $this->warehouse = Warehouse::create(['title' => 'Склад №1']);
    }

    public function test_select_car_returns_view()
    {
        $response = $this->actingAsUser()->get(route('receipts.cars'));

        $response->assertStatus(200);
        $response->assertViewIs('pages.receipts-cars');
        $response->assertViewHas('cars');
    }

    public function test_car_parts_returns_view_with_products()
    {
        $product = Product::create([
            'title' => 'Крыло',
            'category_id' => $this->category->id,
            'car_id' => $this->car->id,
            'cost_price' => 200000,
            'markup' => 100000,
        ]);

        Quantity::create([
            'product_id' => $product->id,
            'warehouse_id' => $this->warehouse->id,
            'quantity' => 10,
        ]);

        $response = $this->actingAsUser()->get(route('receipts.parts', $this->car));

        $response->assertStatus(200);
        $response->assertViewIs('pages.receipts-parts');
        $response->assertViewHas('car', $this->car);
        $response->assertViewHas('products');
        $response->assertViewHas('warehouses');
        $response->assertViewHas('categories');
    }

    public function test_car_parts_filters_by_category()
    {
        $category2 = Category::create(['title' => 'Двигатель']);

        $product1 = Product::create([
            'title' => 'Крыло',
            'category_id' => $this->category->id,
            'car_id' => $this->car->id,
            'cost_price' => 200000,
            'markup' => 100000,
        ]);

        $product2 = Product::create([
            'title' => 'Поршень',
            'category_id' => $category2->id,
            'car_id' => $this->car->id,
            'cost_price' => 150000,
            'markup' => 50000,
        ]);

        $response = $this->actingAsUser()->get(route('receipts.parts', [
            'car' => $this->car,
            'category' => $this->category->id
        ]));

        $response->assertStatus(200);
        $products = $response->viewData('products');
        $this->assertCount(1, $products);
        $this->assertEquals('Крыло', $products->first()->title);
    }

    public function test_add_stock_creates_new_quantity()
    {
        $product = Product::create([
            'title' => 'Крыло',
            'category_id' => $this->category->id,
            'car_id' => $this->car->id,
            'cost_price' => 200000,
            'markup' => 100000,
        ]);

        $data = [
            'product_id' => $product->id,
            'warehouse_id' => $this->warehouse->id,
            'quantity' => 15,
        ];

        $response = $this->actingAsUser()->post(route('receipts.add'), $data);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Поступление добавлено: +15 шт');

        $this->assertDatabaseHas('quantities', [
            'product_id' => $product->id,
            'warehouse_id' => $this->warehouse->id,
            'quantity' => 15,
        ]);
    }

    public function test_add_stock_updates_existing_quantity()
    {
        $product = Product::create([
            'title' => 'Крыло',
            'category_id' => $this->category->id,
            'car_id' => $this->car->id,
            'cost_price' => 200000,
            'markup' => 100000,
        ]);

        // Создаем начальный остаток
        Quantity::create([
            'product_id' => $product->id,
            'warehouse_id' => $this->warehouse->id,
            'quantity' => 10,
        ]);

        $data = [
            'product_id' => $product->id,
            'warehouse_id' => $this->warehouse->id,
            'quantity' => 5,
        ];

        $response = $this->actingAsUser()->post(route('receipts.add'), $data);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Поступление добавлено: +5 шт');

        $this->assertDatabaseHas('quantities', [
            'product_id' => $product->id,
            'warehouse_id' => $this->warehouse->id,
            'quantity' => 15, // 10 + 5
        ]);
    }

    public function test_add_stock_fails_with_invalid_product()
    {
        $data = [
            'product_id' => 999,
            'warehouse_id' => $this->warehouse->id,
            'quantity' => 10,
        ];

        $response = $this->actingAsUser()->post(route('receipts.add'), $data);

        $response->assertSessionHasErrors(['product_id']);
    }

    public function test_add_stock_fails_with_invalid_warehouse()
    {
        $product = Product::create([
            'title' => 'Крыло',
            'category_id' => $this->category->id,
            'car_id' => $this->car->id,
            'cost_price' => 200000,
            'markup' => 100000,
        ]);

        $data = [
            'product_id' => $product->id,
            'warehouse_id' => 999,
            'quantity' => 10,
        ];

        $response = $this->actingAsUser()->post(route('receipts.add'), $data);

        $response->assertSessionHasErrors(['warehouse_id']);
    }

    public function test_add_stock_fails_with_zero_quantity()
    {
        $product = Product::create([
            'title' => 'Крыло',
            'category_id' => $this->category->id,
            'car_id' => $this->car->id,
            'cost_price' => 200000,
            'markup' => 100000,
        ]);

        $data = [
            'product_id' => $product->id,
            'warehouse_id' => $this->warehouse->id,
            'quantity' => 0,
        ];

        $response = $this->actingAsUser()->post(route('receipts.add'), $data);

        $response->assertSessionHasErrors(['quantity']);
    }

    public function test_add_stock_fails_with_negative_quantity()
    {
        $product = Product::create([
            'title' => 'Крыло',
            'category_id' => $this->category->id,
            'car_id' => $this->car->id,
            'cost_price' => 200000,
            'markup' => 100000,
        ]);

        $data = [
            'product_id' => $product->id,
            'warehouse_id' => $this->warehouse->id,
            'quantity' => -5,
        ];

        $response = $this->actingAsUser()->post(route('receipts.add'), $data);

        $response->assertSessionHasErrors(['quantity']);
    }

    public function test_add_stock_handles_space_in_quantity()
    {
        $product = Product::create([
            'title' => 'Крыло',
            'category_id' => $this->category->id,
            'car_id' => $this->car->id,
            'cost_price' => 200000,
            'markup' => 100000,
        ]);

        $data = [
            'product_id' => $product->id,
            'warehouse_id' => $this->warehouse->id,
            'quantity' => '1 500', // С пробелом
        ];

        $response = $this->actingAsUser()->post(route('receipts.add'), $data);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Поступление добавлено: +1500 шт');

        $this->assertDatabaseHas('quantities', [
            'product_id' => $product->id,
            'warehouse_id' => $this->warehouse->id,
            'quantity' => 1500,
        ]);
    }

    // ==================== HELPER METHODS ====================

    protected function actingAsUser()
    {
        $user = \App\Models\User::factory()->create();

        // actingAs() уже возвращает $this (сам TestCase) — именно его и нужно
        // возвращать, чтобы дальше можно было вызвать ->get()/->post() и т.д.
        // Раньше метод возвращал $user, и HTTP-методы вызывались на модели User.
        return $this->actingAs($user);
    }
}