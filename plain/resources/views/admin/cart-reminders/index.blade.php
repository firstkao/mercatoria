@extends('admin.layouts.app', ['title' => 'Cart Reminder'])

@section('actions')
    <form method="POST" action="{{ route('admin.cart-reminders.refresh') }}" class="inline-form">
        @csrf
        <button type="submit" class="btn btn--primary"
                onclick="return confirm('Kirim reminder ke cart yang sudah nganggur ≥30 menit?');">
            🔄 Jalankan Sekarang
        </button>
    </form>
@endsection

@section('content')

    <div class="stats">
        <div class="stat">
            <span class="stat__label">Cart Aktif</span>
            <strong class="stat__value">{{ number_format($stats['active_carts'] ?? 0, 0, ',', '.') }}</strong>
        </div>
        <div class="stat">
            <span class="stat__label">Reminder Terkirim</span>
            <strong class="stat__value">{{ number_format($stats['total_sent'] ?? 0, 0, ',', '.') }}</strong>
        </div>
        <div class="stat">
            <span class="stat__label">30 Hari Terakhir</span>
            <strong class="stat__value">{{ number_format($stats['sent_30d'] ?? 0, 0, ',', '.') }}</strong>
        </div>
        <div class="stat">
            <span class="stat__label">Rata-rata per Cart</span>
            <strong class="stat__value">{{ $stats['avg_per_cart'] ?? 0 }}x</strong>
        </div>
    </div>

    <p class="hint intro">
        Reminder dikirim <strong>setiap 30 menit</strong> ke user yang cart-nya nganggur tanpa checkout.
        Berlanjut otomatis sampai user <strong>checkout</strong> atau <strong>mengosongkan cart</strong>.
        Timer otomatis reset kalau user menambah atau mengubah item.
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
                                    <a href="{{ route('admin.users.show', $reminder->user) }}" class="link">
                                        {{ $reminder->user->displayName() }}
                                    </a>
                                    <div class="muted small">{{ $reminder->user->email }}</div>
                                @else
                                    <span class="muted">—</span>
                                @endif
                            </td>
                            <td class="muted">{{ $reminder->item_count }} item</td>
                            <td class="muted small nowrap">
                                {{ $reminder->sent_at->timezone('Asia/Jakarta')->translatedFormat('j M Y, H:i') }} WIB
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <ul class="cards only-mobile">
                @foreach ($reminders as $reminder)
                    <li>
                        <div class="card-row card-row--stack">
                            <div class="card-row__head">
                                <span class="card-row__title">
                                    @if ($reminder->user)
                                        <a href="{{ route('admin.users.show', $reminder->user) }}" class="link">
                                            {{ $reminder->user->displayName() }}
                                        </a>
                                    @else
                                        <span class="muted">&mdash;</span>
                                    @endif
                                </span>
                                <span class="badge">#{{ $reminder->reminder_number }}</span>
                            </div>
                            <p class="card-row__meta">
                                {{ $reminder->item_count }} item &middot;
                                Dikirim {{ $reminder->sent_at->timezone('Asia/Jakarta')->translatedFormat('j M Y, H:i') }} WIB
                            </p>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>

        @include('admin.partials.pagination', ['paginator' => $reminders])
    @endif
@endsection
