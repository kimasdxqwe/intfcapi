<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Authorize {{ $client->name }}</title>
    <style>
        :root {
            color-scheme: light dark;
        }
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
            max-width: 420px;
            width: 100%;
            padding: 32px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
        }
        .app-icon {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            background: #6366f1;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 18px;
            margin-bottom: 16px;
        }
        h1 {
            font-size: 18px;
            font-weight: 600;
            margin: 0 0 4px;
            line-height: 1.4;
        }
        h1 span {
            color: #4f46e5;
        }
        .subtitle {
            font-size: 14px;
            color: #71717a;
            margin: 0 0 20px;
        }
        .scopes {
            background: #fafafa;
            border: 1px solid #e4e4e7;
            border-radius: 8px;
            padding: 14px 16px;
            margin-bottom: 24px;
        }
        .scopes p {
            font-size: 13px;
            font-weight: 600;
            color: #52525b;
            margin: 0 0 10px;
            text-transform: uppercase;
            letter-spacing: 0.02em;
        }
        .scopes ul {
            margin: 0;
            padding: 0;
            list-style: none;
        }
        .scopes li {
            font-size: 14px;
            color: #3f3f46;
            display: flex;
            align-items: flex-start;
            gap: 8px;
            padding: 4px 0;
        }
        .scopes li::before {
            content: "\2713";
            color: #16a34a;
            font-weight: 700;
            flex-shrink: 0;
        }
        .no-scopes {
            font-size: 14px;
            color: #71717a;
            margin: 0;
        }
        .actions {
            display: flex;
            gap: 12px;
        }
        button {
            flex: 1;
            font-size: 14px;
            font-weight: 600;
            padding: 10px 16px;
            border-radius: 8px;
            border: 1px solid transparent;
            cursor: pointer;
        }
        .approve {
            background: #4f46e5;
            color: #fff;
        }
        .approve:hover {
            background: #4338ca;
        }
        .deny {
            background: #fff;
            color: #3f3f46;
            border-color: #d4d4d8;
        }
        .deny:hover {
            background: #f4f4f5;
        }
        .footer {
            margin-top: 20px;
            font-size: 12px;
            color: #a1a1aa;
            text-align: center;
        }
        @media (prefers-color-scheme: dark) {
            body { background: #09090b; color: #fafafa; }
            .card { background: #18181b; border-color: #27272a; }
            .subtitle { color: #a1a1aa; }
            .scopes { background: #1f1f23; border-color: #27272a; }
            .scopes p { color: #a1a1aa; }
            .scopes li { color: #d4d4d8; }
            .no-scopes { color: #a1a1aa; }
            .deny { background: transparent; color: #e4e4e7; border-color: #3f3f46; }
            .deny:hover { background: #27272a; }
            .footer { color: #52525b; }
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="app-icon">{{ strtoupper(substr($client->name, 0, 1)) }}</div>

        <h1><span>{{ $client->name }}</span> is requesting access to your account</h1>
        <p class="subtitle">
            Signed in as {{ $user->email ?? $user->name ?? 'your account' }}
        </p>

        <div class="scopes">
            <p>This will allow the application to</p>
            @if (count($scopes) > 0)
                <ul>
                    @foreach ($scopes as $scope)
                        <li>{{ $scope->description }}</li>
                    @endforeach
                </ul>
            @else
                <p class="no-scopes">Access basic account information</p>
            @endif
        </div>

        <form method="post" action="{{ url('/oauth/authorize') }}">
            @csrf
            <input type="hidden" name="state" value="{{ $request->state }}">
            <input type="hidden" name="client_id" value="{{ $client->id }}">
            <input type="hidden" name="auth_token" value="{{ $authToken }}">

            <div class="actions">
                <form method="post" action="{{ url('/oauth/authorize') }}">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="state" value="{{ $request->state }}">
                    <input type="hidden" name="client_id" value="{{ $client->id }}">
                    <input type="hidden" name="auth_token" value="{{ $authToken }}">
                    <button type="submit" class="deny">Deny</button>
                </form>

                <form method="post" action="{{ url('/oauth/authorize') }}">
                    @csrf
                    <input type="hidden" name="state" value="{{ $request->state }}">
                    <input type="hidden" name="client_id" value="{{ $client->id }}">
                    <input type="hidden" name="auth_token" value="{{ $authToken }}">
                    <button type="submit" class="approve">Approve</button>
                </form>
            </div>
        </form>

        <p class="footer">
            You can revoke access at any time from your account settings.
        </p>
    </div>
</body>
</html>
