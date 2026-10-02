<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Log in</title>
    <style>
        :root { color-scheme: light dark; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: #f4f4f5;
            color: #18181b;
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .card {
            background: #ffffff;
            border: 1px solid #e4e4e7;
            border-radius: 12px;
            max-width: 380px;
            width: 100%;
            padding: 32px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
        }
        h1 { font-size: 18px; font-weight: 600; margin: 0 0 4px; }
        .subtitle { font-size: 14px; color: #71717a; margin: 0 0 24px; }
        label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #52525b;
            margin-bottom: 6px;
        }
        .field { margin-bottom: 16px; }
        input[type="email"], input[type="password"] {
            width: 100%;
            box-sizing: border-box;
            padding: 10px 12px;
            font-size: 14px;
            border: 1px solid #d4d4d8;
            border-radius: 8px;
            background: #fff;
            color: #18181b;
        }
        input:focus { outline: 2px solid #4f46e5; outline-offset: 1px; }
        .error {
            font-size: 13px;
            color: #dc2626;
            margin-top: 6px;
        }
        .remember {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            color: #3f3f46;
            margin-bottom: 20px;
        }
        .remember input { width: auto; }
        button {
            width: 100%;
            font-size: 14px;
            font-weight: 600;
            padding: 10px 16px;
            border-radius: 8px;
            border: none;
            background: #4f46e5;
            color: #fff;
            cursor: pointer;
        }
        button:hover { background: #4338ca; }
        @media (prefers-color-scheme: dark) {
            body { background: #09090b; color: #fafafa; }
            .card { background: #18181b; border-color: #27272a; }
            .subtitle { color: #a1a1aa; }
            label { color: #a1a1aa; }
            input[type="email"], input[type="password"] {
                background: #1f1f23; border-color: #3f3f46; color: #fafafa;
            }
            .remember { color: #d4d4d8; }
        }
    </style>
</head>
<body>
<div class="card">
    <h1>Welcome back</h1>
    <p class="subtitle">Log in to continue</p>

    <form method="POST" action="{{ route('login.attempt') }}">
        @csrf

        <div class="field">
            <label for="email">Email</label>
            <input id="email" type="email" name="email" value="kmdeguzman@1stslp.com" required autofocus>
            @error('email')
            <div class="error">{{ $message }}</div>
            @enderror
        </div>

        <div class="field">
            <label for="password">Password</label>
            <input id="password" type="password" name="password" required value="password">
            @error('password')
            <div class="error">{{ $message }}</div>
            @enderror
        </div>

        <label class="remember">
            <input type="checkbox" name="remember">
            Remember me
        </label>

        <button type="submit">Log in</button>
    </form>
</div>
</body>
</html>
