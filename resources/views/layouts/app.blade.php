<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Автозапчасти')</title>
    <style>
        * { 
            box-sizing: border-box; 
            margin: 0; 
            padding: 0; 
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif;
            background: #ffffff;
            color: #111111;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Шапка */
        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px 32px;
            border-bottom: 2px solid #024989;
            background: #ffffff;
            flex-shrink: 0;
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
            transition: all 0.2s;
        }
        
        .header form button:hover {
            background: #024989;
            color: #ffffff;
        }

        /* Навбар - начинается сразу после шапки */
        .navbar {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            padding: 16px 32px;
            border-bottom: 2px solid #024989;
            background: #f4f8fb;
            flex-shrink: 0;
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
            transition: all 0.2s;
        }
        
        .navbar a:hover {
            background: #024989;
            color: #ffffff;
        }
        
        .navbar a.active {
            background: #024989;
            color: #ffffff;
        }

        /* Основной контент - занимает оставшееся место */
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 32px 20px;
            width: 100%;
            flex: 1;
        }

        /* Стили для страницы товаров */
        .categories-section {
            background: #f4f8fb;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 24px;
        }

        .categories-section h3 {
            color: #024989;
            margin-bottom: 12px;
            font-size: 20px;
        }

        .category-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .category-tag {
            padding: 6px 16px;
            background: #ffffff;
            border: 2px solid #024989;
            border-radius: 20px;
            color: #024989;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
        }

        .category-tag:hover {
            background: #024989;
            color: #ffffff;
        }

        .category-tag.active {
            background: #024989;
            color: #ffffff;
        }

        /* Сетка товаров */
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 24px;
            margin-top: 24px;
        }

        .product-card {
            border: 2px solid #e0e8f0;
            border-radius: 16px;
            padding: 20px;
            transition: all 0.2s;
            background: #ffffff;
        }

        .product-card:hover {
            border-color: #024989;
            box-shadow: 0 4px 16px rgba(2, 73, 137, 0.1);
            transform: translateY(-2px);
        }

        .product-image {
            width: 100%;
            height: 200px;
            background: #f4f8fb;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 16px;
            font-size: 48px;
            color: #b8cfe0;
        }

        .product-title {
            font-size: 18px;
            font-weight: 600;
            color: #024989;
            margin-bottom: 4px;
        }

        .product-brand {
            font-size: 14px;
            color: #666;
            margin-bottom: 8px;
        }

        .product-price {
            font-size: 22px;
            font-weight: 700;
            color: #024989;
            margin-bottom: 8px;
        }

        .product-stock {
            font-size: 14px;
            color: #666;
        }

        /* Плитки для дашборда */
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
            transition: all 0.2s;
        }
        
        .tile:hover {
            background: #013a6e;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(2, 73, 137, 0.2);
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
            transition: border-color 0.2s;
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
            transition: all 0.2s;
        }
        
        .btn-primary:hover {
            background: #013a6e;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(2, 73, 137, 0.3);
        }

        @media (max-width: 768px) {
            .navbar { 
                padding: 12px 16px; 
                gap: 6px;
            }
            .navbar a {
                padding: 8px 12px;
                font-size: 14px;
                flex: 1;
                text-align: center;
                min-width: 60px;
            }
            .header { 
                padding: 16px 20px; 
            }
            .header h1 {
                font-size: 20px;
            }
            .products-grid {
                grid-template-columns: 1fr 1fr;
                gap: 16px;
            }
            .container {
                padding: 16px;
            }
        }

        @media (max-width: 480px) {
            .products-grid {
                grid-template-columns: 1fr;
            }
            .navbar a {
                font-size: 12px;
                padding: 6px 10px;
                min-width: 50px;
            }
        }
    </style>
    @yield('styles')
</head>
<body>
    <!-- Шапка -->
    <header class="header">
        <h1>Автозапчасти</h1>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit">Выйти</button>
        </form>
    </header>

    <!-- Навбар - сразу после шапки -->
    <nav class="navbar">
        <a href="{{ route('goods') }}" class="{{ request()->routeIs('goods') ? 'active' : '' }}">Товары</a>
        <a href="{{ route('stock') }}" class="{{ request()->routeIs('stock') ? 'active' : '' }}">Склад</a>
        <a href="{{ route('debtors') }}" class="{{ request()->routeIs('debtors') ? 'active' : '' }}">Должники</a>
        <a href="{{ route('sales') }}" class="{{ request()->routeIs('sales') ? 'active' : '' }}">Продажи</a>
        <a href="{{ route('analytics') }}" class="{{ request()->routeIs('analytics') ? 'active' : '' }}">Аналитика</a>
        <a href="{{ route('add') }}" class="{{ request()->routeIs('add') ? 'active' : '' }}">Добавить</a>
    </nav>

    <!-- Основной контент -->
    <main class="main-content">
        <div class="container">
            @yield('content')
        </div>
    </main>
</body>
</html>