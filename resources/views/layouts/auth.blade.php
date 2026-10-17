<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Auth') — Mercatoria</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen items-center justify-center bg-surface p-4">

    <main class="w-full max-w-md">
        <div class="mb-8 text-center">
            <a href="{{ route('home') }}" class="text-3xl font-bold text-navy-500">Mercatoria</a>
            <p class="text-sm text-gray-500 mt-2">@yield('subtitle', '')</p>
        </div>

        <div class="rounded-xl border border-border bg-white p-6 shadow-md md:p-8">
            @if (session('success'))
                <x-alert type="success" class="mb-4">{{ session('success') }}</x-alert>
            @endif
            @if (session('error'))
                <x-alert type="danger" class="mb-4">{{ session('error') }}</x-alert>
            @endif
            @yield('content')
        </div>

        <p class="mt-6 text-center text-xs text-text-light">
            &copy; {{ date('Y') }} Mercatoria
        </p>
    </main>

    <x-ga4-tracking />

    @stack('scripts')
</body>
</html>
