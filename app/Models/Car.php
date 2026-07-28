<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Car extends Model
{
    protected $fillable = ['title'];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}