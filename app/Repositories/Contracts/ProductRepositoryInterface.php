<?php

namespace App\Repositories\Contracts;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface ProductRepositoryInterface
{
    /**
     * Найти товар по ID
     */
    public function find(int $id): ?Product;

    /**
     * Найти товар по характеристикам
     */
    public function findByAttributes(array $attributes): ?Product;

    /**
     * Создать новый товар
     */
    public function create(array $data): Product;

    /**
     * Обновить товар
     */
    public function update(Product $product, array $data): Product;

    /**
     * Удалить товар
     */
    public function delete(Product $product): bool;

    /**
     * Получить все товары с фильтрацией
     */
    public function getAll(array $filters = []): Collection;

    /**
     * Получить товары по категории
     */
    public function getByCategory(int $categoryId): Collection;

    /**
     * Получить товары по машине
     */
    public function getByCar(int $carId): Collection;

    /**
     * Поиск товаров по названию
     */
    public function search(string $query): Collection;

    /**
     * Получить товары с остатками на складах
     */
    public function getWithStock(array $filters = []): Collection;

    /**
     * Обновить количество товара на складе
     */
    public function updateStock(int $productId, int $warehouseId, int $quantity): bool;
}