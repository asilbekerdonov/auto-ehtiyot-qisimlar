# AutoParts CRM

Учётная система для магазина автозапчастей: товары, склад, продажи, должники, аналитика.

## Стек

| Слой | Технология |
|---|---|
| Backend | PHP 8.3+, Laravel 13 |
| БД | SQLite (по умолчанию) или MySQL 8 |
| Экспорт | Laravel Excel (maatwebsite/excel) |
| Frontend | Blade, vanilla JS |
| Тесты | PHPUnit |

## Архитектура

Контроллер → Сервис (бизнес-логика) → Репозиторий (доступ к данным) → Модель.

- `app/Http/Controllers` — обработка запросов
- `app/Http/Requests` — валидация форм
- `app/Services` — бизнес-логика (мерж товаров, продажи, поступления)
- `app/Repositories` — доступ к данным через интерфейсы
- `app/Models` — Eloquent-модели

## Модули
|
| Раздел | Что делает |
|---|---|
| Товары | Каталог: категория, марка авто, позиция, цвет, фото |
| Склад | Остатки по складам, ручная корректировка |
| Поступления | Приход товара на склад по машине |
| Продажи | Корзина, торг, оплата или в долг |
| Должники | Список клиентов с долгом, погашение |
| Аналитика | Продажи по дням/месяцам |
| Экспорт | Выгрузка каталога в Excel |

## Установка

```bash
git clone https://github.com/asilbekerdonov/auto-ehtiyot-qisimlar.git
cd auto-ehtiyot-qisimlar

composer install
cp .env.example .env
php artisan key:generate

# по умолчанию SQLite — файл создастся сам;
# для MySQL укажите DB_CONNECTION=mysql и остальные DB_* в .env

php artisan migrate --seed
php artisan storage:link
php artisan serve
```

Открыть: `http://127.0.0.1:8000`

## Тесты

```bash
php artisan test
php artisan test tests/Unit/Products
```

## Структура

```
app/
├── Http/Controllers/    # Товары, Склад, Продажи, Должники, Аналитика, Поступления
├── Http/Requests/       # Валидация форм
├── Models/              # Product, Quantity, Sale, SaleItem, Customer...
├── Repositories/         # Доступ к данным 
└── Services/             # ProductStockService, SaleService, ReceiptService

database/migrations/     # Схема БД
routes/                   # Один файл роутов на модуль
resources/views/pages/    # Blade-шаблоны
tests/                     # PHPUnit
```

