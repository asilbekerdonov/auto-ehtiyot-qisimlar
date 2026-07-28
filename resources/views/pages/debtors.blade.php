@extends('layouts.app')

@section('title', 'Должники — Автозапчасти')

@section('content')

    {{-- TODO: заменить на данные из БД (customers + сумма непогашенных orders) --}}
    @php
        $debtors = [
            ['name' => 'Бен', 'phone' => '+998 90 123 45 67', 'debt' => '100 000 сум', 'last_sale' => '2 дня назад'],
            ['name' => 'Азиз Каримов', 'phone' => '+998 91 234 56 78', 'debt' => '350 000 сум', 'last_sale' => '5 дней назад'],
        ];
    @endphp

    <div style="border:2px solid #024989; border-radius:10px; overflow:hidden;">
        <div style="display:grid; grid-template-columns: 2fr 1.5fr 1.5fr 2fr 1.5fr; background:#024989; color:#fff;">
            <div style="padding:14px 12px; font-size:18px; font-weight:500;">Клиент</div>
            <div style="padding:14px 12px; font-size:18px; font-weight:500;">Телефон</div>
            <div style="padding:14px 12px; font-size:18px; font-weight:500;">Долг</div>
            <div style="padding:14px 12px; font-size:18px; font-weight:500;">Последняя продажа</div>
            <div style="padding:14px 12px; font-size:18px; font-weight:500;"></div>
        </div>

        @forelse ($debtors as $debtor)
            <div style="display:grid; grid-template-columns: 2fr 1.5fr 1.5fr 2fr 1.5fr; border-top:1px solid #e2e2e2;">
                <div style="padding:14px 12px; font-size:19px;">{{ $debtor['name'] }}</div>
                <div style="padding:14px 12px; font-size:19px;">{{ $debtor['phone'] }}</div>
                <div style="padding:14px 12px; font-size:19px; font-weight:600; color:#024989;">{{ $debtor['debt'] }}</div>
                <div style="padding:14px 12px; font-size:19px;">{{ $debtor['last_sale'] }}</div>
                <div style="padding:10px 12px;">
                    <form method="POST" action="{{ url('/debtors/' . $loop->index . '/pay') }}">
                        @csrf
                        <button type="submit" style="width:100%; padding:10px; font-size:16px; border-radius:8px; border:2px solid #024989; background:#fff; color:#024989; font-weight:500; cursor:pointer;">
                            Оплачено
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div style="padding:20px; font-size:19px; color:#555;">Должников пока нет.</div>
        @endforelse
    </div>
@endsection