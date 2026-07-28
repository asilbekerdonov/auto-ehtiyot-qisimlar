<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class GoodsController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::orderBy('title')->get();

        $selectedCategoryId = $request->query('category');

        // with('category', 'quantities') — грузим связи одним доп. запросом каждая,
        // а не по одному запросу на каждый товар (это и есть защита от N+1)
        $products = Product::with(['category', 'quantities'])
            ->when($selectedCategoryId, fn ($query) => $query->where('category_id', $selectedCategoryId))
            ->orderBy('title')
            ->get();

        return view('pages.goods', [
            'categories' => $categories,
            'products' => $products,
            'selectedCategoryId' => $selectedCategoryId,
        ]);
    }
}