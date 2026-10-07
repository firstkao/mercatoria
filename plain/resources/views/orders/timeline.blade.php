@if ($order->statusHistory->isNotEmpty())
    @php
        // Nama pembeli untuk label "oleh ...". Fallback ke "Pembeli" kalau
        // kolomnya kosong (mis. user sudah dihapus / full_name null).
        $buyerName = $order->user->full_name ?? $order->user->name ?? 'Pembeli';
    @endphp

    <div class="account-card" style="margin-top: 16px;">
        <section class="account-section">
            <div class="account-section__head">
                <h2 class="account-section__title">Riwayat Status</h2>
            </div>
            <ol class="timeline-ledger">
                @foreach ($order->statusHistory as $history)
                    @php($isLatest = $loop->last)
                    <li @class(['timeline-ledger__item', 'is-current' => $isLatest])>
                        <span class="timeline-ledger__dot"></span>
                        <div class="timeline-ledger__body">
                            <strong class="timeline-ledger__title">
                                {{ \App\Enums\OrderStatus::tryFrom($history->to_status)?->label() ?? $history->to_status }}
                            </strong>
                            <span class="timeline-ledger__meta">
                                {{ $history->created_at->timezone('Asia/Jakarta')->translatedFormat('j F Y, H:i') }} WIB
                                @if ($history->changed_by === 'admin')
                                    · oleh Mercatoria
                                @elseif ($history->changed_by === 'user')
                                    · oleh {{ $buyerName }}
                                @elseif ($history->changed_by === 'system')
                                    · otomatis
                                @endif
                            </span>
                            @if ($history->note)
                                <p class="timeline-ledger__note">{{ $history->note }}</p>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ol>
        </section>
    </div>
@endif
