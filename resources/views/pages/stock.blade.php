@extends('layouts.app')

@section('title', 'Склад — Автозапчасти')

@section('styles')
<style>
.stock-layout {
    display: grid;
    grid-template-columns: 25% 75%;
    border: 2px solid #024989;
    border-radius: 10px;
    overflow: hidden;
}

/* Левая колонка: Склады + Категории */
.stock-sidebar {
    background: #024989;
    color: #fff;
}
.sidebar-section-title {
    padding: 16px 18px;
    font-size: 20px;
    font-weight: 500;
    border-bottom: 1px solid rgba(255,255,255,0.3);
}
.sidebar-section-title--categories {
    border-top: 2px solid rgba(255,255,255,0.3);
    margin-top: 10px;
}
.sidebar-link {
    display: block;
    padding: 16px 18px;
    font-size: 19px;
    text-decoration: none;
    color: #fff;
    border-bottom: 1px solid rgba(255,255,255,0.15);
}
.sidebar-link.active {
    background: rgba(255,255,255,0.18);
    font-weight: 500;
}
.sidebar-empty {
    padding: 16px 18px;
    font-size: 18px;
}

/* Правая колонка */
.stock-content {
    padding: 20px;
    background: #fff;
}
.stock-content-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
}
.stock-content-title {
    font-size: 20px;
    font-weight: 600;
    color: #024989;
}
.stock-search-input {
    width: 100%;
    padding: 12px 16px;
    font-size: 17px;
    border: 2px solid #b8cfe0;
    border-radius: 8px;
    margin-bottom: 20px;
    outline: none;
}

/* Таблица товаров склада */
.stock-table {
    display: grid;
    grid-template-columns: 3fr 1fr 1fr;
    font-size: 18px;
}
.stock-table-head {
    font-weight: 500;
    padding: 10px 8px;
    border-bottom: 2px solid #024989;
    color: #024989;
}
.stock-row {
    display: contents; /* десктоп: ячейки строки становятся частью общей 3-колоночной сетки */
}
.stock-cell {
    padding: 14px 8px;
    border-bottom: 1px solid #e2e2e2;
    font-size: 19px;
}
.stock-cell--qty {
    font-weight: 500;
}
.stock-product-meta {
    display: flex;
    align-items: center;
    gap: 6px;
    flex-wrap: wrap;
    margin-top: 4px;
}
.stock-meta-car {
    font-size: 17px;
    color: #0a6b3a;
}
.stock-meta-badge {
    font-size: 17px;
    background: #e8f0f7;
    color: #024989;
    padding: 1px 10px;
    border-radius: 6px;
    font-weight: 500;
}
.stock-meta-category {
    font-size: 17px;
    color: #777;
}
.stock-actions {
    display: flex;
    gap: 10px;
}
.stock-cell-label {
    display: none;
}

/* ===== Планшеты ===== */
@media (max-width: 900px) {
    .stock-layout {
        grid-template-columns: 32% 68%;
    }
}

/* ===== Телефоны ===== */
@media (max-width: 700px) {
    .stock-layout {
        grid-template-columns: 1fr;
        border-radius: 12px;
    }

    /* Склады и категории — горизонтальные прокручиваемые ленты друг под другом */
    .stock-sidebar {
        display: block;
    }
    .sidebar-section-title {
        display: none;
    }
    .sidebar-section-title--categories {
        margin-top: 0;
    }
    .stock-sidebar-row {
        display: flex;
        flex-wrap: nowrap;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
        border-bottom: 1px solid rgba(255,255,255,0.25);
    }
    .stock-sidebar-row::-webkit-scrollbar {
        display: none;
    }
    .sidebar-link {
        flex-shrink: 0;
        white-space: nowrap;
        padding: 14px 18px;
        font-size: 16px;
        border-bottom: none;
        border-right: 1px solid rgba(255,255,255,0.15);
    }

    .stock-content {
        padding: 16px;
    }

    /* Таблица товаров -> карточки */
    .stock-table {
        display: flex;
        flex-direction: column;
        gap: 14px;
    }
    .stock-table-head {
        display: none;
    }
    .stock-row {
        display: block; /* переопределяем display:contents — строка снова единый блок */
        border: 2px solid #024989;
        border-radius: 12px;
        overflow: hidden;
    }
    .stock-cell {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
        padding: 12px 16px;
        font-size: 17px;
        border-bottom: 1px solid #e2e2e2;
        text-align: right;
    }
    .stock-cell-label {
        display: inline;
        font-size: 14px;
        font-weight: 500;
        color: #777;
        text-align: left;
        flex-shrink: 0;
        padding-top: 2px;
    }
    .stock-cell--product {
        flex-direction: column;
        align-items: stretch;
        text-align: left;
    }
    .stock-product-meta {
        margin-top: 8px;
    }
    .stock-cell--actions {
        border-bottom: none;
    }
    .stock-actions {
        width: 100%;
        justify-content: flex-end;
    }
}
</style>
@endsection

@section('content')

<div class="stock-layout">

    {{-- Левая колонка: Склады + Категории --}}
    <div class="stock-sidebar">

        {{-- Склады --}}
        <div class="sidebar-section-title">Склады</div>

        <div class="stock-sidebar-row">
            @forelse ($warehouses as $warehouse)
                <a href="{{ route('stock', ['warehouse' => $warehouse->id]) }}"
                   class="sidebar-link {{ (string) $selectedWarehouseId === (string) $warehouse->id ? 'active' : '' }}">
                    {{ $warehouse->title }}
                </a>
            @empty
                <div class="sidebar-empty">Складов пока нет</div>
            @endforelse
        </div>

        {{-- Категории (ниже складов) --}}
        <div class="sidebar-section-title sidebar-section-title--categories">Категории</div>

        <div class="stock-sidebar-row">
            <a href="{{ route('stock', ['warehouse' => $selectedWarehouseId]) }}"
               class="sidebar-link {{ !$selectedCategoryId ? 'active' : '' }}">
                Все категории
            </a>

            @foreach ($categories as $category)
                <a href="{{ route('stock', ['warehouse' => $selectedWarehouseId, 'category' => $category->id]) }}"
                   class="sidebar-link {{ (string) $selectedCategoryId === (string) $category->id ? 'active' : '' }}">
                    {{ $category->title }}
                </a>
            @endforeach
        </div>
    </div>

    {{-- Правая колонка: Товары склада --}}
    <div class="stock-content">

        @php
            $currentWarehouse = $warehouses->firstWhere('id', (int) $selectedWarehouseId);
        @endphp

        <div class="stock-content-header">
            <div class="stock-content-title">
                {{ $currentWarehouse->title ?? 'Склад' }} — товары
            </div>
        </div>

        {{-- Поисковик --}}
        <input type="text" id="stock-search-input" class="stock-search-input"
               placeholder="Поиск товаров на складе..."
               autocomplete="off">

        @if ($stockItems->isEmpty())
            <p id="stock-empty-message" style="font-size:19px; color:#555;">На этом складе пока нет товаров.</p>
        @else
            <div id="stock-items-container">
                <div class="stock-table">
                    <div class="stock-table-head">Товар</div>
                    <div class="stock-table-head">Кол-во</div>
                    <div class="stock-table-head">Действия</div>

                    @foreach ($stockItems as $item)
                        <div class="stock-row" data-search="{{ Str::lower($item->product->title) }}">
                            <div class="stock-cell stock-cell--product stock-item">
                                <span class="stock-cell-label">Товар</span>
                                <div>
                                    {{ $item->product->title }}
                                    <div class="stock-product-meta">
                                        @if ($item->product->car)
                                            <span class="stock-meta-car">{{ $item->product->car->title }}</span>
                                        @endif
                                        @if ($item->product->position)
                                            <span class="stock-meta-badge">
                                                {{ $item->product->position->title }}
                                            </span>
                                        @endif
                                        @if ($item->product->color)
                                            <span class="stock-meta-badge">
                                                {{ $item->product->color->title }}
                                            </span>
                                        @endif
                                        <span class="stock-meta-category">{{ $item->product->category->title ?? 'Без категории' }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="stock-cell stock-cell--qty stock-item">
                                <span class="stock-cell-label">Кол-во</span>
                                {{ $item->quantity }} шт
                            </div>
                            <div class="stock-cell stock-cell--actions stock-item">
                                <span class="stock-cell-label">Действия</span>
                                <div class="stock-actions">
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

    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('edit-stock-modal');
        if (modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === this) closeEditStockModal();
            });
        }

        document.querySelectorAll('#edit-stock-form .number-spaced').forEach(function(input) {
            input.addEventListener('input', function() {
                const raw = input.value.replace(/\D/g, '');
                input.value = formatSpaced(raw);
            });
        });

        document.getElementById('edit-stock-form').addEventListener('submit', function() {
            const el = document.getElementById('edit-stock-quantity');
            el.value = el.value.replace(/\s/g, '');
        });

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
            const rows = document.querySelectorAll('.stock-row');
            const emptyMessage = document.getElementById('stock-empty-search-message');
            const emptyMainMessage = document.getElementById('stock-empty-message');

            searchInput.addEventListener('input', function() {
                const query = searchInput.value.trim().toLowerCase();
                let visibleCount = 0;

                rows.forEach(function(row) {
                    const searchData = row.dataset.search || '';
                    const isMatch = searchData.includes(query);
                    row.style.display = isMatch ? '' : 'none';
                    if (isMatch) visibleCount++;
                });

                if (emptyMessage) {
                    emptyMessage.style.display = visibleCount === 0 && rows.length > 0 ? 'block' : 'none';
                }

                if (emptyMainMessage) {
                    emptyMainMessage.style.display = rows.length === 0 ? 'block' : 'none';
                }
            });
        }
    });
</script>

@endsection