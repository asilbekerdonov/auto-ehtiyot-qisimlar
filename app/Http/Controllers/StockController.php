<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Quantity;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function index(Request $request)
    {
        $warehouses = Warehouse::orderBy('title')->get();
        $selectedWarehouseId = $request->query('warehouse', optional($warehouses->first())->id);
        
        // Получаем категории для фильтрации
        $categories = Category::orderBy('title')->get();
        $selectedCategoryId = $request->query('category');
        
        $stockItems = collect();
        
        if ($selectedWarehouseId) {
            $stockItems = Quantity::with(['product', 'product.category'])
                ->where('warehouse_id', $selectedWarehouseId)
                ->when($selectedCategoryId, function ($query) use ($selectedCategoryId) {
                    $query->whereHas('product', function ($q) use ($selectedCategoryId) {
                        $q->where('category_id', $selectedCategoryId);
                    });
                })
                ->orderBy('id')
                ->get();
        }
        
        return view('pages.stock', [
            'warehouses' => $warehouses,
            'selectedWarehouseId' => $selectedWarehouseId,
            'selectedCategoryId' => $selectedCategoryId,
            'categories' => $categories,
            'stockItems' => $stockItems,
        ]);
    }
}