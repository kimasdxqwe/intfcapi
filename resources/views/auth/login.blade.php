<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Log in</title>
    @vite(['resources/css/app.css'])
</head>
<body class="flex min-h-screen items-center justify-center bg-gray-100 px-4">
<div class="w-full max-w-sm rounded bg-white border-gray-300 p-6 shadow">
    <div class="mb-6 text-center">
        <h1 class="text-xl font-semibold text-gray-900">Log in to continue</h1>
        <p class="text-sm text-gray-500">Enter your credentials to access your account.</p>
    </div>

    <form id="login-section" method="POST" action="{{ route('login.attempt') }}" class="space-y-5">
        @csrf


        <div>
            <label for="email" class="mb-1 block text-sm font-medium text-gray-700">Email</label>
            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email', config('app.auth_prefill.email')) }}"
                required
                autocomplete="email"
                class="w-full rounded-sm border px-3 py-1 text-gray-900 shadow-sm focus:outline-none focus:ring-2
                           {{ $errors->has('email')
                               ? 'border-red-500 focus:ring-red-200'
                               : 'border-gray-300 focus:border-slate-500 focus:ring-slate-300' }}"
            >
            @error('email')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="mb-1 block text-sm font-medium text-gray-700">Password</label>
            <input
                id="password"
                type="password"
                name="password"
                required
                autocomplete="current-password"
                value="{{ config('app.auth_prefill.password') }}"
                class="w-full rounded-sm border px-3 py-1 text-gray-900 shadow-sm focus:outline-none focus:ring-2
                           {{ $errors->has('password')
                               ? 'border-red-500 focus:ring-red-200'
                               : 'border-gray-300 focus:border-slate-500 focus:ring-slate-300' }}"
            >
            @error('password')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <label class="flex items-center gap-2 text-sm text-gray-700">
            <input
                type="checkbox"
                name="remember"
                class="size-4 rounded border-gray-300 text-slate-600 focus:ring-slate-300"
            >
            Remember me
        </label>

        <div class="flex justify-end">
            <button
                type="submit"
                class="cursor-pointer rounded border border-gray-300 bg-gray-700 px-3 py-1 font-label text-white text-shadow-md hover:bg-gray-600 disabled:cursor-not-allowed disabled:opacity-50 focus:outline-none focus:ring-2 focus:border-slate-500 focus:ring-slate-300">
                Log in
            </button>
        </div>
    </form>
</div>
</body>
</html>
