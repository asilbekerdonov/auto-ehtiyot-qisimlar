<?php

namespace Tests\Unit\Debtors;

use App\Models\Car;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Quantity;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Warehouse;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DebtorControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $category;
    protected $car;
    protected $warehouse;
    protected $user;
    protected $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->category = Category::create(['title' => 'Кузовные']);
        $this->car = Car::create(['title' => 'Cobalt']);
        $this->warehouse = Warehouse::create(['title' => 'Склад №1']);

        $this->user = User::create([
            'username' => 'testuser',

            'password' => bcrypt('password'),
        ]);
        $this->actingAs($this->user);

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

    public function test_index_returns_view_with_debtors()
    {
        // Создаем клиента с долгом
        $customer = Customer::create([
            'name' => 'Иван Иванов',
            'phone' => '+998901234567',
        ]);

        $sale = Sale::create([
            'customer_id' => $customer->id,
            'status' => 'долг',
            'total_amount' => 300000,
        ]);

        SaleItem::create([
            'sale_id' => $sale->id,
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'quantity' => 1,
            'price_per_unit' => 300000,
            'total_price' => 300000,
        ]);

        $response = $this->get(route('debtors.index'));

        $response->assertStatus(200);
        $response->assertViewIs('pages.debtors');
        $response->assertViewHas('debtors');

        $debtors = $response->viewData('debtors');
        $this->assertCount(1, $debtors);
        $this->assertEquals('Иван Иванов', $debtors->first()->name);
        $this->assertEquals(300000, $debtors->first()->total_debt);
        $this->assertEquals(1, $debtors->first()->debt_count);
    }

    public function test_index_filters_by_search()
    {
        $customer1 = Customer::create([
            'name' => 'Иван Иванов',
            'phone' => '+998901234567',
        ]);

        $customer2 = Customer::create([
            'name' => 'Петр Петров',
            'phone' => '+998902345678',
        ]);

        // Создаем долги для обоих
        foreach ([$customer1, $customer2] as $customer) {
            $sale = Sale::create([
                'customer_id' => $customer->id,
                'status' => 'долг',
                'total_amount' => 300000,
            ]);

            SaleItem::create([
                'sale_id' => $sale->id,
                'product_id' => $this->product->id,
                'warehouse_id' => $this->warehouse->id,
                'quantity' => 1,
                'price_per_unit' => 300000,
                'total_price' => 300000,
            ]);
        }

        // Поиск по имени
        $response = $this->get(route('debtors.index', ['search' => 'Иван']));

        $debtors = $response->viewData('debtors');
        $this->assertCount(1, $debtors);
        $this->assertEquals('Иван Иванов', $debtors->first()->name);

        // Поиск по телефону
        $response = $this->get(route('debtors.index', ['search' => '902345678']));

        $debtors = $response->viewData('debtors');
        $this->assertCount(1, $debtors);
        $this->assertEquals('Петр Петров', $debtors->first()->name);
    }

    public function test_index_shows_only_debtors()
    {
        // Клиент с долгом
        $customer1 = Customer::create([
            'name' => 'Иван Иванов',
            'phone' => '+998901234567',
        ]);

        $sale1 = Sale::create([
            'customer_id' => $customer1->id,
            'status' => 'долг',
            'total_amount' => 300000,
        ]);

        SaleItem::create([
            'sale_id' => $sale1->id,
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'quantity' => 1,
            'price_per_unit' => 300000,
            'total_price' => 300000,
        ]);

        // Клиент без долга (оплачено)
        $customer2 = Customer::create([
            'name' => 'Петр Петров',
            'phone' => '+998902345678',
        ]);

        $sale2 = Sale::create([
            'customer_id' => $customer2->id,
            'status' => 'оплачено',
            'total_amount' => 200000,
        ]);

        SaleItem::create([
            'sale_id' => $sale2->id,
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'quantity' => 1,
            'price_per_unit' => 200000,
            'total_price' => 200000,
        ]);

        $response = $this->get(route('debtors.index'));

        $debtors = $response->viewData('debtors');
        $this->assertCount(1, $debtors);
        $this->assertEquals('Иван Иванов', $debtors->first()->name);
        $this->assertNotEquals('Петр Петров', $debtors->first()->name);
    }

    public function test_index_calculates_total_debt_correctly()
    {
        $customer = Customer::create([
            'name' => 'Иван Иванов',
            'phone' => '+998901234567',
        ]);

        // Создаем 3 долга
        for ($i = 1; $i <= 3; $i++) {
            $sale = Sale::create([
                'customer_id' => $customer->id,
                'status' => 'долг',
                'total_amount' => $i * 100000,
            ]);

            SaleItem::create([
                'sale_id' => $sale->id,
                'product_id' => $this->product->id,
                'warehouse_id' => $this->warehouse->id,
                'quantity' => $i,
                'price_per_unit' => 100000,
                'total_price' => $i * 100000,
            ]);
        }

        $response = $this->get(route('debtors.index'));

        $debtors = $response->viewData('debtors');
        $this->assertCount(1, $debtors);
        $this->assertEquals(600000, $debtors->first()->total_debt); // 100000 + 200000 + 300000
        $this->assertEquals(3, $debtors->first()->debt_count);
    }

    public function test_pay_clears_all_debts()
    {
        $customer = Customer::create([
            'name' => 'Иван Иванов',
            'phone' => '+998901234567',
        ]);

        // Создаем 2 долга
        for ($i = 1; $i <= 2; $i++) {
            $sale = Sale::create([
                'customer_id' => $customer->id,
                'status' => 'долг',
                'total_amount' => $i * 100000,
            ]);

            SaleItem::create([
                'sale_id' => $sale->id,
                'product_id' => $this->product->id,
                'warehouse_id' => $this->warehouse->id,
                'quantity' => $i,
                'price_per_unit' => 100000,
                'total_price' => $i * 100000,
            ]);
        }

        // Проверяем что долги есть
        $this->assertDatabaseHas('sales', [
            'customer_id' => $customer->id,
            'status' => 'долг',
        ]);

        $response = $this->post(route('debtors.pay', $customer));

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Долг клиента «Иван Иванов» погашен');

        // Проверяем что все долги погашены
        $this->assertDatabaseHas('sales', [
            'customer_id' => $customer->id,
            'status' => 'оплачено',
        ]);

        $this->assertDatabaseMissing('sales', [
            'customer_id' => $customer->id,
            'status' => 'долг',
        ]);
    }

    public function test_pay_returns_info_when_no_debts()
    {
        $customer = Customer::create([
            'name' => 'Иван Иванов',
            'phone' => '+998901234567',
        ]);

        // Создаем оплаченную продажу (не долг)
        $sale = Sale::create([
            'customer_id' => $customer->id,
            'status' => 'оплачено',
            'total_amount' => 300000,
        ]);

        SaleItem::create([
            'sale_id' => $sale->id,
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'quantity' => 1,
            'price_per_unit' => 300000,
            'total_price' => 300000,
        ]);

        $response = $this->post(route('debtors.pay', $customer));

        $response->assertRedirect();
        $response->assertSessionHas('info', 'У клиента нет долгов');
    }

    public function test_pay_works_with_transaction()
    {
        $customer = Customer::create([
            'name' => 'Иван Иванов',
            'phone' => '+998901234567',
        ]);

        $sale = Sale::create([
            'customer_id' => $customer->id,
            'status' => 'долг',
            'total_amount' => 300000,
        ]);

        SaleItem::create([
            'sale_id' => $sale->id,
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'quantity' => 1,
            'price_per_unit' => 300000,
            'total_price' => 300000,
        ]);

        // Проверяем что транзакция работает (нет ошибок)
        $response = $this->post(route('debtors.pay', $customer));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Проверяем что статус изменился
        $this->assertDatabaseHas('sales', [
            'id' => $sale->id,
            'status' => 'оплачено',
        ]);
    }
}
