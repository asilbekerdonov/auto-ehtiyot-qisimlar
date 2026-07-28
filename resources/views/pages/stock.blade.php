@extends('layouts.app')

@section('title', 'Склад — Автозапчасти')

@section('content')

    <div style="display:grid; grid-template-columns:30% 70%; border:2px solid #024989; border-radius:10px; overflow:hidden;">

        {{-- Склады --}}
        <div style="background:#024989; color:#fff;">
            <div style="padding:16px 18px; font-size:20px; font-weight:500; border-bottom:1px solid rgba(255,255,255,0.3);">
                Склады
            </div>

            @forelse ($warehouses as $warehouse)
                <a href="{{ route('stock', ['warehouse' => $warehouse->id]) }}"
                   style="display:block; padding:16px 18px; font-size:19px; text-decoration:none; color:#fff; {{ (string) $selectedWarehouseId === (string) $warehouse->id ? 'background:rgba(255,255,255,0.18); font-weight:500;' : '' }} border-bottom:1px solid rgba(255,255,255,0.15);">
                    {{ $warehouse->title }}
                </a>
            @empty
                <div style="padding:16px 18px; font-size:18px;">Складов пока нет</div>
            @endforelse
        </div>

        {{-- Товары выбранного склада --}}
        <div style="padding:20px; background:#fff;">
            @php
                $currentWarehouse = $warehouses->firstWhere('id', (int) $selectedWarehouseId);
            @endphp

            <div style="font-size:20px; font-weight:600; color:#024989; margin-bottom:16px;">
                {{ $currentWarehouse->title ?? 'Склад' }} — товары
            </div>

            @if ($stockItems->isEmpty())
                <p style="font-size:19px; color:#555;">На этом складе пока нет товаров.</p>
            @else
                <div style="display:grid; grid-template-columns: 3fr 1fr; font-size:18px;">
                    <div style="font-weight:500; padding:10px 8px; border-bottom:2px solid #024989; color:#024989;">Товар</div>
                    <div style="font-weight:500; padding:10px 8px; border-bottom:2px solid #024989; color:#024989;">Кол-во</div>

                    @foreach ($stockItems as $item)
                        <div style="padding:14px 8px; border-bottom:1px solid #e2e2e2; font-size:19px;">{{ $item->product->title }}</div>
                        <div style="padding:14px 8px; border-bottom:1px solid #e2e2e2; font-size:19px; font-weight:500;">{{ $item->quantity }} шт</div>
                    @endforeach
                </div>
            @endif
        </div>

    </div>
@endsection