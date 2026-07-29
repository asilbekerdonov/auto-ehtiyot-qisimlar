<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PositionColorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Position::insert([
            ['title' => 'Передняя'],
            ['title' => 'Задняя'],
            ['title' => 'L'],
            ['title' => 'R'],
        ]);

        \App\Models\Color::insert([
            ['title' => 'Белый'],
            ['title' => 'JK2'],
        ]);
    }
}
