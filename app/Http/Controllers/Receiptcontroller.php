<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\Category;
use App\Models\Product;
use App\Models\Quantity;
use App\Models\Warehouse;
use App\Services\ReceiptService;
use App\Http\Requests\Receipt\AddStockRequest;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class ReceiptController extends Controller
{
    protected ReceiptService $receiptService;

    public function __construct(ReceiptService $receiptService)
    {
        $this->receiptService = $receiptService;
    }

    /**
     * Экран 1: выбор машины
     */
    public function selectCar()
    {
        $cars = Car::orderBy('title')->get();

        return view('pages.receipts-cars', compact('cars'));
    }

    /**
     * Экран 2: список запчастей машины с остатками по складам
     */
    public function carParts(Request $request, Car $car)
    {
        $categories = Category::orderBy('title')->get();
        $selectedCategoryId = $request->query('category');
        $warehouses = Warehouse::orderBy('title')->get();

        $products = Product::with(['category', 'quantities'])
            ->where('car_id', $car->id)
            ->when($selectedCategoryId, fn($q) => $q->where('category_id', $selectedCategoryId))
            ->orderBy('title')
            ->get();

        return view('pages.receipts-parts', [
            'car' => $car,
            'categories' => $categories,
            'selectedCategoryId' => $selectedCategoryId,
            'products' => $products,
            'warehouses' => $warehouses,
        ]);
    }

    /**
     * Добавить поступление товара на склад
     */
    public function addStock(AddStockRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $this->receiptService->addStock(
            $data['product_id'],
            $data['warehouse_id'],
            $data['quantity']
        );

        return back()->with('success', 'Поступление добавлено: +' . $data['quantity'] . ' шт');
    }
}