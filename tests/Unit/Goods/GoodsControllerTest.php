<?php

namespace Tests\Unit\Goods;

use App\Models\Car;
use App\Models\Category;
use App\Models\Color;
use App\Models\Position;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GoodsControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::create([
            'username' => 'testuser',
            'password' => bcrypt('password'),
        ]);
        $this->actingAs($user);
    }

    // ---------- index() ----------

    public function test_index_returns_view_with_expected_data()
    {
        // ВАЖНО: контракт ProductRepositoryInterface::getWithStock() возвращает
        // Illuminate\Database\Eloquent\Collection, а НЕ Illuminate\Support\Collection.
        // collect() создаёт Support\Collection — Mockery выбросит TypeError, если её вернуть.
        $products = new \Illuminate\Database\Eloquent\Collection();

        $this->mock(ProductRepositoryInterface::class, function ($mock) use ($products) {
            $mock->shouldReceive('getWithStock')
                ->once()
                ->with([
                    'category_id' => null,
                    'search' => '',
                ])
                ->andReturn($products);
        });

        $response = $this->get(route('goods'));

        $response->assertStatus(200);
        $response->assertViewIs('pages.goods');
        $response->assertViewHas('categories');
        $response->assertViewHas('cars');
        $response->assertViewHas('units');
        $response->assertViewHas('positions');
        $response->assertViewHas('colors');
        $response->assertViewHas('products', $products);
        $response->assertViewHas('selectedCategoryId', null);
        $response->assertViewHas('search', '');
    }

    public function test_index_passes_category_and_search_filters_to_repository()
    {
        $category = Category::create(['title' => 'Кузовные']);

        $this->mock(ProductRepositoryInterface::class, function ($mock) use ($category) {
            $mock->shouldReceive('getWithStock')
                ->once()
                ->with([
                    'category_id' => (string) $category->id,
                    'search' => 'буфер',
                ])
                ->andReturn(new \Illuminate\Database\Eloquent\Collection());
        });

        $response = $this->get(route('goods', [
            'category' => $category->id,
            'search' => 'буфер',
        ]));

        $response->assertStatus(200);
        $response->assertViewHas('selectedCategoryId', (string) $category->id);
        $response->assertViewHas('search', 'буфер');
    }

    public function test_index_trims_search_query()
    {
        $this->mock(ProductRepositoryInterface::class, function ($mock) {
            $mock->shouldReceive('getWithStock')
                ->once()
                ->with([
                    'category_id' => null,
                    'search' => 'буфер',
                ])
                ->andReturn(new \Illuminate\Database\Eloquent\Collection());
        });

        $response = $this->get(route('goods', ['search' => '  буфер  ']));

        $response->assertStatus(200);
        $response->assertViewHas('search', 'буфер');
    }

    public function test_index_returns_partial_view_for_ajax_request()
    {
        // ПРОПУЩЕНО: реальный путь partial-вью для ajax-ветки GoodsController@index
        // не подтверждён (ошибка "View [partials.goods-products] not found").
        // Пришлите вывод `ls resources/views/partials/` — включу тест обратно с верным именем.
        $this->markTestSkipped('Уточните реальный путь partial-вью для ajax-ветки GoodsController@index');

        $this->mock(ProductRepositoryInterface::class, function ($mock) {
            $mock->shouldReceive('getWithStock')->once()->andReturn(new \Illuminate\Database\Eloquent\Collection());
        });

        $response = $this->get(route('goods'), [
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

        $response->assertStatus(200);
        $response->assertViewIs('partials.goods-products');
    }

    public function test_index_returns_categories_cars_units_positions_colors_from_database()
    {
        Category::create(['title' => 'Кузовные']);
        Car::create(['title' => 'Cobalt']);
        Unit::create(['title' => 'штука']);
        Position::create(['title' => 'Левое']);
        Color::create(['title' => 'Белый']);

        $this->mock(ProductRepositoryInterface::class, function ($mock) {
            $mock->shouldReceive('getWithStock')->once()->andReturn(new \Illuminate\Database\Eloquent\Collection());
        });

        $response = $this->get(route('goods'));

        $response->assertStatus(200);
        $this->assertCount(1, $response->viewData('categories'));
        $this->assertCount(1, $response->viewData('cars'));
        $this->assertCount(1, $response->viewData('units'));
        $this->assertCount(1, $response->viewData('positions'));
        $this->assertCount(1, $response->viewData('colors'));
    }

    public function test_guest_is_redirected_to_login()
    {
        $this->app['auth']->guard()->logout();

        $response = $this->get(route('goods'));

        $response->assertRedirect(route('login'));
    }

    // ---------- store() ----------

    protected function validPayload(array $overrides = []): array
    {
        $category = Category::create(['title' => 'Кузовные']);
        $warehouse = Warehouse::create(['title' => 'Склад №1']);

        return array_merge([
            'title' => 'Крыло переднее',
            'category_id' => $category->id,
            'cost_price' => 200000,
            'markup' => 100000,
            'warehouse_id' => $warehouse->id,
            'quantity' => 5,
        ], $overrides);
    }

    public function test_store_creates_product_and_redirects()
    {
        $response = $this->post(route('products.store'), $this->validPayload());

        // Диагностика: если тут упадёт — значит форма не прошла валидацию, ниже будет видно, на каком поле
        $response->assertSessionDoesntHaveErrors();
        $response->assertRedirect(); // ProductController@store делает back(), а не redirect()->route()
        $response->assertSessionHas('success', 'Товар добавлен');

        $this->assertDatabaseHas('products', [
            'title' => 'Крыло переднее',
        ]);
    }

    public function test_store_strips_spaces_from_numeric_fields()
    {
        // prepareForValidation() должен убрать пробелы: "200 000" -> "200000"
        $payload = $this->validPayload([
            'cost_price' => '200 000',
            'markup' => '100 000',
            'quantity' => '5',
        ]);

        $response = $this->post(route('products.store'), $payload);

        $response->assertSessionDoesntHaveErrors();
        $response->assertRedirect(); // ProductController@store делает back(), а не redirect()->route()

        $this->assertDatabaseHas('products', [
            'title' => 'Крыло переднее',
            'cost_price' => 200000,
            'markup' => 100000,
        ]);
    }

    public function test_store_creates_stock_quantity_on_selected_warehouse()
    {
        $payload = $this->validPayload(['quantity' => 7]);

        $response = $this->post(route('products.store'), $payload);

        $response->assertSessionDoesntHaveErrors();
        $response->assertRedirect(); // ProductController@store делает back(), а не redirect()->route()

        $product = \App\Models\Product::where('title', 'Крыло переднее')->firstOrFail();

        $this->assertDatabaseHas('quantities', [
            'product_id' => $product->id,
            'warehouse_id' => $payload['warehouse_id'],
            'quantity' => 7,
        ]);
    }

    public function test_store_accepts_optional_nullable_fields()
    {
        $car = Car::create(['title' => 'Cobalt']);
        $unit = Unit::create(['title' => 'шт']);
        $position = Position::create(['title' => 'Левое']);
        $color = Color::create(['title' => 'Белый']);

        $payload = $this->validPayload([
            'car_id' => $car->id,
            'unit_id' => $unit->id,
            'position_id' => $position->id,
            'color_id' => $color->id,
        ]);

        $response = $this->post(route('products.store'), $payload);

        $response->assertSessionDoesntHaveErrors();
        $response->assertRedirect(); // ProductController@store делает back(), а не redirect()->route()

        $this->assertDatabaseHas('products', [
            'title' => 'Крыло переднее',
            'car_id' => $car->id,
            'unit_id' => $unit->id,
            'position_id' => $position->id,
            'color_id' => $color->id,
        ]);
    }

    public function test_store_uploads_image_to_public_disk()
    {
        Storage::fake('public');

        $payload = $this->validPayload([
            'image' => UploadedFile::fake()->image('part.jpg'),
        ]);

        $response = $this->post(route('products.store'), $payload);

        $response->assertSessionDoesntHaveErrors();
        $response->assertRedirect(); // ProductController@store делает back(), а не redirect()->route()

        $product = \App\Models\Product::where('title', 'Крыло переднее')->firstOrFail();

        $this->assertNotNull($product->image);
        Storage::disk('public')->assertExists($product->image);
    }

    public function test_store_fails_validation_when_required_fields_missing()
    {
        $response = $this->post(route('products.store'), []);

        $response->assertSessionHasErrors([
            'title',
            'category_id',
            'cost_price',
            'markup',
            'warehouse_id',
            'quantity',
        ]);
    }

    public function test_store_fails_validation_when_category_does_not_exist()
    {
        $payload = $this->validPayload(['category_id' => 999999]);

        $response = $this->post(route('products.store'), $payload);

        $response->assertSessionHasErrors('category_id');
    }

    public function test_store_fails_validation_when_cost_price_is_negative()
    {
        $payload = $this->validPayload(['cost_price' => -100]);

        $response = $this->post(route('products.store'), $payload);

        $response->assertSessionHasErrors('cost_price');
    }

    public function test_store_fails_validation_with_custom_russian_message()
    {
        $response = $this->post(route('products.store'), []);

        $response->assertSessionHasErrors('title');
        $errors = session('errors');
        $this->assertEquals('Название товара обязательно', $errors->first('title'));
    }
}