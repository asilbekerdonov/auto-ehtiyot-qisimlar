@extends('layouts.app')

@section('title', 'Добавить — Автозапчасти')

@section('content')

    <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:16px; margin-bottom:24px;">
        <a href="#" class="tile" style="font-size:19px;">+ Категория</a>
        <a href="#" class="tile" style="font-size:19px;">+ Товар</a>
        <a href="#" class="tile" style="font-size:19px;">+ Склад</a>
    </div>

    <div style="border:2px solid #024989; border-radius:10px; padding:24px;">
        <div style="font-size:22px; font-weight:600; color:#024989; margin-bottom:20px;">
            Добавить товар
        </div>

        <form method="POST" action="{{ url('/products') }}" style="display:grid; gap:18px; max-width:460px;">
            @csrf

            <div>
                <label for="title">Название</label>
                <input type="text" id="title" name="title" placeholder="Например, Колодки Bosch F1" required>
            </div>

            <div>
                <label for="category_id">Категория</label>
                <select id="category_id" name="category_id" required>
                    {{-- TODO: подставить категории из БД --}}
                    <option>Тормозные колодки</option>
                    <option>Фильтры</option>
                    <option>Масла</option>
                    <option>Свечи зажигания</option>
                </select>
            </div>

            <div>
                <label for="cost_price">Себестоимость</label>
                <input type="number" id="cost_price" name="cost_price" placeholder="45000" required>
            </div>

            <div>
                <label for="markup">Наценка</label>
                <input type="number" id="markup" name="markup" placeholder="15000" required>
            </div>

            <button type="submit" class="btn-primary">Сохранить</button>
        </form>
    </div>

@endsection