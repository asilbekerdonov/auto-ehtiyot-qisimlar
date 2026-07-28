@extends('layouts.app')

@section('title', 'Товары — Автозапчасти')

@section('content')

    {{-- TODO: заменить на данные из БД (categories, products, quantity, image) --}}
    @php
        $categories = ['Все категории', 'Тормозные колодки', 'Фильтры', 'Масла', 'Свечи зажигания'];
        $products = [
            ['name' => 'Колодки Bosch F1',    'price' => '60 000 сум',  'stock' => '23 шт', 'image' => null],
            ['name' => 'Фильтр масляный',      'price' => '18 000 сум',  'stock' => '41 шт', 'image' => null],
            ['name' => 'Свеча NGK',            'price' => '12 000 сум',  'stock' => '67 шт', 'image' => null],
            ['name' => 'Ремень ГРМ Gates',     'price' => '95 000 сум',  'stock' => '12 шт', 'image' => null],
            ['name' => 'Масло Shell 5W-40',    'price' => '85 000 сум',  'stock' => '30 шт', 'image' => null],
            ['name' => 'Тормозной диск',       'price' => '120 000 сум', 'stock' => '8 шт',  'image' => null],
        ];

        // Заглушка, пока у товара нет фото в БД
        $placeholder = 'https://placehold.co/500x300/e8f0f7/024989?text=Фото+товара';
    @endphp

<div style="display:grid; grid-template-columns:20% 70%; border:2px solid #024989; border-radius:10px; overflow:hidden;">

        {{-- Категории --}}
<div style="background:#024989; color:#fff;">
<div style="padding:16px 18px; font-size:20px; font-weight:500; border-bottom:1px solid rgba(255,255,255,0.3);">
                Категории
</div>
            @foreach ($categories as $i => $category)
<div style="padding:16px 18px; font-size:19px; {{ $i === 0 ? 'background:rgba(255,255,255,0.18); font-weight:500;' : '' }} border-bottom:1px solid rgba(255,255,255,0.15); cursor:pointer;">
                    {{ $category }}
</div>
            @endforeach
</div>

        {{-- Карточки товаров --}}
<div style="padding:20px; background:#fff;">
<div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                @foreach ($products as $product)
<div style="border:2px solid #024989; border-radius:12px; overflow:hidden;">
<img src="{{ $product['image'] ?? $placeholder }}" alt="{{ $product['name'] }}" style="width:100%; height:160px; object-fit:cover; display:block; background:#e8f0f7;">
<div style="padding:16px 18px;">
<div style="font-size:19px; font-weight:500; margin-bottom:8px;">{{ $product['name'] }}</div>
<div style="font-size:18px; color:#024989; font-weight:500; margin-bottom:4px;">{{ $product['price'] }}</div>
<div style="font-size:16px; color:#555;">Остаток: {{ $product['stock'] }}</div>
</div>
</div>
                @endforeach
</div>
</div>

</div>
@endsection