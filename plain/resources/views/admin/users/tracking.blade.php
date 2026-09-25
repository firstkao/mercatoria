@php
    $lastLogin = $user->last_login_at ? \Illuminate\Support\Carbon::parse($user->last_login_at) : null;
    $daysLeft = $user->isSpammer() && $user->expires_at ? (int) ceil(now()->diffInHours($user->expires_at, false) / 24) : null;
    $remaining = $user->remainingViewQuota();
@endphp
<span class="tracking">
    <span><span class="muted">Login</span> {{ $lastLogin ? $lastLogin->locale('id')->diffForHumans(short: true) : 'belum' }}</span>
    <span><span class="muted">Order</span> {{ $user->orders_count }}</span>
    <span><span class="muted">Aktivitas</span> {{ $user->activity_logs_count }}</span>
    @if ($user->isSpammer())
        <span @class(['warn' => $remaining <= 2])><span class="muted">Lihat</span> {{ $viewQuota - $remaining }}/{{ $viewQuota }}</span>
        <span @class(['warn' => $daysLeft !== null && $daysLeft <= 7])><span class="muted">Terhapus</span> {{ $daysLeft !== null ? ($daysLeft <= 0 ? 'hari ini' : $daysLeft.' hari lagi') : '—' }}</span>
        @if ($user->email_deletions)
            <span class="warn"><span class="muted">Daftar ke-</span>{{ $user->email_deletions + 1 }}</span>
        @endif
    @endif
</span>