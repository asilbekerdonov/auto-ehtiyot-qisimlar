<?php

namespace Tests\Unit\Sales;

use App\Models\Car;
use App\Models\Category;
use App\Models\Product;
use App\Models\Quantity;
use App\Models\Warehouse;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalesControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $category;
    protected $car;
    protected $warehouse;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->category = Category::create(['title' => 'Кузовные']);
        $this->car = Car::create(['title' => 'Cobalt']);
        $this->warehouse = Warehouse::create(['title' => 'Склад №1']);

        $this->user = User::create([
            'username' => 'aaaaa',
            'password' => bcrypt('password'),
        ]);
        $this->actingAs($this->user);
    }

    public function test_select_car_returns_view()
    {
        $response = $this->get(route('sales.cars'));

        $response->assertStatus(200);
        $response->assertViewIs('pages.sales-cars');
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

        $response = $this->get(route('sales.parts', $this->car));

        $response->assertStatus(200);
        $response->assertViewIs('pages.sales-parts');
        $response->assertViewHas('car', $this->car);
        $response->assertViewHas('products');
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

        $response = $this->get(route('sales.parts', [
            'car' => $this->car,
            'category' => $this->category->id
        ]));

        $response->assertStatus(200);
        $products = $response->viewData('products');
        $this->assertCount(1, $products);
        $this->assertEquals('Крыло', $products->first()->title);
    }

    public function test_add_to_cart_adds_item_to_session()
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

        $data = [
            'product_id' => $product->id,
            'warehouse_id' => $this->warehouse->id,
            'quantity' => 2,
            'price_per_unit' => 300000,
            'car_id' => $this->car->id,
        ];

        $response = $this->post(route('sales.cart.add'), $data);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Товар добавлен в корзину');

        $cart = session('sales_cart');
        $this->assertNotEmpty($cart);
        $key = $product->id . '_' . $this->warehouse->id;
        $this->assertArrayHasKey($key, $cart);
        $this->assertEquals(2, $cart[$key]['quantity']);
        $this->assertEquals(300000, $cart[$key]['price_per_unit']);
    }

    public function test_add_to_cart_fails_when_not_enough_stock()
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
            'quantity' => 5,
        ]);

        $data = [
            'product_id' => $product->id,
            'warehouse_id' => $this->warehouse->id,
            'quantity' => 10,
            'price_per_unit' => 300000,
            'car_id' => $this->car->id,
        ];

        $response = $this->post(route('sales.cart.add'), $data);

        $response->assertSessionHasErrors(['quantity']);
    }

    public function test_cart_returns_view_with_items()
    {
        $product = Product::create([
            'title' => 'Крыло',
            'category_id' => $this->category->id,
            'car_id' => $this->car->id,
            'cost_price' => 200000,
            'markup' => 100000,
        ]);

        // Добавляем товар в корзину
        session(['sales_cart' => [
            $product->id . '_' . $this->warehouse->id => [
                'product_id' => $product->id,
                'warehouse_id' => $this->warehouse->id,
                'quantity' => 2,
                'price_per_unit' => 300000,
            ]
        ]]);

        $response = $this->get(route('sales.cart'));

        $response->assertStatus(200);
        $response->assertViewIs('pages.sales-cart');
        $response->assertViewHas('items');
        $response->assertViewHas('total');
    }

    public function test_remove_from_cart_removes_item()
    {
        $product = Product::create([
            'title' => 'Крыло',
            'category_id' => $this->category->id,
            'car_id' => $this->car->id,
            'cost_price' => 200000,
            'markup' => 100000,
        ]);

        $key = $product->id . '_' . $this->warehouse->id;
        session(['sales_cart' => [
            $key => [
                'product_id' => $product->id,
                'warehouse_id' => $this->warehouse->id,
                'quantity' => 2,
                'price_per_unit' => 300000,
            ]
        ]]);

        $response = $this->delete(route('sales.cart.remove', $key));

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Товар убран из корзины');

        $cart = session('sales_cart');
        $this->assertArrayNotHasKey($key, $cart);
    }

    public function test_checkout_creates_sale()
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

        $key = $product->id . '_' . $this->warehouse->id;
        session(['sales_cart' => [
            $key => [
                'product_id' => $product->id,
                'warehouse_id' => $this->warehouse->id,
                'quantity' => 2,
                'price_per_unit' => 300000,
            ]
        ]]);

        $data = [
            'status' => 'оплачено',
            'prices' => [
                $key => 300000,
            ],
        ];

        $response = $this->post(route('sales.checkout'), $data);

        $response->assertRedirect(route('sales.cars'));
        $response->assertSessionHas('success', 'Продажа оформлена');

        $this->assertDatabaseHas('sales', [
            'status' => 'оплачено',
            'total_amount' => 600000,
        ]);

        $this->assertDatabaseHas('quantities', [
            'product_id' => $product->id,
            'warehouse_id' => $this->warehouse->id,
            'quantity' => 8,
        ]);
    }
}
