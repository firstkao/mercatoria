<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') — Mercatoria</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-surface text-text antialiased">
    <header class="border-b border-border bg-white">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3">
            <a href="{{ route('office.stores.index') }}" class="text-xl font-bold text-navy-500">Mercatoria Admin</a>
            <div class="flex items-center gap-4 text-sm">
                <x-admin-notification-bell />
                <span class="hidden text-text-muted sm:inline">{{ auth()->user()->name }}</span>
                <a href="{{ route('home') }}" class="text-navy-500 hover:underline">Lihat toko</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-danger-500 hover:underline">Keluar</button>
                </form>
            </div>
        </div>
    </header>

    <div class="mx-auto grid max-w-7xl gap-6 px-4 py-6 md:grid-cols-[220px_minmax(0,1fr)]">
        <aside>
            <nav aria-label="Navigasi admin" class="rounded-xl border border-border bg-white p-4">
                <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-text-light">Produk</p>
                <a href="{{ route('office.games.index') }}"
                   @class(['block rounded-lg px-3 py-2 text-sm', 'bg-info-50 font-semibold text-navy-500' => request()->routeIs('office.games.*'), 'text-text-muted hover:bg-surface-2' => ! request()->routeIs('office.games.*')])>
                    Game
                </a>
                <a href="{{ route('office.products.index') }}"
                   @class(['block rounded-lg px-3 py-2 text-sm', 'bg-info-50 font-semibold text-navy-500' => request()->routeIs('office.products.*'), 'text-text-muted hover:bg-surface-2' => ! request()->routeIs('office.products.*')])>
                    Produk
                </a>
                <a href="{{ route('office.orders.index') }}"
                   @class(['block rounded-lg px-3 py-2 text-sm', 'bg-info-50 font-semibold text-navy-500' => request()->routeIs('office.orders.*', 'office.payments.*', 'office.refunds.*'), 'text-text-muted hover:bg-surface-2' => ! request()->routeIs('office.orders.*', 'office.payments.*', 'office.refunds.*')])>
                    Pesanan &amp; Pembayaran
                </a>
                <a href="{{ route('office.stores.index') }}"
                   @class(['block rounded-lg px-3 py-2 text-sm', 'bg-info-50 font-semibold text-navy-500' => request()->routeIs('office.stores.*'), 'text-text-muted hover:bg-surface-2' => ! request()->routeIs('office.stores.*')])>
                    Stores (Tier Ongkir)
                </a>
                <a href="{{ route('office.settings.edit') }}"
                   @class(['block rounded-lg px-3 py-2 text-sm', 'bg-info-50 font-semibold text-navy-500' => request()->routeIs('office.settings.*'), 'text-text-muted hover:bg-surface-2' => ! request()->routeIs('office.settings.*')])>
                    Pengaturan
                </a>
                <p class="mb-2 mt-5 text-xs font-semibold uppercase tracking-wide text-text-light">Customer</p>
                <a href="{{ route('office.users.index') }}"
                   @class(['block rounded-lg px-3 py-2 text-sm', 'bg-info-50 font-semibold text-navy-500' => request()->routeIs('office.users.*'), 'text-text-muted hover:bg-surface-2' => ! request()->routeIs('office.users.*')])>
                    Customer &amp; Migrasi
                </a>
            </nav>
        </aside>

        <main class="min-w-0">
            @if (session('success'))
                <x-alert type="success" class="mb-4">{{ session('success') }}</x-alert>
            @endif
            @yield('content')
        </main>
    </div>

    @stack('scripts')
</body>
</html>
