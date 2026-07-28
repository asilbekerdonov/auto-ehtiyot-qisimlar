<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Warehouse extends Model
{
    protected $fillable = ['title'];

    public function quantities(): HasMany
    {
        return $this->hasMany(Quantity::class);
    }
}