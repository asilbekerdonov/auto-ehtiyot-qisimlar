@extends('layouts.app')

@section('title', 'Товары — Автозапчасти')

@section('content')
    @php
        // Заглушка, пока у товара нет фото
        $placeholder = 'https://placehold.co/500x300/e8f0f7/024989?text=Фото+товара';
    @endphp

    <div style="display:grid; grid-template-columns:25% 75%; border:2px solid #024989; border-radius:10px; overflow:hidden;">
        {{-- Категории --}}
        <div style="background:#024989; color:#fff;">
            <div style="padding:16px 18px; font-size:20px; font-weight:500; border-bottom:1px solid rgba(255,255,255,0.3);">
                Категории
            </div>

            <a href="{{ route('goods') }}"
                style="display:block; padding:16px 18px; font-size:19px; text-decoration:none; color:#fff; {{ !$selectedCategoryId ? 'background:rgba(255,255,255,0.18); font-weight:500;' : '' }} border-bottom:1px solid rgba(255,255,255,0.15);">
                Все категории
            </a>

            @foreach ($categories as $category)
                <a href="{{ route('goods', ['category' => $category->id]) }}"
                    style="display:block; padding:16px 18px; font-size:19px; text-decoration:none; color:#fff; {{ (string) $selectedCategoryId === (string) $category->id ? 'background:rgba(255,255,255,0.18); font-weight:500;' : '' }} border-bottom:1px solid rgba(255,255,255,0.15);">
                    {{ $category->title }}
                </a>
            @endforeach
        </div>

        {{-- Карточки товаров --}}
        <div style="padding:20px; background:#fff;">
            {{-- Живой поиск: чисто на клиенте, без запросов к серверу --}}
            <input type="text" id="goods-search-input"
                placeholder="Например: буф — сразу покажет «Буфер» и похожие"
                autocomplete="off" style="width:100%; margin-bottom:20px;">

            @if ($products->isEmpty())
                <p style="font-size:19px; color:#555;">В этой категории пока нет товаров.</p>
            @else
                <div id="goods-products-grid" style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                    @foreach ($products as $product)
                        <div class="product-card" data-search="{{ Str::lower($product->title) }}" style="border:2px solid #024989; border-radius:12px; overflow:hidden;">
                            <img src="{{ $product->image ? asset('storage/' . $product->image) : $placeholder }}" alt="{{ $product->title }}" style="width:100%; height:160px; object-fit:cover; display:block; background:#e8f0f7;">
                            <div style="padding:16px 18px;">
                                {{-- Название с позицией и цветом сбоку --}}
                                <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap; margin-bottom:4px;">
                                    <span style="font-size:19px; font-weight:500;">{{ $product->title }}</span>
                                    @if ($product->position)
                                        <span style="font-size:15px; background:#e8f0f7; color:#024989; padding:2px 8px; border-radius:6px; font-weight:500;">
                                            {{ $product->position->title }}
                                        </span>
                                    @endif
                                    @if ($product->color)
                                        <span style="font-size:15px; background:#e8f0f7; color:#024989; padding:2px 8px; border-radius:6px; font-weight:500;">
                                            {{ $product->color->title }}
                                        </span>
                                    @endif
                                </div>
                                <div style="font-size:15px; color:#777; margin-bottom:8px;">{{ $product->category->title }}</div>
                            
                                @if ($product->car)
                                    <div style="font-size:14px; color:#0a6b3a; margin-bottom:8px;">{{ $product->car->title }}</div>
                                @endif
                                <div style="font-size:18px; color:#024989; font-weight:600; margin-bottom:4px;">
                                    {{ number_format($product->selling_price, 0, ',', ' ') }} сум
                                </div>
                                <div style="font-size:16px; color:#555; margin-bottom:12px;">Остаток: {{ $product->total_stock }} шт</div>

                                <div style="display:flex; justify-content:flex-end; gap:10px;">
                                    <button type="button"
                                        class="icon-btn icon-btn--edit"
                                        title="Изменить"
                                        aria-label="Изменить товар"
                                        onclick="openEditProductModal(this)"
                                        data-update-url="{{ route('products.update', $product) }}"
                                        data-title="{{ $product->title }}"
                                        data-category-id="{{ $product->category_id }}"
                                        data-position-id="{{ $product->position_id }}"
                                        data-color-id="{{ $product->color_id }}"
                                        data-car-id="{{ $product->car_id }}"
                                        data-unit-id="{{ $product->unit_id }}"
                                        data-cost-price="{{ (int) $product->cost_price }}"
                                        data-markup="{{ (int) $product->markup }}"
                                        data-image="{{ $product->image ? asset('storage/' . $product->image) : '' }}"
                                        data-total-stock="{{ $product->total_stock }}">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M12 20h9"></path>
                                            <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                                        </svg>
                                    </button>

                                    <form method="POST" action="{{ route('products.destroy', $product) }}" class="delete-product-form" data-title="{{ $product->title }}" style="margin:0;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="icon-btn icon-btn--delete" title="Удалить" aria-label="Удалить товар">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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
                <p id="goods-empty-message" style="display:none; font-size:19px; color:#555; margin-top:16px;">Ничего не найдено.</p>
            @endif
        </div>
    </div>

    {{-- Модальное окно "Изменить товар" --}}
    <div id="edit-product-modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center; padding:20px;">
        <div style="background:#fff; border:2px solid #024989; border-radius:10px; padding:24px; max-width:500px; width:100%; max-height:90vh; overflow-y:auto;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                <div style="font-size:22px; font-weight:600; color:#024989;">Изменить товар</div>
                <button type="button" onclick="closeEditProductModal()" aria-label="Закрыть" style="border:none; background:none; font-size:30px; line-height:1; color:#024989; cursor:pointer;">&times;</button>
            </div>

            <form id="edit-product-form" method="POST" action="" enctype="multipart/form-data" style="display:grid; gap:18px;">
                @csrf
                @method('PUT')

                <div id="edit-current-image-wrap" style="display:none;">
                    <label>Текущее фото</label>
                    <img id="edit-current-image" src="" alt="" style="width:120px; height:90px; object-fit:cover; border-radius:8px; border:2px solid #b8cfe0; display:block;">
                </div>

                <div>
                    <label for="edit-image">Новое фото (необязательно)</label>
                    <input type="file" id="edit-image" name="image" accept="image/*" style="width:100%; font-size:17px; padding:10px 0;">
                </div>

                <div>
                    <label for="edit-title">Название</label>
                    <input type="text" id="edit-title" name="title" required>
                </div>

                <div>
                    <label for="edit-category_id">Категория</label>
                    <select id="edit-category_id" name="category_id" required>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->title }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="edit-position_id">Позиция (необязательно)</label>
                    <select id="edit-position_id" name="position_id">
                        <option value="">Не указано</option>
                        @foreach ($positions as $position)
                            <option value="{{ $position->id }}">{{ $position->title }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="edit-color_id">Цвет (необязательно)</label>
                    <select id="edit-color_id" name="color_id">
                        <option value="">Не указано</option>
                        @foreach ($colors as $color)
                            <option value="{{ $color->id }}">{{ $color->title }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="edit-car_id">Марка авто (необязательно)</label>
                    <select id="edit-car_id" name="car_id">
                        <option value="">Без привязки</option>
                        @foreach ($cars as $car)
                            <option value="{{ $car->id }}">{{ $car->title }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="edit-cost_price">Себестоимость</label>
                    <input type="text" inputmode="numeric" id="edit-cost_price" name="cost_price" class="number-spaced" required>
                </div>

                <div>
                    <label for="edit-markup">Наценка</label>
                    <input type="text" inputmode="numeric" id="edit-markup" name="markup" class="number-spaced" required>
                </div>

                <div>
                    <label for="edit-quantity">Количество</label>
                    <input type="text" inputmode="numeric" id="edit-quantity" name="quantity" class="number-spaced" required placeholder="Введите количество">
                </div>

                <div>
                    <label for="edit-unit_id">Единица измерения</label>
                    <select id="edit-unit_id" name="unit_id">
                        @foreach ($units as $unit)
                            <option value="{{ $unit->id }}">{{ $unit->title }}</option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="btn-primary">Сохранить изменения</button>
            </form>
        </div>
    </div>

    <script>
        function formatSpaced(value) {
            if (!value) return '';
            return String(value).replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
        }

        function openEditProductModal(btn) {
            const form = document.getElementById('edit-product-form');
            form.action = btn.dataset.updateUrl;

            document.getElementById('edit-title').value = btn.dataset.title || '';
            document.getElementById('edit-category_id').value = btn.dataset.categoryId || '';
            document.getElementById('edit-position_id').value = btn.dataset.positionId || '';
            document.getElementById('edit-color_id').value = btn.dataset.colorId || '';
            document.getElementById('edit-car_id').value = btn.dataset.carId || '';
            document.getElementById('edit-unit_id').value = btn.dataset.unitId || '';
            document.getElementById('edit-cost_price').value = formatSpaced(btn.dataset.costPrice);
            document.getElementById('edit-markup').value = formatSpaced(btn.dataset.markup);
            document.getElementById('edit-quantity').value = formatSpaced(btn.dataset.totalStock || 0);
            document.getElementById('edit-image').value = '';

            const imageWrap = document.getElementById('edit-current-image-wrap');
            const imageEl = document.getElementById('edit-current-image');
            if (btn.dataset.image) {
                imageEl.src = btn.dataset.image;
                imageWrap.style.display = 'block';
            } else {
                imageWrap.style.display = 'none';
            }

            document.getElementById('edit-product-modal').style.display = 'flex';
        }

        function closeEditProductModal() {
            document.getElementById('edit-product-modal').style.display = 'none';
        }

        document.getElementById('edit-product-modal').addEventListener('click', function (e) {
            if (e.target === this) closeEditProductModal();
        });

        document.querySelectorAll('#edit-product-form .number-spaced').forEach(function (input) {
            input.addEventListener('input', function () {
                const raw = input.value.replace(/\D/g, '');
                input.value = formatSpaced(raw);
            });
        });

        document.getElementById('edit-product-form').addEventListener('submit', function () {
            ['edit-cost_price', 'edit-markup', 'edit-quantity'].forEach(function (id) {
                const el = document.getElementById(id);
                el.value = el.value.replace(/\s/g, '');
            });
        });

        document.querySelectorAll('.delete-product-form').forEach(function (form) {
            form.addEventListener('submit', function (e) {
                const title = form.dataset.title || 'этот товар';
                if (!confirm('Удалить «' + title + '»? Это действие нельзя отменить.')) {
                    e.preventDefault();
                }
            });
        });

        // --- Живой клиентский поиск: без сервера, без задержек ---
        const goodsSearchInput = document.getElementById('goods-search-input');
        if (goodsSearchInput) {
            const productCards = document.querySelectorAll('.product-card');
            const emptyMessage = document.getElementById('goods-empty-message');

            goodsSearchInput.addEventListener('input', function () {
                const query = goodsSearchInput.value.trim().toLowerCase();
                let visibleCount = 0;

                productCards.forEach(function (card) {
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