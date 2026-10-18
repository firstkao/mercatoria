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
            <div class="relative hidden w-full max-w-md px-4 md:block">
                <label for="admin-search" class="sr-only">Cari produk, pesanan, atau customer</label>
                <input id="admin-search" type="search" autocomplete="off"
                    data-search-url="{{ route('office.search') }}"
                    class="w-full rounded-lg border border-border px-3 py-2 text-sm"
                    placeholder="Cari produk, pesanan, customer...">
                <div id="admin-search-results" class="absolute left-4 right-4 top-full z-20 mt-1 hidden max-h-96 overflow-y-auto rounded-lg border border-border bg-white shadow-lg"
                    role="listbox" aria-label="Hasil pencarian admin"></div>
            </div>
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

    <div class="mx-auto grid max-w-7xl gap-6 px-4 py-6 md:grid-cols-[var(--admin-sidebar-width)_minmax(0,1fr)]"
         x-data="{ collapsed: false }"
         :style="collapsed ? '--admin-sidebar-width: 56px' : '--admin-sidebar-width: 240px'">
        <aside>
            <nav aria-label="Navigasi admin" class="rounded-xl border border-border bg-white p-4"
                 :class="collapsed ? 'admin-sidebar-collapsed md:p-2' : 'md:p-4'">
                <div class="mb-3 flex justify-end">
                    <button type="button" x-on:click="collapsed = !collapsed"
                            :aria-label="collapsed ? 'Perluas navigasi admin' : 'Ciutkan navigasi admin'"
                            class="hidden rounded-lg border border-border px-2 py-1 text-sm text-text-muted hover:bg-surface-2 md:block">
                        <span x-show="!collapsed" aria-hidden="true">←</span>
                        <span x-show="collapsed" x-cloak aria-hidden="true">→</span>
                    </button>
                </div>
                <p x-show="!collapsed" class="mb-2 text-xs font-semibold uppercase tracking-wide text-text-light">Produk</p>
                <a href="{{ route('office.games.index') }}"
                   aria-label="Game" title="Game"
                   @class(['flex items-center gap-3 rounded-lg border-l-4 px-3 py-2 text-sm', 'border-white bg-primary-600 font-semibold text-white' => request()->routeIs('office.games.*'), 'border-transparent text-text-muted hover:bg-surface-2' => ! request()->routeIs('office.games.*')])>
                    <span aria-hidden="true">🎮</span><span x-show="!collapsed">Game</span>
                </a>
                <a href="{{ route('office.products.index') }}"
                   aria-label="Produk" title="Produk"
                   @class(['flex items-center gap-3 rounded-lg border-l-4 px-3 py-2 text-sm', 'border-white bg-primary-600 font-semibold text-white' => request()->routeIs('office.products.*'), 'border-transparent text-text-muted hover:bg-surface-2' => ! request()->routeIs('office.products.*')])>
                    <span aria-hidden="true">📦</span><span x-show="!collapsed">Produk</span>
                </a>
                <a href="{{ route('office.stores.index') }}"
                   aria-label="Stores (Tier Ongkir)" title="Stores (Tier Ongkir)"
                   @class(['flex items-center gap-3 rounded-lg border-l-4 px-3 py-2 text-sm', 'border-white bg-primary-600 font-semibold text-white' => request()->routeIs('office.stores.*'), 'border-transparent text-text-muted hover:bg-surface-2' => ! request()->routeIs('office.stores.*')])>
                    <span aria-hidden="true">🏪</span><span x-show="!collapsed">Stores (Tier Ongkir)</span>
                </a>
                <p x-show="!collapsed" class="mb-2 mt-5 text-xs font-semibold uppercase tracking-wide text-text-light">Transaksi</p>
                <a href="{{ route('office.orders.index') }}"
                   aria-label="Pesanan &amp; Pembayaran" title="Pesanan &amp; Pembayaran"
                   @class(['flex items-center gap-3 rounded-lg border-l-4 px-3 py-2 text-sm', 'border-white bg-primary-600 font-semibold text-white' => request()->routeIs('office.orders.*', 'office.payments.*', 'office.refunds.*'), 'border-transparent text-text-muted hover:bg-surface-2' => ! request()->routeIs('office.orders.*', 'office.payments.*', 'office.refunds.*')])>
                    <span aria-hidden="true">🛒</span><span x-show="!collapsed">Pesanan &amp; Pembayaran</span>
                </a>
                <p x-show="!collapsed" class="mb-2 mt-5 text-xs font-semibold uppercase tracking-wide text-text-light">Customer</p>
                <a href="{{ route('office.users.index') }}"
                   aria-label="Customer &amp; Migrasi" title="Customer &amp; Migrasi"
                   @class(['flex items-center gap-3 rounded-lg border-l-4 px-3 py-2 text-sm', 'border-white bg-primary-600 font-semibold text-white' => request()->routeIs('office.users.*'), 'border-transparent text-text-muted hover:bg-surface-2' => ! request()->routeIs('office.users.*')])>
                    <span aria-hidden="true">👥</span><span x-show="!collapsed">Customer &amp; Migrasi</span>
                </a>
                <a href="{{ route('office.spammers.index') }}"
                   aria-label="Spammer" title="Spammer"
                   @class(['flex items-center gap-3 rounded-lg border-l-4 px-3 py-2 text-sm', 'border-white bg-primary-600 font-semibold text-white' => request()->routeIs('office.spammers.*'), 'border-transparent text-text-muted hover:bg-surface-2' => ! request()->routeIs('office.spammers.*')])>
                    <span aria-hidden="true">🚫</span><span x-show="!collapsed">Spammer</span>
                </a>
                <p x-show="!collapsed" class="mb-2 mt-5 text-xs font-semibold uppercase tracking-wide text-text-light">Konten &amp; laporan</p>
                <a href="{{ route('office.pages.index') }}"
                   aria-label="Halaman" title="Halaman"
                   @class(['flex items-center gap-3 rounded-lg border-l-4 px-3 py-2 text-sm', 'border-white bg-primary-600 font-semibold text-white' => request()->routeIs('office.pages.*'), 'border-transparent text-text-muted hover:bg-surface-2' => ! request()->routeIs('office.pages.*')])>
                    <span aria-hidden="true">📄</span><span x-show="!collapsed">Halaman</span>
                </a>
                <a href="{{ route('office.preorder-pages.index') }}"
                   aria-label="Preorder" title="Preorder"
                   @class(['flex items-center gap-3 rounded-lg border-l-4 px-3 py-2 text-sm', 'border-white bg-primary-600 font-semibold text-white' => request()->routeIs('office.preorder-pages.*'), 'border-transparent text-text-muted hover:bg-surface-2' => ! request()->routeIs('office.preorder-pages.*')])>
                    <span aria-hidden="true">🧩</span><span x-show="!collapsed">Preorder</span>
                </a>
                <a href="{{ route('office.vouchers.index') }}"
                   aria-label="Voucher" title="Voucher"
                   @class(['flex items-center gap-3 rounded-lg border-l-4 px-3 py-2 text-sm', 'border-white bg-primary-600 font-semibold text-white' => request()->routeIs('office.vouchers.*'), 'border-transparent text-text-muted hover:bg-surface-2' => ! request()->routeIs('office.vouchers.*')])>
                    <span aria-hidden="true">🎟️</span><span x-show="!collapsed">Voucher</span>
                </a>
                <a href="{{ route('office.announcements.index') }}"
                   aria-label="Announcement Bar" title="Announcement Bar"
                   @class(['flex items-center gap-3 rounded-lg border-l-4 px-3 py-2 text-sm', 'border-white bg-primary-600 font-semibold text-white' => request()->routeIs('office.announcements.*'), 'border-transparent text-text-muted hover:bg-surface-2' => ! request()->routeIs('office.announcements.*')])>
                    <span aria-hidden="true">📣</span><span x-show="!collapsed">Announcement Bar</span>
                </a>
                <a href="{{ route('office.hero-slides.index') }}"
                   aria-label="Hero Carousel" title="Hero Carousel"
                   @class(['flex items-center gap-3 rounded-lg border-l-4 px-3 py-2 text-sm', 'border-white bg-primary-600 font-semibold text-white' => request()->routeIs('office.hero-slides.*'), 'border-transparent text-text-muted hover:bg-surface-2' => ! request()->routeIs('office.hero-slides.*')])>
                    <span aria-hidden="true">🖼️</span><span x-show="!collapsed">Hero Carousel</span>
                </a>
                <a href="{{ route('office.email-templates.index') }}"
                   aria-label="Template Email" title="Template Email"
                   @class(['flex items-center gap-3 rounded-lg border-l-4 px-3 py-2 text-sm', 'border-white bg-primary-600 font-semibold text-white' => request()->routeIs('office.email-templates.*'), 'border-transparent text-text-muted hover:bg-surface-2' => ! request()->routeIs('office.email-templates.*')])>
                    <span aria-hidden="true">✉️</span><span x-show="!collapsed">Template Email</span>
                </a>
                <a href="{{ route('office.reports.index') }}"
                   aria-label="Laporan &amp; Rank" title="Laporan &amp; Rank"
                   @class(['flex items-center gap-3 rounded-lg border-l-4 px-3 py-2 text-sm', 'border-white bg-primary-600 font-semibold text-white' => request()->routeIs('office.reports.*'), 'border-transparent text-text-muted hover:bg-surface-2' => ! request()->routeIs('office.reports.*')])>
                    <span aria-hidden="true">📊</span><span x-show="!collapsed">Laporan &amp; Rank</span>
                </a>
                <a href="{{ route('office.seo.edit') }}"
                   aria-label="SEO" title="SEO"
                   @class(['flex items-center gap-3 rounded-lg border-l-4 px-3 py-2 text-sm', 'border-white bg-primary-600 font-semibold text-white' => request()->routeIs('office.seo.*'), 'border-transparent text-text-muted hover:bg-surface-2' => ! request()->routeIs('office.seo.*')])>
                    <span aria-hidden="true">🔍</span><span x-show="!collapsed">SEO</span>
                </a>
                <p x-show="!collapsed" class="mb-2 mt-5 text-xs font-semibold uppercase tracking-wide text-text-light">Pengaturan</p>
                <a href="{{ route('office.settings.edit') }}"
                   aria-label="Pengaturan" title="Pengaturan"
                   @class(['flex items-center gap-3 rounded-lg border-l-4 px-3 py-2 text-sm', 'border-white bg-primary-600 font-semibold text-white' => request()->routeIs('office.settings.*'), 'border-transparent text-text-muted hover:bg-surface-2' => ! request()->routeIs('office.settings.*')])>
                    <span aria-hidden="true">⚙️</span><span x-show="!collapsed">Pengaturan</span>
                </a>
                <p x-show="!collapsed" class="mb-2 mt-5 text-xs font-semibold uppercase tracking-wide text-text-light">Operasional</p>
                <a href="{{ route('office.logs.activity') }}"
                   aria-label="Audit Log" title="Audit Log"
                   @class(['flex items-center gap-3 rounded-lg border-l-4 px-3 py-2 text-sm', 'border-white bg-primary-600 font-semibold text-white' => request()->routeIs('office.logs.*'), 'border-transparent text-text-muted hover:bg-surface-2' => ! request()->routeIs('office.logs.*')])>
                    <span aria-hidden="true">🧾</span><span x-show="!collapsed">Audit Log</span>
                </a>
            </nav>
        </aside>

        <main class="min-w-0">
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
