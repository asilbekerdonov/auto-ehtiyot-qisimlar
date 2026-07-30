<?php

namespace App\Repositories;

use App\Models\Product;
use App\Models\Quantity;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ProductRepository implements ProductRepositoryInterface
{
    protected $model;

    public function __construct(Product $model)
    {
        $this->model = $model;
    }

    public function find(int $id): ?Product
    {
        return $this->model->with(['category', 'car', 'position', 'color', 'unit', 'quantities.warehouse'])
            ->find($id);
    }

    public function findByAttributes(array $attributes): ?Product
    {
        $query = $this->model->newQuery();

        foreach ($attributes as $key => $value) {
            if (is_null($value)) {
                $query->whereNull($key);
            } else {
                $query->where($key, $value);
            }
        }

        return $query->first();
    }

    public function create(array $data): Product
    {
        return $this->model->create($data);
    }

    public function update(Product $product, array $data): Product
    {
        $product->update($data);
        return $product->fresh();
    }

    public function delete(Product $product): bool
    {
        // Удаляем связи
        Quantity::where('product_id', $product->id)->delete();
        return $product->delete();
    }

    public function getAll(array $filters = []): Collection
    {
        $query = $this->model->with(['category', 'car', 'position', 'color', 'unit', 'quantities.warehouse']);

        if (isset($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (isset($filters['car_id'])) {
            $query->where('car_id', $filters['car_id']);
        }

        if (isset($filters['search'])) {
            $query->where('title', 'like', '%' . $filters['search'] . '%');
        }

        if (isset($filters['sort_by'])) {
            $query->orderBy($filters['sort_by'], $filters['sort_direction'] ?? 'asc');
        }

        return $query->get();
    }

    public function getByCategory(int $categoryId): Collection
    {
        return $this->model->with(['category', 'car', 'quantities.warehouse'])
            ->where('category_id', $categoryId)
            ->get();
    }

    public function getByCar(int $carId): Collection
    {
        return $this->model->with(['category', 'car', 'quantities.warehouse'])
            ->where('car_id', $carId)
            ->get();
    }

    public function search(string $query): Collection
    {
        return $this->model->with(['category', 'car', 'quantities.warehouse'])
            ->where('title', 'like', '%' . $query . '%')
            ->get();
    }

    public function getWithStock(array $filters = []): Collection
    {
        $query = $this->model->with(['category', 'car', 'position', 'color', 'unit', 'quantities.warehouse']);

        if (isset($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (isset($filters['car_id'])) {
            $query->where('car_id', $filters['car_id']);
        }

        if (isset($filters['search'])) {
            $query->where('title', 'like', '%' . $filters['search'] . '%');
        }

        $products = $query->get();

        // Добавляем total_stock к каждому товару
        $products->each(function ($product) {
            $product->total_stock = $product->quantities->sum('quantity');
        });

        return $products;
    }

    public function updateStock(int $productId, int $warehouseId, int $quantity): bool
    {
        $stock = Quantity::updateOrCreate(
            ['product_id' => $productId, 'warehouse_id' => $warehouseId],
            ['quantity' => $quantity]
        );

        return $stock->wasRecentlyCreated || $stock->wasChanged();
    }
}