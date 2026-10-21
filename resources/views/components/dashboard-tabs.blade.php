@php
    $tabs = [
        ['Pesanan Saya', route('dashboard'), request()->routeIs('dashboard', 'orders.index')],
        ['Koin Saya', route('wallet.coins'), request()->routeIs('wallet.coins', 'wallet.index')],
        ['Undang Teman', route('referrals.invite'), request()->routeIs('referrals.invite', 'referrals.index')],
        ['Edit Profil', route('profile.edit'), request()->routeIs('profile.edit', 'profile.edit.update')],
    ];
@endphp
<nav aria-label='Dashboard pelanggan' class='mb-6 overflow-x-auto rounded-xl border border-border bg-white'>
    <div class='flex min-w-max'>
        @foreach ($tabs as [$label, $url, $active])
            <a href='{{ $url }}' @if ($active) aria-current='page' @endif class='border-b-2 px-4 py-3 text-sm font-semibold sm:px-6 {{ $active ? 'border-[#4FC3F7] text-navy-500' : 'border-transparent text-text-muted hover:bg-surface-2' }}'>{{ $label }}</a>
        @endforeach
    </div>
</nav>
