<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Mercatoria')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#F5F7FA] font-sans text-gray-900 antialiased">
    <nav class="bg-white shadow-sm">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-3">
            <div class="flex items-center gap-4 text-sm font-medium">
                <a href="/" class="hover:text-black">Beranda</a>
                <a href="#" class="text-gray-500 hover:text-black">Game</a>
                <a href="#" class="text-gray-500 hover:text-black">Developer</a>
                <a href="#" class="text-gray-500 hover:text-black">Pre-order Baru</a>
            </div>

            <a href="/" class="flex items-center gap-2">
                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-black text-sm font-semibold text-white">M</span>
                <span class="font-semibold tracking-wide">MERCATORIA</span>
            </a>

            <div class="flex items-center gap-3 text-sm">
                <form action="{{ route('search') }}" method="GET" class="hidden md:block">
                    <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari merch..."
                           class="w-48 rounded-full border border-gray-200 px-4 py-1.5 text-sm focus:border-black focus:outline-none">
                </form>

                @auth
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-gray-500 hover:text-black">Keluar</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="font-medium hover:text-black">Masuk</a>
                @endauth
            </div>
        </div>
    </nav>

    @if (session('status'))
        <div class="mx-auto max-w-7xl px-4 pt-4">
            <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                {{ session('status') }}
            </div>
        </div>
    @endif

    <main class="mx-auto max-w-7xl px-4 py-8">
        @yield('content')
    </main>
</body>
</html>
