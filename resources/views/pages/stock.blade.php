@extends('layouts.app')

@section('title', 'Склад — Автозапчасти')

@section('content')

<div style="display:grid; grid-template-columns:25% 75%; border:2px solid #024989; border-radius:10px; overflow:hidden;">

    {{-- Левая колонка: Склады + Категории --}}
    <div style="background:#024989; color:#fff;">
        
        {{-- Склады --}}
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

        {{-- Категории (ниже складов) --}}
        <div style="padding:16px 18px; font-size:20px; font-weight:500; border-top:2px solid rgba(255,255,255,0.3); border-bottom:1px solid rgba(255,255,255,0.3); margin-top:10px;">
            Категории
        </div>

        <a href="{{ route('stock', ['warehouse' => $selectedWarehouseId]) }}"
           style="display:block; padding:16px 18px; font-size:19px; text-decoration:none; color:#fff; {{ !$selectedCategoryId ? 'background:rgba(255,255,255,0.18); font-weight:500;' : '' }} border-bottom:1px solid rgba(255,255,255,0.15);">
            Все категории
        </a>

        @foreach ($categories as $category)
            <a href="{{ route('stock', ['warehouse' => $selectedWarehouseId, 'category' => $category->id]) }}"
               style="display:block; padding:16px 18px; font-size:19px; text-decoration:none; color:#fff; {{ (string) $selectedCategoryId === (string) $category->id ? 'background:rgba(255,255,255,0.18); font-weight:500;' : '' }} border-bottom:1px solid rgba(255,255,255,0.15);">
                {{ $category->title }}
            </a>
        @endforeach
    </div>

    {{-- Правая колонка: Товары склада --}}
    <div style="padding:20px; background:#fff;">

        @php
            $currentWarehouse = $warehouses->firstWhere('id', (int) $selectedWarehouseId);
        @endphp

        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
            <div style="font-size:20px; font-weight:600; color:#024989;">
                {{ $currentWarehouse->title ?? 'Склад' }} — товары
            </div>
        </div>

        {{-- Поисковик --}}
        <input type="text" id="stock-search-input"
               placeholder="Поиск товаров на складе..."
               autocomplete="off" 
               style="width:100%; padding:12px 16px; font-size:17px; border:2px solid #b8cfe0; border-radius:8px; margin-bottom:20px; outline:none;">

        @if ($stockItems->isEmpty())
            <p id="stock-empty-message" style="font-size:19px; color:#555;">На этом складе пока нет товаров.</p>
        @else
            <div id="stock-items-container">
                <div style="display:grid; grid-template-columns: 3fr 1fr; font-size:18px;">
                    <div style="font-weight:500; padding:10px 8px; border-bottom:2px solid #024989; color:#024989;">Товар</div>
                    <div style="font-weight:500; padding:10px 8px; border-bottom:2px solid #024989; color:#024989;">Кол-во</div>

                    @foreach ($stockItems as $item)
                        <div class="stock-item" data-search="{{ Str::lower($item->product->title) }}" style="padding:14px 8px; border-bottom:1px solid #e2e2e2; font-size:19px;">
                            {{ $item->product->title }}
                            <div style="font-size:13px; color:#777; margin-top:2px;">{{ $item->product->category->title ?? 'Без категории' }}</div>
                        </div>
                        <div class="stock-item" data-search="{{ Str::lower($item->product->title) }}" style="padding:14px 8px; border-bottom:1px solid #e2e2e2; font-size:19px; font-weight:500;">
                            {{ $item->quantity }} шт
                        </div>
                    @endforeach
                </div>
                <p id="stock-empty-search-message" style="display:none; font-size:19px; color:#555; margin-top:16px;">Ничего не найдено.</p>
            </div>
        @endif
    </div>

</div>

<script>
    // Живой поиск на складе
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('stock-search-input');
        if (searchInput) {
            const items = document.querySelectorAll('.stock-item');
            const emptyMessage = document.getElementById('stock-empty-search-message');
            const emptyMainMessage = document.getElementById('stock-empty-message');

            searchInput.addEventListener('input', function() {
                const query = searchInput.value.trim().toLowerCase();
                let visibleCount = 0;

                items.forEach(function(item) {
                    const searchData = item.dataset.search || '';
                    const isMatch = searchData.includes(query);
                    item.style.display = isMatch ? '' : 'none';
                    if (isMatch) visibleCount++;
                });

                if (emptyMessage) {
                    emptyMessage.style.display = visibleCount === 0 && items.length > 0 ? 'block' : 'none';
                }
                
                if (emptyMainMessage) {
                    emptyMainMessage.style.display = items.length === 0 ? 'block' : 'none';
                }
            });
        }
    });
</script>

@endsection