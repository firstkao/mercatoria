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
            <div><dt>Status Order</dt><dd>{{ \App\Enums\OrderStatus::tryFrom($proof->order_status)?->label() ?? $proof->order_status }}</dd></div>
            <div><dt>Status Bukti</dt><dd>
                <span @class(['badge', 'badge--on' => $proof->status === 'approved', 'badge--danger' => $proof->status === 'rejected'])>
                    @if ($proof->status === 'pending') Menunggu verifikasi
                    @elseif ($proof->status === 'approved') Disetujui
                    @elseif ($proof->status === 'rejected') Ditolak
                    @else {{ $proof->status }}
                    @endif
                </span>
            </dd></div>
            {{-- uploaded_at bisa NULL (bukti lama yang diunggah sebelum kolom ini
                 diisi). Jangan fallback ke created_at: baris ini stdClass dari
                 DB::table() jadi created_at masih string mentah →
                 "timezone() on string" (500). --}}
            <div><dt>Diunggah</dt><dd>
                {{ $proof->uploaded_at ? $proof->uploaded_at->timezone('Asia/Jakarta')->translatedFormat('j F Y, H:i').' WIB' : '—' }}
            </dd></div>
            @if ($proof->reviewed_at)
                <div><dt>Direview</dt><dd>{{ $proof->reviewed_at->timezone('Asia/Jakarta')->translatedFormat('j F Y, H:i') }} WIB</dd></div>
            @endif
            @if ($proof->reject_reason)
                <div><dt>Alasan Penolakan</dt><dd class="text-danger">{{ $proof->reject_reason }}</dd></div>
            @endif
        </dl>
    </div>

    {{-- Image Preview --}}
    <div class="panel">
        <div class="panel__head">
            <h2>Bukti Pembayaran</h2>
        </div>
        <div class="text-center p-5">
            <img src="{{ $imageUrl }}" alt="Bukti pembayaran" class="img-viewer">
        </div>
    </div>

    {{-- Actions --}}
    {{-- ✅ BUG FIX: aksi verifikasi hanya untuk order yang masih di gerbang
         pembayaran; kalau status sudah digeser manual, bukti hanya dibaca. --}}
    @if ($proof->status === 'pending' && in_array($proof->order_status, ['menunggu_pembayaran', 'ditahan'], true))
        <div class="panel stack">
            <h2>Verifikasi</h2>
            <form method="POST" action="{{ route('admin.payments.approve', $proof->id) }}">
                @csrf
                <button type="submit" class="btn btn--primary btn--block"
                        onclick="return confirm('Setujui pembayaran ini?');">
                    Setujui
                </button>
            </form>

            <form method="POST" action="{{ route('admin.payments.reject', $proof->id) }}" class="stack mt-4">
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