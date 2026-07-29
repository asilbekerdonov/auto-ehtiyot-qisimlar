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
                <div style="display:grid; grid-template-columns: 3fr 1fr 1fr; font-size:18px;">
                    <div style="font-weight:500; padding:10px 8px; border-bottom:2px solid #024989; color:#024989;">Товар</div>
                    <div style="font-weight:500; padding:10px 8px; border-bottom:2px solid #024989; color:#024989;">Кол-во</div>
                    <div style="font-weight:500; padding:10px 8px; border-bottom:2px solid #024989; color:#024989;">Действия</div>

                    @foreach ($stockItems as $item)
                        <div class="stock-item" data-search="{{ Str::lower($item->product->title) }}" style="padding:14px 8px; border-bottom:1px solid #e2e2e2; font-size:19px;">
                            {{ $item->product->title }}
                            <div style="font-size:13px; color:#777; margin-top:2px;">{{ $item->product->category->title ?? 'Без категории' }}</div>
                        </div>
                        <div class="stock-item" data-search="{{ Str::lower($item->product->title) }}" style="padding:14px 8px; border-bottom:1px solid #e2e2e2; font-size:19px; font-weight:500;">
                            {{ $item->quantity }} шт
                        </div>
                        <div class="stock-item" data-search="{{ Str::lower($item->product->title) }}" style="padding:14px 8px; border-bottom:1px solid #e2e2e2;">
                            <div style="display:flex; gap:10px;">
                                <button type="button"
                                    class="icon-btn icon-btn--edit"
                                    title="Изменить количество"
                                    aria-label="Изменить количество"
                                    onclick="openEditStockModal(this)"
                                    data-update-url="{{ route('stock.update', $item) }}"
                                    data-title="{{ $item->product->title }}"
                                    data-quantity="{{ $item->quantity }}">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:20px; height:20px;">
                                        <path d="M12 20h9"></path>
                                        <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                                    </svg>
                                </button>

                                <form method="POST" action="{{ route('stock.destroy', $item) }}" class="delete-stock-form" data-title="{{ $item->product->title }}" style="margin:0;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="icon-btn icon-btn--delete" title="Удалить" aria-label="Удалить товар со склада">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:20px; height:20px;">
                                            <path d="M3 6h18"></path>
                                            <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                            <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"></path>
                                            <path d="M10 11v6"></path>
                                            <path d="M14 11v6"></path>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
                <p id="stock-empty-search-message" style="display:none; font-size:19px; color:#555; margin-top:16px;">Ничего не найдено.</p>
            </div>
        @endif
    </div>

</div>

{{-- Модальное окно для изменения количества --}}
<div id="edit-stock-modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center; padding:20px;">
    <div style="background:#fff; border:2px solid #024989; border-radius:10px; padding:24px; max-width:420px; width:100%;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <div style="font-size:22px; font-weight:600; color:#024989;">Изменить количество</div>
            <button type="button" onclick="closeEditStockModal()" aria-label="Закрыть" style="border:none; background:none; font-size:30px; line-height:1; color:#024989; cursor:pointer;">&times;</button>
        </div>

        <div id="edit-stock-product-title" style="font-size:19px; margin-bottom:16px; color:#333;"></div>

        <form id="edit-stock-form" method="POST" action="" style="display:grid; gap:18px;">
            @csrf
            @method('PUT')

            <div>
                <label for="edit-stock-quantity">Количество</label>
                <input type="text" inputmode="numeric" id="edit-stock-quantity" name="quantity" class="number-spaced" required style="width:100%; font-size:19px; padding:12px; border:2px solid #b8cfe0; border-radius:8px;">
            </div>

            <button type="submit" class="btn-primary">Сохранить</button>
        </form>
    </div>
</div>

<style>
    .icon-btn {
        background: none;
        border: none;
        cursor: pointer;
        padding: 6px;
        border-radius: 6px;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #024989;
    }
    .icon-btn:hover {
        background: #f0f4f8;
    }
    .icon-btn--delete:hover {
        color: #dc3545;
        background: #fff0f0;
    }
    .icon-btn--edit:hover {
        color: #024989;
        background: #e8f0f7;
    }
</style>

<script>
    // Функции для модального окна (глобальные)
    function openEditStockModal(btn) {
        const form = document.getElementById('edit-stock-form');
        form.action = btn.dataset.updateUrl;
        document.getElementById('edit-stock-product-title').textContent = btn.dataset.title;
        document.getElementById('edit-stock-quantity').value = formatSpaced(btn.dataset.quantity);
        document.getElementById('edit-stock-modal').style.display = 'flex';
    }

    function closeEditStockModal() {
        document.getElementById('edit-stock-modal').style.display = 'none';
    }

    function formatSpaced(value) {
        if (!value) return '';
        return String(value).replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
    }

    // Закрытие модального окна при клике на фон
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('edit-stock-modal');
        if (modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === this) closeEditStockModal();
            });
        }

        // Форматирование чисел с пробелами
        document.querySelectorAll('#edit-stock-form .number-spaced').forEach(function(input) {
            input.addEventListener('input', function() {
                const raw = input.value.replace(/\D/g, '');
                input.value = formatSpaced(raw);
            });
        });

        // Перед отправкой убираем пробелы
        document.getElementById('edit-stock-form').addEventListener('submit', function() {
            const el = document.getElementById('edit-stock-quantity');
            el.value = el.value.replace(/\s/g, '');
        });

        // Подтверждение удаления
        document.querySelectorAll('.delete-stock-form').forEach(function(form) {
            form.addEventListener('submit', function(e) {
                const title = form.dataset.title || 'этот товар';
                if (!confirm('Убрать «' + title + '» с этого склада? Это действие нельзя отменить.')) {
                    e.preventDefault();
                }
            });
        });

        // Живой поиск на складе
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