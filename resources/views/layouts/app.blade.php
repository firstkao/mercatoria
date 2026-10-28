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

                <details class="relative">
                    <summary class="cursor-pointer list-none text-gray-500 hover:text-black">Game ▾</summary>
                    <div class="absolute left-0 top-full z-20 mt-2 w-48 rounded-lg border border-gray-100 bg-white py-2 shadow-lg">
                        @forelse ($navGames as $navGame)
                            <a href="{{ route('catalog.game', $navGame->slug) }}"
                               class="block px-4 py-1.5 text-gray-700 hover:bg-gray-50">{{ $navGame->name }}</a>
                        @empty
                            <a href="{{ route('coming-soon') }}" class="block px-4 py-1.5 text-gray-400">Menyusul</a>
                        @endforelse
                    </div>
                </details>

                <details class="relative">
                    <summary class="cursor-pointer list-none text-gray-500 hover:text-black">Developer ▾</summary>
                    <div class="absolute left-0 top-full z-20 mt-2 w-48 rounded-lg border border-gray-100 bg-white py-2 shadow-lg">
                        @forelse ($navDevelopers as $navDeveloper)
                            <a href="{{ route('catalog.developer', $navDeveloper->slug) }}"
                               class="block px-4 py-1.5 text-gray-700 hover:bg-gray-50">{{ $navDeveloper->name }}</a>
                        @empty
                            <a href="{{ route('coming-soon') }}" class="block px-4 py-1.5 text-gray-400">Menyusul</a>
                        @endforelse
                    </div>
                </details>

                <a href="{{ route('coming-soon') }}" class="text-gray-500 hover:text-black">Pre-order Baru</a>
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
                    <a href="{{ route('cart.index') }}" class="text-gray-500 hover:text-black">Keranjang</a>
                    <a href="{{ route('coming-soon') }}" class="text-gray-500 hover:text-black" title="Notifikasi — menyusul">Bell</a>
                    <a href="{{ route('dashboard') }}" class="font-medium hover:text-black">{{ auth()->user()->name }}</a>
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
