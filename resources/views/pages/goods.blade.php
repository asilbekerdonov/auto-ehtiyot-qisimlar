@extends('layouts.app')

@section('title', 'Товары — Автозапчасти')

@section('content')

    @php
        // Заглушка, пока у товара нет поля с фото в БД
        $placeholder = 'https://placehold.co/500x300/e8f0f7/024989?text=Фото+товара';
    @endphp

    <div style="display:grid; grid-template-columns:25% 75%; border:2px solid #024989; border-radius:10px; overflow:hidden;">

        {{-- Категории --}}
        <div style="background:#024989; color:#fff;">
            <div style="padding:16px 18px; font-size:20px; font-weight:500; border-bottom:1px solid rgba(255,255,255,0.3);">
                Категории
            </div>

            <a href="{{ route('goods') }}"
               style="display:block; padding:16px 18px; font-size:19px; text-decoration:none; color:#fff; {{ !$selectedCategoryId ? 'background:rgba(255,255,255,0.18); font-weight:500;' : '' }} border-bottom:1px solid rgba(255,255,255,0.15);">
                Все категории
            </a>

            @foreach ($categories as $category)
                <a href="{{ route('goods', ['category' => $category->id]) }}"
                   style="display:block; padding:16px 18px; font-size:19px; text-decoration:none; color:#fff; {{ (string) $selectedCategoryId === (string) $category->id ? 'background:rgba(255,255,255,0.18); font-weight:500;' : '' }} border-bottom:1px solid rgba(255,255,255,0.15);">
                    {{ $category->title }}
                </a>
            @endforeach
        </div>

        {{-- Карточки товаров --}}
        <div style="padding:20px; background:#fff;">
            @if ($products->isEmpty())
                <p style="font-size:19px; color:#555;">В этой категории пока нет товаров.</p>
            @else
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                    @foreach ($products as $product)
                        <div style="border:2px solid #024989; border-radius:12px; overflow:hidden;">
                            <img src="{{ $placeholder }}" alt="{{ $product->title }}" style="width:100%; height:160px; object-fit:cover; display:block; background:#e8f0f7;">
                            <div style="padding:16px 18px;">
                                <div style="font-size:19px; font-weight:500; margin-bottom:4px;">{{ $product->title }}</div>
                                <div style="font-size:15px; color:#777; margin-bottom:8px;">{{ $product->category->title }}</div>
                                <div style="font-size:18px; color:#024989; font-weight:600; margin-bottom:4px;">
                                    {{ number_format($product->selling_price, 0, ',', ' ') }} сум
                                </div>
                                <div style="font-size:16px; color:#555;">Остаток: {{ $product->total_stock }} шт</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

    </div>
@endsection