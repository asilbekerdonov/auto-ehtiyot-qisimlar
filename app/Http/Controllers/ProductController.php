<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Quantity;
use App\Models\Warehouse;
use App\Services\ProductStockService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Создание нового товара с использованием ProductStockService
     */
    public function store(Request $request, ProductStockService $stockService): RedirectResponse
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
            'car_id' => ['nullable', 'exists:cars,id'],
            'unit_id' => ['nullable', 'exists:units,id'],
            'cost_price' => ['required', 'numeric', 'min:0'],
            'position_id' => 'nullable|exists:positions,id',
            'color_id' => 'nullable|exists:colors,id',
            'markup' => ['required', 'numeric', 'min:0'],
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'quantity' => ['required', 'integer', 'min:0'],
            'image' => ['nullable', 'image', 'max:4096'], // до 4 МБ
        ]);

        DB::transaction(function () use ($data, $request, $stockService) {
            $imagePath = null;

            if ($request->hasFile('image')) {
                // сохраняется в storage/app/public/products, доступно через /storage/products/...
                $imagePath = $request->file('image')->store('products', 'public');
            }

            // Используем ProductStockService для поиска или создания товара
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

            // Добавляем количество на склад через сервис (прибавляет к существующему)
            $stockService->addStock($product, (int) $data['warehouse_id'], (int) $data['quantity']);
        });

        return back()->with('success', 'Товар добавлен');
    }

    // Остальные методы (update, destroy) остаются без изменений

    /**
     * Обновление карточки товара из модального окна "Изменить" на странице Товары.
     * 
     * Теперь также обновляет количество на складе.
     */
    public function update(Request $request, Product $product): RedirectResponse
    {
        // Убираем пробелы из чисел
        $request->merge([
            'cost_price' => str_replace(' ', '', (string) $request->input('cost_price')),
            'markup' => str_replace(' ', '', (string) $request->input('markup')),
            'quantity' => str_replace(' ', '', (string) $request->input('quantity')),
        ]);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'car_id' => ['nullable', 'exists:cars,id'],
            'unit_id' => ['nullable', 'exists:units,id'],
            'color_id' => 'nullable|exists:colors,id',
            'position_id' => 'nullable|exists:positions,id',
            'cost_price' => ['required', 'numeric', 'min:0'],
            'markup' => ['required', 'numeric', 'min:0'],
            'quantity' => ['nullable', 'integer', 'min:0'], 
            'image' => ['nullable', 'image', 'max:4096'],
        ]);

        DB::transaction(function () use ($request, $data, $product) {
            // Обработка изображения
            if ($request->hasFile('image')) {
                if ($product->image) {
                    Storage::disk('public')->delete($product->image);
                }
                $data['image'] = $request->file('image')->store('products', 'public');
            }

            // Обновляем основные данные товара
            $product->update($data);

            // Обновляем количество на складе
            if ($request->has('quantity') && $request->filled('quantity')) {
                // Получаем первый склад (или можно использовать конкретный склад)
                $warehouse = Warehouse::first();
                
                if ($warehouse) {
                    // Ищем существующую запись количества
                    $quantity = Quantity::where('product_id', $product->id)
                        ->where('warehouse_id', $warehouse->id)
                        ->first();
                    
                    if ($quantity) {
                        // Обновляем существующую запись
                        $quantity->update([
                            'quantity' => $data['quantity']
                        ]);
                    } else {
                        // Создаем новую запись
                        Quantity::create([
                            'product_id' => $product->id,
                            'warehouse_id' => $warehouse->id,
                            'quantity' => $data['quantity'],
                        ]);
                    }
                }
            }
        });

        return back()->with('success', 'Товар обновлён');
    }

    /**
     * Удаление товара
     */
    public function destroy(Product $product): RedirectResponse
    {
        // Удаляем связанные количества
        Quantity::where('product_id', $product->id)->delete();
        
        // Удаляем изображение
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return back()->with('success', 'Товар удалён');
    }
}