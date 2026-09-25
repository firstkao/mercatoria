@extends('admin.layouts.app', ['title' => 'Review pembayaran', 'back' => route('admin.payments.index')])

@section('content')
    <section class="panel stack">
        <dl class="details"><div><dt>Order</dt><dd>{{ $proof->order_number }}</dd></div><div><dt>Pembeli</dt><dd>{{ $proof->full_name }} ({{ $proof->email }})</dd></div><div><dt>Metode</dt><dd>{{ $proof->payment_method_label }}</dd></div><div><dt>Nominal</dt><dd>Rp{{ number_format($proof->amount_idr, 0, ',', '.') }}</dd></div><div><dt>Status</dt><dd>{{ $proof->status }}</dd></div></dl>
        <p><a href="{{ $imageUrl }}" target="_blank" rel="noopener"><img src="{{ $imageUrl }}" alt="Bukti pembayaran" style="max-width:100%;height:auto;max-height:600px"></a></p>
        @if ($proof->status === 'pending')
            <div class="actions">
                <form method="POST" action="{{ route('admin.payments.approve', $proof->id) }}">@csrf<button class="btn btn--primary" onclick="return confirm('Setujui bukti pembayaran ini?')">Setujui</button></form>
                <form method="POST" action="{{ route('admin.payments.reject', $proof->id) }}"><input type="hidden" name="reject_reason" value="Bukti pembayaran tidak dapat diverifikasi.">@csrf<button class="btn" onclick="return confirm('Tolak bukti pembayaran ini?')">Tolak</button></form>
            </div>
        @elseif ($proof->reject_reason)
            <p class="alert alert--danger">{{ $proof->reject_reason }}</p>
        @endif
    </section>
@endsection
