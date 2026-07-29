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
        <div style="border:2px solid #024989; border-radius:10px; overflow:hidden; margin-bottom:24px;">
            <div style="display:grid; grid-template-columns: 2fr 1.5fr 1fr 1.5fr 1.5fr 1fr; background:#024989; color:#fff;">
                <div style="padding:14px 12px; font-size:17px; font-weight:500;">Товар</div>
                <div style="padding:14px 12px; font-size:17px; font-weight:500;">Склад</div>
                <div style="padding:14px 12px; font-size:17px; font-weight:500;">Кол-во</div>
                <div style="padding:14px 12px; font-size:17px; font-weight:500;">Цена за шт</div>
                <div style="padding:14px 12px; font-size:17px; font-weight:500;">Сумма</div>
                <div style="padding:14px 12px;"></div>
            </div>

            @foreach ($items as $item)
                <div style="display:grid; grid-template-columns: 2fr 1.5fr 1fr 1.5fr 1.5fr 1fr; border-top:1px solid #e2e2e2;">
                    <div style="padding:14px 12px; font-size:18px;">{{ $item['product']->title }}</div>
                    <div style="padding:14px 12px; font-size:18px;">{{ $item['warehouse']->title ?? '—' }}</div>
                    <div style="padding:14px 12px; font-size:18px;">{{ $item['quantity'] }}</div>
                    <div style="padding:14px 12px; font-size:18px;">{{ number_format($item['price_per_unit'], 0, ',', ' ') }} сум</div>
                    <div style="padding:14px 12px; font-size:18px; font-weight:600; color:#024989;">{{ number_format($item['line_total'], 0, ',', ' ') }} сум</div>
                    <div style="padding:10px 12px;">
                        <form method="POST" action="{{ route('sales.cart.remove', $item['key']) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" style="padding:8px 14px; font-size:15px; border-radius:8px; border:2px solid #a32d2d; background:#fff; color:#a32d2d; cursor:pointer;">Убрать</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>

        <div style="font-size:24px; font-weight:600; color:#024989; margin-bottom:24px; text-align:right;">
            Итого: {{ number_format($total, 0, ',', ' ') }} сум
        </div>

        {{-- Оформление продажи --}}
        <div style="border:2px solid #024989; border-radius:10px; padding:24px; max-width:480px;">
            <div style="font-size:20px; font-weight:600; color:#024989; margin-bottom:20px;">Оформить продажу</div>

            <form method="POST" action="{{ route('sales.checkout') }}" style="display:grid; gap:18px;">
                @csrf

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
            </form>
        </div>
    @endif

    <script>
        function toggleDebtorFields() {
            const isDebt = document.querySelector('input[name="status"]:checked').value === 'долг';
            const fields = document.getElementById('debtor-fields');
            fields.style.display = isDebt ? 'grid' : 'none';

            document.getElementById('customer_name').required = isDebt;
        }
        toggleDebtorFields();
    </script>

@endsection
