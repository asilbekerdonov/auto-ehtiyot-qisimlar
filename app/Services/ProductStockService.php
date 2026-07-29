<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Quantity;

class ProductStockService
{
    /**
     * Найти товар с точно такими же характеристиками
     * (название, категория, марка авто, позиция, цвет, ед. измерения),
     * либо создать новый, если такого нет.
     */
    public function findOrCreateProduct(array $data): Product
    {
        $title = trim($data['title']);

        $query = Product::query()
            ->whereRaw('LOWER(title) = ?', [mb_strtolower($title)])
            ->where('category_id', $data['category_id']);

        // Для необязательных полей: если не указано — ищем именно там, где тоже не указано (NULL),
        // а не совпадение "пусто = что угодно"
        foreach (['car_id', 'position_id', 'color_id', 'unit_id'] as $field) {
            if (empty($data[$field])) {
                $query->whereNull($field);
            } else {
                $query->where($field, $data[$field]);
            }
        }

        $existing = $query->first();

        if ($existing) {
            return $existing;
        }

        return Product::create([
            'title' => $title,
            'category_id' => $data['category_id'],
            'car_id' => $data['car_id'] ?? null,
            'position_id' => $data['position_id'] ?? null,
            'color_id' => $data['color_id'] ?? null,
            'unit_id' => $data['unit_id'] ?? null,
            'cost_price' => $data['cost_price'],
            'markup' => $data['markup'],
            'image' => $data['image'] ?? null,
        ]);
    }

    /**
     * Добавить количество на склад. Если товар уже есть на этом складе —
     * прибавляет к текущему остатку, если нет — создаёт новую запись.
     */
    public function addStock(Product $product, int $warehouseId, int $quantity): Quantity
    {
        $stock = Quantity::firstOrNew([
            'product_id' => $product->id,
            'warehouse_id' => $warehouseId,
        ]);

        $stock->quantity = ($stock->quantity ?? 0) + $quantity;
        $stock->save();

        return $stock;
    }
}