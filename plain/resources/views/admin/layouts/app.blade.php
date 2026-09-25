<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $title ?? 'Dashboard' }} - Admin MERCATORIA</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}?v=4">
    @stack('head')
</head>
<body>
    @php
        $navigation = [
            ['label' => null, 'items' => [
                ['route' => 'admin.dashboard', 'active' => 'admin.dashboard', 'label' => 'Ringkasan', 'icon' => 'home'],
            ]],
            ['label' => 'Penjualan', 'items' => [
                ['route' => 'admin.payments.index', 'active' => 'admin.payments.*', 'label' => 'Pembayaran', 'icon' => 'wallet'],
            ]],
            ['label' => 'Katalog', 'items' => [
                ['route' => 'admin.products.index', 'active' => 'admin.products.*', 'label' => 'Produk', 'icon' => 'box'],
            ]],
            ['label' => 'Pelanggan', 'items' => [
                ['route' => 'admin.users.index', 'active' => 'admin.users.*', 'label' => 'Pengguna', 'icon' => 'users'],
                ['route' => 'admin.identities.index', 'active' => ['admin.identities.*', 'admin.names.*'], 'label' => 'Blokir & banding', 'icon' => 'shield'],
                ['route' => 'admin.logs.index', 'active' => 'admin.logs.*', 'label' => 'Log aktivitas', 'icon' => 'list'],
            ]],
            ['label' => 'Konten', 'items' => [
                ['route' => 'admin.pages.index', 'active' => 'admin.pages.*', 'label' => 'Halaman', 'icon' => 'file'],
            ]],
            ['label' => 'Pengaturan', 'items' => [
                ['route' => 'admin.settings.pricing', 'active' => 'admin.settings.pricing', 'label' => 'Harga & kurs', 'icon' => 'sliders'],
                ['route' => 'admin.settings.tiers', 'active' => 'admin.settings.tiers', 'label' => 'Tier ongkir', 'icon' => 'truck'],
                ['route' => 'admin.settings.marketplaces', 'active' => 'admin.settings.marketplaces', 'label' => 'Marketplace', 'icon' => 'store'],
                ['route' => 'admin.settings.display', 'active' => 'admin.settings.display', 'label' => 'Tampilan toko', 'icon' => 'eye'],
            ]],
        ];
        $tabs = [
            ['route' => 'admin.dashboard', 'active' => 'admin.dashboard', 'label' => 'Ringkasan', 'icon' => 'home'],
            ['route' => 'admin.payments.index', 'active' => 'admin.payments.*', 'label' => 'Pembayaran', 'icon' => 'wallet'],
            ['route' => 'admin.products.index', 'active' => 'admin.products.*', 'label' => 'Produk', 'icon' => 'box'],
            ['route' => 'admin.users.index', 'active' => 'admin.users.*', 'label' => 'Pengguna', 'icon' => 'users'],
        ];
    @endphp

    <div class="shell">
        <aside class="sidebar" aria-label="Menu admin">
            <a href="{{ route('admin.dashboard') }}" class="sidebar__brand">
                @if (file_exists(public_path('images/logo.png')))
                    <img src="{{ asset('images/logo.png') }}" alt="MERCATORIA">
                @else
                    MERCATORIA
                @endif
                <span>Admin</span>
            </a>
            <nav class="sidebar__nav">
                @include('admin.partials.nav', ['navigation' => $navigation])
            </nav>
            <div class="sidebar__footer">
                <span class="sidebar__user">{{ auth('admin')->user()->name }}</span>
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="link">Keluar</button>
                </form>
            </div>
        </aside>

        <div class="main">
            <header class="topbar">
                <div class="topbar__title">
                    @isset($back)
                        <a href="{{ $back }}" class="topbar__back" aria-label="Kembali">@include('admin.partials.icon', ['name' => 'back'])</a>
                    @endisset
                    <h1>{{ $title ?? 'Dashboard' }}</h1>
                </div>
                <div class="topbar__actions">@yield('actions')</div>
            </header>

            @hasSection('tabs')
                <nav class="subtabs" aria-label="Bagian">@yield('tabs')</nav>
            @endif

            @hasSection('filters')
                @php($filtersActive = collect(request()->except('page', 'tab'))->filter(fn ($value) => $value !== null && $value !== '')->isNotEmpty())
                <div @class(['filterbar', 'is-open' => $filtersActive])>
                    <button type="button" class="filterbar__toggle" aria-expanded="{{ $filtersActive ? 'true' : 'false' }}"
                            onclick="var bar = this.parentElement; bar.classList.toggle('is-open'); this.setAttribute('aria-expanded', bar.classList.contains('is-open'));">
                        @include('admin.partials.icon', ['name' => 'filter']) Filter
                    </button>
                    <div class="filterbar__body">@yield('filters')</div>
                </div>
            @endif

            <main class="content">
                @if (session('status'))
                    <div class="alert alert--success" role="status">{{ session('status') }}</div>
                @endif
                @if ($errors->any())
                    <div class="alert alert--danger" role="alert">
                        <strong>Ada {{ $errors->count() }} isian yang perlu diperbaiki.</strong>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <nav class="tabbar" aria-label="Menu admin">
        @foreach ($tabs as $item)
            <a href="{{ route($item['route']) }}" @class(['tabbar__link', 'is-active' => request()->routeIs($item['active'])])>
                @include('admin.partials.icon', ['name' => $item['icon']])
                <span>{{ $item['label'] }}</span>
            </a>
        @endforeach
        <button type="button" class="tabbar__link" aria-controls="mobile-menu" aria-expanded="false" data-menu-toggle>
            @include('admin.partials.icon', ['name' => 'menu'])
            <span>Menu</span>
        </button>
    </nav>

    <div class="sheet" id="mobile-menu" hidden data-menu>
        <div class="sheet__backdrop" data-menu-toggle></div>
        <div class="sheet__panel" role="dialog" aria-label="Semua menu">
            <div class="sheet__head">
                <strong>{{ auth('admin')->user()->name }}</strong>
                <button type="button" class="link" data-menu-toggle>Tutup</button>
            </div>
            <nav class="sheet__nav">
                @include('admin.partials.nav', ['navigation' => $navigation])
            </nav>
            <form method="POST" action="{{ route('admin.logout') }}" class="sheet__logout">
                @csrf
                <button type="submit" class="btn btn--block">@include('admin.partials.icon', ['name' => 'logout']) Keluar</button>
            </form>
        </div>
    </div>

    <script>
        document.querySelectorAll('[data-menu-toggle]').forEach(function (element) {
            element.addEventListener('click', function () {
                var sheet = document.querySelector('[data-menu]');
                sheet.hidden = !sheet.hidden;
                document.querySelector('.tabbar [data-menu-toggle]').setAttribute('aria-expanded', String(!sheet.hidden));
            });
        });
    </script>
    @stack('scripts')
</body>
</html>
