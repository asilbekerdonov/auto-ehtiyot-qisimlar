@extends('layouts.app')

@section('title', 'Корзина — Продажи')

@section('styles')
<style>
.cart-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 24px;
    flex-wrap: wrap;
    gap: 12px;
}
.cart-back-link {
    font-size: 18px;
    color: #024989;
    text-decoration: none;
}
.cart-title {
    font-size: 26px;
    font-weight: 600;
    color: #024989;
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
.checkout-form {
    display: grid;
    gap: 24px;
}

/* Таблица корзины */
.cart-table {
    border: 2px solid #024989;
    border-radius: 10px;
    overflow: hidden;
}
.cart-row {
    display: grid;
    grid-template-columns: 2fr 1.2fr 1.2fr 0.8fr 1.7fr 1.4fr 0.9fr;
}
.cart-header-row {
    background: #024989;
    color: #fff;
}
.cart-header-row .cart-cell {
    font-size: 16px;
    font-weight: 500;
}
.cart-body-row {
    border-top: 1px solid #e2e2e2;
    align-items: center;
}
.cart-cell {
    padding: 14px 10px;
    font-size: 18px;
}
.cart-cell--muted {
    font-size: 17px;
    color: #777;
}
.cart-cell--total {
    font-size: 18px;
    font-weight: 600;
    color: #024989;
}
.cart-cell-label {
    display: none;
}
.price-input {
    width: 110px;
    font-size: 17px;
    padding: 10px 12px;
    border: 2px solid #b8cfe0;
    border-radius: 8px;
    outline: none;
}
.price-error {
    color: #a32d2d;
    font-size: 14px;
    margin-top: 6px;
    max-width: 160px;
}
.remove-btn {
    padding: 8px 14px;
    font-size: 15px;
    border-radius: 8px;
    border: 2px solid #a32d2d;
    background: #fff;
    color: #a32d2d;
    cursor: pointer;
}
.cart-total-row {
    font-size: 24px;
    font-weight: 600;
    color: #024989;
    text-align: right;
}

/* Блок оформления */
.checkout-box {
    border: 2px solid #024989;
    border-radius: 10px;
    padding: 24px;
    max-width: 480px;
}
.checkout-box-title {
    font-size: 20px;
    font-weight: 600;
    color: #024989;
    margin-bottom: 20px;
}
.status-radio-group {
    display: flex;
    gap: 20px;
}
.status-radio-label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 400;
    font-size: 18px;
    color: #111;
}
.status-radio-label input {
    width: 22px;
    height: 22px;
}
#debtor-fields {
    display: grid;
    gap: 18px;
}

/* ===== Телефоны: строка корзины превращается в карточку ===== */
@media (max-width: 700px) {
    .cart-title {
        font-size: 21px;
    }
    .cart-back-link {
        font-size: 16px;
    }

    .cart-header-row {
        display: none;
    }
    .cart-table {
        border: none;
        border-radius: 0;
        display: flex;
        flex-direction: column;
        gap: 14px;
    }
    .cart-row.cart-body-row {
        display: block;
        border: 2px solid #024989;
        border-top: 2px solid #024989;
        border-radius: 12px;
        overflow: hidden;
    }
    .cart-cell {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        padding: 12px 16px;
        font-size: 17px;
        border-bottom: 1px solid #e2e2e2;
        text-align: right;
    }
    .cart-cell-label {
        display: inline;
        font-size: 14px;
        font-weight: 500;
        color: #777;
        text-align: left;
        flex-shrink: 0;
    }

    /* Поле цены растягивается на всю ширину строки на мобильном */
    .cart-cell--price {
        flex-direction: column;
        align-items: stretch;
        text-align: left;
    }
    .cart-cell--price .cart-cell-label {
        margin-bottom: 6px;
    }
    .price-input {
        width: 100%;
        font-size: 16px; /* защита от авто-зума iOS */
    }
    .price-error {
        max-width: none;
    }

    /* Кнопка "Убрать" — во всю ширину внизу карточки */
    .cart-cell--remove {
        border-bottom: none;
        display: block;
        padding: 12px 16px;
    }
    .remove-btn {
        width: 100%;
        padding: 12px;
        font-size: 16px;
    }

    .cart-total-row {
        text-align: center;
        font-size: 20px;
    }

    .checkout-box {
        max-width: none;
        padding: 18px;
    }
    .status-radio-group {
        gap: 24px;
    }
}
</style>
@endsection

@section('content')

<div class="cart-header">
    <a href="{{ route('sales.cars') }}" class="cart-back-link">&larr; К машинам</a>
    <div class="cart-title">Корзина</div>
    <div></div>
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

        <form method="POST" action="{{ route('sales.checkout') }}" class="checkout-form">
            @csrf

            <div class="cart-table">
                <div class="cart-row cart-header-row">
                    <div class="cart-cell">Товар</div>
                    <div class="cart-cell">Склад</div>
                    <div class="cart-cell">Себестоимость</div>
                    <div class="cart-cell">Кол-во</div>
                    <div class="cart-cell">Цена за шт</div>
                    <div class="cart-cell">Сумма</div>
                    <div class="cart-cell"></div>
                </div>

                @foreach ($items as $item)
                    <div class="cart-row cart-body-row">
                        <div class="cart-cell">
                            <span class="cart-cell-label">Товар</span>
                            {{ $item['product']->title }}
                        </div>
                        <div class="cart-cell">
                            <span class="cart-cell-label">Склад</span>
                            {{ $item['warehouse']->title ?? '—' }}
                        </div>
                        <div class="cart-cell cart-cell--muted">
                            <span class="cart-cell-label">Себестоимость</span>
                            {{ number_format($item['product']->cost_price, 0, ',', ' ') }} сум
                        </div>
                        <div class="cart-cell">
                            <span class="cart-cell-label">Кол-во</span>
                            {{ $item['quantity'] }}
                        </div>
                        <div class="cart-cell cart-cell--price">
                            <span class="cart-cell-label">Цена за шт</span>
                            <input type="text" inputmode="numeric"
                                name="prices[{{ $item['key'] }}]"
                                value="{{ old('prices.' . $item['key'], number_format($item['price_per_unit'], 0, '', ' ')) }}"
                                class="number-spaced price-input"
                                data-key="{{ $item['key'] }}">
                            @error('prices.' . $item['key'])
                                <div class="price-error">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="cart-cell cart-cell--total" id="line-total-{{ $item['key'] }}">
                            <span class="cart-cell-label">Сумма</span>
                            {{ number_format($item['line_total'], 0, ',', ' ') }} сум
                        </div>
                        <div class="cart-cell cart-cell--remove">
                            <button type="submit" form="remove-form-{{ $item['key'] }}" class="remove-btn">Убрать</button>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="cart-total-row">
                Итого: <span id="cart-total">{{ number_format($total, 0, ',', ' ') }}</span> сум
            </div>

            {{-- Оформление продажи --}}
            <div class="checkout-box">
                <div class="checkout-box-title">Оформить продажу</div>

                <div style="display:grid; gap:18px;">
                    <div>
                        <label style="margin-bottom:10px;">Статус оплаты</label>
                        <div class="status-radio-group">
                            <label class="status-radio-label">
                                <input type="radio" name="status" value="оплачено" checked onchange="toggleDebtorFields()">
                                Оплачено
                            </label>
                            <label class="status-radio-label">
                                <input type="radio" name="status" value="долг" onchange="toggleDebtorFields()">
                                В долг
                            </label>
                        </div>
                    </div>

                    <div id="debtor-fields" style="display:none;">
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
                const labelEl = lineEl.querySelector('.cart-cell-label');
                lineEl.textContent = '';
                if (labelEl) lineEl.appendChild(labelEl);
                lineEl.append(formatSpaced(lineTotal) + ' сум');
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