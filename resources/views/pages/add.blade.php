@extends('layouts.app')

@section('title', 'Добавить — Автозапчасти')

@section('content')

    @if (session('success'))
        <div style="padding:14px 18px; margin-bottom:20px; background:#e6f4ea; border:2px solid #2e7d32; color:#2e7d32; border-radius:10px; font-size:18px;">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div style="padding:14px 18px; margin-bottom:20px; background:#fdecea; border:2px solid #a32d2d; color:#a32d2d; border-radius:10px; font-size:18px;">
            <div style="font-weight:600; margin-bottom:6px;">Проверьте форму:</div>
            <ul style="padding-left:20px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Быстрые вкладки --}}
    <div style="display:grid; grid-template-columns:1fr 1fr 1fr 1fr; gap:16px; margin-bottom:24px;">
        <button type="button" data-tab="product" class="tile tab-btn" style="font-size:18px; border:none; cursor:pointer;">+ Товар</button>
        <button type="button" data-tab="category" class="tile tab-btn" style="font-size:18px; border:none; cursor:pointer;">+ Категория</button>
        <button type="button" data-tab="warehouse" class="tile tab-btn" style="font-size:18px; border:none; cursor:pointer;">+ Склад</button>
        <button type="button" data-tab="car" class="tile tab-btn" style="font-size:18px; border:none; cursor:pointer;">+ Марка авто</button>
    </div>

    {{-- Товар --}}
    <div data-panel="product" style="border:2px solid #024989; border-radius:10px; padding:24px; margin-bottom:20px;">
        <div style="font-size:22px; font-weight:600; color:#024989; margin-bottom:20px;">Добавить товар</div>

        <form id="product-form" method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data" style="display:grid; gap:18px; max-width:460px;">
            @csrf

            <div>
                <label for="image">Фото товара</label>
                <input type="file" id="image" name="image" accept="image/*" style="width:100%; font-size:17px; padding:10px 0;">
            </div>

            <div>
                <label for="title">Название</label>
                <input type="text" id="title" name="title" value="{{ old('title') }}" placeholder="Поиск" required>
            </div>

            <div>
                <label for="category_id">Категория</label>
                <select id="category_id" name="category_id" required>
                    @forelse ($categories as $category)
                        <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>{{ $category->title }}</option>
                    @empty
                        <option value="" disabled selected>Сначала добавьте категорию</option>
                    @endforelse
                </select>
            </div>

            <div>
                <label for="car_id">Марка авто (необязательно)</label>
                <select id="car_id" name="car_id">
                    <option value="">Без привязки</option>
                    @foreach ($cars as $car)
                        <option value="{{ $car->id }}" @selected(old('car_id') == $car->id)>{{ $car->title }}</option>
                    @endforeach
                </select>
            </div>
   
            <div>
                <label for="position_id">Позиция (необязательно)</label>
                <select id="position_id" name="position_id">
                    <option value="">Не указано</option>
                    @foreach ($positions as $position)
                        <option value="{{ $position->id }}" @selected(old('position_id') == $position->id)>{{ $position->title }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="color_id">Цвет (необязательно)</label>
                <select id="color_id" name="color_id">
                    <option value="">Не указано</option>
                    @foreach ($colors as $color)
                        <option value="{{ $color->id }}" @selected(old('color_id') == $color->id)>{{ $color->title }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="cost_price">Себестоимость</label>
                <input type="text" inputmode="numeric" id="cost_price" name="cost_price" value="{{ old('cost_price') }}" class="number-spaced" placeholder="45 000" required>
            </div>

            <div>
                <label for="markup">Наценка</label>
                <input type="text" inputmode="numeric" id="markup" name="markup" value="{{ old('markup') }}" class="number-spaced" placeholder="15 000" required>
            </div>

            <div>
                <label for="unit_id">Единица измерения</label>
                <select id="unit_id" name="unit_id">
                    @foreach ($units as $unit)
                        <option value="{{ $unit->id }}" @selected(old('unit_id') == $unit->id)>{{ $unit->title }}</option>
                    @endforeach
                </select>
            </div>

            <hr style="border:none; border-top:1px solid #e2e2e2; margin:4px 0;">

            <div>
                <label for="warehouse_id">Склад</label>
                <select id="warehouse_id" name="warehouse_id" required>
                    @forelse ($warehouses as $warehouse)
                        <option value="{{ $warehouse->id }}" @selected(old('warehouse_id') == $warehouse->id)>{{ $warehouse->title }}</option>
                    @empty
                        <option value="" disabled selected>Сначала добавьте склад</option>
                    @endforelse
                </select>
            </div>

            <div>
                <label for="quantity">Количество на складе</label>
                <input type="text" inputmode="numeric" id="quantity" name="quantity" value="{{ old('quantity') }}" class="number-spaced" placeholder="10" required>
            </div>

            <button type="submit" class="btn-primary">Сохранить</button>
        </form>
    </div>

    {{-- Категория --}}
    <div data-panel="category" style="border:2px solid #024989; border-radius:10px; padding:24px; margin-bottom:20px; display:none;">
        <div style="font-size:22px; font-weight:600; color:#024989; margin-bottom:20px;">Категории</div>

        <form method="POST" action="{{ route('categories.store') }}" style="display:flex; gap:12px; max-width:520px; margin-bottom:24px;">
            @csrf
            <input type="text" name="title" placeholder="Название категории" required style="flex:1;">
            <button type="submit" class="btn-primary" style="padding:14px 24px;">Добавить</button>
        </form>

        <div>
            @forelse ($categories as $category)
                <div style="display:flex; align-items:center; justify-content:space-between; padding:12px 0; border-bottom:1px solid #e2e2e2; font-size:19px;">
                    <span>{{ $category->title }}</span>
                    <form method="POST" action="{{ route('categories.destroy', $category) }}" onsubmit="return confirm('Удалить категорию «{{ $category->title }}»?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" style="padding:8px 16px; font-size:16px; border-radius:8px; border:2px solid #a32d2d; background:#fff; color:#a32d2d; cursor:pointer;">Удалить</button>
                    </form>
                </div>
            @empty
                <p style="font-size:18px; color:#555;">Категорий пока нет.</p>
            @endforelse
        </div>
    </div>

    {{-- Склад --}}
    <div data-panel="warehouse" style="border:2px solid #024989; border-radius:10px; padding:24px; margin-bottom:20px; display:none;">
        <div style="font-size:22px; font-weight:600; color:#024989; margin-bottom:20px;">Склады</div>

        <form method="POST" action="{{ route('warehouses.store') }}" style="display:flex; gap:12px; max-width:520px; margin-bottom:24px;">
            @csrf
            <input type="text" name="title" placeholder="Название склада" required style="flex:1;">
            <button type="submit" class="btn-primary" style="padding:14px 24px;">Добавить</button>
        </form>

        <div>
            @forelse ($warehouses as $warehouse)
                <div style="display:flex; align-items:center; justify-content:space-between; padding:12px 0; border-bottom:1px solid #e2e2e2; font-size:19px;">
                    <span>{{ $warehouse->title }}</span>
                    <form method="POST" action="{{ route('warehouses.destroy', $warehouse) }}" onsubmit="return confirm('Удалить склад «{{ $warehouse->title }}»?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" style="padding:8px 16px; font-size:16px; border-radius:8px; border:2px solid #a32d2d; background:#fff; color:#a32d2d; cursor:pointer;">Удалить</button>
                    </form>
                </div>
            @empty
                <p style="font-size:18px; color:#555;">Складов пока нет.</p>
            @endforelse
        </div>
    </div>


{{-- Марка авто --}}
<div data-panel="car" style="border:2px solid #024989; border-radius:10px; padding:24px; display:none;">
    <div style="font-size:22px; font-weight:600; color:#024989; margin-bottom:20px;">Марки авто</div>

    <form method="POST" action="{{ route('cars.store') }}" enctype="multipart/form-data" style="display:grid; gap:18px; max-width:520px; margin-bottom:24px;">
        @csrf
        <div>
            <label for="car_image">Фото марки авто</label>
            <input type="file" id="car_image" name="image" accept="image/*" style="width:100%; font-size:17px; padding:10px 0;">
        </div>

        <div style="display:flex; gap:12px;">
            <input type="text" name="title" placeholder="Например, Chevrolet Cobalt" required style="flex:1;">
            <button type="submit" class="btn-primary" style="padding:14px 24px;">Добавить</button>
        </div>
    </form>

    <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(200px, 1fr)); gap:16px;">
        @forelse ($cars as $car)
            <div style="border:2px solid #024989; border-radius:12px; overflow:hidden; background:#fff;">
                <img src="{{ $car->image ? asset('storage/' . $car->image) : 'https://placehold.co/200x150/e8f0f7/024989?text=Фото+авто' }}" 
                     alt="{{ $car->title }}"
                     style="width:100%; height:120px; object-fit:cover; background:#e8f0f7;">
                <div style="padding:12px 16px; display:flex; align-items:center; justify-content:space-between;">
                    <span style="font-size:17px; font-weight:500;">{{ $car->title }}</span>
                    <form method="POST" action="{{ route('cars.destroy', $car) }}" onsubmit="return confirm('Удалить марку «{{ $car->title }}»?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" style="padding:6px 14px; font-size:14px; border-radius:6px; border:2px solid #a32d2d; background:#fff; color:#a32d2d; cursor:pointer;">Удалить</button>
                    </form>
                </div>
            </div>
        @empty
            <p style="font-size:18px; color:#555; grid-column:1/-1;">Марок пока нет.</p>
        @endforelse
    </div>
</div>

    <script>
        // --- Переключение вкладок ---
        const buttons = document.querySelectorAll('.tab-btn');
        const panels = document.querySelectorAll('[data-panel]');

        function showPanel(name) {
            panels.forEach(p => p.style.display = p.dataset.panel === name ? 'block' : 'none');
        }

        buttons.forEach(btn => {
            btn.addEventListener('click', () => showPanel(btn.dataset.tab));
        });

        // По умолчанию показываем товары (или product, если есть ошибки)
        const defaultTab = '{{ $errors->has('warehouse_id') || $errors->has('quantity') || $errors->has('cost_price') || $errors->has('position_id') || $errors->has('color_id') ? 'product' : 'product' }}';
        showPanel(defaultTab);

        // --- Форматирование чисел пробелами (400000 -> 400 000) ---
        function formatWithSpaces(value) {
            const digits = value.replace(/\D/g, '');
            return digits.replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
        }

        document.querySelectorAll('.number-spaced').forEach(input => {
            input.addEventListener('input', () => {
                const cursorFromEnd = input.value.length - input.selectionStart;
                input.value = formatWithSpaces(input.value);
                const pos = input.value.length - cursorFromEnd;
                input.setSelectionRange(pos, pos);
            });
            // сразу форматируем значение при перезагрузке страницы после ошибки валидации
            input.value = formatWithSpaces(input.value);
        });

        // Перед отправкой формы убираем пробелы — в БД нужно чистое число
        const productForm = document.getElementById('product-form');
        productForm.addEventListener('submit', () => {
            productForm.querySelectorAll('.number-spaced').forEach(input => {
                input.value = input.value.replace(/\s/g, '');
            });
        });
    </script>

@endsection