<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход — Автозапчасти</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif;
            background: #ffffff;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .login-box {
            width: 100%;
            max-width: 440px;
            background: #ffffff;
            border: 2px solid #024989;
            border-radius: 16px;
            padding: 40px 36px;
        }
        .login-box h1 {
            font-size: 28px;
            font-weight: 600;
            color: #024989;
            text-align: center;
            margin-bottom: 8px;
        }
        .login-box p.subtitle {
            font-size: 18px;
            color: #444;
            text-align: center;
            margin-bottom: 32px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            font-size: 18px;
            font-weight: 500;
            color: #024989;
            margin-bottom: 8px;
        }
        input[type="text"],
        input[type="password"] {
            width: 100%;
            font-size: 19px;
            padding: 14px 16px;
            border: 2px solid #b8cfe0;
            border-radius: 10px;
            outline: none;
        }
        input[type="text"]:focus,
        input[type="password"]:focus {
            border-color: #024989;
        }
        .error {
            font-size: 16px;
            color: #a32d2d;
            margin-top: 8px;
        }
        button {
            width: 100%;
            margin-top: 12px;
            padding: 16px;
            font-size: 20px;
            font-weight: 600;
            color: #ffffff;
            background: #024989;
            border: none;
            border-radius: 10px;
            cursor: pointer;
        }
        button:hover {
            background: #013a6e;
        }
        button:active {
            transform: scale(0.98);
        }
    </style>
</head>
<body>
    <div class="login-box">
        <h1>Автозапчасти</h1>
        <p class="subtitle">Вход в систему</p>

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="form-group">
                <label for="username">Логин</label>
                <input type="text" id="username" name="username" value="{{ old('username') }}" autofocus required>
                @error('username')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="password">Пароль</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit">Войти</button>
        </form>
    </div>
</body>
</html>