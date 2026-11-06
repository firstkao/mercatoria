@extends('layouts.app')

@section('title', 'Semua Notifikasi — MERCATORIA')

@section('content')
    @include('frontend.partials._account-style')

    <div class="ac-box">
        <div class="ac-head">
            <h1 class="ac-title">Semua Notifikasi</h1>
        </div>

        @include('frontend.partials._account-nav', ['active' => 'notifications'])

        <section class="ac-sec">
            <div class="ac-chips" role="tablist">
                <button type="button" data-notif-filter="all" class="ac-chip notif-tab is-on">Semua</button>
                <button type="button" data-notif-filter="unread" class="ac-chip notif-tab">Belum Dibaca</button>
                <button type="button" data-notif-filter="order" class="ac-chip notif-tab">Order</button>
                <button type="button" data-notif-filter="koin" class="ac-chip notif-tab">Koin</button>
                <button type="button" data-notif-filter="voucher" class="ac-chip notif-tab">Voucher</button>
                <button type="button" data-notif-filter="state" class="ac-chip notif-tab">State</button>
                <button type="button" data-notif-filter="sistem" class="ac-chip notif-tab">Sistem</button>
            </div>

            <div class="ac-tools">
                <div class="ac-search">
                    <input type="text" id="notif-search" placeholder="Cari notifikasi..." class="ac-input has-icon" autocomplete="off">
                    <svg width="14" height="14" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/></svg>
                </div>
                <select id="notif-sort" class="ac-input" aria-label="Urutkan">
                    <option value="newest">Terbaru</option>
                    <option value="oldest">Terlama</option>
                </select>
            </div>

            <div class="ac-bulk">
                <label class="ac-check"><input type="checkbox" id="notif-select-all"> Pilih Semua</label>
                <button type="button" class="ac-btn is-ghost is-sm">Tandai Dibaca</button>
                <button type="button" class="ac-btn is-ghost is-sm">Tandai Belum</button>
                <button type="button" class="ac-btn is-ghost is-danger is-sm">Hapus</button>
            </div>

            <div class="ac-rows" id="notif-list">
                @forelse ($notifications as $notification)
                    <div class="ac-row {{ $notification->is_read ? 'is-read' : '' }}" data-notif-item
                         data-type="{{ $notification->type ?? 'sistem' }}" data-read="{{ $notification->is_read ? '1' : '0' }}"
                         data-ts="{{ $notification->created_at->timestamp }}">
                        <div class="ac-row-main">
                            <input type="checkbox" aria-label="Pilih notifikasi">
                            <div>
                                <p class="ac-row-t {{ $notification->is_read ? '' : 'is-unread' }}">{{ $notification->title }}</p>
                                <p class="ac-row-s">{{ $notification->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="ac-empty">Belum ada notifikasi.</div>
                @endforelse
            </div>
        </section>

        <div class="ac-pager">{{ $notifications->links('vendor.pagination.catalog-pager') }}</div>
    </div>

    <script>
        (function () {
            var tabs = document.querySelectorAll('.notif-tab');
            var list = document.getElementById('notif-list');
            var items = Array.prototype.slice.call(document.querySelectorAll('[data-notif-item]'));
            var search = document.getElementById('notif-search');
            var sort = document.getElementById('notif-sort');
            var all = document.getElementById('notif-select-all');
            var filter = 'all';

            function visible(item) { return item.style.display !== 'none'; }

            function apply() {
                var q = (search.value || '').trim().toLowerCase();
                items.forEach(function (item) {
                    var ok = filter === 'all'
                        || (filter === 'unread' ? item.getAttribute('data-read') === '0' : item.getAttribute('data-type') === filter);
                    if (ok && q) ok = item.textContent.toLowerCase().indexOf(q) > -1;
                    item.style.display = ok ? '' : 'none';
                });
            }

            tabs.forEach(function (tab) {
                tab.addEventListener('click', function () {
                    tabs.forEach(function (t) { t.classList.remove('is-on'); });
                    tab.classList.add('is-on');
                    filter = tab.getAttribute('data-notif-filter');
                    apply();
                });
            });

            search.addEventListener('input', apply);

            sort.addEventListener('change', function () {
                var dir = sort.value === 'oldest' ? 1 : -1;
                items.sort(function (a, b) { return dir * (a.getAttribute('data-ts') - b.getAttribute('data-ts')); })
                     .forEach(function (item) { list.appendChild(item); });
            });

            all.addEventListener('change', function () {
                items.forEach(function (item) {
                    if (visible(item)) item.querySelector('input[type="checkbox"]').checked = all.checked;
                });
            });
        })();
    </script>
@endsection
