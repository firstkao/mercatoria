@extends('admin.layouts.app', ['title' => 'Bukti Pembayaran ' . $proof->order_number, 'back' => route('admin.payments.index')])

@section('content')
    <div class="panel">
        <div class="panel__head">
            <h1>{{ $proof->order_number }}</h1>
        </div>

        <dl class="deflist">
            <div><dt>Pembeli</dt><dd>{{ $proof->full_name }} ({{ $proof->email }})</dd></div>
            <div><dt>Metode Pembayaran</dt><dd>{{ $proof->payment_method_label }}</dd></div>
            <div><dt>Jumlah</dt><dd>Rp {{ number_format($proof->amount_idr, 0, ',', '.') }}</dd></div>
            <div><dt>Status Order</dt><dd>{{ $proof->order_status }}</dd></div>
            <div><dt>Status Bukti</dt><dd>
                <span @class(['badge', 'badge--on' => $proof->status === 'approved', 'badge--danger' => $proof->status === 'rejected'])>
                    @if ($proof->status === 'pending') Menunggu verifikasi
                    @elseif ($proof->status === 'approved') Disetujui
                    @elseif ($proof->status === 'rejected') Ditolak
                    @else {{ $proof->status }}
                    @endif
                </span>
            </dd></div>
            <div><dt>Diunggah</dt><dd>{{ $proof->uploaded_at->timezone('Asia/Jakarta')->translatedFormat('j F Y, H:i') }} WIB</dd></div>
            @if ($proof->reviewed_at)
                <div><dt>Direview</dt><dd>{{ $proof->reviewed_at->timezone('Asia/Jakarta')->translatedFormat('j F Y, H:i') }} WIB</dd></div>
            @endif
            @if ($proof->reject_reason)
                <div><dt>Alasan Penolakan</dt><dd style="color:var(--danger);">{{ $proof->reject_reason }}</dd></div>
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
            <form method="POST" action="{{ route('admin.payments.approve', $proof->id) }}">
                @csrf
                <button type="submit" class="btn btn--primary btn--block"
                        onclick="return confirm('Setujui pembayaran ini?');">
                    Setujui
                </button>
            </form>

            <form method="POST" action="{{ route('admin.payments.reject', $proof->id) }}" class="stack" style="margin-top:16px;">
                @csrf
                <label class="field">
                    <span>Atau Tolak (isi alasan)</span>
                    <textarea name="reject_reason" rows="3" required
                              placeholder="Jelaskan mengapa bukti pembayaran ditolak..."></textarea>
                </label>
                <button type="submit" class="btn btn--danger"
                        onclick="return confirm('Tolak bukti pembayaran ini?');">
                    Tolak
                </button>
            </form>
        </div>
    @endif
@endsection