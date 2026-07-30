@extends('layouts.app')

@section('title', 'Должники — Автозапчасти')

@section('content')

<div style="border:2px solid #024989; border-radius:10px; overflow:hidden;">
<div style="display:grid; grid-template-columns: 2fr 1.5fr 1.5fr 2fr 1.5fr; background:#024989; color:#fff;">
<div style="padding:14px 12px; font-size:18px; font-weight:500;">Клиент</div>
<div style="padding:14px 12px; font-size:18px; font-weight:500;">Телефон</div>
<div style="padding:14px 12px; font-size:18px; font-weight:500;">Долг</div>
<div style="padding:14px 12px; font-size:18px; font-weight:500;">Последняя продажа</div>
<div style="padding:14px 12px; font-size:18px; font-weight:500;"></div>
</div>

    @forelse ($debtors as $customer)
<div style="display:grid; grid-template-columns: 2fr 1.5fr 1.5fr 2fr 1.5fr; border-top:1px solid #e2e2e2;">
<div style="padding:14px 12px; font-size:19px;">{{ $customer->name }}</div>
<div style="padding:14px 12px; font-size:19px;">{{ $customer->phone }}</div>
<div style="padding:14px 12px; font-size:19px; font-weight:600; color:#024989;">
                {{ number_format($customer->debt_amount, 0, ',', ' ') }} сум
</div>
<div style="padding:14px 12px; font-size:19px;">
                {{ $customer->sales->max('created_at')?->diffForHumans() ?? '—' }}
</div>
<div style="padding:10px 12px;">
<form method="POST" action="{{ route('debtors.pay', $customer) }}" class="pay-debt-form" data-name="{{ $customer->name }}">
                    @csrf
<button type="submit" style="width:100%; padding:10px; font-size:16px; border-radius:8px; border:2px solid #024989; background:#fff; color:#024989; font-weight:500; cursor:pointer;">
                        Оплатил
</button>
</form>
</div>
</div>
    @empty
<div style="padding:20px; font-size:19px; color:#555;">Должников пока нет.</div>
    @endforelse
</div>

<script>
    document.querySelectorAll('.pay-debt-form').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            const name = form.dataset.name || 'этого клиента';
            if (!confirm('Отметить долг клиента «' + name + '» как полностью оплаченный?')) {
                e.preventDefault();
            }
        });
    });
</script>
@endsection