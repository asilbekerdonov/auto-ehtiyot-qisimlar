<?php

namespace Tests\Unit\Products;

use App\Models\Product;
use App\Models\Category;
use App\Models\Car;
use App\Models\Unit;
use App\Models\Quantity;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_create_a_product()
    {
        $category = Category::create(['title' => 'Кузовные']);
        $car = Car::create(['title' => 'Cobalt']);
        $unit = Unit::create(['title' => 'шт']);

        $product = Product::create([
            'title' => 'Крыло переднее',
            'category_id' => $category->id,
            'car_id' => $car->id,
            'unit_id' => $unit->id,
            'cost_price' => 200000,
            'markup' => 100000,
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'title' => 'Крыло переднее',
            'cost_price' => 200000,
            'markup' => 100000,
        ]);
    }

    public function test_it_has_relationships()
    {
        $category = Category::create(['title' => 'Кузовные']);
        $car = Car::create(['title' => 'Cobalt']);
        $unit = Unit::create(['title' => 'шт']);

        $product = Product::create([
            'title' => 'Бампер',
            'category_id' => $category->id,
            'car_id' => $car->id,
            'unit_id' => $unit->id,
            'cost_price' => 300000,
            'markup' => 150000,
        ]);

        $this->assertInstanceOf(Category::class, $product->category);
        $this->assertInstanceOf(Car::class, $product->car);
        $this->assertInstanceOf(Unit::class, $product->unit);
    }

    public function test_it_calculates_selling_price()
    {
        $category = Category::create(['title' => 'Кузовные']);

        $product = Product::create([
            'title' => 'Крыло',
            'category_id' => $category->id,
            'cost_price' => 200000,
            'markup' => 100000,
        ]);

        $this->assertEquals(300000, $product->cost_price + $product->markup);
    }

    public function test_it_has_quantities()
    {
        $category = Category::create(['title' => 'Кузовные']);
        $warehouse = Warehouse::create(['title' => 'Склад №1']);

        $product = Product::create([
            'title' => 'Крыло',
            'category_id' => $category->id,
            'cost_price' => 200000,
            'markup' => 100000,
        ]);

        Quantity::create([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 10,
        ]);

        $this->assertCount(1, $product->quantities);
        $this->assertEquals(10, $product->quantities->first()->quantity);
    }

    public function test_it_can_calculate_total_stock()
    {
        $category = Category::create(['title' => 'Кузовные']);
        $warehouse1 = Warehouse::create(['title' => 'Склад №1']);
        $warehouse2 = Warehouse::create(['title' => 'Склад №2']);

        $product = Product::create([
            'title' => 'Крыло',
            'category_id' => $category->id,
            'cost_price' => 200000,
            'markup' => 100000,
        ]);

        Quantity::create([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse1->id,
            'quantity' => 10,
        ]);

        Quantity::create([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse2->id,
            'quantity' => 20,
        ]);

        $totalStock = $product->quantities->sum('quantity');
        $this->assertEquals(30, $totalStock);
    }

    public function test_it_has_optional_fields_nullable()
    {
        $category = Category::create(['title' => 'Кузовные']);

        $product = Product::create([
            'title' => 'Крыло',
            'category_id' => $category->id,
            'cost_price' => 200000,
            'markup' => 100000,
        ]);

        $this->assertNull($product->car_id);
        $this->assertNull($product->unit_id);
        $this->assertNull($product->position_id);
        $this->assertNull($product->color_id);
        $this->assertNull($product->image);
    }
}