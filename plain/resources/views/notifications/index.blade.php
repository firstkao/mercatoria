@extends('layouts.app', ['title' => 'Notifikasi'])

@section('content')
<section class="card" style="max-width: 780px;">
    <div class="catalog__head" style="padding:0 0 16px;">
        <h1 style="margin:0;">Notifikasi</h1>
        @if ($unreadCount > 0)
            <form method="POST" action="{{ route('notifications.read-all') }}">
                @csrf
                @method('PUT')
                <button type="submit" class="button button--small" style="background:#e5e7eb;color:#111;">Tandai semua dibaca</button>
            </form>
        @endif
    </div>

    @if (session('status'))
        <div class="notice">{{ session('status') }}</div>
    @endif

    <nav class="subtabs" style="margin-bottom:20px; padding:0;">
        <a href="{{ route('notifications.index') }}" @class(['subtab', 'is-active' => ! $filter])>Semua</a>
        <a href="{{ route('notifications.index', ['filter' => 'unread']) }}" @class(['subtab', 'is-active' => $filter === 'unread'])>
            Belum dibaca
            @if ($unreadCount > 0)
                <span class="subtab__count">{{ $unreadCount }}</span>
            @endif
        </a>
    </nav>

    @if ($notifications->isEmpty())
        <div class="empty">
            <p>Belum ada notifikasi.</p>
            <p class="muted">Setiap ada update pesanan atau koin, kamu akan dapat notifikasi di sini.</p>
        </div>
    @else
        <ul class="notif-list">
            @foreach ($notifications as $notif)
                <li @class(['notif', 'notif--unread' => ! $notif->isRead()])>
                    <a href="{{ route('notifications.read', $notif) }}" class="notif__link">
                        <span class="notif__icon notif__icon--{{ $notif->icon }}">
                            @if ($notif->icon === 'box')
                                📦
                            @elseif ($notif->icon === 'wallet')
                                💰
                            @else
                                🔔
                            @endif
                        </span>
                        <span class="notif__body">
                            <strong class="notif__title">{{ $notif->title }}</strong>
                            <span class="notif__text">{{ $notif->body }}</span>
                            <span class="notif__time">{{ $notif->created_at->timezone('Asia/Jakarta')->diffForHumans(short: true) }}</span>
                        </span>
                    </a>
                    <form method="POST" action="{{ route('notifications.destroy', $notif) }}"
                          onsubmit="return confirm('Hapus notifikasi ini?');"
                          class="notif__delete">
                        @csrf
                        @method('DELETE')
                        <button type="submit" aria-label="Hapus">×</button>
                    </form>
                </li>
            @endforeach
        </ul>

        @include('partials.pagination', ['paginator' => $notifications])
    @endif
</section>
@endsection