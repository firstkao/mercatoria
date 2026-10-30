@extends('layouts.app')

@section('title', 'Semua Notifikasi — Mercatoria')

@section('content')
    <p class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink/45">Akun</p>
    <h1 class="mt-2 font-display text-[1.75rem] leading-tight font-semibold text-ink sm:text-3xl">Semua Notifikasi</h1>

    <div class="mt-5 flex flex-wrap gap-1 border-b border-rule">
        <a href="{{ route('dashboard') }}" class="border-b-2 border-transparent px-4 py-2.5 text-sm text-ink/55 transition-colors hover:text-ink">Pesanan Saya</a>
        <a href="{{ route('profile.notifications') }}" class="border-b-2 border-ink px-4 py-2.5 text-sm font-semibold text-ink">Notifikasi</a>
        <a href="{{ route('profile.coins') }}" class="border-b-2 border-transparent px-4 py-2.5 text-sm text-ink/55 transition-colors hover:text-ink">Koin</a>
        <a href="{{ route('profile.wallet') }}" class="border-b-2 border-transparent px-4 py-2.5 text-sm text-ink/55 transition-colors hover:text-ink">Voucher</a>
        <a href="{{ route('profile.referral') }}" class="border-b-2 border-transparent px-4 py-2.5 text-sm text-ink/55 transition-colors hover:text-ink">Undang Teman</a>
        <a href="{{ route('profile.edit') }}" class="border-b-2 border-transparent px-4 py-2.5 text-sm text-ink/55 transition-colors hover:text-ink">Edit Profil</a>
    </div>

    {{-- Filter Tab (DS 11.1) --}}
    <div class="mt-5 flex flex-wrap gap-1 border-b border-rule" role="tablist">
        <button type="button" data-notif-filter="all" class="notif-tab border-b-2 border-ink px-4 py-2 text-sm font-semibold text-ink">Semua</button>
        <button type="button" data-notif-filter="unread" class="notif-tab border-b-2 border-transparent px-4 py-2 text-sm text-ink/55 transition-colors hover:text-ink">Belum Dibaca</button>
        <button type="button" data-notif-filter="order" class="notif-tab border-b-2 border-transparent px-4 py-2 text-sm text-ink/55 transition-colors hover:text-ink">Order</button>
        <button type="button" data-notif-filter="koin" class="notif-tab border-b-2 border-transparent px-4 py-2 text-sm text-ink/55 transition-colors hover:text-ink">Koin</button>
        <button type="button" data-notif-filter="voucher" class="notif-tab border-b-2 border-transparent px-4 py-2 text-sm text-ink/55 transition-colors hover:text-ink">Voucher</button>
        <button type="button" data-notif-filter="state" class="notif-tab border-b-2 border-transparent px-4 py-2 text-sm text-ink/55 transition-colors hover:text-ink">State</button>
        <button type="button" data-notif-filter="sistem" class="notif-tab border-b-2 border-transparent px-4 py-2 text-sm text-ink/55 transition-colors hover:text-ink">Sistem</button>
    </div>

    {{-- Search + Sort (DS 11.1) --}}
    <div class="mt-4 flex flex-wrap items-center gap-2">
        <div class="relative flex-1 min-w-[200px]">
            <input type="text" id="notif-search" placeholder="Cari notifikasi..."
                   class="w-full rounded-lg border border-rule bg-white px-3.5 py-2 pl-9 text-sm text-ink placeholder:text-ink/35 focus:border-ink focus:outline-none focus:ring-2 focus:ring-accent/45">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 text-ink/40" width="14" height="14" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/></svg>
        </div>
        <select id="notif-sort" class="rounded-lg border border-rule bg-white px-3 py-2 text-sm text-ink">
            <option value="newest">Terbaru</option>
            <option value="oldest">Terlama</option>
        </select>
    </div>

    {{-- Bulk Action (DS 11.1) --}}
    <div class="mt-3 flex flex-wrap items-center gap-2 text-sm">
        <label class="flex items-center gap-1.5">
            <input type="checkbox" id="notif-select-all" class="rounded border-rule"> Pilih Semua
        </label>
        <button type="button" class="rounded-lg border border-rule px-3 py-1.5 text-xs font-semibold text-ink hover:border-ink">Tandai Dibaca</button>
        <button type="button" class="rounded-lg border border-rule px-3 py-1.5 text-xs font-semibold text-ink hover:border-ink">Tandai Belum</button>
        <button type="button" class="rounded-lg border border-danger/40 px-3 py-1.5 text-xs font-semibold text-danger hover:border-danger">Hapus</button>
    </div>

    <div class="mt-4 space-y-2">
        @forelse ($notifications as $notification)
            <div class="flex items-start gap-3 rounded-xl border border-rule bg-white px-4 py-3 transition-colors hover:border-ink {{ $notification->is_read ? 'opacity-60' : '' }}" data-notif-item data-type="{{ $notification->type ?? 'sistem' }}" data-read="{{ $notification->is_read ? '1' : '0' }}">
                <input type="checkbox" class="mt-1 rounded border-rule">
                <div class="flex-1 min-w-0">
                    <p class="text-sm text-ink {{ $notification->is_read ? '' : 'font-semibold' }}">{{ $notification->title }}</p>
                    <p class="mt-0.5 font-mono text-[10px] uppercase tracking-[0.12em] text-ink/45">{{ $notification->created_at->format('d/m/Y H:i') }}</p>
                </div>
            </div>
        @empty
            <div class="rounded-xl border border-rule bg-white p-8 text-center text-sm text-ink/45">
                Belum ada notifikasi.
            </div>
        @endforelse
    </div>

    <div class="mt-4">{{ $notifications->links() }}</div>

    <script>
        (function () {
            var tabs = document.querySelectorAll('.notif-tab');
            var items = document.querySelectorAll('[data-notif-item]');
            tabs.forEach(function (tab) {
                tab.addEventListener('click', function () {
                    tabs.forEach(function (t) {
                        t.classList.remove('border-ink', 'font-semibold', 'text-ink');
                        t.classList.add('border-transparent', 'text-ink/55');
                    });
                    tab.classList.remove('border-transparent', 'text-ink/55');
                    tab.classList.add('border-ink', 'font-semibold', 'text-ink');
                    var filter = tab.getAttribute('data-notif-filter');
                    items.forEach(function (item) {
                        if (filter === 'all') { item.style.display = ''; return; }
                        if (filter === 'unread') {
                            item.style.display = item.getAttribute('data-read') === '0' ? '' : 'none';
                            return;
                        }
                        item.style.display = item.getAttribute('data-type') === filter ? '' : 'none';
                    });
                });
            });
        })();
    </script>
@endsection
