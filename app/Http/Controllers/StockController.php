<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Quantity;
use App\Models\Warehouse;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Http\Requests\Stock\UpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class StockController extends Controller
{
    protected ProductRepositoryInterface $productRepository;

    public function __construct(ProductRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function index(Request $request)
    {
        $warehouses = Warehouse::orderBy('title')->get();
        $selectedWarehouseId = $request->query('warehouse', optional($warehouses->first())->id);
        
        $categories = Category::orderBy('title')->get();
        $selectedCategoryId = $request->query('category');
        
        $stockItems = $this->getStockItems($selectedWarehouseId, $selectedCategoryId);
        
        return view('pages.stock', [
            'warehouses' => $warehouses,
            'selectedWarehouseId' => $selectedWarehouseId,
            'selectedCategoryId' => $selectedCategoryId,
            'categories' => $categories,
            'stockItems' => $stockItems,
        ]);
    }

    public function update(UpdateRequest $request, Quantity $quantity): RedirectResponse
    {
        $quantity->update($request->validated());

        return back()->with('success', 'Количество обновлено');
    }

    public function destroy(Quantity $quantity): RedirectResponse
    {
        $quantity->delete();

        return back()->with('success', 'Товар удалён со склада');
    }

    /**
     * Получить товары на складе с фильтрацией
     */
    private function getStockItems($warehouseId, $categoryId = null)
    {
        if (!$warehouseId) {
            return collect();
        }

        $query = Quantity::with(['product', 'product.category', 'product.car', 'product.position', 'product.color'])
            ->where('warehouse_id', $warehouseId);

        if ($categoryId) {
            $query->whereHas('product', function ($q) use ($categoryId) {
                $q->where('category_id', $categoryId);
            });
        }

        return $query->orderBy('id')->get();
    }
}