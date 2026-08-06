@extends('layouts.app')

@section('title', 'Должники — Автозапчасти')

@section('styles')
<style>
.debtors-table {
    border: 2px solid #024989;
    border-radius: 10px;
    overflow: hidden;
}
.debtors-row {
    display: grid;
    grid-template-columns: 2fr 1.5fr 1.5fr 2fr 1.5fr;
}
.debtors-header {
    background: #024989;
    color: #fff;
}
.debtors-header .debtors-cell {
    font-weight: 500;
    font-size: 18px;
}
.debtors-body-row {
    border-top: 1px solid #e2e2e2;
}
.debtors-cell {
    padding: 14px 12px;
    font-size: 19px;
}
.debtors-cell--debt {
    font-weight: 600;
    color: #024989;
}
.debtors-cell--action {
    padding: 10px 12px;
}
.debtors-cell-label {
    display: none; /* подписи полей нужны только на мобильной карточке */
}
.pay-btn {
    width: 100%;
    padding: 10px;
    font-size: 16px;
    border-radius: 8px;
    border: 2px solid #024989;
    background: #fff;
    color: #024989;
    font-weight: 500;
    cursor: pointer;
}
.debtors-empty {
    padding: 20px;
    font-size: 19px;
    color: #555;
}

/* ===== Мобильные: строка таблицы превращается в карточку ===== */
@media (max-width: 700px) {
    .debtors-header {
        display: none; /* шапка таблицы не нужна — подписи уходят внутрь карточки */
    }
    .debtors-table {
        border: none;
        border-radius: 0;
        display: flex;
        flex-direction: column;
        gap: 14px;
    }
    .debtors-body-row {
        display: block;
        border: 2px solid #024989;
        border-radius: 12px;
        border-top: 2px solid #024989;
        overflow: hidden;
    }
    .debtors-cell {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        padding: 12px 16px;
        font-size: 17px;
        border-bottom: 1px solid #e2e2e2;
        text-align: right;
    }
    .debtors-cell-label {
        display: inline;
        font-size: 14px;
        font-weight: 500;
        color: #777;
        text-align: left;
        flex-shrink: 0;
    }
    .debtors-cell--action {
        border-bottom: none;
        display: block;
        padding: 12px 16px;
    }
}
</style>
@endsection

@section('content')

<div class="debtors-table">
    <div class="debtors-row debtors-header">
        <div class="debtors-cell">Клиент</div>
        <div class="debtors-cell">Телефон</div>
        <div class="debtors-cell">Долг</div>
        <div class="debtors-cell">Последняя продажа</div>
        <div class="debtors-cell"></div>
    </div>

    @forelse ($debtors as $customer)
        <div class="debtors-row debtors-body-row">
            <div class="debtors-cell">
                <span class="debtors-cell-label">Клиент</span>
                {{ $customer->name }}
            </div>
            <div class="debtors-cell">
                <span class="debtors-cell-label">Телефон</span>
                {{ $customer->phone }}
            </div>
            <div class="debtors-cell debtors-cell--debt">
                <span class="debtors-cell-label">Долг</span>
                {{ number_format($customer->debt_amount, 0, ',', ' ') }} сум
            </div>
            <div class="debtors-cell">
                <span class="debtors-cell-label">Последняя продажа</span>
                {{ $customer->sales->max('created_at')?->diffForHumans() ?? '—' }}
            </div>
            <div class="debtors-cell debtors-cell--action">
                <form method="POST" action="{{ route('debtors.pay', $customer) }}" class="pay-debt-form" data-name="{{ $customer->name }}">
                    @csrf
                    <button type="submit" class="pay-btn">
                        Оплатил
                    </button>
                </form>
            </div>
        </div>
    @empty
        <div class="debtors-empty">Должников пока нет.</div>
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