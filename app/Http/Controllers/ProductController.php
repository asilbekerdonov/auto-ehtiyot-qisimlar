<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Quantity;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

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
            'car_id' => ['exists:cars,id'],
            'unit_id' => ['exists:units,id'],
            'cost_price' => ['required', 'numeric', 'min:0'],
            'markup' => ['required', 'numeric', 'min:0'],
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'quantity' => ['required', 'integer', 'min:0'],
        ]);

        DB::transaction(function () use ($data) {
            $product = Product::create([
                'title' => $data['title'],
                'category_id' => $data['category_id'],
                'car_id' => $data['car_id'] ,
                'unit_id' => $data['unit_id'] ,
                'cost_price' => $data['cost_price'],
                'markup' => $data['markup'],
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
        $product->delete();

        return back()->with('success', 'Товар удалён');
    }
}