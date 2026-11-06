{{-- Tab navigasi akun. Simpan di: resources/views/frontend/partials/_account-nav.blade.php
     Pemakaian: @include('frontend.partials._account-nav', ['active' => 'coins']) --}}
@php
    $accountTabs = [
        'orders' => [route('dashboard'), 'Pesanan Saya'],
        'notifications' => [route('profile.notifications'), 'Notifikasi'],
        'coins' => [route('profile.coins'), 'Koin'],
        'wallet' => [route('profile.wallet'), 'Voucher'],
        'referral' => [route('profile.referral'), 'Undang Teman'],
        'edit' => [route('profile.edit'), 'Edit Profil'],
    ];
@endphp
<nav class="ac-tabs" aria-label="Menu akun">
    @foreach ($accountTabs as $key => [$url, $label])
        <a href="{{ $url }}" class="ac-tab {{ ($active ?? '') === $key ? 'is-on' : '' }}">{{ $label }}</a>
    @endforeach
</nav>
