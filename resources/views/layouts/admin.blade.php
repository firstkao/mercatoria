<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') — Mercatoria</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @if (session()->has('ga4_events'))
        <x-ga4-tracking />
    @endif
</head>
<body class="min-h-screen bg-[#F8F9FA] text-text antialiased"
      x-data="{ collapsed: false, drawer: false, searchOpen: false }">
    @php
        $emailsPaused = filter_var(setting('pause_customer_emails', config('mercatoria.mail_control.pause_customer_emails', false)), FILTER_VALIDATE_BOOLEAN);
        $menu = [
            ['📊', 'Dashboard', [
                ['Dashboard', 'office.dashboard', ['office.dashboard']],
                ['Activity Log', 'office.logs.activity', ['office.logs.activity']],
                ['Auth Log', 'office.logs.auth', ['office.logs.auth']],
                ['Admin Log', 'office.logs.admin', ['office.logs.admin']],
            ]],
            ['📦', 'Produk', [
                ['Semua Produk', 'office.products.index', ['office.products.*']],
                ['Kategori Merch', 'office.merch-categories.index', ['office.merch-categories.*']],
                ['Tag', 'office.tags.index', ['office.tags.*']],
                ['Stores (Tier Ongkir)', 'office.stores.index', ['office.stores.*']],
            ]],
            ['🎮', 'Katalog', [
                ['Game', 'office.games.index', ['office.games.*']],
                ['Developer', 'office.developers.index', ['office.developers.*']],
            ]],
            ['🛒', 'Transaksi', [
                ['Orders', 'office.orders.index', ['office.orders.*']],
                ['Verifikasi Pembayaran', 'office.payments.index', ['office.payments.*']],
                ['Refunds', 'office.refunds.index', ['office.refunds.*']],
                ['Voucher', 'office.vouchers.index', ['office.vouchers.*']],
            ]],
            ['👥', 'Pengguna', [
                ['Users', 'office.users.index', ['office.users.*']],
                ['Spammers', 'office.spammers.index', ['office.spammers.*']],
            ]],
            ['📄', 'Konten', [
                ['Pages CMS', 'office.pages.index', ['office.pages.*']],
                ['Email Templates', 'office.email-templates.index', ['office.email-templates.*']],
                ['Preorder Builder', 'office.preorder-pages.index', ['office.preorder-pages.*']],
                ['Announcement Bar', 'office.announcements.index', ['office.announcements.*']],
                ['Hero Carousel', 'office.hero-slides.index', ['office.hero-slides.*']],
            ]],
            ['🔍', 'SEO', [
                ['SEO', 'office.seo.edit', ['office.seo.*']],
            ]],
            ['📈', 'Laporan', [
                ['Reports', 'office.reports.index', ['office.reports.index']],
            ]],
            ['🏆', 'Pelanggan', [
                ['Rank Pelanggan', 'office.customers.rank', ['office.customers.*', 'office.reports.customers']],
            ]],
            ['⚙️', 'Settings', [
                ['Settings', 'office.settings.edit', ['office.settings.*']],
            ]],
        ];
    @endphp
    <header class="sticky top-0 z-40 flex h-14 items-center gap-2 border-b border-border bg-white px-3 md:gap-3 md:px-4">
        <button type="button" x-on:click="drawer = !drawer" aria-label="Buka navigasi admin"
                class="rounded-lg p-2 text-xl md:hidden">☰</button>
        <a href="{{ route('office.dashboard') }}" class="text-base font-bold text-navy-500 md:hidden">MERCATORIA</a>
        <div class="absolute inset-x-0 top-14 border-b border-border bg-white p-2 md:static md:block md:flex-1 md:border-0 md:p-0"
             :class="searchOpen ? 'block' : 'hidden md:block'">
            <div class="relative md:max-w-md">
                <label for="admin-search" class="sr-only">Cari produk, pesanan, atau customer</label>
                <input id="admin-search" type="search" autocomplete="off"
                    data-search-url="{{ route('office.search') }}"
                    class="w-full rounded-lg border border-border px-3 py-2 text-sm"
                    placeholder="🔍 Cari Produk/Order/User...">
                <div id="admin-search-results" class="absolute left-0 right-0 top-full z-20 mt-1 hidden max-h-96 overflow-y-auto rounded-lg border border-border bg-white shadow-lg"
                    role="listbox" aria-label="Hasil pencarian admin"></div>
            </div>
        </div>
        <div class="ml-auto flex items-center gap-2 text-sm md:gap-4">
            @if ($emailsPaused)
                <a href="{{ route('office.settings.edit', ['tab' => 'email']) }}" role="status"
                   class="hidden items-center gap-1 rounded-full bg-danger-500 px-3 py-1 text-xs font-semibold text-white sm:inline-flex">
                    🔇 EMAIL CUSTOMER: OFF (Mode Testing) · Aktifkan →
                </a>
            @endif
            <button type="button" x-on:click="searchOpen = !searchOpen" aria-label="Cari" class="rounded-lg p-2 text-lg md:hidden">🔍</button>
            <a href="{{ route('home') }}" class="hidden text-navy-500 hover:underline md:inline">🌐 Lihat Frontend</a>
            <x-admin-notification-bell />
            <div x-data="{ open: false }" @click.outside="open = false" class="relative">
                <button type="button" x-on:click="open = !open" :aria-expanded="open" aria-label="Menu profil"
                        class="flex items-center gap-1 rounded-lg p-2 hover:bg-surface-2">
                    <span aria-hidden="true">👤</span><span class="hidden text-text-muted sm:inline">{{ auth()->user()->name }}</span><span aria-hidden="true">▾</span>
                </button>
                <div x-show="open" x-cloak class="absolute right-0 z-50 mt-2 w-48 rounded-lg border border-border bg-white py-1 shadow-lg">
                    <a href="{{ route('home') }}" class="block px-4 py-2 hover:bg-surface-2 md:hidden">🌐 Lihat Frontend</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="block w-full px-4 py-2 text-left text-danger-500 hover:bg-surface-2">Keluar</button>
                    </form>
                </div>
            </div>
        </div>
    </header>
    @if ($emailsPaused)
        <a href="{{ route('office.settings.edit', ['tab' => 'email']) }}" role="status"
           class="block bg-danger-500 px-4 py-2 text-center text-xs font-semibold text-white sm:hidden">
            🔇 EMAIL CUSTOMER: OFF (Mode Testing) · Aktifkan →
        </a>
    @endif

    <div class="md:flex">
        <div x-show="drawer" x-cloak x-on:click="drawer = false" class="fixed inset-0 z-20 bg-black/40 md:hidden"></div>
        <aside class="fixed inset-y-14 left-0 z-30 w-60 shrink-0 overflow-y-auto border-r border-border bg-white transition-transform md:sticky md:top-14 md:h-[calc(100vh-3.5rem)] md:translate-x-0"
               :class="[drawer ? 'translate-x-0' : '-translate-x-full', collapsed ? 'md:w-14' : 'md:w-60']">
            <nav aria-label="Navigasi admin" class="p-3" :class="collapsed ? 'admin-sidebar-collapsed md:p-2' : ''">
                <p class="mb-3 px-2 text-sm font-bold text-navy-500" x-show="!collapsed">🌐 MERCATORIA</p>
                @foreach ($menu as [$groupIcon, $groupLabel, $items])
                    <p x-show="!collapsed" class="mb-1 mt-4 px-2 text-xs font-semibold uppercase tracking-wide text-text-light">{{ $groupIcon }} {{ $groupLabel }}</p>
                    @foreach ($items as [$label, $routeName, $patterns])
                        @php $active = request()->routeIs(...$patterns); @endphp
                        <a href="{{ route($routeName) }}" aria-label="{{ $label }}" title="{{ $label }}"
                           @if ($active) aria-current="page" @endif
                           @class(['flex items-center gap-3 rounded-lg border-l-4 px-3 py-2 text-sm', 'border-white bg-primary-300 font-semibold text-white' => $active, 'border-transparent text-text-muted hover:bg-surface-2' => ! $active])>
                            <span aria-hidden="true">{{ $groupIcon }}</span><span x-show="!collapsed">{{ $label }}</span>
                        </a>
                    @endforeach
                @endforeach
                <button type="button" x-on:click="collapsed = !collapsed"
                        :aria-label="collapsed ? 'Perluas navigasi admin' : 'Ciutkan navigasi admin'"
                        class="mt-4 hidden w-full rounded-lg border border-border px-2 py-1 text-sm text-text-muted hover:bg-surface-2 md:block">
                    <span x-show="!collapsed">← Collapse</span>
                    <span x-show="collapsed" x-cloak aria-hidden="true">→</span>
                </button>
            </nav>
        </aside>

        <main class="min-w-0 flex-1 p-4 md:p-6">
            @if (session('success'))
                <x-alert type="success" class="mb-4">{{ session('success') }}</x-alert>
            @endif
            @if (session('warning'))
                <x-alert type="warning" class="mb-4">{{ session('warning') }}</x-alert>
            @endif
            @yield('content')
        </main>
    </div>

    @stack('scripts')
    <script>
        (() => {
            const input = document.getElementById('admin-search');
            const results = document.getElementById('admin-search-results');
            if (!input || !results) return;
            let timer;
            const close = () => {
                results.replaceChildren();
                results.classList.add('hidden');
            };
            input.addEventListener('input', () => {
                window.clearTimeout(timer);
                const term = input.value.trim();
                if (term.length < 2) {
                    close();
                    return;
                }
                timer = window.setTimeout(async () => {
                    try {
                        const response = await fetch(`${input.dataset.searchUrl}?q=${encodeURIComponent(term)}`, {
                            headers: { 'Accept': 'application/json' },
                        });
                        if (!response.ok) throw new Error('Admin search request failed.');
                        const groups = await response.json();
                        results.replaceChildren();
                        for (const [label, items] of Object.entries(groups)) {
                            if (!items.length) continue;
                            const heading = document.createElement('p');
                            heading.className = 'bg-surface-2 px-3 py-2 text-xs font-semibold uppercase';
                            heading.textContent = label;
                            results.append(heading);
                            for (const item of items) {
                                const link = document.createElement('a');
                                link.className = 'block px-3 py-2 text-sm hover:bg-surface-2';
                                link.href = item.url;
                                link.textContent = item.label;
                                results.append(link);
                            }
                        }
                        if (!results.childElementCount) {
                            const empty = document.createElement('p');
                            empty.className = 'px-3 py-3 text-sm text-text-muted';
                            empty.textContent = 'Tidak ada hasil.';
                            results.append(empty);
                        }
                        results.classList.remove('hidden');
                    } catch (error) {
                        close();
                        console.error(error);
                    }
                }, 250);
            });
            document.addEventListener('click', event => {
                if (!input.contains(event.target) && !results.contains(event.target)) close();
            });
            input.addEventListener('keydown', event => {
                if (event.key === 'Escape') close();
            });
        })();
    </script>
</body>
</html>
