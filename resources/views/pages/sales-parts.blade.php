@extends('layouts.app')

@section('title', $car->title . ' — Продажи')

@section('content')

    @php
        $placeholder = 'https://placehold.co/500x300/e8f0f7/024989?text=Фото+товара';
    @endphp

    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px; flex-wrap:wrap; gap:12px;">
        <a href="{{ route('sales.cars') }}" style="font-size:18px; color:#024989; text-decoration:none;">&larr; Назад к машинам</a>
        <div style="font-size:24px; font-weight:600; color:#024989;">{{ $car->title }}</div>
        <a href="{{ route('sales.cart') }}" class="tile" style="padding:12px 22px; font-size:17px; min-height:auto;">Корзина</a>
    </div>

    @if (session('success'))
        <div style="padding:14px 18px; margin-bottom:20px; background:#e6f4ea; border:2px solid #2e7d32; color:#2e7d32; border-radius:10px; font-size:18px;">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div style="padding:14px 18px; margin-bottom:20px; background:#fdecea; border:2px solid #a32d2d; color:#a32d2d; border-radius:10px; font-size:18px;">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div style="display:grid; grid-template-columns:25% 75%; border:2px solid #024989; border-radius:10px; overflow:hidden;">

        {{-- Категории --}}
        <div style="background:#024989; color:#fff;">
            <div style="padding:16px 18px; font-size:20px; font-weight:500; border-bottom:1px solid rgba(255,255,255,0.3);">
                Категории
            </div>
            <a href="{{ route('sales.parts', $car) }}"
               style="display:block; padding:16px 18px; font-size:19px; text-decoration:none; color:#fff; {{ !$selectedCategoryId ? 'background:rgba(255,255,255,0.18); font-weight:500;' : '' }} border-bottom:1px solid rgba(255,255,255,0.15);">
                Все категории
            </a>
            @foreach ($categories as $category)
                <a href="{{ route('sales.parts', [$car, 'category' => $category->id]) }}"
                   style="display:block; padding:16px 18px; font-size:19px; text-decoration:none; color:#fff; {{ (string) $selectedCategoryId === (string) $category->id ? 'background:rgba(255,255,255,0.18); font-weight:500;' : '' }} border-bottom:1px solid rgba(255,255,255,0.15);">
                    {{ $category->title }}
                </a>
            @endforeach
        </div>

        {{-- Запчасти --}}
        <div style="padding:20px; background:#fff;">

            <input type="text" id="parts-search-input" placeholder="Поиск запчасти" autocomplete="off" style="width:100%; margin-bottom:20px;">

            @if ($products->isEmpty())
                <p style="font-size:19px; color:#555;">Для этой машины пока нет запчастей.</p>
            @else
                <div id="parts-list" style="display:grid; gap:16px;">
                    @foreach ($products as $product)
                        <div class="part-card" data-search="{{ Str::lower($product->title) }}"
                             style="border:2px solid #024989; border-radius:12px; padding:16px; display:grid; grid-template-columns:120px 1fr; gap:16px;">

                            <img src="{{ $product->image ? asset('storage/' . $product->image) : $placeholder }}" alt="{{ $product->title }}"
                                 style="width:120px; height:100px; object-fit:cover; border-radius:8px; background:#e8f0f7;">

                            <div>
                                <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap; margin-bottom:4px;">
                                    <span style="font-size:19px; font-weight:600;">{{ $product->title }}</span>
                                    @if ($product->position)
                                        <span style="font-size:19px; background:#e8f0f7; color:#024989; padding:2px 10px; border-radius:6px; font-weight:500;">
                                            {{ $product->position->title }}
                                        </span>
                                    @endif
                                    @if ($product->color)
                                        <span style="font-size:13px; background:#e8f0f7; color:#024989; padding:2px 10px; border-radius:6px; font-weight:500;">
                                            {{ $product->color->title }}
                                        </span>
                                    @endif
                                </div>
                                <div style="font-size:15px; color:#777; margin-bottom:8px;">{{ $product->category->title ?? '—' }}</div>

                                <div style="font-size:16px; color:#555; margin-bottom:10px;">
                                    Наличие:
                                    @forelse ($product->quantities as $q)
                                        @if ($q->quantity > 0)
                                            <span style="margin-right:12px;">{{ $q->warehouse->title }}: <strong>{{ $q->quantity }} шт</strong></span>
                                        @endif
                                    @empty
                                        нет в наличии
                                    @endforelse
                                </div>

                                <form method="POST" action="{{ route('sales.cart.add') }}" style="display:flex; gap:10px; flex-wrap:wrap; align-items:flex-end;">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                    <input type="hidden" name="car_id" value="{{ $car->id }}">

                                    <div>
                                        <label style="font-size:14px; margin-bottom:4px;">Склад</label>
                                        <select name="warehouse_id" required style="padding:10px; font-size:16px;">
                                            @foreach ($product->quantities as $q)
                                                @if ($q->quantity > 0)
                                                    <option value="{{ $q->warehouse_id }}">{{ $q->warehouse->title }} ({{ $q->quantity }} шт)</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label style="font-size:14px; margin-bottom:4px;">Кол-во</label>
                                        <input type="number" name="quantity" value="1" min="1" required style="width:80px; padding:10px; font-size:16px;">
                                    </div>

                                    <div>
                                        <label style="font-size:14px; margin-bottom:4px;">Цена с торгом (за 1 шт)</label>
                                        <input type="text" inputmode="numeric" name="price_per_unit" class="number-spaced"
                                               value="{{ (int) $product->selling_price }}" required style="width:150px; padding:10px; font-size:16px;">
                                    </div>

                                    <button type="submit" class="btn-primary" style="padding:12px 20px; width:auto;">Добавить в корзину</button>
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