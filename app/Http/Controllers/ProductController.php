<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Warehouse;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Services\ProductStockService;
use App\Http\Requests\Product\StoreRequest;
use App\Http\Requests\Product\UpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    protected ProductRepositoryInterface $productRepository;

    public function __construct(ProductRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function store(StoreRequest $request, ProductStockService $stockService): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $request, $stockService) {
            $imagePath = null;

            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('products', 'public');
            }

            $product = $stockService->findOrCreateProduct([
                'title' => $data['title'],
                'category_id' => $data['category_id'],
                'car_id' => $data['car_id'] ?? null,
                'position_id' => $data['position_id'] ?? null,
                'color_id' => $data['color_id'] ?? null,
                'unit_id' => $data['unit_id'] ?? null,
                'cost_price' => $data['cost_price'],
                'markup' => $data['markup'],
                'image' => $imagePath,
            ]);

            $stockService->addStock($product, (int) $data['warehouse_id'], (int) $data['quantity']);
        });

        return back()->with('success', 'Товар добавлен');
    }

    public function update(UpdateRequest $request, Product $product): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($request, $data, $product) {
            if ($request->hasFile('image')) {
                if ($product->image) {
                    Storage::disk('public')->delete($product->image);
                }
                $data['image'] = $request->file('image')->store('products', 'public');
            }

            $this->productRepository->update($product, $data);

            if ($request->has('quantity') && $request->filled('quantity')) {
                $warehouse = Warehouse::first();

                if ($warehouse) {
                    $this->productRepository->updateStock(
                        $product->id,
                        $warehouse->id,
                        (int) $data['quantity']
                    );
                }
            }
        });

        return back()->with('success', 'Товар обновлён');
    }

    public function destroy(Product $product): RedirectResponse
    {
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $this->productRepository->delete($product);

        return back()->with('success', 'Товар удалён');
    }
}