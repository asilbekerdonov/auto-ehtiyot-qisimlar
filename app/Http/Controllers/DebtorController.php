<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\RedirectResponse;

class DebtorController extends Controller
{
    public function index()
    {
        // Клиенты, у которых есть хотя бы одна продажа со статусом "долг".
        // sales подгружаем полностью (не только "долг"), чтобы посчитать
        // дату последней продажи любого статуса.
        $debtors = Customer::whereHas('sales', fn ($query) => $query->where('status', 'долг'))
            ->with('sales')
            ->orderBy('name')
            ->get();

        return view('pages.debtors', compact('debtors'));
    }

    public function pay(Customer $customer): RedirectResponse
    {
        // Гасим весь долг клиента разом: все его продажи со статусом "долг" → "оплачено"
        $customer->sales()->where('status', 'долг')->update(['status' => 'оплачено']);

        return back()->with('success', 'Долг клиента «' . $customer->name . '» погашен');
    }
}