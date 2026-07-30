<?php

namespace Tests\Unit\Analytics;

use App\Models\Car;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Quantity;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Warehouse;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalyticsControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $product;
    protected $category;
    protected $car;
    protected $warehouse;

    protected function setUp(): void
    {
        parent::setUp();

        $this->category = Category::create(['title' => 'Кузовные']);
        $this->car = Car::create(['title' => 'Cobalt']);
        $this->warehouse = Warehouse::create(['title' => 'Склад №1']);

        $user = User::create([
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);
        $this->actingAs($user);

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

    public function test_index_returns_view()
    {
        $response = $this->get(route('analytics'));

        $response->assertStatus(200);
        $response->assertViewIs('pages.analytics');
        $response->assertViewHas('selectedDate');
        $response->assertViewHas('selectedMonth');
        $response->assertViewHas('monthLabel');
        $response->assertViewHas('paidItems');
        $response->assertViewHas('debtItems');
        $response->assertViewHas('totalPaid');
        $response->assertViewHas('totalDebt');
        $response->assertViewHas('daysWithSalesInMonth');
        $response->assertViewHas('months');
    }

    public function test_index_shows_todays_data_by_default()
    {
        $today = Carbon::today();

        // Создаем продажу за сегодня
        $customer = Customer::create(['name' => 'Иван Иванов', 'phone' => '+998901234567']);
        $sale = Sale::create([
            'customer_id' => $customer->id,
            'status' => 'оплачено',
            'total_amount' => 300000,
            'created_at' => $today,
        ]);

        SaleItem::create([
            'sale_id' => $sale->id,
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'quantity' => 1,
            'price_per_unit' => 300000,
            'total_price' => 300000,
        ]);

        $response = $this->get(route('analytics'));

        $response->assertStatus(200);
        $this->assertEquals($today->format('Y-m-d'), $response->viewData('selectedDate')->format('Y-m-d'));
        $this->assertEquals(300000, $response->viewData('totalPaid'));
        $this->assertEquals(0, $response->viewData('totalDebt'));
        $this->assertCount(1, $response->viewData('paidItems'));
    }

    public function test_index_shows_selected_date_data()
    {
        $date = Carbon::today()->subDays(5);
        $customer = Customer::create(['name' => 'Иван Иванов', 'phone' => '+998901234567']);

        // Создаем продажу за выбранную дату
        $sale = Sale::create([
            'customer_id' => $customer->id,
            'status' => 'оплачено',
            'total_amount' => 500000,
            'created_at' => $date,
        ]);

        SaleItem::create([
            'sale_id' => $sale->id,
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'quantity' => 1,
            'price_per_unit' => 500000,
            'total_price' => 500000,
        ]);

        $response = $this->get(route('analytics', ['date' => $date->format('Y-m-d')]));

        $response->assertStatus(200);
        $this->assertEquals($date->format('Y-m-d'), $response->viewData('selectedDate')->format('Y-m-d'));
        $this->assertEquals(500000, $response->viewData('totalPaid'));
        $this->assertCount(1, $response->viewData('paidItems'));
    }

    public function test_index_shows_debt_items_separately()
    {
        $today = Carbon::today();
        $customer = Customer::create(['name' => 'Иван Иванов', 'phone' => '+998901234567']);

        // Оплаченная продажа
        $sale1 = Sale::create([
            'customer_id' => $customer->id,
            'status' => 'оплачено',
            'total_amount' => 300000,
            'created_at' => $today,
        ]);

        SaleItem::create([
            'sale_id' => $sale1->id,
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'quantity' => 1,
            'price_per_unit' => 300000,
            'total_price' => 300000,
        ]);

        // Продажа в долг
        $sale2 = Sale::create([
            'customer_id' => $customer->id,
            'status' => 'долг',
            'total_amount' => 200000,
            'created_at' => $today,
        ]);

        SaleItem::create([
            'sale_id' => $sale2->id,
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'quantity' => 1,
            'price_per_unit' => 200000,
            'total_price' => 200000,
        ]);

        $response = $this->get(route('analytics'));

        $response->assertStatus(200);
        $this->assertEquals(300000, $response->viewData('totalPaid'));
        $this->assertEquals(200000, $response->viewData('totalDebt'));
        $this->assertCount(1, $response->viewData('paidItems'));
        $this->assertCount(1, $response->viewData('debtItems'));
    }

    public function test_index_returns_months_history()
    {
        // Создаем продажи за последние 3 месяца
        $customer = Customer::create(['name' => 'Иван Иванов', 'phone' => '+998901234567']);

        for ($i = 0; $i < 3; $i++) {
            $date = Carbon::today()->subMonths($i);
            $sale = Sale::create([
                'customer_id' => $customer->id,
                'status' => 'оплачено',
                'total_amount' => 100000 * ($i + 1),
                'created_at' => $date,
            ]);

            SaleItem::create([
                'sale_id' => $sale->id,
                'product_id' => $this->product->id,
                'warehouse_id' => $this->warehouse->id,
                'quantity' => $i + 1,
                'price_per_unit' => 100000,
                'total_price' => 100000 * ($i + 1),
            ]);
        }

        $response = $this->get(route('analytics'));

        $months = $response->viewData('months');
        $this->assertNotEmpty($months);
        $this->assertGreaterThanOrEqual(3, count($months));
    }

    public function test_index_handles_no_sales()
    {
        $response = $this->get(route('analytics'));

        $response->assertStatus(200);
        $this->assertEquals(0, $response->viewData('totalPaid'));
        $this->assertEquals(0, $response->viewData('totalDebt'));
        $this->assertCount(0, $response->viewData('paidItems'));
        $this->assertCount(0, $response->viewData('debtItems'));
    }

    public function test_index_accepts_month_parameter()
    {
        $month = Carbon::today()->subMonth();

        $response = $this->get(route('analytics', ['month' => $month->format('Y-m')]));

        $response->assertStatus(200);
        $this->assertEquals($month->format('Y-m'), $response->viewData('selectedMonth')->format('Y-m'));
    }

    public function test_index_calculates_days_with_sales_in_month()
    {
        $today = Carbon::today();
        $customer = Customer::create(['name' => 'Иван Иванов', 'phone' => '+998901234567']);

        // Создаем продажи в разные дни месяца
        $dates = [
            Carbon::today()->subDays(2),
            Carbon::today()->subDays(5),
            Carbon::today()->subDays(10),
        ];

        foreach ($dates as $date) {
            $sale = Sale::create([
                'customer_id' => $customer->id,
                'status' => 'оплачено',
                'total_amount' => 100000,
                'created_at' => $date,
            ]);

            SaleItem::create([
                'sale_id' => $sale->id,
                'product_id' => $this->product->id,
                'warehouse_id' => $this->warehouse->id,
                'quantity' => 1,
                'price_per_unit' => 100000,
                'total_price' => 100000,
            ]);
        }

        $response = $this->get(route('analytics'));

        $daysWithSales = $response->viewData('daysWithSalesInMonth');
        $this->assertCount(3, $daysWithSales);
        $this->assertContains($dates[0]->day, $daysWithSales);
        $this->assertContains($dates[1]->day, $daysWithSales);
        $this->assertContains($dates[2]->day, $daysWithSales);
    }
}
