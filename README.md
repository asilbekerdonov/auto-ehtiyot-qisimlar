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


## CI

При каждом push/PR в `main` GitHub Actions прогоняет тесты на Ubuntu (Linux), не macOS — это важно, см. раздел ниже.

Workflow (`.github/workflows/ci.yml`) выполняет:
1. Установку зависимостей (`composer install`)
2. Создание и миграцию тестовой SQLite-базы
3. Очистку кеша и `composer dump-autoload`
4. `php artisan test`
5. Проверку соответствия имени файла и имени класса (PSR-4) в `app/`

Проверить статус: вкладка [Actions](https://github.com/asilbekerdonov/auto-ehtiyot-qisimlar/actions).

## Соглашения по именованию

⚠️ **Важно для разработчиков на macOS/Windows.** Файловая система на этих ОС не чувствительна к регистру, а на Linux (в т.ч. в CI и на большинстве production-серверов) — чувствительна. Файл `receiptcontroller.php` и `ReceiptController.php` — это один и тот же файл на Mac, но два разных на Linux.

Правила:
- Имя файла класса должно **точно** совпадать с именем класса, включая регистр (PSR-4): `ReceiptController.php` → `class ReceiptController`.
- Перед пушем проверяйте регистр новых файлов: `git ls-files | grep -i имяфайла`.
- CI автоматически проверяет это на каждом прогоне (шаг "Check filename/class name match").