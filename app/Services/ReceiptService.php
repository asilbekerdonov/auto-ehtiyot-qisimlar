<?php

namespace App\Services;

use App\Models\Quantity;
use App\Models\Product;
use App\Models\Warehouse;

class ReceiptService
{
    /**
     * Добавить поступление товара на склад
     */
    public function addStock(int $productId, int $warehouseId, int $quantity): Quantity
    {
        $stock = Quantity::firstOrNew([
            'product_id' => $productId,
            'warehouse_id' => $warehouseId,
        ]);

        $stock->quantity = ($stock->quantity ?? 0) + $quantity;
        $stock->save();

        return $stock;
    }

    /**
     * Получить остатки товара на складе
     */
    public function getStock(int $productId, int $warehouseId): int
    {
        return Quantity::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->value('quantity') ?? 0;
    }

    /**
     * Получить остатки товара по всем складам
     */
    public function getStocks(int $productId): array
    {
        return Quantity::where('product_id', $productId)
            ->with('warehouse')
            ->get()
            ->map(fn($q) => [
                'warehouse' => $q->warehouse->title,
                'quantity' => $q->quantity,
            ])
            ->toArray();
    }

    /**
     * Проверить, есть ли товар на складе
     */
    public function hasStock(int $productId, int $warehouseId): bool
    {
        return $this->getStock($productId, $warehouseId) > 0;
    }

    /**
     * Обновить остаток товара на складе
     */
    public function updateStock(int $productId, int $warehouseId, int $quantity): Quantity
    {
        $stock = Quantity::firstOrNew([
            'product_id' => $productId,
            'warehouse_id' => $warehouseId,
        ]);

        $stock->quantity = $quantity;
        $stock->save();

        return $stock;
    }
}