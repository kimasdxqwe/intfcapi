<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Authorize {{ $client->name }}</title>
    @vite(['resources/css/app.css'])

    <style>
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
    </style>
</head>
<body class="flex min-h-screen items-center justify-center bg-gray-100 px-4">
    <div class="w-full max-w-md rounded bg-white border-gray-300 p-6 shadow">
        <div class="mb-6 space-y-4">
            <h1>
                <span class="text-xl font-semibold text-gray-900">{{ $client->name }}</span> is requesting access to your account
            </h1>
            <p class="text-sm text-gray-500">
                Signed in as {{ $user->email ?? $user->name ?? 'your account' }}
            </p>
        </div>

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

            <div class="flex gap-2">
                <form method="post" action="{{ url('/oauth/authorize') }}">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="state" value="{{ $request->state }}">
                    <input type="hidden" name="client_id" value="{{ $client->id }}">
                    <input type="hidden" name="auth_token" value="{{ $authToken }}">

                    <button
                        type="submit"
                        class="flex-1 cursor-pointer rounded border border-gray-300 bg-white px-3 py-1 font-label text-gray-700 hover:bg-gray-200 disabled:cursor-not-allowed disabled:opacity-50 focus:outline-none focus:ring-2 focus:border-slate-500 focus:ring-slate-300">
                        Deny
                    </button>

                </form>

                <form method="post" action="{{ url('/oauth/authorize') }}">
                    @csrf
                    <input type="hidden" name="state" value="{{ $request->state }}">
                    <input type="hidden" name="client_id" value="{{ $client->id }}">
                    <input type="hidden" name="auth_token" value="{{ $authToken }}">

                    <button
                        type="submit"
                        class="cursor-pointer rounded border border-gray-300 bg-gray-700 px-3 py-1 font-label text-white text-shadow-md hover:bg-gray-600 disabled:cursor-not-allowed disabled:opacity-50 focus:outline-none focus:ring-2 focus:border-slate-500 focus:ring-slate-300">
                        Approve
                    </button>
                </form>
            </div>
        </form>

        <p class="mt-4 text-sm text-gray-500">
            You can revoke access at any time from your account settings.
        </p>
    </div>
</body>
</html>
