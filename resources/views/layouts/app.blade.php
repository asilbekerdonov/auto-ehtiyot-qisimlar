<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
<title>@yield('title', 'Автозапчасти')</title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
html { -webkit-text-size-adjust: 100%; }
body {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif;
    background: #ffffff;
    color: #111111;
    min-height: 100vh;
    overflow-x: hidden;
}

/* Шапка */
.header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 20px 32px;
    border-bottom: 2px solid #024989;
}
.header-left {
    display: flex;
    align-items: center;
    gap: 16px;
}
.header h1 {
    font-size: 26px;
    font-weight: 600;
    color: #024989;
    white-space: nowrap;
}
.header form button {
    padding: 12px 20px;
    font-size: 17px;
    font-weight: 500;
    color: #024989;
    background: #ffffff;
    border: 2px solid #024989;
    border-radius: 10px;
    cursor: pointer;
    white-space: nowrap;
}
.header form button:hover {
    background: #f4f8fb;
}

/* Кнопка-бургер — скрыта на десктопе, показывается только на мобильных */
.burger-btn {
    display: none;
    width: 44px;
    height: 44px;
    align-items: center;
    justify-content: center;
    background: #ffffff;
    border: 2px solid #024989;
    border-radius: 10px;
    cursor: pointer;
    flex-shrink: 0;
}
.burger-btn svg {
    width: 24px;
    height: 24px;
    stroke: #024989;
}

/* Навбар — десктоп: обычная строка */
.navbar {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    padding: 16px 32px;
    border-bottom: 2px solid #024989;
    background: #f4f8fb;
}
.navbar a {
    padding: 10px 18px;
    font-size: 17px;
    font-weight: 500;
    border-radius: 8px;
    border: 2px solid #024989;
    text-decoration: none;
    color: #024989;
    background: #ffffff;
    white-space: nowrap;
}
.navbar a.active {
    background: #024989;
    color: #ffffff;
}

/* Затемнение фона при открытом меню */
.nav-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.4);
    z-index: 90;
}

.container {
    padding: 32px;
}

/* Плитки */
.tiles {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
    gap: 16px;
}
.tile {
    background: #024989;
    color: #ffffff;
    border-radius: 16px;
    padding: 28px 20px;
    min-height: 90px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    font-weight: 600;
    text-decoration: none;
    text-align: center;
}
.tile:hover {
    background: #013a6e;
}

/* Формы */
label {
    display: block;
    font-size: 18px;
    font-weight: 500;
    color: #024989;
    margin-bottom: 8px;
}
input[type="text"],
input[type="number"],
select {
    width: 100%;
    font-size: 19px;
    padding: 14px 16px;
    border: 2px solid #b8cfe0;
    border-radius: 10px;
    outline: none;
    -webkit-appearance: none;
}
input:focus, select:focus {
    border-color: #024989;
}
.btn-primary {
    width: 100%;
    padding: 16px;
    font-size: 20px;
    font-weight: 600;
    color: #ffffff;
    background: #024989;
    border: none;
    border-radius: 10px;
    cursor: pointer;
}
.btn-primary:hover {
    background: #013a6e;
}

.table-wrap {
    width: 100%;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}
table {
    width: 100%;
    border-collapse: collapse;
    min-width: 480px;
}

.icon-btn {
    width: 44px;
    height: 44px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
    border: 2px solid currentColor;
    background: #ffffff;
    cursor: pointer;
    flex-shrink: 0;
}
.icon-btn svg {
    width: 22px;
    height: 22px;
}
.icon-btn--edit {
    color: #b8860b;
}
.icon-btn--edit:hover {
    background: #fff8e1;
}
.icon-btn--delete {
    color: #a32d2d;
}
.icon-btn--delete:hover {
    background: #fdecea;
}

/* ===== Планшеты ===== */
@media (max-width: 900px) {
    .container { padding: 24px; }
    .header { padding: 18px 24px; }
    .navbar { padding: 14px 24px; }
}

/* ===== Телефоны: навбар превращается в выезжающее бургер-меню ===== */
@media (max-width: 700px) {
    .header {
        padding: 14px 16px;
    }
    .header h1 {
        font-size: 20px;
    }
    .header form button {
        padding: 10px 14px;
        font-size: 15px;
    }
    .burger-btn {
        display: inline-flex;
    }

    /* Навбар прячется за экран справа и выезжает при открытии */
    .navbar {
        position: fixed;
        top: 0;
        right: 0;
        height: 100vh;
        width: min(78vw, 320px);
        background: #ffffff;
        flex-direction: column;
        flex-wrap: nowrap;
        align-items: stretch;
        padding: 24px 20px;
        border-bottom: none;
        border-left: 2px solid #024989;
        transform: translateX(100%);
        transition: transform 0.25s ease;
        z-index: 100;
        overflow-y: auto;
    }
    .navbar.open {
        transform: translateX(0);
    }
    .navbar a {
        text-align: center;
        padding: 14px 16px;
        font-size: 17px;
    }
    .nav-overlay.open {
        display: block;
    }

    .container {
        padding: 16px;
    }

    .tiles {
        grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
        gap: 12px;
    }
    .tile {
        padding: 20px 12px;
        font-size: 18px;
        min-height: 76px;
        border-radius: 12px;
    }

    label {
        font-size: 16px;
    }
    input[type="text"],
    input[type="number"],
    select {
        font-size: 16px;
        padding: 12px 14px;
    }
    .btn-primary {
        padding: 14px;
        font-size: 18px;
    }
}

@media (max-width: 360px) {
    .header h1 { font-size: 18px; }
    .tiles { grid-template-columns: repeat(auto-fit, minmax(110px, 1fr)); }
}
</style>
    @yield('styles')
</head>
<body>
<div class="header">
    <div class="header-left">
        <button class="burger-btn" id="burgerBtn" aria-label="Меню" type="button">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round">
                <line x1="3" y1="6" x2="21" y2="6"></line>
                <line x1="3" y1="12" x2="21" y2="12"></line>
                <line x1="3" y1="18" x2="21" y2="18"></line>
            </svg>
        </button>
        <h1>Автозапчасти</h1>
    </div>
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit">Выйти</button>
    </form>
</div>

<div class="nav-overlay" id="navOverlay"></div>

<div class="navbar" id="navbar">
    <a href="{{ route('goods') }}" class="{{ request()->routeIs('goods') ? 'active' : '' }}">Товары</a>
    <a href="{{ route('stock') }}" class="{{ request()->routeIs('stock') ? 'active' : '' }}">Склад</a>
    <a href="{{ route('debtors.index') }}" class="{{ request()->routeIs('debtors*') ? 'active' : '' }}">Должники</a>
    <a href="{{ route('sales.cars') }}" class="{{ request()->routeIs('sales*') ? 'active' : '' }}">Продажи</a>
    <a href="{{ route('analytics') }}" class="{{ request()->routeIs('analytics') ? 'active' : '' }}">Аналитика</a>
    <a href="{{ route('receipts.cars') }}" class="{{ request()->routeIs('receipts*') ? 'active' : '' }}">Поступление</a>
    <a href="{{ route('add') }}" class="{{ request()->routeIs('add') ? 'active' : '' }}">Добавить</a>
</div>

<div class="container">
    @yield('content')
</div>

<script>
const burgerBtn = document.getElementById('burgerBtn');
const navbar = document.getElementById('navbar');
const navOverlay = document.getElementById('navOverlay');

function closeMenu() {
    navbar.classList.remove('open');
    navOverlay.classList.remove('open');
}

burgerBtn.addEventListener('click', () => {
    navbar.classList.toggle('open');
    navOverlay.classList.toggle('open');
});

navOverlay.addEventListener('click', closeMenu);

// Закрывать меню при клике на пункт (переход на другую страницу)
navbar.querySelectorAll('a').forEach(link => {
    link.addEventListener('click', closeMenu);
});
</script>
</body>
</html>