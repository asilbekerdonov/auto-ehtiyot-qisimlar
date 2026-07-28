<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\Category;
use App\Models\Unit;
use App\Models\Warehouse;

class AddController extends Controller
{
    public function index()
    {
        return view('pages.add', [
            'categories' => Category::latest()->get(),
            'warehouses' => Warehouse::latest()->get(),
            'cars' => Car::latest()->get(),
            'units' => Unit::orderBy('title')->get(),
        ]);
    }
}