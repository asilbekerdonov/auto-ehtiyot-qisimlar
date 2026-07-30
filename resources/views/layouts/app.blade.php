<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Автозапчасти')</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif;
            background: #ffffff;
            color: #111111;
            min-height: 100vh;
        }

        /* Шапка */
        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px 32px;
            border-bottom: 2px solid #024989;
        }
        .header h1 {
            font-size: 26px;
            font-weight: 600;
            color: #024989;
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
        }
        .header form button:hover {
            background: #f4f8fb;
        }

        /* Навбар (скрыт на dashboard) */
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
        }
        .navbar a.active {
            background: #024989;
            color: #ffffff;
        }

        .container {
            padding: 32px;
        }

        /* Плитки (переиспользуются на dashboard и в "Добавить") */
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
        }
        input:focus, select:focus {
            border-color: #024989;
        }
        .btn-primary {
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

        /* Кнопки-иконки на карточках (изменить / удалить) */
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
        }
        .icon-btn svg {
            width: 22px;
            height: 22px;
        }
        .icon-btn--edit {
            color: #b8860b; /* тёмно-жёлтый — на белом фоне так контрастнее, чем чистый жёлтый */
        }
        .icon-btn--edit:hover {
            background: #fff8e1;
        }
        .icon-btn--delete {
            color: #a32d2d; /* совпадает с цветом ошибок в общей палитре */
        }
        .icon-btn--delete:hover {
            background: #fdecea;
        }

        @media (max-width: 600px) {
            .navbar { padding: 12px 16px; }
            .header { padding: 16px 20px; }
        }
    </style>
    @yield('styles')
</head>
<body>
    <div class="header">
        <h1>Автозапчасти</h1>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit">Выйти</button>
        </form>
    </div>

    @unless(request()->routeIs('dashboard'))
        <div class="navbar">
            <a href="{{ route('goods') }}" class="{{ request()->routeIs('goods') ? 'active' : '' }}">Товары</a>
            <a href="{{ route('stock') }}" class="{{ request()->routeIs('stock') ? 'active' : '' }}">Склад</a>
            <a href="{{ url('/debtors') }}" class="{{ request()->routeIs('debtors') ? 'active' : '' }}">Должники</a>
            <a href="{{ url('/sales') }}" class="{{ request()->routeIs('sales') ? 'active' : '' }}">Продажи</a>
            <a href="{{ url('/analytics') }}" class="{{ request()->routeIs('analytics') ? 'active' : '' }}">Аналитика</a>
            <a href="{{ route('receipts.cars') }}" class="{{ request()->routeIs('receipts') ? 'active' : '' }}">Поступление</a>
            <a href="{{ route('add') }}" class="{{ request()->routeIs('add') ? 'active' : '' }}">Добавить</a>
        </div>
    @endunless

    <div class="container">
        @yield('content')
    </div>
</body>
</html>