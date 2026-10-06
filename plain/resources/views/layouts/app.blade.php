<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ isset($title) ? $title . ' - ' : '' }}{{ $metaTitle ?? 'MERCATORIA' }}</title>
    <meta name="description" content="{{ $metaDescription ?? '' }}">
    @if (! empty($metaKeywords))
        <meta name="keywords" content="{{ $metaKeywords }}">
    @endif
    <link rel="canonical" href="{{ url()->current() }}">

    <meta property="og:type" content="{{ $ogType ?? 'website' }}">
    <meta property="og:title" content="{{ isset($title) ? $title . ' - ' : '' }}{{ $metaTitle ?? 'MERCATORIA' }}">
    <meta property="og:description" content="{{ $metaDescription ?? '' }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:site_name" content="MERCATORIA">
    @if (! empty($metaImage))
        <meta property="og:image" content="{{ $metaImage }}">
    @elseif ($hasLogo ?? false)
        <meta property="og:image" content="{{ asset('images/logo.png') }}">
    @endif

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ isset($title) ? $title . ' - ' : '' }}{{ $metaTitle ?? 'MERCATORIA' }}">
    <meta name="twitter:description" content="{{ $metaDescription ?? '' }}">
    @if (! empty($metaImage))
        <meta name="twitter:image" content="{{ $metaImage }}">
    @endif

    @if (file_exists(public_path('favicon.ico')))
        <link rel="icon" href="{{ asset('favicon.ico') }}">
    @endif

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&family=Roboto:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v=36">
    @stack('head')
</head>
<body>
    <a class="skip-link" href="#main">Lewati ke konten</a>

   {{-- ===================== PROMO BAR ===================== --}}
    @if (! empty($promoBarText))
        @php
            // Escape dulu (teks datang dari pengaturan admin), baru beri format tebal/miring.
            // Kata kunci di bawah tidak mengandung karakter khusus HTML, jadi aman di-replace setelah escape.
            $formattedPromoText = str_replace('NEWWEB', '<strong>NEWWEB</strong>', e($promoBarText));
            $formattedPromoText = str_replace('Rp 25.000', '<em>Rp 25.000</em>', $formattedPromoText);
            $formattedPromoText = str_replace('Rp 150.000', '<em>Rp 150.000</em>', $formattedPromoText);
        @endphp
    
        <div class="promo-bar" style="background-color: #D3D3D3; color: #333333; border-bottom: 1px solid #fecaca; padding: 14px 24px; text-align: center; font-size: 13.5px; font-weight: 500;">
            {!! $formattedPromoText !!}
        </div>
    @endif

    {{-- ===================== HEADER ===================== --}}
    <header class="site-header">
        <div class="site-header__inner">

            {{-- KIRI: Menu --}}
            <nav class="site-nav" aria-label="Menu utama">
                <a href="{{ route('home') }}" class="site-nav__link {{ request()->routeIs('home') ? 'is-active' : '' }}">Beranda</a>

                @if ($navGames->isNotEmpty())
                    <div class="nav-dropdown">
                        <button type="button" class="site-nav__link nav-dropdown__trigger">Game <span class="nav-dropdown__caret">▾</span></button>
                        <div class="nav-dropdown__menu">
                            @foreach ($navGames as $game)
                                <a href="{{ route('catalog.index', ['game' => $game->slug]) }}" class="nav-dropdown__item">{{ $game->name }}</a>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if ($navDevelopers->isNotEmpty())
                    <div class="nav-dropdown">
                        <button type="button" class="site-nav__link nav-dropdown__trigger">Developer <span class="nav-dropdown__caret">▾</span></button>
                        <div class="nav-dropdown__menu">
                            @foreach ($navDevelopers as $developer)
                                <a href="{{ route('catalog.index', ['developer' => $developer->slug]) }}" class="nav-dropdown__item">{{ $developer->name }}</a>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- "Pre-order Baru" = menu utama header (global, tanpa login).
                     Dulu dibungkus cek "ada PO aktif", tapi kalau belum ada halaman
                     PO yang dibuat admin maka menu hilang total dan terlihat seperti
                     bug. Sekarang selalu tampil; halaman /pre-order-baru menampilkan
                     empty-state rapi bila memang belum ada PO. --}}
                @if (Route::has('preorder.show'))
                    <a href="{{ route('preorder.show') }}" class="site-nav__link {{ request()->routeIs('preorder.show') ? 'is-active' : '' }}">Pre-order Baru</a>
                @endif

                {{-- UX: 'Pesanan Saya' dipindah dari navbar kiri ke ikon Akun (kanan).
                     Navbar kiri kini fokus navigasi katalog; semua hal personal
                     (pesanan, tagihan, koin, profil) hidup di bawah satu pintu "Akun".
                     CTA Tagihan Menunggu ikut pindah ke dashboard akun + badge lonceng. --}}
            </nav>

            {{-- TENGAH: Logo --}}
            <a href="{{ route('home') }}" class="site-logo">
                @if ($hasLogo ?? false)
                    <img src="{{ asset('images/logo.png') }}" alt="MERCATORIA">
                @else
                    <span class="site-logo__mark">M</span>
                    <span class="site-logo__block">
                        <span class="site-logo__text">MERCATORIA</span>
                    </span>
                @endif
            </a>

            {{-- KANAN: Aksi --}}
            <div class="header-actions">
                @if (Route::has('search.index'))
                    <a href="{{ route('search.index') }}" class="header-icon" aria-label="Cari" title="Cari">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"></circle>
                            <path d="m21 21-4.3-4.3"></path>
                        </svg>
                    </a>
                @endif

                {{-- SOSIAL MEDIA DINAMIS (tabel social_media, kelola di Admin > Pengaturan Umum).
                     BUG FIX: guard lama `@if (! empty($sm->url))` membuat baris aktif yang
                     URL-nya kosong / belum terisi ikut tersembunyi. Sekarang ikon selalu
                     dirender selama punya url ATAU icon_url ATAU icon_key bawaan; kalau
                     tidak ada URL-nya, dirend sebagai span non-link agar tetap terlihat. --}}
                @foreach (($socialMedias ?? collect()) as $sm)
                    @php($smHref = trim((string) ($sm->url ?? '')))
                    @if ($smHref !== '')
                        <a href="{{ $smHref }}" target="_blank" rel="noopener" class="header-icon" aria-label="{{ $sm->name }}" title="{{ $sm->name }}">
                            @include('partials.social-icon', ['social' => $sm])
                        </a>
                    @elseif (trim((string) ($sm->icon_url ?? '')) !== '' || ! empty($sm->icon_key))
                        <span class="header-icon" aria-label="{{ $sm->name }}" title="{{ $sm->name }}">
                            @include('partials.social-icon', ['social' => $sm])
                        </span>
                    @endif
                @endforeach

                @auth
                    {{-- Total harga cart --}}
                    @if (($cartTotal ?? 0) > 0)
                        <span class="header-cart-total">{{ \App\Support\PriceCalculator::formatRupiah($cartTotal) }}</span>
                    @endif

                    <a href="{{ route('cart.index') }}" class="header-icon header-icon--cart" aria-label="Keranjang" title="Keranjang">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="9" cy="21" r="1"></circle>
                            <circle cx="20" cy="21" r="1"></circle>
                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                        </svg>
                        @if (($cartCount ?? 0) > 0)
                            <span class="cart-badge">{{ $cartCount > 99 ? '99+' : $cartCount }}</span>
                        @endif
                    </a>

                    {{-- Badge tagihan menunggu + badge unread notif dihitung di
                         View Composer AppServiceProvider (layouts.app) dan dikirim
                         sebagai headerUnpaidCount / unreadNotifHeader. Fallback nol
                         memakai null-coalescing di setiap pemakaian. Jangan pernah
                         menaruh blok PHP inline atau menulis directive persis seperti
                         hasil compile di dalam komentar — parser tetap mendeteksinya
                         meski di dalam komentar, dan isinya bisa bocor mentah ke
                         halaman ketika compiled view basi. --}}
                    <a href="{{ route('notifications.index') }}" class="header-icon" aria-label="Notifikasi" title="Notifikasi">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                                <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                            </svg>
                            @if (($unreadNotifHeader ?? 0) > 0)
                                <span class="cart-badge cart-badge--notif">{{ ($unreadNotifHeader ?? 0) > 99 ? '99+' : ($unreadNotifHeader ?? 0) }}</span>
                            @endif
                        </a>
                    <div class="nav-dropdown header-account">
                        <button type="button" class="header-icon" aria-label="Akun" title="Akun">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                            @if (($headerUnpaidCount ?? 0) > 0)
                                <span class="cart-badge">{{ ($headerUnpaidCount ?? 0) > 99 ? '99+' : ($headerUnpaidCount ?? 0) }}</span>
                            @endif
                        </button>
                        <div class="nav-dropdown__menu nav-dropdown__menu--right">
                            <a href="{{ route('account.show') }}" class="nav-dropdown__item">Dashboard Saya</a>
                            <a href="{{ route('account.orders.index') }}" class="nav-dropdown__item">
                                Pesanan Saya
                                @if (($headerUnpaidCount ?? 0) > 0)
                                    <span class="badge badge--danger small" style="margin-left:6px;">{{ $headerUnpaidCount ?? 0 }} tagihan</span>
                                @endif
                            </a>
                            <a href="{{ route('account.coins.index') }}" class="nav-dropdown__item">Koin Saya</a>
                            {{-- Permintaan user: menu referral/undang belum ada di dropdown profil. --}}
                            @if (Route::has('referral.index'))
                                <a href="{{ route('referral.index') }}" class="nav-dropdown__item">Undang Teman</a>
                            @endif
                            {{-- Permintaan user: item "Notifikasi" dihapus dari dropdown
                                 profil karena ikon lonceng + badge sudah berdiri sendiri
                                 di header (hindari duplikasi akses). --}}
                            <a href="{{ route('account.profile.edit') }}" class="nav-dropdown__item">Edit Profil</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="nav-dropdown__item" style="width:100%;text-align:left;background:none;border:0;cursor:pointer;font:inherit;color:#c0392b;">Keluar</button>
                            </form>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="header-icon" aria-label="Masuk" title="Masuk">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                    </a>
                @endauth
            </div>
        </div>
    </header>

    {{-- Warning email belum diverifikasi --}}
    @auth
        @if (method_exists(auth()->user(), 'hasVerifiedEmail') && ! auth()->user()->hasVerifiedEmail() && Route::has('verification.notice'))
            <div class="verify-banner">
                <div class="container" style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
                    <span>⚠️ Email kamu belum diverifikasi. Verifikasi dulu supaya bisa checkout.</span>
                    <a href="{{ route('verification.notice') }}" style="margin-left:auto;font-weight:600;color:#fff;text-decoration:underline;">Verifikasi Sekarang →</a>
                </div>
            </div>
        @endif
    @endauth

    {{-- GATEKEEPER notices (IP blacklist / guest barrier / data integrity) --}}
    @if (session('gatekeeper_error'))
        <div class="verify-banner" style="background:#c0392b;" role="alert">
            <div class="container">{{ session('gatekeeper_error') }}</div>
        </div>
    @endif
    @if (session('gatekeeper_notice'))
        <div class="verify-banner" style="background:#e67e22;" role="status">
            <div class="container">{{ session('gatekeeper_notice') }}</div>
        </div>
    @endif

    <main id="main" class="@yield('main_class', 'main--default')">
    @yield('content')
    </main>

    {{-- ===================== FOOTER ===================== --}}
    <footer class="site-footer">
        <div class="container">
            {{-- Grid Utama: 4 Kolom --}}
            <div class="site-footer__grid footer-grid">
                {{-- Kolom 1: Brand + Social + Marketplace --}}
            <div class="footer-col footer-col--brand">
                <a href="{{ route('home') }}" class="footer-brand">
                    @if ($hasLogo ?? false)
                        <img src="{{ asset('images/logo.png') }}" alt="MERCATORIA">
                    @else
                        <span class="footer-brand__mark">M</span>
                        <span class="footer-brand__block">
                            <span class="footer-brand__text">MERCATORIA</span>
                            <span class="footer-brand__tagline">MERCH FOR BETTER FUTURE</span>
                        </span>
                    @endif
                </a>

                {{-- Social Media Icons — DINAMIS dari tabel social_media (Admin > Pengaturan Umum).
                     Guard !empty(url) lama dibuang: ikon aktif selalu dirender walau URL kosong. --}}
                <div class="footer-social">
                    @foreach (($socialMedias ?? collect()) as $sm)
                        @php($smHref = trim((string) ($sm->url ?? '')))
                        @if ($smHref !== '')
                            <a href="{{ $smHref }}" target="_blank" rel="noopener" aria-label="{{ $sm->name }}">
                                @include('partials.social-icon', ['social' => $sm])
                            </a>
                        @elseif (trim((string) ($sm->icon_url ?? '')) !== '' || ! empty($sm->icon_key))
                            <span aria-label="{{ $sm->name }}">
                                @include('partials.social-icon', ['social' => $sm])
                            </span>
                        @endif
                    @endforeach
                </div>

                {{-- Marketplace Buttons (TOCO & SHOPEE) --}}
                <div class="footer-links footer-marketplace">
                    @if (! empty($footerTocoUrl))
                        <a href="{{ $footerTocoUrl }}" target="_blank" rel="noopener" class="footer-btn">TOCO <span>→</span></a>
                    @endif
                    @if (! empty($footerShopeeUrl))
                        <a href="{{ $footerShopeeUrl }}" target="_blank" rel="noopener" class="footer-btn">SHOPEE <span>→</span></a>
                    @endif
                </div>
            </div>

            {{-- Kolom 2: Game --}}
            <div class="footer-col">
                <h4 class="footer-col__title">Game</h4>
                <ul class="footer-col__list">
                    @foreach ($navGames as $game)
                        <li><a href="{{ route('catalog.index', ['game' => $game->slug]) }}">{{ $game->name }}</a></li>
                    @endforeach
                </ul>
            </div>

            {{-- Kolom 3: Developer + Jelajahi (satu kolom, sesuai desain user) --}}
            <div class="footer-col">
                <h4 class="footer-col__title">Developer</h4>
                <ul class="footer-col__list">
                    @foreach ($navDevelopers as $developer)
                        <li><a href="{{ route('catalog.index', ['developer' => $developer->slug]) }}">{{ $developer->name }}</a></li>
                    @endforeach
                </ul>

                {{-- Jelajahi (di dalam kolom 3) --}}
                <h4 class="footer-jelajahi__title">Jelajahi</h4>
                <ul class="footer-col__list footer-jelajahi__links">
                    {{-- BUG FIX (500 "unexpected end of file, expecting elseif/else/endif"):
                         loop ini dulu ditulis sebagai blok foreach + empty terpisah (dua direktif
                         yang tidak seimbang), sehingga Blade meng-compile pembuka tanpa penutup
                         -> syntax error saat layout dirender SEMUA halaman publik.
                         Diperbaiki jadi satu blok forelse/empty/endforelse utuh. --}}
                    @forelse (($footerPages ?? collect()) as $fpage)
                        <li><a href="{{ route('slug.show', $fpage->slug) }}">{{ $fpage->title }}</a></li>
                    @empty
                        <li><a href="{{ route('reseller.create') }}">Reseller</a></li>
                        <li><a href="{{ route('legal.show', 'faq') }}">Tanya Jawab Umum</a></li>
                        <li><a href="{{ route('legal.show', 'kebijakan-privasi') }}">Kebijakan Privasi</a></li>
                        <li><a href="{{ route('legal.show', 'syarat-dan-ketentuan') }}">Syarat &amp; Ketentuan</a></li>
                    @endforelse
                </ul>
            </div>

            {{-- Kolom 4: Produk Terpopuler --}}
            <div class="footer-col footer-products-col">
                <h4 class="footer-col__title">Produk Terpopuler</h4>
                <div class="footer-produk">
                    @if (isset($footerProducts) && $footerProducts->isNotEmpty())
                        @foreach ($footerProducts->take(4) as $fp)
                            {{-- BUG FIX (500): sellingPrice() bisa melempar TypeError saat
                                 price_yuan null, dan relasi variants/images bisa belum
                                 ter-load di jalur render tertentu. Dibungkus data_get +
                                 filter is_numeric supaya footer tidak pernah menjatuhkan
                                 halaman manapun. --}}
                            @php($fpPrices = collect(data_get($fp, 'variants', []))->map(fn ($v) => is_numeric($v->price_yuan ?? null) ? $v->sellingPrice($calculator ?? \App\Support\PriceCalculator::fromSettings()) : null)->filter(fn ($p) => is_numeric($p)))
                            <a href="{{ route('slug.show', $fp->slug) }}" class="footer-produk__item">
                                <div class="footer-produk__thumb">
                                    @if (count(data_get($fp, 'images', [])) > 0)
                                        <img src="{{ $fp->images->first()->url() }}" alt="{{ $fp->name }}">
                                    @else
                                        <div class="footer-produk__placeholder">No Image</div>
                                    @endif
                                </div>
                                <div class="footer-produk__info">
                                    <span class="footer-produk__name">{{ $fp->name }}</span>
                                    @if ($fpPrices->isNotEmpty())
                                        <span class="footer-produk__price">
                                            {{ \App\Support\PriceCalculator::formatRupiah($fpPrices->min()) }}
                                        </span>
                                    @endif
                                </div>
                            </a>
                        @endforeach
                    @endif
                </div>
            </div>
            </div>
        </div>

        {{-- Bottom Bar --}}
        <div class="container site-footer__bottom">
            <span class="copyright">{{ $footerCopyright ?: '© ' . date('Y') . ' MERCATORIA' }}</span>
            <span class="powered-by">{{ $footerPoweredBy ?? 'Powered by MERCATORIA' }}</span>
        </div>
    </footer>

    {{-- WA Widget --}}
    @if (View::exists('partials.wa-widget'))
        @include('partials.wa-widget')
    @endif

    {{-- Back to top --}}
    <button type="button" class="back-to-top" aria-label="Kembali ke atas" data-back-to-top>↑</button>

    <script>
        (function () {
            var btn = document.querySelector('[data-back-to-top]');
            if (!btn) return;
            window.addEventListener('scroll', function () {
                btn.classList.toggle('is-visible', window.scrollY > 400);
            });
            btn.addEventListener('click', function () {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        })();
    </script>

    @stack('scripts')
    {{-- BUG FIX: modal popup pengganti window.alert() browser. --}}
    @include('partials.modal-alert')
</body>
</html>
