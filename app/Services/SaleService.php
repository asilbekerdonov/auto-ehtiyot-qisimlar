<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Quantity;
use Illuminate\Support\Facades\DB;

class SaleService
{
    /**
     * Создать продажу
     */
    public function createSale(array $cart, array $data): Sale
    {
        return DB::transaction(function () use ($cart, $data) {
            // Создаем клиента (если долг)
            $customerId = null;
            if ($data['status'] === 'долг') {
                $customer = Customer::create([
                    'name' => $data['customer_name'],
                    'phone' => $data['customer_phone'] ?? null,
                ]);
                $customerId = $customer->id;
            }

            // Считаем общую сумму
            $total = collect($cart)->sum(fn($row) => $row['quantity'] * $row['price_per_unit']);

            // Создаем продажу
            $sale = Sale::create([
                'customer_id' => $customerId,
                'status' => $data['status'],
                'total_amount' => $total,
            ]);

            // Создаем позиции продажи и списываем остатки
            foreach ($cart as $row) {
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $row['product_id'],
                    'warehouse_id' => $row['warehouse_id'],
                    'quantity' => $row['quantity'],
                    'price_per_unit' => $row['price_per_unit'],
                    'total_price' => $row['quantity'] * $row['price_per_unit'],
                ]);

                // Списываем со склада
                Quantity::where('product_id', $row['product_id'])
                    ->where('warehouse_id', $row['warehouse_id'])
                    ->decrement('quantity', $row['quantity']);
            }

            return $sale;
        });
    }

    /**
     * Проверить наличие товара на складе
     */
    public function checkAvailability(int $productId, int $warehouseId, int $quantity): bool
    {
        $available = Quantity::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->value('quantity') ?? 0;

        return $quantity <= $available;
    }

    /**
     * Получить доступное количество
     */
    public function getAvailableQuantity(int $productId, int $warehouseId): int
    {
        return Quantity::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->value('quantity') ?? 0;
    }
}