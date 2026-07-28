<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Http\Request;

class GoodsController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::orderBy('title')->get();
        $cars = Car::orderBy('title')->get();
        $units = Unit::orderBy('title')->get();
        $selectedCategoryId = $request->query('category');
        $search = trim((string) $request->query('search', ''));

        $products = Product::with(['category', 'quantities'])
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
            'products' => $products,
            'selectedCategoryId' => $selectedCategoryId,
            'search' => $search,
        ]);
    }
}