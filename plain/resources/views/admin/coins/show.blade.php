@extends('admin.layouts.app', ['title' => 'Koin: ' . $user->displayName(), 'back' => route('admin.coins.index')])

@section('content')
<div class="form-grid">
    <div class="form-grid__main stack">

        {{-- ============ RINGKASAN ============ --}}
        <x-admin.card>
            <h2>Ringkasan Koin</h2>
            <p class="muted small" style="margin-top:-6px;">{{ $user->email ?? '—' }} · {{ $user->whatsapp ? '+'.$user->whatsapp : '—' }}</p>

            <dl class="deflist">
                <div><dt>Koin Aktif</dt><dd class="text-price">{{ number_format($summary['active'], 0, ',', '.') }}</dd></div>
                <div><dt>Segera Hangus (≤30 hari)</dt><dd class="{{ $summary['expiring_soon'] > 0 ? 'text-warning' : '' }}">{{ number_format($summary['expiring_soon'], 0, ',', '.') }}</dd></div>
                <div><dt>Sudah Hangus</dt><dd class="{{ $summary['expired'] > 0 ? 'text-danger' : 'muted' }}">{{ number_format($summary['expired'], 0, ',', '.') }}</dd></div>
                <div><dt>Total Pernah Didapat</dt><dd>{{ number_format($summary['total_earned'], 0, ',', '.') }}</dd></div>
                <div><dt>Total Terpakai</dt><dd>{{ number_format($summary['total_spent'], 0, ',', '.') }}</dd></div>
            </dl>
        </x-admin.card>

        {{-- ============ RIWAYAT LOT ============ --}}
        <x-admin.card padding="flush">
            <div class="panel__head p-5">
                <h2>Riwayat Lot Koin</h2>
            </div>

            @if ($lots->isEmpty())
                <div class="empty"><p>Belum ada riwayat koin.</p></div>
            @else
                <table class="table">
                    <thead>
                        <tr>
                            <th>Didapat</th>
                            <th>Sumber</th>
                            <th class="is-right">Jumlah</th>
                            <th class="is-right">Sisa</th>
                            <th>Kedaluwarsa</th>
                            <th>Status</th>
                            <th class="is-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($lots as $lot)
                            @php
                                $isExpired   = $lot->expires_at && $lot->expires_at->isPast();
                                $isExhausted = $lot->remaining == 0;
                                $isSoon      = $lot->expires_at && ! $isExpired
                                    && $lot->expires_at->lt(now()->addDays(30))
                                    && ! $isExhausted;
                            @endphp
                            <tr>
                                <td class="muted small">
                                    {{ $lot->earned_at?->timezone('Asia/Jakarta')->translatedFormat('j M Y') ?? '—' }}
                                </td>
                                <td>{{ str_replace('_', ' ', \Illuminate\Support\Str::title($lot->source)) }}</td>
                                <td class="is-right text-price">+{{ number_format($lot->amount, 0, ',', '.') }}</td>
                                <td class="is-right">{{ number_format($lot->remaining, 0, ',', '.') }}</td>
                                <td class="muted small">
                                    {{ $lot->expires_at?->timezone('Asia/Jakarta')->translatedFormat('j M Y') ?? '∞' }}
                                </td>
                                <td>
                                    @if ($isExpired && $lot->remaining > 0)
                                        <span class="badge badge--danger">Hangus</span>
                                    @elseif ($isExpired)
                                        <span class="badge">Kedaluwarsa</span>
                                    @elseif ($isExhausted)
                                        <span class="badge">Habis</span>
                                    @elseif ($isSoon)
                                        <span class="badge badge--warn">Segera Hangus</span>
                                    @else
                                        <span class="badge badge--on">Aktif</span>
                                    @endif
                                </td>
                                <td class="is-right nowrap">
                                    <form method="POST" action="{{ route('admin.coins.lots.destroy', $lot) }}"
                                          onsubmit="return confirm('Hapus lot koin ini? Tindakan tidak bisa dibatalkan.');"
                                          class="inline-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="link text-danger">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="p-5">
                    {{ $lots->links('admin.partials.pagination') }}
                </div>
            @endif
        </x-admin.card>

    </div>

    {{-- ============ SIDEBAR: FORM PENYESUAIAN ============ --}}
    <aside class="form-grid__side stack">
        <x-admin.card>
            <h2>Penyesuaian Koin</h2>
            <p class="muted small" style="margin-top:-6px;">
                Untuk koreksi manual (komplain customer, bonus, error sistem). Semua perubahan dicatat di log admin.
            </p>

            <form action="{{ route('admin.coins.adjust', $user) }}" method="POST" id="coin-adjust-form">
                @csrf

                <div class="field mb-4">
                    <span>Aksi</span>
                    <select name="action" id="coin-action" required>
                        <option value="add">Tambah Koin</option>
                        <option value="subtract">Kurangi Koin</option>
                        <option value="reset_expiry">Reset Masa Berlaku Koin Aktif</option>
                    </select>
                </div>

                <div class="field mb-4" id="coin-amount-row">
                    <span>Jumlah Koin</span>
                    <input type="number" name="amount" min="1" step="1" placeholder="Contoh: 5000">
                    @error('amount')<small class="text-danger">{{ $message }}</small>@enderror
                </div>

                <div class="field mb-4" id="coin-expiry-row">
                    <span>Masa Berlaku (bulan)</span>
                    <input type="number" name="expires_months" min="1" max="120" value="12">
                    <small class="muted small">Untuk koin baru atau perpanjangan.</small>
                </div>

                <div class="field mb-4">
                    <span>Alasan</span>
                    <textarea name="reason" rows="3" maxlength="500" required
                              placeholder="Wajib. Akan tersimpan di log untuk audit."></textarea>
                    @error('reason')<small class="text-danger">{{ $message }}</small>@enderror
                </div>

                <button type="submit" class="btn btn--primary btn--block"
                        onclick="return confirm('Terapkan penyesuaian koin ini?');">
                    Terapkan
                </button>
            </form>
        </x-admin.card>
    </aside>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var action = document.getElementById('coin-action');
    var amount = document.getElementById('coin-amount-row');
    var expiry = document.getElementById('coin-expiry-row');
    if (! action || ! amount || ! expiry) return;

    function sync() {
        var v = action.value;
        amount.style.display = (v === 'add' || v === 'subtract') ? '' : 'none';
        expiry.style.display = (v === 'add' || v === 'reset_expiry') ? '' : 'none';
    }
    action.addEventListener('change', sync);
    sync();
})();
</script>
@endpush
