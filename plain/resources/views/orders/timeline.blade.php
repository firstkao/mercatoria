@if ($order->statusHistory->isNotEmpty())
    <div class="panel">
        <div class="panel__head">
            <h2 style="margin:0;">Riwayat Status</h2>
        </div>
        <ul class="status-timeline">
            @foreach ($order->statusHistory as $index => $history)
                @php($isLatest = $loop->last)
                <li class="status-timeline__item {{ $isLatest ? 'is-current' : '' }}">
                    <span class="status-timeline__dot"></span>
                    <div class="status-timeline__body">
                        <strong class="status-timeline__title">
                            {{ \App\Enums\OrderStatus::tryFrom($history->to_status)?->label() ?? $history->to_status }}
                        </strong>
                        <span class="status-timeline__meta">
                            {{ $history->created_at->timezone('Asia/Jakarta')->translatedFormat('j F Y, H:i') }} WIB
                            @if ($history->changed_by === 'admin' && $history->admin)
                                · oleh {{ $history->admin->name }}
                            @elseif ($history->changed_by === 'user')
                                · oleh pembeli
                            @elseif ($history->changed_by === 'system')
                                · otomatis
                            @endif
                        </span>
                        @if ($history->note)
                            <p class="status-timeline__note">{{ $history->note }}</p>
                        @endif
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
@endif