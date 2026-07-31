<?php

namespace Tests\Unit\Products;

use App\Models\Product;
use App\Models\Category;
use App\Models\Car;
use App\Models\Unit;
use App\Models\Quantity;
use App\Models\Warehouse;
use App\Repositories\ProductRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected $repository;
    protected $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = app(ProductRepository::class);
        $this->category = Category::create(['title' => 'Кузовные']);
    }

    public function test_can_find_product_by_id()
    {
        $product = Product::create([
            'title' => 'Крыло',
            'category_id' => $this->category->id,
            'cost_price' => 200000,
            'markup' => 100000,
        ]);

        $found = $this->repository->find($product->id);

        $this->assertNotNull($found);
        $this->assertEquals($product->id, $found->id);
        $this->assertEquals('Крыло', $found->title);
    }

    public function test_returns_null_when_product_not_found()
    {
        $found = $this->repository->find(999);

        $this->assertNull($found);
    }

    public function test_can_find_product_by_attributes()
    {
        $car = Car::create(['title' => 'Cobalt']);

        Product::create([
            'title' => 'Бампер',
            'category_id' => $this->category->id,
            'car_id' => $car->id,
            'cost_price' => 300000,
            'markup' => 150000,
        ]);

        $found = $this->repository->findByAttributes([
            'title' => 'Бампер',
            'category_id' => $this->category->id,
        ]);

        $this->assertNotNull($found);
        $this->assertEquals('Бампер', $found->title);
    }

    public function test_can_find_by_attributes_with_null_values()
    {
        Product::create([
            'title' => 'Крыло',
            'category_id' => $this->category->id,
            'car_id' => null,
            'cost_price' => 200000,
            'markup' => 100000,
        ]);

        $found = $this->repository->findByAttributes([
            'title' => 'Крыло',
            'category_id' => $this->category->id,
            'car_id' => null,
        ]);

        $this->assertNotNull($found);
    }

    public function test_can_create_product()
    {
        $product = $this->repository->create([
            'title' => 'Фара',
            'category_id' => $this->category->id,
            'cost_price' => 150000,
            'markup' => 75000,
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'title' => 'Фара',
        ]);

        $this->assertEquals('Фара', $product->title);
    }

    public function test_can_update_product()
    {
        $product = Product::create([
            'title' => 'Старое название',
            'category_id' => $this->category->id,
            'cost_price' => 100000,
            'markup' => 50000,
        ]);

        $updated = $this->repository->update($product, [
            'title' => 'Новое название',
            'cost_price' => 200000,
        ]);

        $this->assertEquals('Новое название', $updated->title);
        $this->assertEquals(200000, $updated->cost_price);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'title' => 'Новое название',
        ]);
    }

    public function test_can_delete_product()
    {
        $product = Product::create([
            'title' => 'Удаляемый товар',
            'category_id' => $this->category->id,
            'cost_price' => 100000,
            'markup' => 50000,
        ]);

        $result = $this->repository->delete($product);

        $this->assertTrue($result);
        $this->assertSoftDeleted('products', ['id' => $product->id]);
        // $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function test_can_update_stock()
    {
        $warehouse = Warehouse::create(['title' => 'Склад']);

        $product = Product::create([
            'title' => 'Тестовый товар',
            'category_id' => $this->category->id,
            'cost_price' => 100000,
            'markup' => 50000,
        ]);

        $result = $this->repository->updateStock($product->id, $warehouse->id, 25);

        $this->assertTrue($result);

        $this->assertDatabaseHas('quantities', [
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 25,
        ]);
    }

    public function test_can_update_existing_stock()
    {
        $warehouse = Warehouse::create(['title' => 'Склад']);

        $product = Product::create([
            'title' => 'Тестовый товар',
            'category_id' => $this->category->id,
            'cost_price' => 100000,
            'markup' => 50000,
        ]);

        Quantity::create([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 10,
        ]);

        $result = $this->repository->updateStock($product->id, $warehouse->id, 25);

        $this->assertTrue($result);

        $this->assertDatabaseHas('quantities', [
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 25,
        ]);
    }

    public function test_can_get_all_products_with_filters()
    {
        $car = Car::create(['title' => 'Cobalt']);

        Product::create([
            'title' => 'Товар 1',
            'category_id' => $this->category->id,
            'car_id' => $car->id,
            'cost_price' => 100000,
            'markup' => 50000,
        ]);

        Product::create([
            'title' => 'Товар 2',
            'category_id' => $this->category->id,
            'car_id' => null,
            'cost_price' => 200000,
            'markup' => 100000,
        ]);

        $filtered = $this->repository->getAll(['car_id' => $car->id]);

        $this->assertCount(1, $filtered);
        $this->assertEquals('Товар 1', $filtered->first()->title);

        $all = $this->repository->getAll();
        $this->assertCount(2, $all);
    }
}