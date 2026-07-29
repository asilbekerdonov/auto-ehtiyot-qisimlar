<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Quantity;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesController extends Controller
{
    private const CART_SESSION_KEY = 'sales_cart';

    // Экран 1: выбор машины
    public function selectCar()
    {
        $cars = Car::withCount('products')->orderBy('title')->get();

        return view('pages.sales-cars', [
            'cars' => $cars,
        ]);
    }

    // Экран 2: список запчастей для выбранной машины
    public function carParts(Request $request, Car $car)
    {
        $categories = Category::orderBy('title')->get();
        $selectedCategoryId = $request->query('category');

        // with(['category', 'quantities.warehouse']) — защита от N+1:
        // категория и остатки по складам для каждого товара грузятся одним доп. запросом
        $products = Product::with(['category', 'quantities.warehouse'])
            ->where('car_id', $car->id)
            ->when($selectedCategoryId, fn ($q) => $q->where('category_id', $selectedCategoryId))
            ->orderBy('title')
            ->get();

        return view('pages.sales-parts', [
            'car' => $car,
            'categories' => $categories,
            'selectedCategoryId' => $selectedCategoryId,
            'products' => $products,
        ]);
    }

    // Добавить товар в корзину (корзина хранится в сессии)
    public function addToCart(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'price_per_unit' => ['required', 'numeric', 'min:0'],
            'car_id' => ['required', 'exists:cars,id'], // чтобы вернуться на ту же машину
        ]);

        $available = Quantity::where('product_id', $data['product_id'])
            ->where('warehouse_id', $data['warehouse_id'])
            ->value('quantity') ?? 0;

        if ($data['quantity'] > $available) {
            return back()->withErrors(['quantity' => 'На складе недостаточно товара (в наличии: ' . $available . ')']);
        }

        $cart = session(self::CART_SESSION_KEY, []);
        $key = $data['product_id'] . '_' . $data['warehouse_id'];

        if (isset($cart[$key])) {
            $cart[$key]['quantity'] += $data['quantity'];
        } else {
            $cart[$key] = [
                'product_id' => $data['product_id'],
                'warehouse_id' => $data['warehouse_id'],
                'quantity' => $data['quantity'],
            ];
        }

        // цену с торгом всегда берём последнюю введённую
        $cart[$key]['price_per_unit'] = $data['price_per_unit'];

        session([self::CART_SESSION_KEY => $cart]);

        return back()->with('success', 'Товар добавлен в корзину');
    }

    // Экран 3: корзина (отдельная страница)
    public function cart()
    {
        $cart = session(self::CART_SESSION_KEY, []);
        $items = [];
        $total = 0;

        if (! empty($cart)) {
            $productIds = collect($cart)->pluck('product_id')->unique();
            $warehouseIds = collect($cart)->pluck('warehouse_id')->unique();

            $products = Product::whereIn('id', $productIds)->get()->keyBy('id');
            $warehouses = Warehouse::whereIn('id', $warehouseIds)->get()->keyBy('id');

            foreach ($cart as $key => $row) {
                $product = $products->get($row['product_id']);
                if (! $product) {
                    continue;
                }

                $lineTotal = $row['quantity'] * $row['price_per_unit'];
                $total += $lineTotal;

                $items[] = [
                    'key' => $key,
                    'product' => $product,
                    'warehouse' => $warehouses->get($row['warehouse_id']),
                    'quantity' => $row['quantity'],
                    'price_per_unit' => $row['price_per_unit'],
                    'line_total' => $lineTotal,
                ];
            }
        }

        return view('pages.sales-cart', [
            'items' => $items,
            'total' => $total,
        ]);
    }

    // Убрать позицию из корзины
    public function removeFromCart(string $key)
    {
        $cart = session(self::CART_SESSION_KEY, []);
        unset($cart[$key]);
        session([self::CART_SESSION_KEY => $cart]);

        return back()->with('success', 'Товар убран из корзины');
    }

    // Оформление продажи: создаёт Sale + SaleItems, списывает остатки,
    // при статусе "долг" — создаёт клиента через мини-форму имя+телефон
    public function checkout(Request $request)
    {
        $cart = session(self::CART_SESSION_KEY, []);

        if (empty($cart)) {
            return back()->withErrors(['cart' => 'Корзина пуста']);
        }

        $data = $request->validate([
            'status' => ['required', 'in:оплачено,долг'],
            'customer_name' => ['required_if:status,долг', 'nullable', 'string', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:50'],
        ]);

        DB::transaction(function () use ($cart, $data) {
            $customerId = null;

            if ($data['status'] === 'долг') {
                $customer = Customer::create([
                    'name' => $data['customer_name'],
                    'phone' => $data['customer_phone'] ?? null,
                ]);
                $customerId = $customer->id;
            }

            $total = 0;
            foreach ($cart as $row) {
                $total += $row['quantity'] * $row['price_per_unit'];
            }

            $sale = Sale::create([
                'customer_id' => $customerId,
                'status' => $data['status'],
                'total_amount' => $total,
            ]);

            foreach ($cart as $row) {
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $row['product_id'],
                    'warehouse_id' => $row['warehouse_id'],
                    'quantity' => $row['quantity'],
                    'price_per_unit' => $row['price_per_unit'],
                    'total_price' => $row['quantity'] * $row['price_per_unit'],
                ]);

                // списываем остаток со склада
                Quantity::where('product_id', $row['product_id'])
                    ->where('warehouse_id', $row['warehouse_id'])
                    ->decrement('quantity', $row['quantity']);
            }
        });

        session()->forget(self::CART_SESSION_KEY);

        return redirect()->route('sales.cars')->with('success', 'Продажа оформлена');
    }
}