@extends('layouts.app')

@section('title', 'Выбор машины — Поступление')

@section('styles')
<style>
.receipts-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 24px;
    flex-wrap: wrap;
    gap: 12px;
}
.receipts-header-title {
    font-size: 24px;
    font-weight: 600;
    color: #024989;
}
.success-message {
    padding: 14px 18px;
    margin-bottom: 20px;
    background: #e6f4ea;
    border: 2px solid #2e7d32;
    color: #2e7d32;
    border-radius: 10px;
    font-size: 18px;
}
.cars-search-input {
    width: 100%;
    margin-bottom: 20px;
}
.cars-grid {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 20px;
}
.car-card {
    display: block;
    text-decoration: none;
    color: inherit;
    border: 2px solid #024989;
    border-radius: 12px;
    overflow: hidden;
}
.car-card img {
    width: 100%;
    height: 150px;
    object-fit: cover;
    display: block;
    background: #e8f0f7;
}
.car-card-body {
    padding: 16px;
}
.car-card-title {
    font-size: 18px;
    font-weight: 500;
    color: #024989;
}

/* ===== Планшеты ===== */
@media (max-width: 900px) {
    .cars-grid {
        grid-template-columns: 1fr 1fr;
    }
}

/* ===== Телефоны ===== */
@media (max-width: 600px) {
    .receipts-header-title {
        font-size: 20px;
    }
    .cars-grid {
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }
    .car-card img {
        height: 110px;
    }
    .car-card-body {
        padding: 10px 12px;
    }
    .car-card-title {
        font-size: 15px;
    }
}

/* Очень маленькие экраны — одна колонка, карточка шире */
@media (max-width: 380px) {
    .cars-grid {
        grid-template-columns: 1fr;
        max-width: 400px;
        margin: 0 auto;
    }
    .car-card img {
        height: 160px;
    }
    .car-card-title {
        font-size: 17px;
    }
}
</style>
@endsection

@section('content')
<div class="receipts-header">
    <div class="receipts-header-title">Поступление — выберите машину</div>
</div>

    @if (session('success'))
        <div class="success-message">
            {{ session('success') }}
        </div>
    @endif

<input type="text" id="cars-search-input" class="cars-search-input" placeholder="Поиск машины" autocomplete="off">

    @if ($cars->isEmpty())
        <p style="font-size:19px; color:#555;">Машин пока нет.</p>
    @else
        <div id="cars-grid" class="cars-grid">
            @foreach ($cars as $car)
                <a class="car-card" href="{{ route('receipts.parts', $car) }}" data-search="{{ Str::lower($car->title) }}">
                    <img src="{{ $car->image ? asset('storage/' . $car->image) : 'https://placehold.co/400x260/e8f0f7/024989?text=Фото+авто' }}"
                        alt="{{ $car->title }}">
                    <div class="car-card-body">
                        <div class="car-card-title">{{ $car->title }}</div>
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