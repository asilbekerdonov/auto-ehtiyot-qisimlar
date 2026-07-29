<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $fillable = ['name', 'phone'];

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    // Сумма непогашенного долга по всем продажам этого клиента со статусом "долг"
    public function getDebtAmountAttribute(): float
    {
        return (float) $this->sales()->where('status', 'долг')->sum('total_amount');
    }
}