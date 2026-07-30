<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\Category;
use App\Models\Product;
use App\Models\Quantity;
use App\Models\Warehouse;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Services\SaleService;
use App\Http\Requests\Sale\AddToCartRequest;
use App\Http\Requests\Sale\CheckoutRequest;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class SalesController extends Controller
{
    private const CART_SESSION_KEY = 'sales_cart';

    protected ProductRepositoryInterface $productRepository;
    protected SaleService $saleService;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        SaleService $saleService
    ) {
        $this->productRepository = $productRepository;
        $this->saleService = $saleService;
    }

    /**
     * Экран 1: выбор машины
     */
    public function selectCar()
    {
        $cars = Car::withCount('products')->orderBy('title')->get();

        return view('pages.sales-cars', compact('cars'));
    }

    /**
     * Экран 2: список запчастей для выбранной машины
     */
    public function carParts(Request $request, Car $car)
    {
        $categories = Category::orderBy('title')->get();
        $selectedCategoryId = $request->query('category');

        $products = Product::with(['category', 'quantities.warehouse'])
            ->where('car_id', $car->id)
            ->when($selectedCategoryId, fn($q) => $q->where('category_id', $selectedCategoryId))
            ->orderBy('title')
            ->get();

        return view('pages.sales-parts', [
            'car' => $car,
            'categories' => $categories,
            'selectedCategoryId' => $selectedCategoryId,
            'products' => $products,
        ]);
    }

    /**
     * Добавить товар в корзину
     */
    public function addToCart(AddToCartRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // Проверяем наличие на складе
        $available = Quantity::where('product_id', $data['product_id'])
            ->where('warehouse_id', $data['warehouse_id'])
            ->value('quantity') ?? 0;

        if ($data['quantity'] > $available) {
            return back()->withErrors([
                'quantity' => 'На складе недостаточно товара (в наличии: ' . $available . ')'
            ]);
        }

        // Добавляем в корзину
        $this->addToCartSession($data);

        return back()->with('success', 'Товар добавлен в корзину');
    }

    /**
     * Экран 3: корзина
     */
    public function cart()
    {
        $cart = session(self::CART_SESSION_KEY, []);
        $items = [];
        $total = 0;

        if (!empty($cart)) {
            $result = $this->buildCartItems($cart);
            $items = $result['items'];
            $total = $result['total'];
        }

        return view('pages.sales-cart', [
            'items' => $items,
            'total' => $total,
        ]);
    }

    /**
     * Убрать позицию из корзины
     */
    public function removeFromCart(string $key): RedirectResponse
    {
        $cart = session(self::CART_SESSION_KEY, []);
        unset($cart[$key]);
        session([self::CART_SESSION_KEY => $cart]);

        return back()->with('success', 'Товар убран из корзины');
    }

    /**
     * Обновить количество в корзине
     */
    public function updateCartItem(Request $request, string $key): RedirectResponse
    {
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $cart = session(self::CART_SESSION_KEY, []);

        if (!isset($cart[$key])) {
            return back()->withErrors(['error' => 'Товар не найден в корзине']);
        }

        // Проверяем наличие на складе
        $available = Quantity::where('product_id', $cart[$key]['product_id'])
            ->where('warehouse_id', $cart[$key]['warehouse_id'])
            ->value('quantity') ?? 0;

        if ($data['quantity'] > $available) {
            return back()->withErrors([
                'quantity' => 'На складе недостаточно товара (в наличии: ' . $available . ')'
            ]);
        }

        $cart[$key]['quantity'] = $data['quantity'];
        session([self::CART_SESSION_KEY => $cart]);

        return back()->with('success', 'Количество обновлено');
    }

    /**
     * Оформление продажи
     */
    public function checkout(CheckoutRequest $request): RedirectResponse
    {
        $cart = session(self::CART_SESSION_KEY, []);

        if (empty($cart)) {
            return back()->withErrors(['cart' => 'Корзина пуста']);
        }

        $data = $request->validated();

        // Проверяем цены на складе
        $errors = $this->validatePrices($cart, $data['prices'] ?? []);
        if (!empty($errors)) {
            return back()->withErrors($errors);
        }

        // Применяем новые цены
        $cart = $this->applyPrices($cart, $data['prices'] ?? []);

        // Создаем продажу через сервис
        $this->saleService->createSale($cart, $data);

        // Очищаем корзину
        session()->forget(self::CART_SESSION_KEY);

        return redirect()->route('sales.cars')->with('success', 'Продажа оформлена');
    }

    // ==================== PRIVATE METHODS ====================

    /**
     * Добавить товар в корзину (сессия)
     */
    private function addToCartSession(array $data): void
    {
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

        $cart[$key]['price_per_unit'] = $data['price_per_unit'];

        session([self::CART_SESSION_KEY => $cart]);
    }

    /**
     * Собрать товары корзины для отображения
     */
    private function buildCartItems(array $cart): array
    {
        $productIds = collect($cart)->pluck('product_id')->unique();
        $warehouseIds = collect($cart)->pluck('warehouse_id')->unique();

        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');
        $warehouses = Warehouse::whereIn('id', $warehouseIds)->get()->keyBy('id');

        $items = [];
        $total = 0;

        foreach ($cart as $key => $row) {
            $product = $products->get($row['product_id']);
            if (!$product) {
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

        return ['items' => $items, 'total' => $total];
    }

    /**
     * Валидация цен (не ниже себестоимости)
     */
    private function validatePrices(array $cart, array $prices): array
    {
        $errors = [];
        $productIds = collect($cart)->pluck('product_id')->unique();
        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

        foreach ($cart as $key => $row) {
            $price = $prices[$key] ?? $row['price_per_unit'];
            $product = $products->get($row['product_id']);

            if ($product && $price < $product->cost_price) {
                $errors['prices.' . $key] = 'Цена на «' . $product->title . '» не может быть ниже себестоимости (' 
                    . number_format($product->cost_price, 0, ',', ' ') . ' сум)';
            }
        }

        return $errors;
    }

    /**
     * Применить новые цены к корзине
     */
    private function applyPrices(array $cart, array $prices): array
    {
        foreach ($cart as $key => &$row) {
            if (isset($prices[$key])) {
                $row['price_per_unit'] = $prices[$key];
            }
        }

        return $cart;
    }
}