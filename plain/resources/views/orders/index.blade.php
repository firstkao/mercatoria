@extends('layouts.app', ['title' => 'Pesanan Saya'])

@section('content')
<div class="account-page">
    <div class="account-card">
        <header class="account-head">
            <p class="account-head__eyebrow">Akun Saya</p>
            <h1 class="account-head__title">Pesanan Saya</h1>
            <div class="account-head__meta">
                <span>{{ $orders->total() }} pesanan</span>
            </div>
        </header>

        @if (session('status'))
            <div class="account-notice">{{ session('status') }}</div>
        @endif

        {{-- ========== TAGIHAN MENUNGGU ========== --}}
        @if ($unpaid->isNotEmpty())
            <section class="account-section">
                <div class="account-section__head">
                    <h2 class="account-section__title">Tagihan Menunggu</h2>
                    <span class="account-section__hint">{{ $unpaid->count() }} pesanan</span>
                </div>
                <ul class="account-unpaid-list">
                    @foreach($unpaid as $u)
                        <li class="account-unpaid-list__item">
                            <div class="account-unpaid-list__info">
                                <span class="account-unpaid-list__id">{{ $u->order_number }}</span>
                                <span class="account-unpaid-list__amount">
                                    · {{ \App\Support\PriceCalculator::formatRupiah($u->pay_now_idr) }}
                                    ({{ $u->payment_scheme === 'FP' ? 'lunas' : 'DP' }})
                                </span>
                                @if($u->payment_deadline_at)
                                    <div class="account-unpaid-list__deadline">
                                        Batas bayar:
                                        {{ $u->payment_deadline_at->timezone('Asia/Jakarta')->translatedFormat('j M Y, H:i') }}
                                        WIB
                                    </div>
                                @endif
                            </div>
                            <a href="{{ route('account.orders.show', $u->order_number) }}#upload-bukti"
                               class="account-unpaid-list__cta">
                                Bayar &amp; unggah bukti
                            </a>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        {{-- ========== SEMUA PESANAN ========== --}}
        <section class="account-section">
            <div class="account-section__head">
                <h2 class="account-section__title">Semua Pesanan</h2>
            </div>

            @if($orders->isEmpty())
                <div class="account-empty">
                    <h3 class="account-empty__title">Belum ada pesanan.</h3>
                    <p class="account-empty__sub">Begitu kamu checkout, semua riwayat pesanan muncul di sini.</p>
                    <a href="{{ route('catalog.index') }}" class="account-empty__cta">Mulai belanja</a>
                </div>
            @else
                <div class="account-table-wrap">
                    <table class="account-table">
                        <thead>
                            <tr>
                                <th>No. Pesanan</th>
                                <th>Tanggal</th>
                                <th class="is-right">Tagihan</th>
                                <th>Status</th>
                                <th class="is-right"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orders as $order)
                                @php
                                    $badge = $order->statusBadgeClass();
                                    $mod = str_contains($badge, 'danger')
                                        ? 'account-status--danger'
                                        : (str_contains($badge, 'success') ? 'account-status--ok' : '');
                                @endphp
                                <tr>
                                    <td class="account-table__id">
                                    @php $firstItem = $order->items->first(); @endphp
                                    <div style="display:flex;align-items:center;gap:8px;">
                                        @if ($firstItem)
                                            @php $imgUrl = $firstItem->variant?->imageUrl(); @endphp
                                            @if ($imgUrl)
                                                <img src="{{ $imgUrl }}"
                                                     alt=""
                                                     style="width:36px;height:36px;min-width:36px;max-width:36px;min-height:36px;max-height:36px;object-fit:cover;border-radius:6px;">
                                            @endif
                                        @endif
                                        <span>{{ $order->order_number }}</span>
                                    </div>
                                </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="account-pagination">
                    {{ $orders->links('partials.pagination') }}
                </div>
            @endif
        </section>

    </div>
</div>
@endsection
