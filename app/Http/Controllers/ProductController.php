<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Quantity;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        // На фронте цифры показываются с пробелами (400 000) для читаемости,
        // здесь убираем пробелы перед валидацией/сохранением — в БД пробелов быть не должно
        $request->merge([
            'cost_price' => str_replace(' ', '', (string) $request->input('cost_price')),
            'markup' => str_replace(' ', '', (string) $request->input('markup')),
            'quantity' => str_replace(' ', '', (string) $request->input('quantity')),
        ]);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'car_id' => [ 'exists:cars,id'],
            'unit_id' => [ 'exists:units,id'],
            'cost_price' => ['required', 'numeric', 'min:0'],
            'markup' => ['required', 'numeric', 'min:0'],
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'quantity' => ['required', 'integer', 'min:0'],
            'image' => ['nullable', 'image', 'max:4096'], // до 4 МБ
        ]);

        DB::transaction(function () use ($data, $request) {
            $imagePath = null;

            if ($request->hasFile('image')) {
                // сохраняется в storage/app/public/products, доступно через /storage/products/...
                $imagePath = $request->file('image')->store('products', 'public');
            }

            $product = Product::create([
                'title' => $data['title'],
                'category_id' => $data['category_id'],
                'car_id' => $data['car_id'] ?? null,
                'unit_id' => $data['unit_id'] ?? null,
                'cost_price' => $data['cost_price'],
                'markup' => $data['markup'],
                'image' => $imagePath,
            ]);

            // Остаток на выбранном складе создаётся/обновляется той же операцией
            Quantity::updateOrCreate(
                [
                    'warehouse_id' => $data['warehouse_id'],
                    'product_id' => $product->id,
                ],
                [
                    'quantity' => $data['quantity'],
                ]
            );
        });

        return back()->with('success', 'Товар добавлен');
    }

    public function destroy(Product $product): RedirectResponse
    {
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return back()->with('success', 'Товар удалён');
    }
}