@extends('layouts.app')

@section('title', $car->title . ' — Продажи')

@section('styles')
<style>
.parts-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 20px;
    flex-wrap: wrap;
    gap: 12px;
}
.parts-back-link {
    font-size: 18px;
    color: #024989;
    text-decoration: none;
}
.parts-title {
    font-size: 24px;
    font-weight: 600;
    color: #024989;
}
.parts-cart-btn {
    padding: 12px 22px;
    font-size: 17px;
    min-height: auto;
}
.alert-success {
    padding: 14px 18px;
    margin-bottom: 20px;
    background: #e6f4ea;
    border: 2px solid #2e7d32;
    color: #2e7d32;
    border-radius: 10px;
    font-size: 18px;
}
.alert-error {
    padding: 14px 18px;
    margin-bottom: 20px;
    background: #fdecea;
    border: 2px solid #a32d2d;
    color: #a32d2d;
    border-radius: 10px;
    font-size: 18px;
}

/* Категории + запчасти */
.parts-layout {
    display: grid;
    grid-template-columns: 25% 75%;
    border: 2px solid #024989;
    border-radius: 10px;
    overflow: hidden;
}
.parts-categories {
    background: #024989;
    color: #fff;
}
.parts-categories-title {
    padding: 16px 18px;
    font-size: 20px;
    font-weight: 500;
    border-bottom: 1px solid rgba(255,255,255,0.3);
}
.parts-cat-link {
    display: block;
    padding: 16px 18px;
    font-size: 19px;
    text-decoration: none;
    color: #fff;
    border-bottom: 1px solid rgba(255,255,255,0.15);
}
.parts-cat-link.active {
    background: rgba(255,255,255,0.18);
    font-weight: 500;
}
.parts-content {
    padding: 20px;
    background: #fff;
}
.parts-search-input {
    width: 100%;
    margin-bottom: 20px;
}
.parts-list {
    display: grid;
    gap: 16px;
}

/* Карточка запчасти */
.part-card {
    border: 2px solid #024989;
    border-radius: 12px;
    padding: 16px;
    display: grid;
    grid-template-columns: 120px 1fr;
    gap: 16px;
}
.part-card img {
    width: 120px;
    height: 100px;
    object-fit: cover;
    border-radius: 8px;
    background: #e8f0f7;
}
.part-title-row {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
    margin-bottom: 4px;
}
.part-title {
    font-size: 19px;
    font-weight: 600;
}
.part-position-badge {
    font-size: 19px;
    background: #e8f0f7;
    color: #024989;
    padding: 2px 10px;
    border-radius: 6px;
    font-weight: 500;
}
.part-color-badge {
    font-size: 13px;
    background: #e8f0f7;
    color: #024989;
    padding: 2px 10px;
    border-radius: 6px;
    font-weight: 500;
}
.part-category {
    font-size: 15px;
    color: #777;
    margin-bottom: 8px;
}
.part-stock {
    font-size: 16px;
    color: #555;
    margin-bottom: 10px;
}
.part-stock-item {
    margin-right: 12px;
}

/* Форма добавления в корзину */
.add-to-cart-form {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    align-items: flex-end;
}
.add-to-cart-form label {
    font-size: 14px;
    margin-bottom: 4px;
}
.add-to-cart-form select {
    padding: 10px;
    font-size: 16px;
}
.qty-input {
    width: 80px;
    padding: 10px;
    font-size: 16px;
}
.price-input {
    width: 150px;
    padding: 10px;
    font-size: 16px;
}
.add-to-cart-btn {
    padding: 12px 20px;
    width: auto;
}

/* ===== Планшеты ===== */
@media (max-width: 900px) {
    .parts-layout {
        grid-template-columns: 30% 70%;
    }
}

/* ===== Телефоны ===== */
@media (max-width: 700px) {
    .parts-title {
        font-size: 20px;
        order: -1;
        width: 100%;
        text-align: center;
    }

    .parts-layout {
        grid-template-columns: 1fr;
        border-radius: 12px;
    }

    /* Категории — горизонтальная прокручиваемая лента */
    .parts-categories {
        display: flex;
        flex-wrap: nowrap;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
    }
    .parts-categories::-webkit-scrollbar {
        display: none;
    }
    .parts-categories-title {
        display: none;
    }
    .parts-cat-link {
        flex-shrink: 0;
        white-space: nowrap;
        padding: 14px 18px;
        font-size: 16px;
        border-bottom: none;
        border-right: 1px solid rgba(255,255,255,0.15);
    }

    .parts-content {
        padding: 16px;
    }

    /* Карточка запчасти: фото сверху, контент под ним */
    .part-card {
        grid-template-columns: 1fr;
        padding: 12px;
    }
    .part-card img {
        width: 100%;
        height: 160px;
    }

    /* Форма: каждое поле на всю ширину, друг под другом */
    .add-to-cart-form {
        flex-direction: column;
        align-items: stretch;
        gap: 14px;
    }
    .add-to-cart-form > div {
        width: 100%;
    }
    .add-to-cart-form label {
        font-size: 15px;
    }
    .add-to-cart-form select,
    .qty-input,
    .price-input {
        width: 100%;
        font-size: 16px; /* защита от авто-зума iOS */
        padding: 12px;
    }
    .add-to-cart-btn {
        width: 100%;
    }
}
</style>
@endsection

@section('content')

    @php
        $placeholder = 'https://placehold.co/500x300/e8f0f7/024989?text=Фото+товара';
    @endphp

    <div class="parts-header">
        <a href="{{ route('sales.cars') }}" class="parts-back-link">&larr; Назад к машинам</a>
        <div class="parts-title">{{ $car->title }}</div>
        <a href="{{ route('sales.cart') }}" class="tile parts-cart-btn">Корзина</a>
    </div>

    @if (session('success'))
        <div class="alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert-error">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="parts-layout">

        {{-- Категории --}}
        <div class="parts-categories">
            <div class="parts-categories-title">Категории</div>
            <a href="{{ route('sales.parts', $car) }}" class="parts-cat-link {{ !$selectedCategoryId ? 'active' : '' }}">
                Все категории
            </a>
            @foreach ($categories as $category)
                <a href="{{ route('sales.parts', [$car, 'category' => $category->id]) }}"
                   class="parts-cat-link {{ (string) $selectedCategoryId === (string) $category->id ? 'active' : '' }}">
                    {{ $category->title }}
                </a>
            @endforeach
        </div>

        {{-- Запчасти --}}
        <div class="parts-content">

            <input type="text" id="parts-search-input" class="parts-search-input" placeholder="Поиск запчасти" autocomplete="off">

            @if ($products->isEmpty())
                <p style="font-size:19px; color:#555;">Для этой машины пока нет запчастей.</p>
            @else
                <div id="parts-list" class="parts-list">
                    @foreach ($products as $product)
                        <div class="part-card" data-search="{{ Str::lower($product->title) }}">

                            <img src="{{ $product->image ? asset('storage/' . $product->image) : $placeholder }}" alt="{{ $product->title }}">

                            <div>
                                <div class="part-title-row">
                                    <span class="part-title">{{ $product->title }}</span>
                                    @if ($product->position)
                                        <span class="part-position-badge">
                                            {{ $product->position->title }}
                                        </span>
                                    @endif
                                    @if ($product->color)
                                        <span class="part-color-badge">
                                            {{ $product->color->title }}
                                        </span>
                                    @endif
                                </div>
                                <div class="part-category">{{ $product->category->title ?? '—' }}</div>

                                <div class="part-stock">
                                    Наличие:
                                    @forelse ($product->quantities as $q)
                                        @if ($q->quantity > 0)
                                            <span class="part-stock-item">{{ $q->warehouse->title }}: <strong>{{ $q->quantity }} шт</strong></span>
                                        @endif
                                    @empty
                                        нет в наличии
                                    @endforelse
                                </div>

                                <form method="POST" action="{{ route('sales.cart.add') }}" class="add-to-cart-form">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                    <input type="hidden" name="car_id" value="{{ $car->id }}">

                                    <div>
                                        <label>Склад</label>
                                        <select name="warehouse_id" required>
                                            @foreach ($product->quantities as $q)
                                                @if ($q->quantity > 0)
                                                    <option value="{{ $q->warehouse_id }}">{{ $q->warehouse->title }} ({{ $q->quantity }} шт)</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label>Кол-во</label>
                                        <input type="number" name="quantity" value="1" min="1" required class="qty-input">
                                    </div>

                                    <div>
                                        <label>Цена с торгом (за 1 шт)</label>
                                        <input type="text" inputmode="numeric" name="price_per_unit" class="number-spaced price-input"
                                               value="{{ (int) $product->selling_price }}" required>
                                    </div>

                                    <button type="submit" class="btn-primary add-to-cart-btn">Добавить в корзину</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
                <p id="parts-empty-message" style="display:none; font-size:19px; color:#555; margin-top:16px;">Ничего не найдено.</p>
            @endif
        </div>
    </div>

    <script>
        // Живой клиентский поиск по запчастям
        const partsSearchInput = document.getElementById('parts-search-input');
        if (partsSearchInput) {
            const partCards = document.querySelectorAll('.part-card');
            const emptyMessage = document.getElementById('parts-empty-message');

            partsSearchInput.addEventListener('input', function () {
                const query = partsSearchInput.value.trim().toLowerCase();
                let visibleCount = 0;

                partCards.forEach(function (card) {
                    const isMatch = card.dataset.search.includes(query);
                    card.style.display = isMatch ? '' : 'none';
                    if (isMatch) visibleCount++;
                });

                if (emptyMessage) {
                    emptyMessage.style.display = visibleCount === 0 ? 'block' : 'none';
                }
            });
        }

        // Форматирование цены с торгом пробелами
        document.querySelectorAll('.number-spaced').forEach(function (input) {
            input.addEventListener('input', function () {
                const raw = input.value.replace(/\D/g, '');
                input.value = raw.replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
            });
            input.closest('form').addEventListener('submit', function () {
                input.value = input.value.replace(/\s/g, '');
            });
        });
    </script>

@endsection