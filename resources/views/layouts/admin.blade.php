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
                <a href="{{ route('office.spammers.index') }}"
                   @class(['block rounded-lg px-3 py-2 text-sm', 'bg-info-50 font-semibold text-navy-500' => request()->routeIs('office.spammers.*'), 'text-text-muted hover:bg-surface-2' => ! request()->routeIs('office.spammers.*')])>
                    Spammer
                </a>
                <p class="mb-2 mt-5 text-xs font-semibold uppercase tracking-wide text-text-light">Konten &amp; laporan</p>
                <a href="{{ route('office.pages.index') }}"
                   @class(['block rounded-lg px-3 py-2 text-sm', 'bg-info-50 font-semibold text-navy-500' => request()->routeIs('office.pages.*'), 'text-text-muted hover:bg-surface-2' => ! request()->routeIs('office.pages.*')])>
                    Halaman
                </a>
                <a href="{{ route('office.preorder-pages.index') }}"
                   @class(['block rounded-lg px-3 py-2 text-sm', 'bg-info-50 font-semibold text-navy-500' => request()->routeIs('office.preorder-pages.*'), 'text-text-muted hover:bg-surface-2' => ! request()->routeIs('office.preorder-pages.*')])>
                    Preorder
                </a>
                <a href="{{ route('office.vouchers.index') }}"
                   @class(['block rounded-lg px-3 py-2 text-sm', 'bg-info-50 font-semibold text-navy-500' => request()->routeIs('office.vouchers.*'), 'text-text-muted hover:bg-surface-2' => ! request()->routeIs('office.vouchers.*')])>
                    Voucher
                </a>
                <a href="{{ route('office.announcements.index') }}"
                   @class(['block rounded-lg px-3 py-2 text-sm', 'bg-info-50 font-semibold text-navy-500' => request()->routeIs('office.announcements.*'), 'text-text-muted hover:bg-surface-2' => ! request()->routeIs('office.announcements.*')])>
                    Announcement Bar
                </a>
                <a href="{{ route('office.hero-slides.index') }}"
                   @class(['block rounded-lg px-3 py-2 text-sm', 'bg-info-50 font-semibold text-navy-500' => request()->routeIs('office.hero-slides.*'), 'text-text-muted hover:bg-surface-2' => ! request()->routeIs('office.hero-slides.*')])>
                    Hero Carousel
                </a>
                <a href="{{ route('office.email-templates.index') }}"
                   @class(['block rounded-lg px-3 py-2 text-sm', 'bg-info-50 font-semibold text-navy-500' => request()->routeIs('office.email-templates.*'), 'text-text-muted hover:bg-surface-2' => ! request()->routeIs('office.email-templates.*')])>
                    Template Email
                </a>
                <a href="{{ route('office.reports.index') }}"
                   @class(['block rounded-lg px-3 py-2 text-sm', 'bg-info-50 font-semibold text-navy-500' => request()->routeIs('office.reports.*'), 'text-text-muted hover:bg-surface-2' => ! request()->routeIs('office.reports.*')])>
                    Laporan &amp; Rank
                </a>
                <a href="{{ route('office.seo.edit') }}"
                   @class(['block rounded-lg px-3 py-2 text-sm', 'bg-info-50 font-semibold text-navy-500' => request()->routeIs('office.seo.*'), 'text-text-muted hover:bg-surface-2' => ! request()->routeIs('office.seo.*')])>
                    SEO
                </a>
                <p class="mb-2 mt-5 text-xs font-semibold uppercase tracking-wide text-text-light">Operasional</p>
                <a href="{{ route('office.logs.activity') }}"
                   @class(['block rounded-lg px-3 py-2 text-sm', 'bg-info-50 font-semibold text-navy-500' => request()->routeIs('office.logs.*'), 'text-text-muted hover:bg-surface-2' => ! request()->routeIs('office.logs.*')])>
                    Audit Log
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
