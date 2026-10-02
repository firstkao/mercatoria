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
    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v=18">
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

                {{-- Permintaan user: "Pre-order Baru" jadi menu utama di header (global,
                     bisa dilihat tanpa login), isinya halaman PO aktif. Editor admin
                     sudah semodel Elementor: blok teks + banner foto bebas diatur
                     urutan/posisi via form & drag sort_order. --}}
                @php
                    $hasPreorder = false;
                    try { $hasPreorder = (bool) \App\Models\PreorderPage::current(); } catch (\Throwable $e) {}
                @endphp
                @if ($hasPreorder && Route::has('preorder.show'))
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

                @if (! empty($socialInstagram))
                    <a href="{{ $socialInstagram }}" target="_blank" rel="noopener" class="header-icon" aria-label="Instagram" title="Instagram">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect>
                            <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path>
                            <line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line>
                        </svg>
                    </a>
                @endif

                @if (! empty($socialFacebook))
                    <a href="{{ $socialFacebook }}" target="_blank" rel="noopener" class="header-icon" aria-label="Facebook" title="Facebook">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M22 12a10 10 0 1 0-11.56 9.88v-6.99H7.9V12h2.54V9.8c0-2.5 1.49-3.89 3.77-3.89 1.09 0 2.24.2 2.24.2v2.46h-1.26c-1.24 0-1.63.77-1.63 1.56V12h2.78l-.45 2.89h-2.33v6.99A10 10 0 0 0 22 12z"/>
                        </svg>
                    </a>
                @endif

                @if (! empty($socialX))
                    <a href="{{ $socialX }}" target="_blank" rel="noopener" class="header-icon" aria-label="X" title="X">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                        </svg>
                    </a>
                @endif

                {{-- Permintaan user: Threads selalu tampil di header (link resmi).
                     Ukuran disamakan dgn sosmed lain (20px, bukan 16px yg bikin kecil sendiri). --}}
                <a href="https://www.threads.com/@mercatoria_id" target="_blank" rel="noopener" class="header-icon" aria-label="Threads" title="Threads">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 21c4.97 0 9-4.03 9-9s-4.03-9-9-9-9 4.03-9 9c0 3.41 1.9 6.37 4.7 7.9"/>
                        <path d="M12 16.5c-2.2 0-3.9-1.45-3.9-3.4S9.8 9.7 12 9.7c1.6 0 2.9.75 3.5 1.9"/>
                        <path d="M14.2 13.9c.35-.45.55-1.05.55-1.7 0-1.5-1.1-2.5-2.75-2.5"/>
                    </svg>
                </a>

                @if (! empty($contactWhatsapp))
                    @php($waNum = preg_replace('/\D/', '', $contactWhatsapp))
                    @if (str_starts_with($waNum, '0'))
                        @php($waNum = '62' . substr($waNum, 1))
                    @endif
                    <a href="https://wa.me/{{ $waNum }}" target="_blank" rel="noopener" class="header-icon" aria-label="WhatsApp" title="WhatsApp">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                        </svg>
                    </a>
                @endif

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

                    {{-- Badge tagihan menunggu (akun) dihitung di View Composer
                         AppServiceProvider untuk layouts.app dan dikirim sebagai
                         $headerUnpaidCount. Fallback nol memakai null-coalescing
                         di setiap pemakaian. JANGAN pernah menaruh blok PHP inline
                         atau menulis directive-nya persis seperti saat compile di
                         dalam komentar ini — parser tetap mendeteksinya bahkan di
                         dalam komentar, dan bisa bocor mentah ke halaman ketika
                         compiled view basi. --}}
                    {{-- Ikon lonceng notifikasi + badge angka unread.
                         Perhitungan dilakukan di View Composer layouts.app
                         (AppServiceProvider) lalu dikirim sebagai variabel
                         'unreadNotifHeader' + dishare global. JANGAN pernah
                         menghitungnya lewat blok PHP inline di sini: compiled
                         view basi akan melempar Undefined variable dan
                         merobohkan seluruh halaman (bug 500 sebelumnya).
                         Safety net ?? 0 di setiap pemakaian. --}}
                    <a href="{{ route('notifications.index') }}" class="header-icon" aria-label="Notifikasi" title="Notifikasi">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                                <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                            </svg>
                            @if (($unreadNotifHeader ?? 0) > 0)
                                <span class="cart-badge cart-badge--notif">{{ $unreadNotifHeader > 99 ? '99+' : $unreadNotifHeader }}</span>
                            @endif
                        </a>
                    <div class="nav-dropdown header-account">
                        <button type="button" class="header-icon" aria-label="Akun" title="Akun">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                            @if (($headerUnpaidCount ?? 0) > 0)
                                <span class="cart-badge">{{ $headerUnpaidCount > 99 ? '99+' : $headerUnpaidCount }}</span>
                            @endif
                        </button>
                        <div class="nav-dropdown__menu nav-dropdown__menu--right">
                            <a href="{{ route('account.show') }}" class="nav-dropdown__item">Dashboard Saya</a>
                            <a href="{{ route('account.orders.index') }}" class="nav-dropdown__item">
                                Pesanan Saya
                                @if (($headerUnpaidCount ?? 0) > 0)
                                    <span class="badge badge--danger small" style="margin-left:6px;">{{ $headerUnpaidCount }} tagihan</span>
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

                {{-- Social Media Icons --}}
                <div class="footer-social">
                    @if (! empty($socialInstagram))
                        <a href="{{ $socialInstagram }}" target="_blank" rel="noopener" aria-label="Instagram">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect>
                                <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path>
                            </svg>
                        </a>
                    @endif
                    @if (! empty($socialFacebook))
                        <a href="{{ $socialFacebook }}" target="_blank" rel="noopener" aria-label="Facebook">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M22 12a10 10 0 1 0-11.56 9.88v-6.99H7.9V12h2.54V9.8c0-2.5 1.49-3.89 3.77-3.89 1.09 0 2.24.2 2.24.2v2.46h-1.26c-1.24 0-1.63.77-1.63 1.56V12h2.78l-.45 2.89h-2.33v6.99A10 10 0 0 0 22 12z"/>
                            </svg>
                        </a>
                    @endif
                    @if (! empty($socialX))
                        <a href="{{ $socialX }}" target="_blank" rel="noopener" aria-label="X">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                            </svg>
                        </a>
                    @endif
                    {{-- Permintaan user: Threads selalu tampil di footer (link resmi). --}}
                    <a href="https://www.threads.com/@mercatoria_id" target="_blank" rel="noopener" aria-label="Threads">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M17.3 17.47c-.32.26-1.06.48-1.8.54-.9.07-1.81-.11-2.69-.5-1.7-.74-2.93-2.08-3.41-3.52l1.7-.54c.34 1.05 1.27 2.06 2.56 2.62.7.3 1.39.45 2.06.45.25 0 .5-.02.73-.07.48-.09.78-.25.9-.34.37-.3.37-.84.37-1.3v-.02c-.53.2-1.2.34-1.96.34-2.6 0-4.7-1.7-4.7-4.06 0-2.36 2.1-4.06 4.7-4.06 1.8 0 3.34.9 4.1 2.36l-1.5.8c-.47-.9-1.5-1.5-2.6-1.5-1.6 0-2.8.96-2.8 2.4s1.2 2.4 2.8 2.4c.7 0 1.36-.18 1.86-.5V11.9c0-1.2-.5-1.9-1.6-1.9h-.9v-1.6h.9c2.1 0 3.2 1.2 3.2 3.5v3.6c0 .6 0 1.2-.3 1.7-.2.4-.5.8-.9 1.1z"/>
                        </svg>
                    </a>
                    @if (! empty($contactWhatsapp))
                        @php($waNumFooter = preg_replace('/\D/', '', $contactWhatsapp))
                        @if (str_starts_with($waNumFooter, '0'))
                            @php($waNumFooter = '62' . substr($waNumFooter, 1))
                        @endif
                        <a href="https://wa.me/{{ $waNumFooter }}" target="_blank" rel="noopener" aria-label="WhatsApp">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                            </svg>
                        </a>
                    @endif
                </div>

                {{-- Marketplace Buttons (TOCO & SHOPEE) --}}
                <div class="footer-links footer-marketplace">
                    @if (! empty($footerTocoUrl))
                        <a href="{{ $footerTocoUrl }}" target="_blank" rel="noopener" class="footer-btn">TOCO <span>→</span></a>
                    @endif
                    @if (! empty($footerTokopediaUrl))
                        <a href="{{ $footerTokopediaUrl }}" target="_blank" rel="noopener" class="footer-btn">TOKOPEDIA <span>→</span></a>
                    @endif
                    @if (! empty($footerShopeeUrl))
                        <a href="{{ $footerShopeeUrl }}" target="_blank" rel="noopener" class="footer-btn">SHOPEE <span>→</span></a>
                    @endif
                    @if (! empty($footerTiktokShopUrl))
                        <a href="{{ $footerTiktokShopUrl }}" target="_blank" rel="noopener" class="footer-btn">TIKTOK SHOP <span>→</span></a>
                    @endif
                </div>
            </div>

            {{-- Kolom 2: Game --}}
            <div class="footer-col">
                <h4 class="footer-col__title">Game</h4>
                <ul class="footer-col__list">
                    @if ($navGames->isNotEmpty())
                        @foreach ($navGames as $game)
                            <li><a href="{{ route('catalog.index', ['game' => $game->slug]) }}">{{ $game->name }}</a></li>
                        @endforeach
                    @else
                        <li><a href="#">Aether Gazer</a></li>
                        <li><a href="#">Arknights</a></li>
                        <li><a href="#">Arknights: Endfield</a></li>
                        <li><a href="#">Azur Lane</a></li>
                        <li><a href="#">Beyond The World</a></li>
                        <li><a href="#">Duet Night Abyss</a></li>
                        <li><a href="#">Genshin Impact</a></li>
                        <li><a href="#">Honkai Impact 3</a></li>
                        <li><a href="#">Honkai: Star Rail</a></li>
                        <li><a href="#">Light and Night</a></li>
                    @endif
                </ul>
            </div>

            {{-- Kolom 3: Developer + Jelajahi (satu kolom, sesuai desain user) --}}
            <div class="footer-col">
                <h4 class="footer-col__title">Developer</h4>
                <ul class="footer-col__list">
                    @if ($navDevelopers->isNotEmpty())
                        @foreach ($navDevelopers as $developer)
                            <li><a href="{{ route('catalog.index', ['developer' => $developer->slug]) }}">{{ $developer->name }}</a></li>
                        @endforeach
                    @else
                        <li><a href="#">Aisno Games</a></li>
                        <li><a href="#">BluePoch</a></li>
                        <li><a href="#">HyperGryph</a></li>
                        <li><a href="#">Kuro Games</a></li>
                        <li><a href="#">Manjuu Game</a></li>
                        <li><a href="#">miHoYo</a></li>
                        <li><a href="#">NetEase Games</a></li>
                        <li><a href="#">Papergames</a></li>
                        <li><a href="#">Tencent Games</a></li>
                        <li><a href="#">Yongshi Technology</a></li>
                    @endif
                </ul>

                {{-- Jelajahi (di dalam kolom 3) --}}
                <h4 class="footer-jelajahi__title">Jelajahi</h4>
                <ul class="footer-col__list footer-jelajahi__links">
                    @forelse ($footerPages as $fpage)
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
                            @php($fpPrices = $fp->variants->map(fn ($v) => $v->sellingPrice($calculator ?? \App\Support\PriceCalculator::fromSettings()))->filter())
                            <a href="{{ route('products.show', $fp) }}" class="footer-produk__item">
                                <div class="footer-produk__thumb">
                                    @if ($fp->images->isNotEmpty())
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
                    @else
                        {{-- Fallback produk dummy selagi belum ada data --}}
                        <a href="#" class="footer-produk__item">
                            <div class="footer-produk__thumb"><div class="footer-produk__placeholder">IMG</div></div>
                            <div class="footer-produk__info">
                                <span class="footer-produk__name">[WHERE WINDS MEET] Chirp Wooden Assembly Toy</span>
                                <span class="footer-produk__price">Rp405.000</span>
                            </div>
                        </a>
                        <a href="#" class="footer-produk__item">
                            <div class="footer-produk__thumb"><div class="footer-produk__placeholder">IMG</div></div>
                            <div class="footer-produk__info">
                                <span class="footer-produk__name">[PATH TO NOWHERE] PTN X Happy Zoo Chibi Acrylic Ornament</span>
                                <span class="footer-produk__price">Rp220.000</span>
                            </div>
                        </a>
                        <a href="#" class="footer-produk__item">
                            <div class="footer-produk__thumb"><div class="footer-produk__placeholder">IMG</div></div>
                            <div class="footer-produk__info">
                                <span class="footer-produk__name">[GENSHIN IMPACT] PVC Figure 1/7 Yae Miko</span>
                                <span class="footer-produk__price">Rp3.985.000</span>
                            </div>
                        </a>
                        <a href="#" class="footer-produk__item">
                            <div class="footer-produk__thumb"><div class="footer-produk__placeholder">IMG</div></div>
                            <div class="footer-produk__info">
                                <span class="footer-produk__name">[WUTHERING WAVES] Porcelain Orchid Shadow Series Wall Painting</span>
                                <span class="footer-produk__price">Rp285.000</span>
                            </div>
                        </a>
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
</body>
</html>
