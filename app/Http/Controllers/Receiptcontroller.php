<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\Category;
use App\Models\Product;
use App\Models\Quantity;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class ReceiptController extends Controller
{
    // Экран 1: выбор машины (тот же паттерн, что и в "Продажах")
    public function selectCar()
    {
        $cars = Car::orderBy('title')->get();

        return view('pages.receipts-cars', [
            'cars' => $cars,
        ]);
    }

    // Экран 2: список запчастей машины с остатками по каждому складу + карандаш/плюсик
    public function carParts(Request $request, Car $car)
    {
        $categories = Category::orderBy('title')->get();
        $selectedCategoryId = $request->query('category');
        $warehouses = Warehouse::orderBy('title')->get();

        // with(['category', 'quantities']) — защита от N+1:
        // категория и остатки по всем складам для каждого товара грузятся одним доп. запросом
        $products = Product::with(['category', 'quantities'])
            ->where('car_id', $car->id)
            ->when($selectedCategoryId, fn ($q) => $q->where('category_id', $selectedCategoryId))
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

    // Плюсик: прибавить количество к текущему остатку (создаёт строку в quantities, если её ещё не было)
    public function addStock(Request $request)
    {
        $request->merge([
            'quantity' => str_replace(' ', '', (string) $request->input('quantity')),
        ]);

        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $quantity = Quantity::firstOrNew([
            'product_id' => $data['product_id'],
            'warehouse_id' => $data['warehouse_id'],
        ]);

        $quantity->quantity = ($quantity->quantity ?? 0) + $data['quantity'];
        $quantity->save();

        return back()->with('success', 'Поступление добавлено: +' . $data['quantity'] . ' шт');
    }
}