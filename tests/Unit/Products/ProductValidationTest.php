<?php

namespace Tests\Unit\Products;

use App\Http\Requests\Product\StoreRequest;
use App\Http\Requests\Product\UpdateRequest;
use App\Models\Category;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ProductValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_request_has_required_rules()
    {
        $rules = (new StoreRequest())->rules();

        $this->assertArrayHasKey('title', $rules);
        $this->assertArrayHasKey('category_id', $rules);
        $this->assertArrayHasKey('cost_price', $rules);
        $this->assertArrayHasKey('markup', $rules);
        $this->assertArrayHasKey('warehouse_id', $rules);
        $this->assertArrayHasKey('quantity', $rules);

        $this->assertEquals(['required', 'string', 'max:255'], $rules['title']);
        $this->assertEquals(['required', 'exists:categories,id'], $rules['category_id']);
        $this->assertEquals(['required', 'numeric', 'min:0'], $rules['cost_price']);
        $this->assertEquals(['required', 'numeric', 'min:0'], $rules['markup']);
        $this->assertEquals(['required', 'exists:warehouses,id'], $rules['warehouse_id']);
        $this->assertEquals(['required', 'integer', 'min:0'], $rules['quantity']);
    }

    public function test_store_request_has_optional_rules()
    {
        $rules = (new StoreRequest())->rules();

        $this->assertEquals(['nullable', 'exists:cars,id'], $rules['car_id']);
        $this->assertEquals(['nullable', 'exists:units,id'], $rules['unit_id']);
        $this->assertEquals(['nullable', 'exists:positions,id'], $rules['position_id']);
        $this->assertEquals(['nullable', 'exists:colors,id'], $rules['color_id']);
        $this->assertEquals(['nullable', 'image', 'max:4096'], $rules['image']);
    }

    public function test_store_request_fails_with_empty_data()
    {
        $validator = Validator::make([], (new StoreRequest())->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('title', $validator->errors()->toArray());
        $this->assertArrayHasKey('category_id', $validator->errors()->toArray());
        $this->assertArrayHasKey('cost_price', $validator->errors()->toArray());
        $this->assertArrayHasKey('markup', $validator->errors()->toArray());
        $this->assertArrayHasKey('warehouse_id', $validator->errors()->toArray());
        $this->assertArrayHasKey('quantity', $validator->errors()->toArray());
    }

    public function test_store_request_passes_with_valid_data()
    {
        $category = Category::create(['title' => 'Кузовные']);
        $warehouse = Warehouse::create(['title' => 'Склад №1']);

        $validator = Validator::make([
            'title' => 'Крыло',
            'category_id' => $category->id,
            'cost_price' => 200000,
            'markup' => 100000,
            'warehouse_id' => $warehouse->id,
            'quantity' => 10,
        ], (new StoreRequest())->rules());

        $this->assertFalse($validator->fails());
    }

    public function test_store_request_rejects_negative_quantity()
    {
        $category = Category::create(['title' => 'Кузовные']);
        $warehouse = Warehouse::create(['title' => 'Склад №1']);

        $validator = Validator::make([
            'title' => 'Крыло',
            'category_id' => $category->id,
            'cost_price' => 200000,
            'markup' => 100000,
            'warehouse_id' => $warehouse->id,
            'quantity' => -5,
        ], (new StoreRequest())->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('quantity', $validator->errors()->toArray());
    }

    public function test_store_request_rejects_negative_prices()
    {
        $category = Category::create(['title' => 'Кузовные']);
        $warehouse = Warehouse::create(['title' => 'Склад №1']);

        $validator = Validator::make([
            'title' => 'Крыло',
            'category_id' => $category->id,
            'cost_price' => -1000,
            'markup' => -500,
            'warehouse_id' => $warehouse->id,
            'quantity' => 10,
        ], (new StoreRequest())->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('cost_price', $validator->errors()->toArray());
        $this->assertArrayHasKey('markup', $validator->errors()->toArray());
    }

    public function test_update_request_has_similar_rules()
    {
        $rules = (new UpdateRequest())->rules();

        $this->assertArrayHasKey('title', $rules);
        $this->assertArrayHasKey('category_id', $rules);
        $this->assertArrayHasKey('cost_price', $rules);
        $this->assertArrayHasKey('markup', $rules);
        $this->assertEquals(['nullable', 'integer', 'min:0'], $rules['quantity']);
    }

    public function test_update_request_quantity_is_optional()
    {
        $rules = (new UpdateRequest())->rules();

        $this->assertArrayHasKey('quantity', $rules);
        $this->assertEquals(['nullable', 'integer', 'min:0'], $rules['quantity']);

        $category = Category::create(['title' => 'Кузовные']);

        $validator = Validator::make([
            'title' => 'Крыло',
            'category_id' => $category->id,
            'cost_price' => 200000,
            'markup' => 100000,
        ], $rules);

        $this->assertFalse($validator->fails());
    }
}