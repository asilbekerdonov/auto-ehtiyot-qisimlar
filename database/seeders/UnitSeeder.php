<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        Unit::insert([
            ['title' => 'шт', 'created_at' => now(), 'updated_at' => now()],
            ['title' => 'комплект', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}