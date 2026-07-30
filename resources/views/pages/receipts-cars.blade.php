@extends('layouts.app')

@section('title', 'Выбор машины — Поступление')

@section('content')
<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:24px; flex-wrap:wrap; gap:12px;">
    <div style="font-size:24px; font-weight:600; color:#024989;">Поступление — выберите машину</div>
</div>

    @if (session('success'))
        <div style="padding:14px 18px; margin-bottom:20px; background:#e6f4ea; border:2px solid #2e7d32; color:#2e7d32; border-radius:10px; font-size:18px;">
            {{ session('success') }}
        </div>
    @endif

<input type="text" id="cars-search-input" placeholder="Поиск машины" autocomplete="off" style="width:100%; margin-bottom:20px;">

    @if ($cars->isEmpty())
        <p style="font-size:19px; color:#555;">Машин пока нет.</p>
    @else
<div id="cars-grid" style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:20px;">
            @foreach ($cars as $car)
<a class="car-card" href="{{ route('receipts.parts', $car) }}" data-search="{{ Str::lower($car->title) }}" style="display:block; text-decoration:none; color:inherit; border:2px solid #024989; border-radius:12px; overflow:hidden;">
<img src="{{ $car->image ? asset('storage/' . $car->image) : 'https://placehold.co/400x260/e8f0f7/024989?text=Фото+авто' }}"
alt="{{ $car->title }}"
style="width:100%; height:150px; object-fit:cover; display:block; background:#e8f0f7;">
<div style="padding:16px;">
<div style="font-size:18px; font-weight:500; color:#024989;">{{ $car->title }}</div>
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