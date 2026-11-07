<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Office') — Mercatoria</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600;9..144,700&family=IBM+Plex+Mono:wght@400;500;600&family=Schibsted+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="{{ asset('js/app-modal.js') }}" defer></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        .nav-link-active {
            background: rgba(255,255,255,0.15) !important;
            color: #fff !important;
            font-weight: 600;
            border-left: 3px solid #FFB300;
            padding-left: calc(0.75rem - 3px);
        }

        #sidebar-nav::-webkit-scrollbar { display: none; }
        #sidebar-nav { -ms-overflow-style: none; scrollbar-width: none; }

        /* ── SIDEBAR ── */
        #sidebar-desktop {
            width: 256px !important;
            min-width: 256px !important;
            overflow: hidden;
            transition: width 0.25s ease, min-width 0.25s ease;
        }
        #sidebar-desktop .nav-text {
            transition: opacity 0.15s ease;
            white-space: nowrap;
        }
        #sidebar-desktop .nav-section-label {
            overflow: hidden; white-space: nowrap;
        }
        #sidebar-desktop .sidebar-brand-full { display: block; }
        #sidebar-desktop .sidebar-brand-mini { display: none; }

        /* ── COLLAPSED ── */
        #sidebar-desktop.collapsed {
            width: 68px !important;
            min-width: 68px !important;
        }
        #sidebar-desktop.collapsed .nav-text { opacity: 0; width: 0; overflow: hidden; }
        #sidebar-desktop.collapsed .nav-section-label {
            opacity: 0; height: 12px; padding-top: 0; padding-bottom: 0;
        }
        #sidebar-desktop.collapsed .nav-link {
            justify-content: center;
            gap: 0 !important;
            padding-left: 0 !important; padding-right: 0 !important;
        }
        #sidebar-desktop.collapsed .nav-link i { margin: 0 !important; }
        #sidebar-desktop.collapsed .sidebar-brand-full { display: none; }
        #sidebar-desktop.collapsed .sidebar-brand-mini { display: block; text-align: center; }

        /* Tooltip collapsed */
        #sidebar-desktop.collapsed .nav-link { position: relative; }
        #sidebar-desktop.collapsed .nav-link:hover .nav-text {
            opacity: 1; width: auto; gap: 0;
            position: absolute; left: calc(100% + 8px); top: 50%; transform: translateY(-50%);
            background: #2A2A3D; color: #fff; padding: 5px 14px;
            border-radius: 6px; font-size: 12px; z-index: 60;
            box-shadow: 0 4px 16px rgba(0,0,0,0.25);
            pointer-events: none;
        }

        /* ── MOBILE ── */
        #sidebar-overlay { display: none; }
        #sidebar-overlay.active { display: block; }
        @media (max-width: 768px) {
            #sidebar-desktop {
                position: fixed; left: -280px; top: 0; bottom: 0; z-index: 50;
                width: 280px !important; min-width: 280px !important;
                transition: left 0.25s ease;
            }
            #sidebar-desktop.open { left: 0; }
            #sidebar-overlay.active {
                display: block; position: fixed; inset: 0;
                background: rgba(0,0,0,0.4); z-index: 40;
            }
            #sidebar-desktop .nav-text {
                opacity: 1 !important; width: auto !important;
                position: static !important; background: none !important;
                padding: 0 !important; box-shadow: none !important;
                transform: none !important;
            }
            #sidebar-desktop .nav-section-label {
                opacity: 1 !important; height: auto !important;
            }
            #sidebar-desktop .nav-link {
                justify-content: flex-start !important;
                gap: 12px !important;
                padding-left: 12px !important; padding-right: 12px !important;
            }
            #sidebar-desktop .sidebar-brand-full { display: block !important; }
            #sidebar-desktop .sidebar-brand-mini { display: none !important; }
        }
    </style>
</head>
<body class="min-h-screen bg-[#F8F9FA] font-sans text-ink antialiased">
    <div class="flex min-h-screen">

        <div id="sidebar-overlay" onclick="toggleSidebar()"></div>

        {{-- ═══════════════════════════════════════ --}}
        {{-- SIDEBAR                                  --}}
        {{-- ═══════════════════════════════════════ --}}
        <aside id="sidebar-desktop" class="shrink-0 text-white" style="background-color:#1E1E2D;">

            {{-- Brand --}}
            <div class="flex h-[60px] items-center border-b border-white/10 px-4">
                <div class="sidebar-brand-full flex-1 min-w-0">
                    <p class="font-mono text-[10px] uppercase tracking-[0.22em] text-white/45">Back-Office</p>
                    <p class="mt-0.5 truncate font-display text-lg font-semibold tracking-wide text-white">MERCATORIA</p>
                </div>
                <div class="sidebar-brand-mini flex-1">
                    <p class="font-display text-xl font-bold leading-none text-white">M</p>
                </div>
                <button onclick="toggleSidebar()"
                        class="shrink-0 rounded-lg p-2 text-white/50 hover:bg-white/10 hover:text-white md:hidden"
                        title="Tutup">
                    <i class="bi bi-chevron-left" style="font-size:1.15rem;"></i>
                </button>
            </div>

                        {{-- Nav --}}
            <nav id="sidebar-nav" class="space-y-0.5 px-3 py-3 text-[13px]" style="max-height:calc(100vh - 60px); overflow-y:auto;">
                @php
                    $currentRoute = request()->route()?->getName() ?? '';
                    $isActive = fn($prefix) => str_starts_with($currentRoute, $prefix) ? 'nav-link-active' : '';
                @endphp

                @php $navItems = [
                        ['__SECTION__','','Dashboard'],
                        ['office.dashboard','bi-speedometer2','Dashboard','office.dashboard'],
                    
                        ['__SECTION__','','Log Sistem'],
                        ['office.activity-logs','bi-activity','Log Aktivitas','office.activity-logs'],
                        ['office.auth-logs','bi-shield-lock','Log Autentikasi','office.auth-logs'],
                        ['office.admin-logs','bi-person-gear','Log Admin','office.admin-logs'],
                    
                        ['__SECTION__','','Produk'],
                        ['office.products.index','bi-box-seam','Semua Produk','office.products'],
                        ['office.merch-categories.index','bi-grid','Kategori Merch','office.merch-categories'],
                        ['office.tags.index','bi-tags','Tag','office.tags'],
                    
                        ['__SECTION__','','Katalog & Game'],
                        ['office.games.index','bi-controller','Game','office.games'],
                        ['office.developers.index','bi-buildings','Developer','office.developers'],
                    
                        ['__SECTION__','','Toko'],
                        ['office.stores.index','bi-shop','Daftar Toko','office.stores'],
                    
                        ['__SECTION__','','Transaksi'],
                        ['office.orders.index','bi-receipt','Pesanan','office.orders'],
                        ['office.payments.index','bi-credit-card','Verifikasi Pembayaran','office.payments'],
                        ['office.refunds.index','bi-arrow-counterclockwise','Pengembalian (Refund)','office.refunds'],
                        ['office.vouchers.index','bi-ticket-perforated','Voucher','office.vouchers'],
                    
                        ['__SECTION__','','Pengguna'],
                        ['office.users.index','bi-people','Pengguna','office.users'],
                        ['office.customers.rank','bi-trophy','Peringkat Pelanggan','office.customers'],
                        ['office.spammers.index','bi-shield-exclamation','Spammer','office.spammers'],
                    
                        ['__SECTION__','','Konten & CMS'],
                        ['office.pages.index','bi-file-text','Halaman','office.pages'],
                        ['office.preorder.index','bi-layout-text-window-reverse','Preorder Builder','office.preorder'],
                        ['office.hero-slides.index','bi-image','Banner Slider','office.hero-slides'],
                    
                        ['__SECTION__','','Pengaturan'],
                        ['office.email-templates.index','bi-envelope-paper','Template Email','office.email-templates'],
                        ['office.reports.index','bi-bar-chart','Laporan','office.reports'],
                        ['office.payment-methods.index','bi-wallet2','Metode Pembayaran','office.payment-methods'],
                        ['office.seo.index','bi-globe','SEO','office.seo'],
                        ['office.settings.index','bi-gear','Pengaturan Sistem','office.settings'],
                ]; @endphp

                @foreach ($navItems as $item)
                    @if ($item[0] === '__SECTION__')
                        <p class="nav-section-label px-3 pt-4 pb-1.5 font-mono text-[9px] uppercase tracking-[0.18em] text-white/40">{{ $item[2] }}</p>
                    @else
                        <a href="{{ route($item[0]) }}"
                           class="nav-link flex items-center gap-3 rounded px-3 py-2.5 transition-colors hover:bg-white/10 {{ $isActive($item[3]) ? 'nav-link-active' : 'text-white/80' }}">
                            <i class="bi {{ $item[1] }} shrink-0" style="font-size:1.05rem;"></i>
                            <span class="nav-text">{{ $item[2] }}</span>
                        </a>
                    @endif
                @endforeach
            </nav>
            <script>
                // Navigasi = full page load: simpan & pulihkan scrollTop sidebar sebelum paint.
                (function () {
                    var nav = document.getElementById('sidebar-nav');
                    if (!nav) return;
                    var key = 'admin-sidebar-scroll';
                    try {
                        var saved = parseInt(sessionStorage.getItem(key), 10);
                        if (!isNaN(saved)) nav.scrollTop = saved;
                    } catch (e) {}

                    var active = nav.querySelector('.nav-link-active');
                    if (active) {
                        var top = active.offsetTop - nav.offsetTop;
                        var visible = top >= nav.scrollTop && (top + active.offsetHeight) <= (nav.scrollTop + nav.clientHeight);
                        if (!visible) {
                            nav.scrollTop = Math.max(0, top - (nav.clientHeight - active.offsetHeight) / 2);
                        }
                    }

                    function save() {
                        try { sessionStorage.setItem(key, String(nav.scrollTop)); } catch (e) {}
                    }
                    save();
                    nav.addEventListener('scroll', save, { passive: true });
                    window.addEventListener('pagehide', save);
                })();
            </script>
        </aside>

        {{-- ═══════════════════════════════════════ --}}
        {{-- MAIN                                     --}}
        {{-- ═══════════════════════════════════════ --}}
        <div class="flex flex-1 flex-col min-w-0">

            {{-- ═══ TOPBAR ═══ --}}
            <header class="flex min-h-[60px] items-center justify-between gap-3 border-b border-[#EAEAEA] bg-white px-3 lg:px-5">

                {{-- Left --}}
                <div class="flex items-center gap-2">
                    {{-- Mobile hamburger --}}
                    <button onclick="toggleSidebar()"
                            class="md:hidden shrink-0 rounded-lg p-2 text-ink/70 hover:bg-paper hover:text-ink">
                        <i class="bi bi-list" style="font-size:1.35rem;"></i>
                    </button>

                    {{-- ★ Desktop collapse toggle — SELALU terlihat di topbar --}}
                    <button onclick="toggleSidebarDesktop()"
                            class="hidden shrink-0 rounded-lg p-2 text-ink/50 transition-colors hover:bg-paper hover:text-ink md:flex"
                            title="Buka/tutup sidebar">
                        <i class="bi bi-layout-sidebar-inset" style="font-size:1.25rem;"></i>
                    </button>

                    {{-- Search --}}
                    @if (\Illuminate\Support\Facades\Route::has('office.search'))
                        <div class="relative" id="admin-search">
                            <div class="relative">
                                <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-ink/35" style="font-size:0.85rem;"></i>
                                <input type="text" id="admin-search-input" placeholder="Cari Produk/Order/User..."
                                       class="w-44 rounded-lg border border-rule bg-white py-2 pl-9 pr-3 text-sm text-ink placeholder:text-ink/35 focus:border-ink focus:outline-none focus:ring-2 focus:ring-accent/45 sm:w-52 lg:w-64">
                            </div>
                            <div id="admin-search-results" class="absolute left-0 top-full z-30 mt-1 hidden w-72 rounded-lg border border-rule bg-white shadow-md"></div>
                        </div>
                    @endif
                </div>

                {{-- Right --}}
                <div class="flex shrink-0 items-center gap-1.5">

                    {{-- Email Kill-Switch Pill --}}
                    @php($emailPaused = !empty($customerEmailsPaused))
                    @if($emailPaused)
                        <div class="flex shrink-0 items-center gap-1.5 rounded-full border border-[#E53935]/30 bg-[#FDECEA] px-2.5 py-1">
                            <i class="bi bi-megaphone-fill text-[#E53935]" style="font-size:0.8rem;"></i>
                            <span class="hidden font-mono text-[9px] font-semibold uppercase tracking-[0.12em] text-[#E53935] sm:inline">
                                Email Customer: OFF
                            </span>
                            <span class="rounded-full bg-[#FF8B00] px-1.5 py-0.5 text-[8px] font-bold uppercase tracking-wider text-white">
                                Mode Testing
                            </span>
                            @if(Route::has('office.settings.update'))
                                <form method="POST" action="{{ route('office.settings.update') }}" class="inline ms-1">
                                    @csrf
                                    <input type="hidden" name="settings[pause_customer_emails]" value="0">
                                    <button type="submit"
                                            class="rounded-full bg-[#E53935] px-2 py-0.5 text-[9px] font-semibold text-white transition-colors hover:bg-[#C62828] whitespace-nowrap">
                                        Aktifkan Kembali
                                    </button>
                                </form>
                            @endif
                        </div>
                    @endif

                    {{-- Bell --}}
                    @if (\Illuminate\Support\Facades\Route::has('office.notifications.read-all'))
                        <details class="relative shrink-0" id="admin-bell">
                            <summary class="flex cursor-pointer list-none items-center rounded-lg p-2 text-ink/70 transition-colors hover:bg-paper hover:text-ink" aria-label="Notifikasi">
                                <i class="bi bi-bell" style="font-size:1.15rem;"></i>
                                @if (!empty($adminBellUnreadCount) && $adminBellUnreadCount > 0)
                                    <span class="absolute -right-0.5 -top-0.5 flex h-[18px] min-w-[18px] items-center justify-center rounded-full bg-[#E53935] px-1 text-[9px] font-semibold text-white">
                                        {{ $adminBellUnreadCount > 99 ? '99+' : $adminBellUnreadCount }}
                                    </span>
                                @endif
                            </summary>
                            <div class="absolute right-0 top-full z-30 mt-1 w-80 rounded-xl border border-rule bg-white shadow-lg">
                                <div class="flex items-center justify-between border-b border-rule px-4 py-2.5">
                                    <p class="font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">Notifikasi</p>
                                    <form method="POST" action="{{ route('office.notifications.read-all') }}">
                                        @csrf
                                        <button class="text-xs text-accent hover:underline">Tandai semua dibaca</button>
                                    </form>
                                </div>
                                <ul class="max-h-72 overflow-y-auto">
                                    @forelse ($adminBellNotifications ?? [] as $notif)
                                        <li class="border-b border-rule/50 px-4 py-2.5 text-sm transition-colors hover:bg-paper/40 {{ ($notif->is_read ?? false) ? 'opacity-60' : '' }}">
                                            <p class="{{ ($notif->is_read ?? false) ? '' : 'font-semibold' }} text-ink">
                                                {{ $notif->title ?? $notif->message ?? '-' }}
                                            </p>
                                            <p class="mt-0.5 font-mono text-[10px] uppercase tracking-[0.1em] text-ink/45">
                                                {{ isset($notif->created_at) ? $notif->created_at->diffForHumans() : '' }}
                                            </p>
                                        </li>
                                    @empty
                                        <li class="px-4 py-6 text-center text-sm text-ink/45">Belum ada notifikasi.</li>
                                    @endforelse
                                </ul>
                            </div>
                        </details>
                    @endif

                    {{-- Frontend --}}
                    <a href="/" class="shrink-0 rounded-lg p-2 text-ink/70 transition-colors hover:bg-paper hover:text-ink" title="Lihat Frontend">
                        <i class="bi bi-globe2" style="font-size:1.15rem;"></i>
                    </a>

                    {{-- Divider --}}
                    <div class="mx-1 h-6 w-px bg-rule"></div>

                                        {{-- User --}}
                    <div class="flex items-center gap-2.5">
                        {{-- Avatar --}}
                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-[#1E1E2D]">
                            <span class="text-xs font-bold text-white">{{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}</span>
                        </div>

                        {{-- NAMA — SELALU tampil, warna inline --}}
                        <div class="leading-tight">
                            <p class="max-w-[130px] truncate text-[13px] font-semibold" style="color:#1E1E2D;">
                                {{ auth()->user()->name ?? 'Admin' }}
                            </p>
                            <p class="text-[10px]" style="color:#999;">Admin</p>
                        </div>

                        {{-- Logout --}}
                        <form method="POST" action="{{ route('logout') }}" class="shrink-0">
                            @csrf
                            <button type="submit"
                                    class="flex h-8 w-8 items-center justify-center rounded-lg transition-colors hover:bg-red-50"
                                    style="color:#999;"
                                    onmouseover="this.style.color='#E53935'"
                                    onmouseout="this.style.color='#999'"
                                    title="Keluar">
                                <i class="bi bi-box-arrow-right" style="font-size:1.15rem;"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            {{-- ═══ MAIN CONTENT ═══ --}}
            <main class="flex-1 p-4 lg:p-6">
                @if (session('status'))
                    <div class="mb-4 flex items-center gap-2 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                        <i class="bi bi-check-circle" style="font-size:1rem;"></i>
                        {{ session('status') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-4 flex items-center gap-2 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                        <i class="bi bi-exclamation-circle" style="font-size:1rem;"></i>
                        {{ session('error') }}
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    {{-- ═══ SCRIPTS ═══ --}}
    <script>
        // Mobile drawer
        function toggleSidebar() {
            document.getElementById('sidebar-desktop').classList.toggle('open');
            document.getElementById('sidebar-overlay').classList.toggle('active');
        }

        // ★ Desktop collapse — fungsi BARU yang terpisah
        function toggleSidebarDesktop() {
            var el = document.getElementById('sidebar-desktop');
            if (!el) return;
            el.classList.toggle('collapsed');
            try { localStorage.setItem('sidebar-collapsed', el.classList.contains('collapsed') ? '1' : '0'); } catch(e) {}
        }

        // Restore state
        (function () {
            try {
                if (localStorage.getItem('sidebar-collapsed') === '1') {
                    document.getElementById('sidebar-desktop').classList.add('collapsed');
                }
            } catch(e) {}
        })();
    </script>

    {{-- Search JS --}}
    @if (\Illuminate\Support\Facades\Route::has('office.search'))
    <script>
        (function () {
            var input = document.getElementById('admin-search-input');
            var box = document.getElementById('admin-search-results');
            if (!input || !box) return;
            var t;
            input.addEventListener('input', function () {
                clearTimeout(t);
                var q = input.value.trim();
                if (q.length < 2) { box.classList.add('hidden'); box.innerHTML = ''; return; }
                t = setTimeout(function () {
                    fetch('{{ route("office.search") }}?q=' + encodeURIComponent(q), { headers: { 'Accept': 'application/json' } })
                        .then(function (r) { return r.json(); })
                        .then(function (d) {
                            var html = '';
                            function sec(label, items, render) {
                                if (!items || !items.length) return;
                                html += '<p class="px-3 py-1.5 font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">' + label + '</p>';
                                items.forEach(function (x) { html += render(x); });
                            }
                            sec('Produk', d.products, function (x) {
                                return '<a href="/office/products/' + x.id + '/edit" class="block px-3 py-1.5 text-sm text-ink hover:bg-paper">' + (x.name || '') + '</a>';
                            });
                            sec('Orders', d.orders, function (x) {
                                return '<a href="/office/orders/' + (x.no_order || x.id) + '" class="block px-3 py-1.5 text-sm text-ink hover:bg-paper">' + (x.no_order || '') + '</a>';
                            });
                            sec('Users', d.users, function (x) {
                                return '<a href="/office/users/' + x.id + '/edit" class="block px-3 py-1.5 text-sm text-ink hover:bg-paper">' + (x.name || x.email || '') + '</a>';
                            });
                            if (!html) html = '<p class="px-3 py-4 text-center text-sm text-ink/45">Tidak ada hasil.</p>';
                            box.innerHTML = html;
                            box.classList.remove('hidden');
                        })
                        .catch(function () { box.classList.add('hidden'); });
                }, 250);
            });
            document.addEventListener('click', function (e) {
                if (!e.target.closest('#admin-search')) box.classList.add('hidden');
            });
        })();
    </script>
    @endif
</body>
</html>
