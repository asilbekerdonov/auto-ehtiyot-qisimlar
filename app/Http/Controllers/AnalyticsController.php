<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

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

        // --- 1. Товары за выбранный день ---
        $dayData = $this->getDayData($selectedDate);

        // --- 2. Дни с продажами в выбранном месяце ---
        $daysWithSalesInMonth = $this->getDaysWithSalesInMonth($selectedMonth);

        // --- 3. История за последние 12 месяцев ---
        $months = $this->getMonthsHistory();

        return view('pages.analytics', [
            'selectedDate' => $selectedDate,
            'selectedMonth' => $selectedMonth,
            'monthLabel' => self::RU_MONTHS[$selectedMonth->month] . ' ' . $selectedMonth->year,
            'paidItems' => $dayData['paidItems'],
            'debtItems' => $dayData['debtItems'],
            'totalPaid' => $dayData['totalPaid'],
            'totalDebt' => $dayData['totalDebt'],
            'daysWithSalesInMonth' => $daysWithSalesInMonth,
            'months' => $months,
        ]);
    }

    /**
     * Получить данные за день (продажи и долги)
     */
    private function getDayData(Carbon $date): array
    {
        $sales = Sale::with(['items.product.car', 'customer'])
            ->whereDate('created_at', $date)
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

        return [
            'paidItems' => $paidItems,
            'debtItems' => $debtItems,
            'totalPaid' => $paidItems->sum(fn($item) => $item->quantity * $item->price_per_unit),
            'totalDebt' => $debtItems->sum(fn($item) => $item->quantity * $item->price_per_unit),
        ];
    }

    /**
     * Получить дни с продажами в месяце
     */
    private function getDaysWithSalesInMonth(Carbon $month): array
    {
        return Sale::whereBetween('created_at', [
                $month->copy()->startOfMonth(),
                $month->copy()->endOfMonth(),
            ])
            ->get()
            ->map(fn($sale) => (int) $sale->created_at->format('j'))
            ->unique()
            ->values()
            ->toArray();
    }

    /**
     * Получить историю за последние 12 месяцев
     */
    private function getMonthsHistory(): array
    {
        $yearStart = Carbon::now()->subMonths(11)->startOfMonth();
        $yearSales = Sale::whereBetween('created_at', [$yearStart, Carbon::now()->endOfMonth()])->get();

        $months = [];
        $cursor = Carbon::now()->startOfMonth();

        for ($i = 0; $i < 12; $i++) {
            $monthStart = $cursor->copy();

            $salesInMonth = $yearSales->filter(fn($sale) =>
                $sale->created_at->month === $monthStart->month
                && $sale->created_at->year === $monthStart->year
            );

            $months[] = [
                'key' => $monthStart->format('Y-m'),
                'label' => self::RU_MONTHS[$monthStart->month] . ' ' . $monthStart->year,
                'total' => $salesInMonth->sum('total_amount'),
                'sales_count' => $salesInMonth->count(),
                'days_in_month' => $monthStart->daysInMonth,
                'days_with_sales' => $salesInMonth
                    ->map(fn($sale) => (int) $sale->created_at->format('j'))
                    ->unique()
                    ->sort()
                    ->values()
                    ->toArray(),
            ];

            $cursor->subMonth();
        }

        return $months;
    }
}