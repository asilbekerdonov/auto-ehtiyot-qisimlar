@extends('layouts.app')

@section('title', 'Аналитика — Автозапчасти')

@section('styles')
<style>
/* ===== Основная сетка ===== */
.analytics-layout {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 24px;
    align-items: start;
}

/* ===== Левая колонка ===== */
.analytics-date-header {
    font-size: 24px;
    font-weight: 600;
    color: #024989;
    margin-bottom: 20px;
}
.analytics-date-header .today-badge {
    font-size: 16px;
    font-weight: 400;
    color: #555;
}

.analytics-block {
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 20px;
}
.analytics-block--sales {
    border: 2px solid #024989;
}
.analytics-block--debt {
    border: 2px solid #a32d2d;
}

.analytics-block-title {
    font-size: 20px;
    font-weight: 600;
    margin-bottom: 14px;
}
.analytics-block-title--sales {
    color: #024989;
}
.analytics-block-title--debt {
    color: #a32d2d;
}

.analytics-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 0;
    border-bottom: 1px solid #e2e2e2;
    font-size: 18px;
}
.analytics-item--debt {
    border-bottom-color: #f0dede;
}
.analytics-item-title {
    font-weight: 500;
}
.analytics-item-sub {
    font-size: 14px;
    color: #777;
}
.analytics-item-price {
    text-align: right;
}
.analytics-item-total {
    font-weight: 600;
}
.analytics-item-total--sales {
    color: #024989;
}
.analytics-item-total--debt {
    color: #a32d2d;
}

.analytics-total-row {
    display: flex;
    justify-content: space-between;
    padding-top: 14px;
    font-size: 20px;
    font-weight: 700;
}
.analytics-total-row--sales {
    color: #024989;
}
.analytics-total-row--debt {
    color: #a32d2d;
}

.analytics-empty {
    font-size: 17px;
    color: #555;
}

/* ===== Правая колонка ===== */
.analytics-calendar {
    border: 2px solid #024989;
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 20px;
}

.analytics-month-nav {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 20px;
}
.analytics-month-nav .nav-btn {
    font-size: 22px;
    color: #024989;
    text-decoration: none;
    padding: 6px 14px;
    border: 2px solid #024989;
    border-radius: 8px;
    line-height: 1;
}
.analytics-month-nav .month-label {
    font-size: 22px;
    font-weight: 600;
    color: #024989;
}

.analytics-days-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 8px;
}
.analytics-day-cell {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 2px;
    padding: 10px 0;
    border-radius: 8px;
    text-decoration: none;
    background: #f4f8fb;
    color: #111;
}
.analytics-day-cell--selected {
    background: #024989;
    color: #fff;
}
.analytics-day-cell .day-number {
    font-size: 17px;
}
.analytics-day-cell .day-number--selected {
    font-weight: 700;
}
.analytics-day-cell .day-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #024989;
}
.analytics-day-cell .day-dot--selected {
    background: #fff;
}

.analytics-history {
    border: 2px solid #024989;
    border-radius: 10px;
    padding: 20px;
}
.analytics-history-title {
    font-size: 20px;
    font-weight: 600;
    color: #024989;
    margin-bottom: 14px;
}

.analytics-month-details {
    border-bottom: 1px solid #e2e2e2;
    padding: 10px 0;
}
.analytics-month-summary {
    cursor: pointer;
    font-size: 18px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    list-style: none;
}
.analytics-month-summary::-webkit-details-marker {
    display: none;
}
.analytics-month-summary .month-name {
    font-weight: 500;
}
.analytics-month-summary .month-total {
    color: #024989;
    font-weight: 600;
}

.analytics-month-days {
    margin-top: 12px;
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}
.analytics-month-days .day-link {
    padding: 6px 12px;
    font-size: 15px;
    border-radius: 8px;
    border: 2px solid #024989;
    color: #024989;
    text-decoration: none;
}
.analytics-month-days .no-sales {
    font-size: 15px;
    color: #777;
}

/* ===== Адаптив: как в примере с товарами ===== */
@media (max-width: 700px) {
    .analytics-layout {
        grid-template-columns: 1fr;
        gap: 20px;
    }

    /* Левая и правая части идут друг под другом */
    .analytics-left,
    .analytics-right {
        width: 100%;
    }

    /* Даты в левой части */
    .analytics-date-header {
        font-size: 20px;
        margin-bottom: 16px;
    }

    .analytics-block {
        padding: 16px;
    }
    .analytics-block-title {
        font-size: 18px;
    }
    .analytics-item {
        font-size: 16px;
        padding: 8px 0;
    }
    .analytics-total-row {
        font-size: 18px;
    }

    /* Календарь */
    .analytics-calendar {
        padding: 16px;
    }
    .analytics-month-nav .nav-btn {
        font-size: 18px;
        padding: 4px 12px;
    }
    .analytics-month-nav .month-label {
        font-size: 18px;
    }
    .analytics-days-grid {
        gap: 6px;
    }
    .analytics-day-cell {
        padding: 8px 0;
    }
    .analytics-day-cell .day-number {
        font-size: 15px;
    }

    /* История */
    .analytics-history {
        padding: 16px;
    }
    .analytics-history-title {
        font-size: 18px;
    }
    .analytics-month-summary {
        font-size: 16px;
    }
    .analytics-month-days .day-link {
        font-size: 14px;
        padding: 4px 10px;
    }
}
</style>
@endsection

@section('content')
<div class="analytics-layout">
    {{-- ===== ЛЕВАЯ КОЛОНКА ===== --}}
    <div class="analytics-left">
        <div class="analytics-date-header">
            {{ $selectedDate->format('d.m.Y') }}
            @if ($selectedDate->isToday())
                <span class="today-badge">(сегодня)</span>
            @endif
        </div>

        {{-- Продано --}}
        <div class="analytics-block analytics-block--sales">
            <div class="analytics-block-title analytics-block-title--sales">Продано</div>

            @forelse ($paidItems as $item)
                <div class="analytics-item">
                    <div>
                        <div class="analytics-item-title">{{ $item->product->title ?? 'Товар удалён' }}</div>
                        @if ($item->product && $item->product->car)
                            <div class="analytics-item-sub">{{ $item->product->car->title }}</div>
                        @endif
                    </div>
                    <div class="analytics-item-price">
                        <div>{{ $item->quantity }} × {{ number_format($item->price_per_unit, 0, ',', ' ') }}</div>
                        <div class="analytics-item-total analytics-item-total--sales">{{ number_format($item->quantity * $item->price_per_unit, 0, ',', ' ') }} сум</div>
                    </div>
                </div>
            @empty
                <p class="analytics-empty">Продаж за этот день нет.</p>
            @endforelse

            @if ($paidItems->isNotEmpty())
                <div class="analytics-total-row analytics-total-row--sales">
                    <span>Сумма:</span>
                    <span>{{ number_format($totalPaid, 0, ',', ' ') }} сум</span>
                </div>
            @endif
        </div>

        {{-- Долг --}}
        <div class="analytics-block analytics-block--debt">
            <div class="analytics-block-title analytics-block-title--debt">Взято в долг</div>

            @forelse ($debtItems as $item)
                <div class="analytics-item analytics-item--debt">
                    <div>
                        <div class="analytics-item-title">{{ $item->product->title ?? 'Товар удалён' }}</div>
                        <div class="analytics-item-sub">
                            {{ $item->sale_customer->name ?? 'Клиент не указан' }}
                            @if ($item->product && $item->product->car)
                                &middot; {{ $item->product->car->title }}
                            @endif
                        </div>
                    </div>
                    <div class="analytics-item-price">
                        <div>{{ $item->quantity }} × {{ number_format($item->price_per_unit, 0, ',', ' ') }}</div>
                        <div class="analytics-item-total analytics-item-total--debt">{{ number_format($item->quantity * $item->price_per_unit, 0, ',', ' ') }} сум</div>
                    </div>
                </div>
            @empty
                <p class="analytics-empty">Долгов за этот день нет.</p>
            @endforelse

            @if ($debtItems->isNotEmpty())
                <div class="analytics-total-row analytics-total-row--debt">
                    <span>Сумма:</span>
                    <span>{{ number_format($totalDebt, 0, ',', ' ') }} сум</span>
                </div>
            @endif
        </div>
    </div>

    {{-- ===== ПРАВАЯ КОЛОНКА ===== --}}
    <div class="analytics-right">
        {{-- Календарь --}}
        <div class="analytics-calendar">
            <div class="analytics-month-nav">
                <a href="{{ route('analytics', ['month' => $selectedMonth->copy()->subMonth()->format('Y-m'), 'date' => $selectedDate->format('Y-m-d')]) }}"
                   class="nav-btn">&larr;</a>
                <span class="month-label">{{ $monthLabel }}</span>
                <a href="{{ route('analytics', ['month' => $selectedMonth->copy()->addMonth()->format('Y-m'), 'date' => $selectedDate->format('Y-m-d')]) }}"
                   class="nav-btn">&rarr;</a>
            </div>

            <div class="analytics-days-grid">
                @for ($day = 1; $day <= $selectedMonth->daysInMonth; $day++)
                    @php
                        $cellDate = $selectedMonth->copy()->day($day);
                        $isSelected = $cellDate->isSameDay($selectedDate);
                        $hasSales = in_array($day, $daysWithSalesInMonth);
                    @endphp
                    <a href="{{ route('analytics', ['month' => $selectedMonth->format('Y-m'), 'date' => $cellDate->format('Y-m-d')]) }}"
                       class="analytics-day-cell {{ $isSelected ? 'analytics-day-cell--selected' : '' }}">
                        <span class="day-number {{ $isSelected ? 'day-number--selected' : '' }}">{{ $day }}</span>
                        @if ($hasSales)
                            <span class="day-dot {{ $isSelected ? 'day-dot--selected' : '' }}"></span>
                        @endif
                    </a>
                @endfor
            </div>
        </div>

        {{-- История по месяцам --}}
        <div class="analytics-history">
            <div class="analytics-history-title">История по месяцам</div>

            @foreach ($months as $month)
                <details class="analytics-month-details" {{ $month['key'] === $selectedMonth->format('Y-m') ? 'open' : '' }}>
                    <summary class="analytics-month-summary">
                        <span class="month-name">{{ $month['label'] }}</span>
                        <span class="month-total">{{ number_format($month['total'], 0, ',', ' ') }} сум</span>
                    </summary>

                    <div class="analytics-month-days">
                        @if (empty($month['days_with_sales']))
                            <span class="no-sales">Продаж не было.</span>
                        @else
                            @foreach ($month['days_with_sales'] as $day)
                                <a href="{{ route('analytics', ['month' => $month['key'], 'date' => $month['key'] . '-' . str_pad($day, 2, '0', STR_PAD_LEFT)]) }}"
                                   class="day-link">
                                    {{ $day }}
                                </a>
                            @endforeach
                        @endif
                    </div>
                </details>
            @endforeach
        </div>
    </div>
</div>
@endsection