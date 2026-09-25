@php
    $navigation = [
        ['label' => null, 'items' => [
            ['route' => 'admin.dashboard', 'active' => 'admin.dashboard', 'label' => 'Ringkasan', 'icon' => 'home'],
        ]],
        ['label' => 'Penjualan', 'items' => [
            ['route' => 'admin.payments.index', 'active' => 'admin.payments.*', 'label' => 'Pembayaran', 'icon' => 'list'],
        ]],
    ];
    $tabs = [
        ['route' => 'admin.dashboard', 'active' => 'admin.dashboard', 'label' => 'Ringkasan', 'icon' => 'home'],
        ['route' => 'admin.payments.index', 'active' => 'admin.payments.*', 'label' => 'Pembayaran', 'icon' => 'list'],
    ];
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $title ?? 'Dashboard' }} - Admin MERCATORIA</title>
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}?v=4">
    @stack('head')
</head>
<body>
    <div class="shell">
        <aside class="sidebar" aria-label="Menu admin">
            <a href="{{ route('admin.dashboard') }}" class="sidebar__brand">MERCATORIA <span>Admin</span></a>
            <nav class="sidebar__nav">@include('admin.partials.nav', ['navigation' => $navigation])</nav>
            <div class="sidebar__footer">
                <span class="sidebar__user">{{ auth('admin')->user()->name }}</span>
                <form method="POST" action="{{ route('admin.logout') }}">@csrf<button type="submit" class="link">Keluar</button></form>
            </div>
        </aside>
        <div class="main">
            <header class="topbar"><div class="topbar__title">@isset($back)<a href="{{ $back }}" class="topbar__back">Kembali</a>@endisset<h1>{{ $title ?? 'Dashboard' }}</h1></div><div class="topbar__actions">@yield('actions')</div></header>
            <main class="content">
                @if (session('status'))<div class="alert alert--success" role="status">{{ session('status') }}</div>@endif
                @if ($errors->any())<div class="alert alert--danger" role="alert">{{ $errors->first() }}</div>@endif
                @yield('content')
            </main>
        </div>
    </div>
    <nav class="tabbar" aria-label="Menu admin">
        @foreach ($tabs as $item)<a href="{{ route($item['route']) }}" @class(['tabbar__link', 'is-active' => request()->routeIs($item['active'])])>@include('admin.partials.icon', ['name' => $item['icon']])<span>{{ $item['label'] }}</span></a>@endforeach
    </nav>
    @stack('scripts')
</body>
</html>
