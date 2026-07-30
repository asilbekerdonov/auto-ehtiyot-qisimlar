<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes; 

class Product extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'category_id',
        'position_id',
        'color_id',
        'car_id',
        'unit_id',
        'title',
        'cost_price',
        'markup',
        'image',
    ];

    protected function casts(): array
    {
        return [
            'cost_price' => 'decimal:2',
            'markup' => 'decimal:2',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }


    public function saleItems(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\SaleItem::class);
    }
    public function quantities(): HasMany
    {
        return $this->hasMany(Quantity::class);
    }

    // Себестоимость + наценка = цена продажи
    public function getSellingPriceAttribute(): float
    {
        return (float) $this->cost_price + (float) $this->markup;
    }

    // Суммарный остаток по всем складам.
    // Работает без доп. запроса, ЕСЛИ quantities уже подгружены через with('quantities')
    public function getTotalStockAttribute(): int
    {
        return $this->quantities->sum('quantity');
    }
    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    public function color()
    {
        return $this->belongsTo(Color::class);
    }
}