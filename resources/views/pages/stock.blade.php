@extends('layouts.app')

@section('title', 'Склад — Автозапчасти')

@section('content')

    {{-- TODO: заменить на данные из БД (warehouses, quantity, products) --}}
    @php
        $warehouses = ['Основной склад', 'Склад №2 (гараж)'];
        $stockItems = [
            ['name' => 'Колодки Bosch F1', 'qty' => '23 шт'],
            ['name' => 'Фильтр масляный', 'qty' => '41 шт'],
            ['name' => 'Свеча NGK', 'qty' => '67 шт'],
        ];
    @endphp

    <div style="display:grid; grid-template-columns:30% 70%; border:2px solid #024989; border-radius:10px; overflow:hidden;">

        {{-- Склады --}}
        <div style="background:#024989; color:#fff;">
            <div style="padding:16px 18px; font-size:20px; font-weight:500; border-bottom:1px solid rgba(255,255,255,0.3);">
                Склады
            </div>
            @foreach ($warehouses as $i => $warehouse)
                <div style="padding:16px 18px; font-size:19px; {{ $i === 0 ? 'background:rgba(255,255,255,0.18); font-weight:500;' : '' }} border-bottom:1px solid rgba(255,255,255,0.15); cursor:pointer;">
                    {{ $warehouse }}
                </div>
            @endforeach
        </div>

        {{-- Товары выбранного склада --}}
        <div style="padding:20px; background:#fff;">
            <div style="font-size:20px; font-weight:600; color:#024989; margin-bottom:16px;">
                {{ $warehouses[0] }} — товары
            </div>

            <div style="display:grid; grid-template-columns: 3fr 1fr; font-size:18px;">
                <div style="font-weight:500; padding:10px 8px; border-bottom:2px solid #024989; color:#024989;">Товар</div>
                <div style="font-weight:500; padding:10px 8px; border-bottom:2px solid #024989; color:#024989;">Кол-во</div>

                @foreach ($stockItems as $item)
                    <div style="padding:14px 8px; border-bottom:1px solid #e2e2e2; font-size:19px;">{{ $item['name'] }}</div>
                    <div style="padding:14px 8px; border-bottom:1px solid #e2e2e2; font-size:19px; font-weight:500;">{{ $item['qty'] }}</div>
                @endforeach
            </div>
        </div>

    </div>
@endsection