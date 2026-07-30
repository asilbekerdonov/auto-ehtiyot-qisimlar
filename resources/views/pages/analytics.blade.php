@extends('layouts.app')

@section('title', 'Аналитика — Автозапчасти')

@section('content')

<div style="display:grid; grid-template-columns:1fr 1fr; gap:24px; align-items:start;">

    {{-- ЛЕВАЯ ПОЛОВИНА: что продано / в долг за выбранный день --}}
    <div>
        <div style="font-size:24px; font-weight:600; color:#024989; margin-bottom:20px;">
            {{ $selectedDate->format('d.m.Y') }}
            @if ($selectedDate->isToday())
                <span style="font-size:16px; font-weight:400; color:#555;">(сегодня)</span>
            @endif
        </div>

        {{-- Продано --}}
        <div style="border:2px solid #024989; border-radius:10px; padding:20px; margin-bottom:20px;">
            <div style="font-size:20px; font-weight:600; color:#024989; margin-bottom:14px;">Продано</div>

            @forelse ($paidItems as $item)
                <div style="display:flex; justify-content:space-between; align-items:center; padding:10px 0; border-bottom:1px solid #e2e2e2; font-size:18px;">
                    <div>
                        <div style="font-weight:500;">{{ $item->product->title ?? 'Товар удалён' }}</div>
                        @if ($item->product && $item->product->car)
                            <div style="font-size:14px; color:#777;">{{ $item->product->car->title }}</div>
                        @endif
                    </div>
                    <div style="text-align:right;">
                        <div>{{ $item->quantity }} × {{ number_format($item->price_per_unit, 0, ',', ' ') }}</div>
                        <div style="font-weight:600; color:#024989;">{{ number_format($item->quantity * $item->price_per_unit, 0, ',', ' ') }} сум</div>
                    </div>
                </div>
            @empty
                <p style="font-size:17px; color:#555;">Продаж за этот день нет.</p>
            @endforelse

            @if ($paidItems->isNotEmpty())
                <div style="display:flex; justify-content:space-between; padding-top:14px; font-size:20px; font-weight:700; color:#024989;">
                    <span>Сумма:</span>
                    <span>{{ number_format($totalPaid, 0, ',', ' ') }} сум</span>
                </div>
            @endif
        </div>

        {{-- Долг --}}
        <div style="border:2px solid #a32d2d; border-radius:10px; padding:20px;">
            <div style="font-size:20px; font-weight:600; color:#a32d2d; margin-bottom:14px;">Взято в долг</div>

            @forelse ($debtItems as $item)
                <div style="display:flex; justify-content:space-between; align-items:center; padding:10px 0; border-bottom:1px solid #f0dede; font-size:18px;">
                    <div>
                        <div style="font-weight:500;">{{ $item->product->title ?? 'Товар удалён' }}</div>
                        <div style="font-size:14px; color:#777;">
                            {{ $item->sale_customer->name ?? 'Клиент не указан' }}
                            @if ($item->product && $item->product->car)
                                &middot; {{ $item->product->car->title }}
                            @endif
                        </div>
                    </div>
                    <div style="text-align:right;">
                        <div>{{ $item->quantity }} × {{ number_format($item->price_per_unit, 0, ',', ' ') }}</div>
                        <div style="font-weight:600; color:#a32d2d;">{{ number_format($item->quantity * $item->price_per_unit, 0, ',', ' ') }} сум</div>
                    </div>
                </div>
            @empty
                <p style="font-size:17px; color:#555;">Долгов за этот день нет.</p>
            @endforelse

            @if ($debtItems->isNotEmpty())
                <div style="display:flex; justify-content:space-between; padding-top:14px; font-size:20px; font-weight:700; color:#a32d2d;">
                    <span>Сумма:</span>
                    <span>{{ number_format($totalDebt, 0, ',', ' ') }} сум</span>
                </div>
            @endif
        </div>
    </div>

    {{-- ПРАВАЯ ПОЛОВИНА: календарь + сворачиваемая история по месяцам --}}
    <div>
        <div style="border:2px solid #024989; border-radius:10px; padding:20px; margin-bottom:20px;">

            {{-- Переключение месяца --}}
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px;">
                <a href="{{ route('analytics', ['month' => $selectedMonth->copy()->subMonth()->format('Y-m'), 'date' => $selectedDate->format('Y-m-d')]) }}"
                   style="font-size:22px; color:#024989; text-decoration:none; padding:6px 14px; border:2px solid #024989; border-radius:8px;">&larr;</a>

                <div style="font-size:22px; font-weight:600; color:#024989;">{{ $monthLabel }}</div>

                <a href="{{ route('analytics', ['month' => $selectedMonth->copy()->addMonth()->format('Y-m'), 'date' => $selectedDate->format('Y-m-d')]) }}"
                   style="font-size:22px; color:#024989; text-decoration:none; padding:6px 14px; border:2px solid #024989; border-radius:8px;">&rarr;</a>
            </div>

            {{-- Сетка дней --}}
            <div style="display:grid; grid-template-columns:repeat(7, 1fr); gap:8px;">
                @for ($day = 1; $day <= $selectedMonth->daysInMonth; $day++)
                    @php
                        $cellDate = $selectedMonth->copy()->day($day);
                        $isSelected = $cellDate->isSameDay($selectedDate);
                        $hasSales = in_array($day, $daysWithSalesInMonth);
                    @endphp
                    <a href="{{ route('analytics', ['month' => $selectedMonth->format('Y-m'), 'date' => $cellDate->format('Y-m-d')]) }}"
                       style="display:flex; flex-direction:column; align-items:center; justify-content:center; gap:2px; padding:10px 0; border-radius:8px; text-decoration:none;
                              {{ $isSelected ? 'background:#024989; color:#fff;' : 'background:#f4f8fb; color:#111;' }}">
                        <span style="font-size:17px; font-weight:{{ $isSelected ? '700' : '400' }};">{{ $day }}</span>
                        @if ($hasSales)
                            <span style="width:6px; height:6px; border-radius:50%; background:{{ $isSelected ? '#fff' : '#024989' }};"></span>
                        @endif
                    </a>
                @endfor
            </div>
        </div>

        {{-- История по месяцам (год), сворачиваемая --}}
        <div style="border:2px solid #024989; border-radius:10px; padding:20px;">
            <div style="font-size:20px; font-weight:600; color:#024989; margin-bottom:14px;">История по месяцам</div>

            @foreach ($months as $month)
                <details style="border-bottom:1px solid #e2e2e2; padding:10px 0;" {{ $month['key'] === $selectedMonth->format('Y-m') ? 'open' : '' }}>
                    <summary style="cursor:pointer; font-size:18px; display:flex; justify-content:space-between; align-items:center; list-style:none;">
                        <span style="font-weight:500;">{{ $month['label'] }}</span>
                        <span style="color:#024989; font-weight:600;">{{ number_format($month['total'], 0, ',', ' ') }} сум</span>
                    </summary>

                    <div style="margin-top:12px;">
                        @if (empty($month['days_with_sales']))
                            <p style="font-size:15px; color:#777;">Продаж не было.</p>
                        @else
                            <div style="display:flex; flex-wrap:wrap; gap:8px;">
                                @foreach ($month['days_with_sales'] as $day)
                                    <a href="{{ route('analytics', ['month' => $month['key'], 'date' => $month['key'] . '-' . str_pad($day, 2, '0', STR_PAD_LEFT)]) }}"
                                       style="padding:6px 12px; font-size:15px; border-radius:8px; border:2px solid #024989; color:#024989; text-decoration:none;">
                                        {{ $day }}
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </details>
            @endforeach
        </div>
    </div>

</div>

@endsection