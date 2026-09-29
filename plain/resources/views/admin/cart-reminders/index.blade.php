@extends('admin.layouts.app', ['title' => 'Cart Reminder'])

@section('actions')
    <form method="POST" action="{{ route('admin.cart-reminders.refresh') }}" style="display:inline;">
        @csrf
        <button type="submit" class="btn btn--primary" onclick="return confirm('Kirim reminder cart abandonment sekarang?');">
            🔄 Jalankan Sekarang
        </button>
    </form>
@endsection

@section('content')
    @if (session('status'))
        <div class="alert alert--success">{{ session('status') }}</div>
    @endif

    <div class="stats">
        <div class="stat">
            <span class="stat__label">Cart Aktif</span>
            <strong class="stat__value">{{ number_format($stats['active_carts'], 0, ',', '.') }}</strong>
        </div>
        <div class="stat">
            <span class="stat__label">Reminder Terkirim</span>
            <strong class="stat__value">{{ number_format($stats['total_sent'], 0, ',', '.') }}</strong>
        </div>
        <div class="stat">
            <span class="stat__label">30 Hari Terakhir</span>
            <strong class="stat__value">{{ number_format($stats['sent_30d'], 0, ',', '.') }}</strong>
        </div>
        <div class="stat">
            <span class="stat__label">Reminder #1 / #2</span>
            <strong class="stat__value">{{ $stats['reminder1'] }} / {{ $stats['reminder2'] }}</strong>
        </div>
    </div>

    <p class="hint intro">
        Sistem otomatis kirim reminder ke user yang cart-nya nganggur tanpa checkout.
        Reminder #1 setelah <strong>4 jam</strong>, reminder #2 setelah <strong>24 jam</strong>. Maks 2 reminder per cart session (reset kalau user ubah cart).
    </p>

    @if ($reminders->isEmpty())
        <div class="empty"><p>Belum ada reminder terkirim.</p></div>
    @else
        <div class="panel panel--flush">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Pembeli</th>
                        <th>Item</th>
                        <th>Dikirim</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($reminders as $reminder)
                        <tr>
                            <td><span class="badge">#{{ $reminder->reminder_number }}</span></td>
                            <td>
                                @if ($reminder->user)
                                    <a href="{{ route('admin.users.show', $reminder->user) }}" class="link">{{ $reminder->user->displayName() }}</a>
                                    <div class="muted small">{{ $reminder->user->email }}</div>
                                @else
                                    <span class="muted">—</span>
                                @endif
                            </td>
                            <td class="muted">{{ $reminder->item_count }} item</td>
                            <td class="muted small nowrap">{{ $reminder->sent_at->timezone('Asia/Jakarta')->translatedFormat('j M Y, H:i') }} WIB</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @include('admin.partials.pagination', ['paginator' => $reminders])
    @endif
@endsection