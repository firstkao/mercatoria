@extends('layouts.app')

@section('title', 'Semua Notifikasi — MERCATORIA')

@section('content')
    @include('frontend.partials._auth-style')

    <style>
        body { background: #f7f7f7; }

        .nt, .nt *, .nt *::before, .nt *::after { box-sizing: border-box; }
        .nt { font-family: 'Roboto', system-ui, sans-serif; color: #333; -webkit-font-smoothing: antialiased; }
        .nt a { text-decoration: none; }

        .nt-nav { display: flex; flex-wrap: wrap; gap: 2px; margin: 24px 0 0; border-bottom: 1px solid #e1e1e1; }
        .nt-nav a { display: block; padding: 10px 16px; font-size: 14px; line-height: 20px; color: #555; text-decoration: none; border-bottom: 2px solid transparent; margin-bottom: -1px; transition: color .15s; }
        .nt-nav a:hover { color: #000; }
        .nt-nav a.is-on { color: #000; font-weight: 700; border-bottom-color: #000; }

        .nt-tabs { display: flex; flex-wrap: wrap; gap: 2px; margin: 24px 0 0; border-bottom: 1px solid #e1e1e1; }
        .nt-tabs button { padding: 8px 16px; font: 400 13px/20px 'Roboto', system-ui, sans-serif; color: #555; background: none; border: 0; border-bottom: 2px solid transparent; margin-bottom: -1px; cursor: pointer; transition: color .15s; }
        .nt-tabs button:hover { color: #000; }
        .nt-tabs button.is-on { color: #000; font-weight: 700; border-bottom-color: #000; }

        .nt-toolbar { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; margin-top: 16px; }
        .nt-search-wrap { position: relative; flex: 1 1 200px; min-width: 200px; }
        .nt-search { display: block; width: 100%; height: 42px; padding: 0 14px 0 38px; border: 1px solid #e1e1e1; border-radius: 0; background: #fff;
                     font: 400 14px/20px 'Roboto', system-ui, sans-serif; color: #000; outline: 0; transition: border-color .15s; }
        .nt-search:focus { border-color: #000; }
        .nt-search::placeholder { color: #aaa; }
        .nt-search-icon { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #aaa; pointer-events: none; }
        .nt-sort { display: block; height: 42px; padding: 0 12px; border: 1px solid #e1e1e1; border-radius: 0; background: #fff;
                   font: 400 14px/20px 'Roboto', system-ui, sans-serif; color: #333; outline: 0; cursor: pointer; }
        .nt-sort:focus { border-color: #000; }

        .nt-bulk { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; margin-top: 12px; font-size: 13px; color: #333; }
        .nt-bulk-check { display: flex; align-items: center; gap: 6px; cursor: pointer; }
        .nt-bulk-check input { width: 15px; height: 15px; accent-color: #000; }
        .nt-bulk-btn { display: inline-block; height: 32px; padding: 0 14px; border: 1px solid #e1e1e1; border-radius: 0; background: #fff;
                       font: 500 12px/30px 'Roboto', system-ui, sans-serif; color: #333; cursor: pointer; transition: border-color .15s; }
        .nt-bulk-btn:hover { border-color: #000; }
        .nt-bulk-btn.is-danger { color: #e53935; border-color: rgba(229,57,53,.4); }
        .nt-bulk-btn.is-danger:hover { border-color: #e53935; }

        .nt-list { display: flex; flex-direction: column; gap: 8px; margin-top: 16px; }
        .nt-item { display: flex; align-items: flex-start; gap: 12px; padding: 12px 16px; background: #fff; border: 1px solid #e1e1e1; transition: border-color .15s; }
        .nt-item:hover { border-color: #000; }
        .nt-item.is-read { opacity: .6; }
        .nt-item input[type="checkbox"] { flex: none; width: 15px; height: 15px; margin-top: 3px; accent-color: #000; }
        .nt-item-body { flex: 1 1 0; min-width: 0; }
        .nt-item-title { margin: 0; font-size: 14px; line-height: 20px; color: #333; }
        .nt-item-title.is-unread { font-weight: 700; color: #000; }
        .nt-item-time { margin: 3px 0 0; font-size: 11px; line-height: 16px; letter-spacing: .06em; text-transform: uppercase; color: #888; }

        .nt-empty { padding: 32px 20px; text-align: center; font-size: 14px; color: #888; background: #fff; border: 1px solid #e1e1e1; }
        .nt-pagination { margin-top: 16px; }
    </style>

    <div class="nt">
        <p class="au-eyebrow">Akun</p>
        <h1 class="au-title">Semua Notifikasi</h1>

        <div class="nt-nav">
            <a href="{{ route('dashboard') }}">Pesanan Saya</a>
            <a href="{{ route('profile.notifications') }}" class="is-on">Notifikasi</a>
            <a href="{{ route('profile.coins') }}">Koin</a>
            <a href="{{ route('profile.wallet') }}">Voucher</a>
            <a href="{{ route('profile.referral') }}">Undang Teman</a>
            <a href="{{ route('profile.edit') }}">Edit Profil</a>
        </div>

        {{-- Filter Tab (DS 11.1) --}}
        <div class="nt-tabs" role="tablist">
            <button type="button" data-notif-filter="all" class="notif-tab is-on">Semua</button>
            <button type="button" data-notif-filter="unread" class="notif-tab">Belum Dibaca</button>
            <button type="button" data-notif-filter="order" class="notif-tab">Order</button>
            <button type="button" data-notif-filter="koin" class="notif-tab">Koin</button>
            <button type="button" data-notif-filter="voucher" class="notif-tab">Voucher</button>
            <button type="button" data-notif-filter="state" class="notif-tab">State</button>
            <button type="button" data-notif-filter="sistem" class="notif-tab">Sistem</button>
        </div>

        {{-- Search + Sort (DS 11.1) --}}
        <div class="nt-toolbar">
            <div class="nt-search-wrap">
                <input type="text" id="notif-search" placeholder="Cari notifikasi..." class="nt-search">
                <svg class="nt-search-icon" width="14" height="14" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/></svg>
            </div>
            <select id="notif-sort" class="nt-sort">
                <option value="newest">Terbaru</option>
                <option value="oldest">Terlama</option>
            </select>
        </div>

        {{-- Bulk Action (DS 11.1) --}}
        <div class="nt-bulk">
            <label class="nt-bulk-check">
                <input type="checkbox" id="notif-select-all"> Pilih Semua
            </label>
            <button type="button" class="nt-bulk-btn">Tandai Dibaca</button>
            <button type="button" class="nt-bulk-btn">Tandai Belum</button>
            <button type="button" class="nt-bulk-btn is-danger">Hapus</button>
        </div>

        <div class="nt-list">
            @forelse ($notifications as $notification)
                <div class="nt-item {{ $notification->is_read ? 'is-read' : '' }}" data-notif-item data-type="{{ $notification->type ?? 'sistem' }}" data-read="{{ $notification->is_read ? '1' : '0' }}">
                    <input type="checkbox">
                    <div class="nt-item-body">
                        <p class="nt-item-title {{ $notification->is_read ? '' : 'is-unread' }}">{{ $notification->title }}</p>
                        <p class="nt-item-time">{{ $notification->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
            @empty
                <div class="nt-empty">
                    Belum ada notifikasi.
                </div>
            @endforelse
        </div>

        <div class="nt-pagination">{{ $notifications->links() }}</div>
    </div>

    <script>
        (function () {
            var tabs = document.querySelectorAll('.notif-tab');
            var items = document.querySelectorAll('[data-notif-item]');
            tabs.forEach(function (tab) {
                tab.addEventListener('click', function () {
                    tabs.forEach(function (t) {
                        t.classList.remove('is-on');
                    });
                    tab.classList.add('is-on');
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
