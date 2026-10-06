@extends('layouts.app', ['title' => 'Notifikasi'])

@section('content')
<div class="account-page">
    <div class="account-card">
        <header class="account-head">
            <p class="account-head__eyebrow">Akun Saya</p>
            <h1 class="account-head__title">Notifikasi</h1>
            <div class="account-head__meta">
                <span>{{ $unreadCount > 0 ? $unreadCount . ' belum dibaca' : 'Semua sudah dibaca' }}</span>
                @if ($unreadCount > 0)
                    <span class="account-head__dot">·</span>
                    <form method="POST" action="{{ route('notifications.read-all') }}" style="display:inline;">
                        @csrf
                        @method('PUT')
                        <button type="submit" class="account-link-btn">Tandai semua dibaca</button>
                    </form>
                @endif
            </div>
        </header>

        @if (session('status'))
            <div class="account-notice">{{ session('status') }}</div>
        @endif

        <nav class="account-tabs">
            <a href="{{ route('notifications.index') }}" @class(['account-tab', 'is-active' => ! $filter])>Semua</a>
            <a href="{{ route('notifications.index', ['filter' => 'unread']) }}" @class(['account-tab', 'is-active' => $filter === 'unread'])>
                Belum dibaca
                @if ($unreadCount > 0)
                    <span class="account-tab__count">{{ $unreadCount }}</span>
                @endif
            </a>
        </nav>

        <section class="account-section account-section--flush">
            @if ($notifications->isEmpty())
                <div class="account-empty">
                    <h3 class="account-empty__title">Belum ada notifikasi.</h3>
                    <p class="account-empty__sub">Setiap update pesanan atau koin akan muncul di sini.</p>
                </div>
            @else
                <ul class="notif-ledger">
                    @foreach ($notifications as $notif)
                        <li @class(['notif-ledger__item', 'is-unread' => ! $notif->isRead()])>
                            <a href="{{ route('notifications.read', $notif) }}" class="notif-ledger__link">
                                <span class="notif-ledger__dot"></span>
                                <span class="notif-ledger__body">
                                    <strong class="notif-ledger__title">{{ $notif->title }}</strong>
                                    <span class="notif-ledger__text">{{ $notif->body }}</span>
                                    <span class="notif-ledger__time">
                                        {{ $notif->created_at->timezone('Asia/Jakarta')->diffForHumans(short: true) }}
                                    </span>
                                </span>
                            </a>
                            <form method="POST" action="{{ route('notifications.destroy', $notif) }}"
                                  onsubmit="return confirm('Hapus notifikasi ini?');"
                                  class="notif-ledger__delete">
                                @csrf
                                @method('DELETE')
                                <button type="submit" aria-label="Hapus notifikasi">×</button>
                            </form>
                        </li>
                    @endforeach
                </ul>

                <div class="account-pagination">
                    @include('partials.pagination', ['paginator' => $notifications])
                </div>
            @endif
        </section>
    </div>
</div>
@endsection
