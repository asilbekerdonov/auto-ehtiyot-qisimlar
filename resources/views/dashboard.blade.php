@extends('layouts.app')

@section('title', 'Дашборд — Автозапчасти')

@section('content')
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
        <a href="{{ route('goods') }}" class="tile">Товары</a>
        <a href="{{ url('/stock') }}" class="tile">Склад</a>
        <a href="{{ url('/debtors') }}" class="tile">Должники</a>
        <a href="{{ url('/sales') }}" class="tile">Продажи</a>

        <div style="grid-column: span 2; display:grid; grid-template-columns:1fr 1fr; gap:20px;">
            <a href="{{ url('/analytics') }}" class="tile">Аналитика</a>
            <a href="{{ route('add') }}" class="tile">Добавить</a>
        </div>
    </div>
@endsection