<?php

namespace Tests\Unit\Add;

use App\Models\Car;
use App\Models\Category;
use App\Models\Color;
use App\Models\Position;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AddControllerTest extends TestCase
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

    public function test_index_returns_view()
    {
        $response = $this->get(route('add'));

        $response->assertStatus(200);
        $response->assertViewIs('pages.add');
        $response->assertViewHas('categories');
        $response->assertViewHas('warehouses');
        $response->assertViewHas('cars');
        $response->assertViewHas('units');
        $response->assertViewHas('positions');
        $response->assertViewHas('colors');
    }

    public function test_index_returns_all_categories()
    {
        Category::create(['title' => 'Кузовные']);
        Category::create(['title' => 'Двигатель']);

        $response = $this->get(route('add'));

        $response->assertStatus(200);
        $this->assertCount(2, $response->viewData('categories'));
    }

    public function test_index_returns_all_warehouses()
    {
        Warehouse::create(['title' => 'Склад №1']);
        Warehouse::create(['title' => 'Склад №2']);

        $response = $this->get(route('add'));

        $response->assertStatus(200);
        $this->assertCount(2, $response->viewData('warehouses'));
    }

    public function test_index_returns_all_cars()
    {
        Car::create(['title' => 'Cobalt']);
        Car::create(['title' => 'Nexia']);

        $response = $this->get(route('add'));

        $response->assertStatus(200);
        $this->assertCount(2, $response->viewData('cars'));
    }

    public function test_index_returns_units_ordered_by_title()
    {
        Unit::create(['title' => 'штука']);
        Unit::create(['title' => 'комплект']);

        $response = $this->get(route('add'));

        $response->assertStatus(200);
        $units = $response->viewData('units');
        $this->assertCount(2, $units);
        $this->assertEquals('комплект', $units->first()->title);
    }

    public function test_index_returns_positions_ordered_by_title()
    {
        Position::create(['title' => 'Правое']);
        Position::create(['title' => 'Левое']);

        $response = $this->get(route('add'));

        $response->assertStatus(200);
        $positions = $response->viewData('positions');
        $this->assertCount(2, $positions);
        $this->assertEquals('Левое', $positions->first()->title);
    }

    public function test_index_returns_colors_ordered_by_title()
    {
        Color::create(['title' => 'Чёрный']);
        Color::create(['title' => 'Белый']);

        $response = $this->get(route('add'));

        $response->assertStatus(200);
        $colors = $response->viewData('colors');
        $this->assertCount(2, $colors);
        $this->assertEquals('Белый', $colors->first()->title);
    }

    public function test_index_returns_empty_collections_when_nothing_added()
    {
        $response = $this->get(route('add'));

        $response->assertStatus(200);
        $this->assertCount(0, $response->viewData('categories'));
        $this->assertCount(0, $response->viewData('warehouses'));
        $this->assertCount(0, $response->viewData('cars'));
        $this->assertCount(0, $response->viewData('units'));
        $this->assertCount(0, $response->viewData('positions'));
        $this->assertCount(0, $response->viewData('colors'));
    }

    public function test_guest_is_redirected_to_login()
    {
        // Новый "гостевой" запрос без аутентификации — сбрасываем сессию actingAs
        $this->app['auth']->guard()->logout();

        $response = $this->get(route('add'));

        $response->assertRedirect(route('login'));
    }
}