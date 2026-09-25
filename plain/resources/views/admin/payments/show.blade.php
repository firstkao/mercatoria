@extends('admin.layouts.app', ['title' => 'Bukti Pembayaran ' . $proof->order_number], ['back' => route('admin.payments.index')])

@section('content')
    <div class="panel">
        <div class="panel__head">
            <h1>{{ $proof->order_number }}</h1>
        </div>

        <dl class="definition-list">
            <div><dt>Pembeli</dt><dd>{{ $proof->full_name }} ({{ $proof->email }})</dd></div>
            <div><dt>Metode Pembayaran</dt><dd>{{ $proof->payment_method_label }}</dd></div>
            <div><dt>Jumlah</dt><dd>Rp {{ number_format($proof->amount_idr, 0, ',', '.') }}</dd></div>
            <div><dt>Status Order</dt><dd>{{ $proof->order_status }}</dd></div>
            <div><dt>Status Bukti</dt><dd>
                <span @class(['badge', 'badge--on' => $proof->status === 'approved', 'badge--off' => $proof->status === 'rejected'])>
                    @match($proof->status)
                        'pending' => 'Menunggu verifikasi',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                    @endmatch
                </span>
            </dd></div>
            <div><dt>Diunggah</dt><dd>{{ $proof->uploaded_at->timezone('Asia/Jakarta')->translatedFormat('j F Y, H:i') }} WIB</dd></div>
            @if ($proof->reviewed_at)
                <div><dt>Direview</dt><dd>{{ $proof->reviewed_at->timezone('Asia/Jakarta')->translatedFormat('j F Y, H:i') }} WIB</dd></div>
            @endif
        </dl>
    </div>

    {{-- Image Preview --}}
    <div class="panel">
        <div class="panel__head">
            <h2>Bukti Pembayaran</h2>
        </div>
        <div style="text-align: center; padding: 20px;">
            <img src="{{ $imageUrl }}" alt="Bukti pembayaran" style="max-width: 100%; max-height: 500px; border-radius: 8px;">
        </div>
    </div>

    {{-- Actions --}}
    @if ($proof->status === 'pending')
        <div class="panel stack">
            <h2>Verifikasi</h2>
            <div style="display: flex; gap: 10px;">
                <form method="POST" action="{{ route('admin.payments.approve', $proof->id) }}" style="flex: 1;">
                    @csrf
                    <button type="submit" class="btn btn--success btn--block">Setujui</button>
                </form>
                <button type="button" class="btn btn--danger btn--block" data-action="reject">Tolak</button>
            </div>
        </div>

        {{-- Reject Modal Form (hidden by default) --}}
        <div id="reject-form" class="panel" style="display: none;">
            <h2>Tolak Bukti Pembayaran</h2>
            <form method="POST" action="{{ route('admin.payments.reject', $proof->id) }}" class="stack">
                @csrf
                <label class="field">
                    <span>Alasan Penolakan</span>
                    <textarea name="reason" rows="4" placeholder="Jelaskan mengapa bukti pembayaran ditolak..." required></textarea>
                </label>
                <div style="display: flex; gap: 10px;">
                    <button type="submit" class="btn btn--danger">Tolak</button>
                    <button type="button" class="btn btn--secondary" data-action="cancel-reject">Batal</button>
                </div>
            </form>
        </div>

        <script>
            document.querySelector('[data-action="reject"]')?.addEventListener('click', function() {
                document.getElementById('reject-form').style.display = 'block';
            });
            document.querySelector('[data-action="cancel-reject"]')?.addEventListener('click', function() {
                document.getElementById('reject-form').style.display = 'none';
            });
        </script>
    @elseif ($proof->status === 'rejected')
        <div class="panel">
            <h2>Alasan Penolakan</h2>
            <p>{{ $proof->rejection_reason ?? '—' }}</p>
        </div>
    @endif
@endsection
