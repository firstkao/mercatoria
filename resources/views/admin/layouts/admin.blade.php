<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Office') — Mercatoria</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#F8F9FA] font-sans text-gray-900 antialiased">
    <div class="flex min-h-screen">
        <aside class="w-60 shrink-0 bg-black text-white">
            <div class="px-4 py-5 text-lg font-semibold tracking-wide">MERCATORIA</div>
            <nav class="space-y-1 px-3 text-sm">
                <a href="{{ route('office.dashboard') }}" class="block rounded px-3 py-2 hover:bg-white/10">Dashboard</a>

                <p class="px-3 pt-4 text-[11px] uppercase tracking-wide text-white/40">Log</p>
                <a href="{{ route('office.activity-logs') }}" class="block rounded px-3 py-2 hover:bg-white/10">Activity Log</a>
                <a href="{{ route('office.auth-logs') }}" class="block rounded px-3 py-2 hover:bg-white/10">Auth Log</a>
                <a href="{{ route('office.admin-logs') }}" class="block rounded px-3 py-2 hover:bg-white/10">Admin Log</a>

                <p class="px-3 pt-4 text-[11px] uppercase tracking-wide text-white/40">Produk</p>
                <a href="{{ route('office.products.index') }}" class="block rounded px-3 py-2 hover:bg-white/10">Semua Produk</a>
                <a href="{{ route('office.merch-categories.index') }}" class="block rounded px-3 py-2 hover:bg-white/10">Kategori Merch</a>
                <a href="{{ route('office.tags.index') }}" class="block rounded px-3 py-2 hover:bg-white/10">Tag</a>
                <a href="{{ route('office.stores.index') }}" class="block rounded px-3 py-2 hover:bg-white/10">Stores</a>

                <p class="px-3 pt-4 text-[11px] uppercase tracking-wide text-white/40">Katalog</p>
                <a href="{{ route('office.games.index') }}" class="block rounded px-3 py-2 hover:bg-white/10">Game</a>
                <a href="{{ route('office.developers.index') }}" class="block rounded px-3 py-2 hover:bg-white/10">Developer</a>

                <p class="px-3 pt-4 text-[11px] uppercase tracking-wide text-white/40">Transaksi</p>
                <a href="{{ route('office.orders.index') }}" class="block rounded px-3 py-2 hover:bg-white/10">Orders</a>
                <a href="{{ route('office.payments.index') }}" class="block rounded px-3 py-2 hover:bg-white/10">Verifikasi Pembayaran</a>

                <p class="px-3 pt-4 text-[11px] uppercase tracking-wide text-white/40">Pengguna</p>
                <a href="{{ route('office.users.index') }}" class="block rounded px-3 py-2 hover:bg-white/10">Users</a>

                <p class="px-3 pt-4 text-[11px] uppercase tracking-wide text-white/40">Lainnya</p>
                <a href="{{ route('office.reports.index') }}" class="block rounded px-3 py-2 hover:bg-white/10">Laporan</a>
                <a href="{{ route('office.settings.index') }}" class="block rounded px-3 py-2 hover:bg-white/10">Settings</a>
            </nav>
        </aside>

        <div class="flex-1">
            <header class="flex h-14 items-center justify-between border-b border-gray-200 bg-white px-6">
                <span class="text-sm text-gray-500">@yield('title', 'Office')</span>
                <div class="flex items-center gap-3 text-sm">
                    <a href="/" class="text-gray-500 hover:text-black">Lihat Frontend</a>
                    <span>{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="rounded border border-gray-300 px-3 py-1 hover:border-black">Keluar</button>
                    </form>
                </div>
            </header>

            <main class="p-6">
                @if (session('status'))
                    <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                        {{ session('status') }}
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
