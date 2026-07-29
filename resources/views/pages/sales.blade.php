@extends('layouts.app')

@section('title', 'Продажи — выбор машины')

@section('content')

    @php
        $placeholder = 'https://placehold.co/400x260/e8f0f7/024989?text=Фото+авто';
    @endphp

    <input type="text" id="cars-search-input" placeholder="Поиск машины, например: Cobalt"
           autocomplete="off" style="width:100%; margin-bottom:24px;">

    @if ($cars->isEmpty())
        <p style="font-size:19px; color:#555;">Марки авто пока не добавлены. Добавьте их на странице «Добавить».</p>
    @else
        <div id="cars-grid" style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:20px;">
            @foreach ($cars as $car)
                <a href="{{ route('sales.parts', $car) }}" class="car-card" data-search="{{ Str::lower($car->title) }}"
                   style="display:block; text-decoration:none; color:inherit; border:2px solid #024989; border-radius:12px; overflow:hidden;">
                    <img src="{{ $placeholder }}" alt="{{ $car->title }}" style="width:100%; height:150px; object-fit:cover; display:block; background:#e8f0f7;">
                    <div style="padding:16px;">
                        <div style="font-size:20px; font-weight:600; color:#024989; margin-bottom:6px;">{{ $car->title }}</div>
                        <div style="font-size:16px; color:#555;">{{ $car->products_count }} {{ $car->products_count == 1 ? 'деталь' : 'деталей' }}</div>
                    </div>
                </a>
            @endforeach
        </div>
        <p id="cars-empty-message" style="display:none; font-size:19px; color:#555; margin-top:16px;">Ничего не найдено.</p>
    @endif

    <script>
        const carsSearchInput = document.getElementById('cars-search-input');
        if (carsSearchInput) {
            const carCards = document.querySelectorAll('.car-card');
            const emptyMessage = document.getElementById('cars-empty-message');

            carsSearchInput.addEventListener('input', function () {
                const query = carsSearchInput.value.trim().toLowerCase();
                let visibleCount = 0;

                carCards.forEach(function (card) {
                    const isMatch = card.dataset.search.includes(query);
                    card.style.display = isMatch ? '' : 'none';
                    if (isMatch) visibleCount++;
                });

                if (emptyMessage) {
                    emptyMessage.style.display = visibleCount === 0 ? 'block' : 'none';
                }
            });
        }
    </script>

@endsection