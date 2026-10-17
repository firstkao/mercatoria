<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Mercatoria') — Mercatoria</title>
    <meta name="description" content="@yield('meta_description', 'Marketplace merchandise IP Cina untuk penggemar Indonesia')">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="min-h-screen flex flex-col bg-white text-text antialiased">

    @auth
        <x-navbar-user />
    @else
        <x-navbar-guest />
    @endauth

    <x-verify-banner-compact />

    <main class="flex-1">
        @yield('content')
    </main>

    <x-footer />

    <x-toast />

    @stack('scripts')
</body>
</html>