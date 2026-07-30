@extends('layouts.app')

@section('title', $car->title . ' — Поступление')

@section('content')

    @php
        $placeholder = 'https://placehold.co/500x300/e8f0f7/024989?text=Фото+товара';
    @endphp

    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px; flex-wrap:wrap; gap:12px;">
        <a href="{{ route('receipts.cars') }}" style="font-size:18px; color:#024989; text-decoration:none;">&larr; Назад к машинам</a>
        <div style="font-size:24px; font-weight:600; color:#024989;">{{ $car->title }}</div>
        <div></div>
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
            <a href="{{ route('receipts.parts', $car) }}"
               style="display:block; padding:16px 18px; font-size:19px; text-decoration:none; color:#fff; {{ !$selectedCategoryId ? 'background:rgba(255,255,255,0.18); font-weight:500;' : '' }} border-bottom:1px solid rgba(255,255,255,0.15);">
                Все категории
            </a>
            @foreach ($categories as $category)
                <a href="{{ route('receipts.parts', [$car, 'category' => $category->id]) }}"
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
                             style="border:2px solid #024989; border-radius:12px; padding:16px; display:grid; grid-template-columns:100px 1fr; gap:16px;">

                            <img src="{{ $product->image ? asset('storage/' . $product->image) : $placeholder }}" alt="{{ $product->title }}"
                                 style="width:100px; height:90px; object-fit:cover; border-radius:8px; background:#e8f0f7;">

                            <div>
                                <div style="font-size:19px; font-weight:600; margin-bottom:2px;">{{ $product->title }}</div>
                                <div style="font-size:15px; color:#777; margin-bottom:12px;">{{ $product->category->title ?? '—' }}</div>

                                <div style="display:grid; gap:8px;">
                                    @foreach ($warehouses as $warehouse)
                                        @php
                                            $qtyRow = $product->quantities->firstWhere('warehouse_id', $warehouse->id);
                                            $qtyValue = $qtyRow->quantity ?? 0;
                                        @endphp
                                        <div style="display:flex; align-items:center; justify-content:space-between; padding:10px 14px; background:#f4f8fb; border-radius:8px; flex-wrap:wrap; gap:10px;">
                                            <div style="font-size:17px;">{{ $warehouse->title }}</div>

                                            <div style="display:flex; align-items:center; gap:16px;">
                                                <div style="font-size:18px; font-weight:600; color:#024989;">{{ $qtyValue }} шт</div>

                                                <div style="display:flex; gap:10px;">
                                                    @if ($qtyRow)
                                                        <button type="button"
                                                            class="icon-btn icon-btn--edit"
                                                            title="Изменить количество"
                                                            aria-label="Изменить количество"
                                                            onclick="openEditQtyModal(this)"
                                                            data-update-url="{{ route('stock.update', $qtyRow) }}"
                                                            data-title="{{ $product->title }} — {{ $warehouse->title }}"
                                                            data-quantity="{{ $qtyRow->quantity }}">
                                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:20px; height:20px;">
                                                                <path d="M12 20h9"></path>
                                                                <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                                                            </svg>
                                                        </button>
                                                    @endif

                                                    <button type="button"
                                                        class="icon-btn icon-btn--add"
                                                        title="Добавить поступление"
                                                        aria-label="Добавить поступление"
                                                        onclick="openAddQtyModal(this)"
                                                        data-product-id="{{ $product->id }}"
                                                        data-warehouse-id="{{ $warehouse->id }}"
                                                        data-title="{{ $product->title }} — {{ $warehouse->title }}">
                                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:20px; height:20px;">
                                                            <path d="M12 5v14"></path>
                                                            <path d="M5 12h14"></path>
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <p id="parts-empty-message" style="display:none; font-size:19px; color:#555; margin-top:16px;">Ничего не найдено.</p>
            @endif
        </div>
    </div>

    {{-- Модалка "Изменить количество" (карандаш) --}}
    <div id="edit-qty-modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center; padding:20px;">
        <div style="background:#fff; border:2px solid #024989; border-radius:10px; padding:24px; max-width:420px; width:100%;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                <div style="font-size:20px; font-weight:600; color:#024989;">Изменить количество</div>
                <button type="button" onclick="closeEditQtyModal()" aria-label="Закрыть" style="border:none; background:none; font-size:28px; line-height:1; color:#024989; cursor:pointer;">&times;</button>
            </div>
            <div id="edit-qty-item-name" style="font-size:17px; color:#555; margin-bottom:18px;"></div>

            <form id="edit-qty-form" method="POST" action="" style="display:grid; gap:16px;">
                @csrf
                @method('PUT')
                <div>
                    <label for="edit-qty-value">Новое количество (шт)</label>
                    <input type="text" inputmode="numeric" id="edit-qty-value" name="quantity" class="number-spaced" required>
                </div>
                <button type="submit" class="btn-primary">Сохранить</button>
            </form>
        </div>
    </div>

    {{-- Модалка "Добавить поступление" (плюсик) --}}
    <div id="add-qty-modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center; padding:20px;">
        <div style="background:#fff; border:2px solid #2e7d32; border-radius:10px; padding:24px; max-width:420px; width:100%;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                <div style="font-size:20px; font-weight:600; color:#2e7d32;">Добавить поступление</div>
                <button type="button" onclick="closeAddQtyModal()" aria-label="Закрыть" style="border:none; background:none; font-size:28px; line-height:1; color:#2e7d32; cursor:pointer;">&times;</button>
            </div>
            <div id="add-qty-item-name" style="font-size:17px; color:#555; margin-bottom:18px;"></div>

            <form id="add-qty-form" method="POST" action="{{ route('receipts.add') }}" style="display:grid; gap:16px;">
                @csrf
                <input type="hidden" id="add-qty-product-id" name="product_id" value="">
                <input type="hidden" id="add-qty-warehouse-id" name="warehouse_id" value="">
                <div>
                    <label for="add-qty-value">Сколько добавить (шт)</label>
                    <input type="text" inputmode="numeric" id="add-qty-value" name="quantity" class="number-spaced" placeholder="10" required>
                </div>
                <button type="submit" class="btn-primary" style="background:#2e7d32;">Добавить</button>
            </form>
        </div>
    </div>

    <style>
        .icon-btn--add {
            color: #2e7d32;
            border-color: #2e7d32;
        }
        .icon-btn--add:hover {
            background: #e6f4ea;
        }
    </style>

    <script>
        function formatSpaced(value) {
            if (!value) return '';
            return String(value).replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
        }

        // --- Модалка "Изменить количество" ---
        function openEditQtyModal(btn) {
            document.getElementById('edit-qty-form').action = btn.dataset.updateUrl;
            document.getElementById('edit-qty-item-name').textContent = btn.dataset.title;
            document.getElementById('edit-qty-value').value = formatSpaced(btn.dataset.quantity);
            document.getElementById('edit-qty-modal').style.display = 'flex';
        }
        function closeEditQtyModal() {
            document.getElementById('edit-qty-modal').style.display = 'none';
        }
        document.getElementById('edit-qty-modal').addEventListener('click', function (e) {
            if (e.target === this) closeEditQtyModal();
        });

        // --- Модалка "Добавить поступление" ---
        function openAddQtyModal(btn) {
            document.getElementById('add-qty-product-id').value = btn.dataset.productId;
            document.getElementById('add-qty-warehouse-id').value = btn.dataset.warehouseId;
            document.getElementById('add-qty-item-name').textContent = btn.dataset.title;
            document.getElementById('add-qty-value').value = '';
            document.getElementById('add-qty-modal').style.display = 'flex';
        }
        function closeAddQtyModal() {
            document.getElementById('add-qty-modal').style.display = 'none';
        }
        document.getElementById('add-qty-modal').addEventListener('click', function (e) {
            if (e.target === this) closeAddQtyModal();
        });

        // Форматирование чисел пробелами + очистка перед отправкой
        document.querySelectorAll('.number-spaced').forEach(function (input) {
            input.addEventListener('input', function () {
                const raw = input.value.replace(/\D/g, '');
                input.value = formatSpaced(raw);
            });
            input.closest('form').addEventListener('submit', function () {
                input.value = input.value.replace(/\s/g, '');
            });
        });

        // --- Живой клиентский поиск по запчастям ---
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
    </script>

@endsection