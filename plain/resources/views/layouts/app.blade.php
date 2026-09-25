<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title) ? $title.' - ' : '' }}MERCATORIA</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Roboto:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v=2">
    @stack('head')
</head>
<body>
    <a class="skip-link" href="#main">Lewati ke konten</a>

    @if ($promoBarText)
        <div class="promo-bar">{{ $promoBarText }}</div>
    @endif

    <header class="site-header">
        <div class="container site-header__inner">
            <a href="{{ route('home') }}" class="site-logo">
                @if ($hasLogo)
                    <img src="{{ asset('images/logo.png') }}" alt="MERCATORIA" width="160" height="49">
                @else
                    MERCATORIA
                @endif
            </a>
            <nav class="site-nav" aria-label="Akun">
                @auth
                    <a href="{{ route('catalog.index') }}">Katalog</a>
                    <a href="{{ route('account.show') }}">Akun saya</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="link-button">Keluar</button>
                    </form>
                @else
                    <a href="{{ route('login') }}">Masuk</a>
                    <a href="{{ route('register') }}" class="button button--small">Daftar</a>
                @endauth
            </nav>
        </div>
    </header>

    <main id="main" class="container site-main">
        @yield('content')
    </main>

    <footer class="site-footer">
        <div class="container site-footer__inner">
            <nav aria-label="Informasi">
                <a href="{{ route('legal.show', 'faq') }}">Tanya Jawab Umum</a>
                <a href="{{ route('reseller.create') }}">Reseller</a>
                <a href="{{ route('legal.show', 'syarat-dan-ketentuan') }}">Syarat &amp; Ketentuan</a>
                <a href="{{ route('legal.show', 'kebijakan-privasi') }}">Kebijakan Privasi</a>
            </nav>
            <p>&copy; 2022 MERCATORIA</p>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>