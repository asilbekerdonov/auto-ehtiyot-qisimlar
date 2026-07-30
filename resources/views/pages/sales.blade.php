@extends('layouts.app')

@section('title', 'Корзина — Продажи')

@section('content')

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:24px; flex-wrap:wrap; gap:12px;">
<a href="{{ route('sales.cars') }}" style="font-size:18px; color:#024989; text-decoration:none;">&larr; К машинам</a>
<div style="font-size:26px; font-weight:600; color:#024989;">Корзина</div>
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

    @if (empty($items))
<p style="font-size:19px; color:#555;">Корзина пуста. Выберите машину и добавьте запчасти.</p>
    @else

        {{-- Отдельные формы удаления — вне формы оформления, чтобы не вкладывать <form> в <form> --}}
        @foreach ($items as $item)
<form id="remove-form-{{ $item['key'] }}" method="POST" action="{{ route('sales.cart.remove', $item['key']) }}" style="display:none;">
                @csrf
                @method('DELETE')
</form>
        @endforeach

<form method="POST" action="{{ route('sales.checkout') }}" style="display:grid; gap:24px;">
            @csrf

<div style="border:2px solid #024989; border-radius:10px; overflow:hidden;">
<div style="display:grid; grid-template-columns: 2fr 1.2fr 1.2fr 0.8fr 1.7fr 1.4fr 0.9fr; background:#024989; color:#fff;">
<div style="padding:14px 10px; font-size:16px; font-weight:500;">Товар</div>
<div style="padding:14px 10px; font-size:16px; font-weight:500;">Склад</div>
<div style="padding:14px 10px; font-size:16px; font-weight:500;">Себестоимость</div>
<div style="padding:14px 10px; font-size:16px; font-weight:500;">Кол-во</div>
<div style="padding:14px 10px; font-size:16px; font-weight:500;">Цена за шт</div>
<div style="padding:14px 10px; font-size:16px; font-weight:500;">Сумма</div>
<div style="padding:14px 10px;"></div>
</div>

                @foreach ($items as $item)
<div style="display:grid; grid-template-columns: 2fr 1.2fr 1.2fr 0.8fr 1.7fr 1.4fr 0.9fr; border-top:1px solid #e2e2e2; align-items:center;">
<div style="padding:14px 10px; font-size:18px;">{{ $item['product']->title }}</div>
<div style="padding:14px 10px; font-size:18px;">{{ $item['warehouse']->title ?? '—' }}</div>
<div style="padding:14px 10px; font-size:17px; color:#777;">{{ number_format($item['product']->cost_price, 0, ',', ' ') }} сум</div>
<div style="padding:14px 10px; font-size:18px;">{{ $item['quantity'] }}</div>
<div style="padding:14px 10px;">
<input type="text" inputmode="numeric"
name="prices[{{ $item['key'] }}]"
value="{{ old('prices.' . $item['key'], number_format($item['price_per_unit'], 0, '', ' ')) }}"
class="number-spaced price-input"
data-key="{{ $item['key'] }}"
style="width:110px; font-size:17px; padding:10px 12px; border:2px solid #b8cfe0; border-radius:8px; outline:none;">
                            @error('prices.' . $item['key'])
<div style="color:#a32d2d; font-size:14px; margin-top:6px; max-width:160px;">{{ $message }}</div>
                            @enderror
</div>
<div id="line-total-{{ $item['key'] }}" style="padding:14px 10px; font-size:18px; font-weight:600; color:#024989;">
                            {{ number_format($item['line_total'], 0, ',', ' ') }} сум
</div>
<div style="padding:10px 10px;">
<button type="submit" form="remove-form-{{ $item['key'] }}" style="padding:8px 14px; font-size:15px; border-radius:8px; border:2px solid #a32d2d; background:#fff; color:#a32d2d; cursor:pointer;">Убрать</button>
</div>
</div>
                @endforeach
</div>

<div style="font-size:24px; font-weight:600; color:#024989; text-align:right;">
                Итого: <span id="cart-total">{{ number_format($total, 0, ',', ' ') }}</span> сум
</div>

            {{-- Оформление продажи --}}
<div style="border:2px solid #024989; border-radius:10px; padding:24px; max-width:480px;">
<div style="font-size:20px; font-weight:600; color:#024989; margin-bottom:20px;">Оформить продажу</div>

<div style="display:grid; gap:18px;">
<div>
<label style="margin-bottom:10px;">Статус оплаты</label>
<div style="display:flex; gap:20px;">
<label style="display:flex; align-items:center; gap:8px; font-weight:400; font-size:18px; color:#111;">
<input type="radio" name="status" value="оплачено" checked style="width:22px; height:22px;" onchange="toggleDebtorFields()">
                                Оплачено
</label>
<label style="display:flex; align-items:center; gap:8px; font-weight:400; font-size:18px; color:#111;">
<input type="radio" name="status" value="долг" style="width:22px; height:22px;" onchange="toggleDebtorFields()">
                                В долг
</label>
</div>
</div>

<div id="debtor-fields" style="display:none; display:grid; gap:18px;">
<div>
<label for="customer_name">Имя клиента</label>
<input type="text" id="customer_name" name="customer_name" placeholder="Например, Бен">
</div>
<div>
<label for="customer_phone">Телефон</label>
<input type="text" id="customer_phone" name="customer_phone" placeholder="+998 90 123 45 67">
</div>
</div>

<button type="submit" class="btn-primary">Оформить продажу</button>
</div>
</div>
</form>
    @endif

<script>
    function toggleDebtorFields() {
        const isDebt = document.querySelector('input[name="status"]:checked').value === 'долг';
        const fields = document.getElementById('debtor-fields');
        fields.style.display = isDebt ? 'grid' : 'none';
        document.getElementById('customer_name').required = isDebt;
    }
    toggleDebtorFields();

    function formatSpaced(value) {
        if (!value) return '0';
        return String(value).replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
    }

    const cartQuantities = {
        @foreach ($items as $item)
            '{{ $item['key'] }}': {{ $item['quantity'] }},
        @endforeach
    };

    function recalcCartTotal() {
        let total = 0;

        document.querySelectorAll('.price-input').forEach(function (input) {
            const key = input.dataset.key;
            const price = parseInt(input.value.replace(/\s/g, ''), 10) || 0;
            const qty = cartQuantities[key] || 0;
            const lineTotal = price * qty;
            total += lineTotal;

            const lineEl = document.getElementById('line-total-' + key);
            if (lineEl) {
                lineEl.textContent = formatSpaced(lineTotal) + ' сум';
            }
        });

        const totalEl = document.getElementById('cart-total');
        if (totalEl) {
            totalEl.textContent = formatSpaced(total);
        }
    }

    // Форматирование цены пробелами при вводе + пересчёт суммы на лету (без запросов к серверу)
    document.querySelectorAll('.price-input').forEach(function (input) {
        input.addEventListener('input', function () {
            const raw = input.value.replace(/\D/g, '');
            input.value = formatSpaced(raw);
            recalcCartTotal();
        });
    });
</script>

@endsection