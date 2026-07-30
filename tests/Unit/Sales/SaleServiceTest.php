<?php

namespace Tests\Unit\Sales;

use App\Models\Car;
use App\Models\Category;
use App\Models\Product;
use App\Models\Quantity;
use App\Models\Warehouse;
use App\Models\Sale;
use App\Models\Customer;
use App\Services\SaleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SaleServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $saleService;
    protected $category;
    protected $car;
    protected $warehouse;
    protected $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->saleService = app(SaleService::class);
        $this->category = Category::create(['title' => 'Кузовные']);
        $this->car = Car::create(['title' => 'Cobalt']);
        $this->warehouse = Warehouse::create(['title' => 'Склад №1']);

        $this->product = Product::create([
            'title' => 'Крыло',
            'category_id' => $this->category->id,
            'car_id' => $this->car->id,
            'cost_price' => 200000,
            'markup' => 100000,
        ]);

        Quantity::create([
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'quantity' => 20,
        ]);
    }

    public function test_check_availability_returns_true_when_enough_stock()
    {
        $result = $this->saleService->checkAvailability(
            $this->product->id,
            $this->warehouse->id,
            10
        );

        $this->assertTrue($result);
    }

    public function test_check_availability_returns_false_when_not_enough_stock()
    {
        $result = $this->saleService->checkAvailability(
            $this->product->id,
            $this->warehouse->id,
            30
        );

        $this->assertFalse($result);
    }

    public function test_get_available_quantity_returns_correct_value()
    {
        $quantity = $this->saleService->getAvailableQuantity(
            $this->product->id,
            $this->warehouse->id
        );

        $this->assertEquals(20, $quantity);
    }

    public function test_create_sale_with_paid_status()
    {
        $cart = [
            $this->product->id . '_' . $this->warehouse->id => [
                'product_id' => $this->product->id,
                'warehouse_id' => $this->warehouse->id,
                'quantity' => 5,
                'price_per_unit' => 300000,
            ]
        ];

        $data = [
            'status' => 'оплачено',
        ];

        $sale = $this->saleService->createSale($cart, $data);

        $this->assertInstanceOf(Sale::class, $sale);
        $this->assertEquals('оплачено', $sale->status);
        $this->assertEquals(1500000, $sale->total_amount);
        $this->assertNull($sale->customer_id);

        $this->assertDatabaseHas('sales', [
            'id' => $sale->id,
            'status' => 'оплачено',
        ]);

        $this->assertDatabaseHas('sale_items', [
            'sale_id' => $sale->id,
            'product_id' => $this->product->id,
            'quantity' => 5,
            'price_per_unit' => 300000,
            'total_price' => 1500000,
        ]);

        $this->assertDatabaseHas('quantities', [
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'quantity' => 15,
        ]);
    }

    public function test_create_sale_with_debt_status_creates_customer()
    {
        $cart = [
            $this->product->id . '_' . $this->warehouse->id => [
                'product_id' => $this->product->id,
                'warehouse_id' => $this->warehouse->id,
                'quantity' => 3,
                'price_per_unit' => 300000,
            ]
        ];

        $data = [
            'status' => 'долг',
            'customer_name' => 'Иван Иванов',
            'customer_phone' => '+998901234567',
        ];

        $sale = $this->saleService->createSale($cart, $data);

        $this->assertInstanceOf(Sale::class, $sale);
        $this->assertEquals('долг', $sale->status);
        $this->assertNotNull($sale->customer_id);

        $this->assertDatabaseHas('customers', [
            'name' => 'Иван Иванов',
            'phone' => '+998901234567',
        ]);

        $this->assertDatabaseHas('sales', [
            'id' => $sale->id,
            'status' => 'долг',
        ]);

        $this->assertDatabaseHas('quantities', [
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'quantity' => 17,
        ]);
    }

    public function test_create_sale_with_multiple_items()
    {
        $product2 = Product::create([
            'title' => 'Бампер',
            'category_id' => $this->category->id,
            'car_id' => $this->car->id,
            'cost_price' => 300000,
            'markup' => 150000,
        ]);

        Quantity::create([
            'product_id' => $product2->id,
            'warehouse_id' => $this->warehouse->id,
            'quantity' => 15,
        ]);

        $cart = [
            $this->product->id . '_' . $this->warehouse->id => [
                'product_id' => $this->product->id,
                'warehouse_id' => $this->warehouse->id,
                'quantity' => 2,
                'price_per_unit' => 300000,
            ],
            $product2->id . '_' . $this->warehouse->id => [
                'product_id' => $product2->id,
                'warehouse_id' => $this->warehouse->id,
                'quantity' => 3,
                'price_per_unit' => 450000,
            ]
        ];

        $data = [
            'status' => 'оплачено',
        ];

        $sale = $this->saleService->createSale($cart, $data);

        $this->assertEquals(600000 + 1350000, $sale->total_amount);

        $this->assertDatabaseHas('sale_items', [
            'sale_id' => $sale->id,
            'product_id' => $this->product->id,
            'quantity' => 2,
        ]);

        $this->assertDatabaseHas('sale_items', [
            'sale_id' => $sale->id,
            'product_id' => $product2->id,
            'quantity' => 3,
        ]);
    }

    public function test_create_sale_handles_decimal_quantities()
    {
        // Хотя quantity обычно integer, тест на всякий случай
        $cart = [
            $this->product->id . '_' . $this->warehouse->id => [
                'product_id' => $this->product->id,
                'warehouse_id' => $this->warehouse->id,
                'quantity' => 2.5,
                'price_per_unit' => 300000,
            ]
        ];

        $data = [
            'status' => 'оплачено',
        ];

        $sale = $this->saleService->createSale($cart, $data);

        $this->assertEquals(750000, $sale->total_amount);
    }
}
