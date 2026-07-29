<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Http\Request;
use App\Models\Position;
use App\Models\Color;
use App\Services\ProductStockService;

class GoodsController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::orderBy('title')->get();
        $cars = Car::orderBy('title')->get();
        $units = Unit::orderBy('title')->get();
        $positions = Position::orderBy('id')->get();
        $colors = Color::orderBy('id')->get();

        $selectedCategoryId = $request->query('category');
        $search = trim((string) $request->query('search', ''));

        $products = Product::with(['category', 'quantities', 'position', 'color', 'car'])
            ->when($selectedCategoryId, fn ($query) => $query->where('category_id', $selectedCategoryId))
            ->when($search !== '', fn ($query) => $query->where('title', 'like', '%' . $search . '%'))
            ->orderBy('title')
            ->get();

        // Добавляем total_stock к каждому продукту
        $products->each(function ($product) {
            $product->total_stock = $product->quantities->sum('quantity');
        });

        if ($request->ajax()) {
            return view('partials.goods-products', [
                'products' => $products,
                'search' => $search,
            ]);
        }

        return view('pages.goods', [
            'categories' => $categories,
            'cars' => $cars,
            'units' => $units,
            'positions' => $positions,
            'colors' => $colors,
            'products' => $products,
            'selectedCategoryId' => $selectedCategoryId,
            'search' => $search,
            
        ]);
    }


    public function store(Request $request, ProductStockService $stockService)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'car_id' => 'nullable|exists:cars,id',
            'position_id' => 'nullable|exists:positions,id',
            'color_id' => 'nullable|exists:colors,id',
            'unit_id' => 'nullable|exists:units,id',
            'cost_price' => 'required|numeric',
            'markup' => 'required|numeric',
            'warehouse_id' => 'required|exists:warehouses,id',
            'quantity' => 'required|integer|min:1',
            'image' => 'nullable|image|max:5120',
    ]);

    if ($request->hasFile('image')) {
        $validated['image'] = $request->file('image')->store('products', 'public');
    }

    $product = $stockService->findOrCreateProduct($validated);
    $stockService->addStock($product, (int) $validated['warehouse_id'], (int) $validated['quantity']);

    return redirect()->route('goods')->with('success', 'Товар сохранён');
    }
}