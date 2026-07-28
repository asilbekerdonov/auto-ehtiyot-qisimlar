<?php

namespace App\Http\Controllers;

use App\Models\Car;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class CarController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
        ]);

        Car::create($data);

        return back()->with('success', 'Марка авто добавлена');
    }

    public function destroy(Car $car): RedirectResponse
    {
        $car->delete();

        return back()->with('success', 'Марка авто удалена');
    }
}