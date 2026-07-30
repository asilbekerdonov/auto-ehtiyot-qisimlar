<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    private const RU_MONTHS = [
        1 => 'Январь', 2 => 'Февраль', 3 => 'Март', 4 => 'Апрель',
        5 => 'Май', 6 => 'Июнь', 7 => 'Июль', 8 => 'Август',
        9 => 'Сентябрь', 10 => 'Октябрь', 11 => 'Ноябрь', 12 => 'Декабрь',
    ];

    public function index(Request $request)
    {
        $selectedDate = $request->query('date')
            ? Carbon::parse($request->query('date'))
            : Carbon::today();

        $selectedMonth = $request->query('month')
            ? Carbon::parse($request->query('month') . '-01')
            : $selectedDate->copy()->startOfMonth();

        // --- Товары за выбранный день ---
        // with('items.product.car') — грузим позиции, товары и их машины одним доп. запросом каждая,
        // а не отдельным запросом на каждую продажу/товар (защита от N+1)
        $sales = Sale::with(['items.product.car', 'customer'])
            ->whereDate('created_at', $selectedDate)
            ->get();

        $paidItems = collect();
        $debtItems = collect();

        foreach ($sales as $sale) {
            foreach ($sale->items as $item) {
                $item->sale_status = $sale->status;
                $item->sale_customer = $sale->customer;

                if ($sale->status === 'долг') {
                    $debtItems->push($item);
                } else {
                    $paidItems->push($item);
                }
            }
        }

        // --- Дни с продажами в выбранном месяце (для точек в календаре) ---
        $monthSalesForCalendar = Sale::whereBetween('created_at', [
            $selectedMonth->copy()->startOfMonth(),
            $selectedMonth->copy()->endOfMonth(),
        ])->get();

        $daysWithSalesInMonth = $monthSalesForCalendar
            ->map(fn ($sale) => (int) $sale->created_at->format('j'))
            ->unique()
            ->values()
            ->toArray();

        // --- История за последние 12 месяцев, сворачиваемая ---
        // Один запрос на весь год, дальше группируем в PHP — не 12 отдельных запросов
        $yearStart = Carbon::now()->subMonths(11)->startOfMonth();
        $yearSales = Sale::whereBetween('created_at', [$yearStart, Carbon::now()->endOfMonth()])->get();

        $months = [];
        $cursor = Carbon::now()->startOfMonth();

        for ($i = 0; $i < 12; $i++) {
            $monthStart = $cursor->copy();

            $salesInMonth = $yearSales->filter(fn ($sale) =>
                $sale->created_at->month === $monthStart->month
                && $sale->created_at->year === $monthStart->year
            );

            $daysWithSales = $salesInMonth
                ->map(fn ($sale) => (int) $sale->created_at->format('j'))
                ->unique()
                ->sort()
                ->values()
                ->toArray();

            $months[] = [
                'key' => $monthStart->format('Y-m'),
                'label' => self::RU_MONTHS[$monthStart->month] . ' ' . $monthStart->year,
                'total' => $salesInMonth->sum('total_amount'),
                'sales_count' => $salesInMonth->count(),
                'days_in_month' => $monthStart->daysInMonth,
                'days_with_sales' => $daysWithSales,
            ];

            $cursor->subMonth();
        }

        return view('pages.analytics', [
            'selectedDate' => $selectedDate,
            'selectedMonth' => $selectedMonth,
            'monthLabel' => self::RU_MONTHS[$selectedMonth->month] . ' ' . $selectedMonth->year,
            'paidItems' => $paidItems,
            'debtItems' => $debtItems,
            'totalPaid' => $paidItems->sum(fn ($item) => $item->quantity * $item->price_per_unit),
            'totalDebt' => $debtItems->sum(fn ($item) => $item->quantity * $item->price_per_unit),
            'daysWithSalesInMonth' => $daysWithSalesInMonth,
            'months' => $months,
        ]);
    }
}