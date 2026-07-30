<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\Category;
use App\Models\Position;
use App\Models\Color;
use App\Models\Unit;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Http\Request;
use App\Services\ProductStockService;

class GoodsController extends Controller
{
    protected ProductRepositoryInterface $productRepository;

    public function __construct(ProductRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function index(Request $request)
    {
        $categories = Category::orderBy('title')->get();
        $cars = Car::orderBy('title')->get();
        $units = Unit::orderBy('title')->get();
        $positions = Position::orderBy('id')->get();
        $colors = Color::orderBy('id')->get();

        $selectedCategoryId = $request->query('category');
        $search = trim((string) $request->query('search', ''));

        // Используем репозиторий для получения товаров с остатками
        $products = $this->productRepository->getWithStock([
            'category_id' => $selectedCategoryId,
            'search' => $search,
        ]);

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


    public function store(StoreRequest $request, ProductStockService $stockService)
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $product = $stockService->findOrCreateProduct($data);
        $stockService->addStock($product, (int) $data['warehouse_id'], (int) $data['quantity']);

        return redirect()->route('goods')->with('success', 'Товар сохранён');
    }
}