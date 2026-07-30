<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DebtorController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        // Клиенты, у которых есть хотя бы одна продажа со статусом "долг".
        // with(['sales' => ...]) грузит долговые продажи одним доп. запросом
        // (защита от N+1), дальше total_debt/debt_count считаются в PHP
        // по уже загруженной коллекции — без лишних запросов к БД.
        $debtors = Customer::whereHas('sales', fn ($query) => $query->where('status', 'долг'))
            ->with(['sales' => fn ($query) => $query->where('status', 'долг')])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                      ->orWhere('phone', 'like', '%' . $search . '%');
                });
            })
            ->orderBy('name')
            ->get()
            ->map(function ($customer) {
                $customer->total_debt = $customer->sales->sum('total_amount');
                $customer->debt_count = $customer->sales->count();

                return $customer;
            });

        return view('pages.debtors', compact('debtors'));
    }

    public function pay(Customer $customer): RedirectResponse
    {
        return DB::transaction(function () use ($customer) {
            // update() возвращает количество затронутых строк — не нужен доп. запрос,
            // чтобы узнать, были ли у клиента вообще долги
            $affected = $customer->sales()->where('status', 'долг')->update(['status' => 'оплачено']);

            if ($affected === 0) {
                return back()->with('info', 'У клиента нет долгов');
            }

            return back()->with('success', 'Долг клиента «' . $customer->name . '» погашен');
        });
    }
}