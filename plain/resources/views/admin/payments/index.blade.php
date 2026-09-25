@extends('admin.layouts.app', ['title' => 'Pembayaran'])

@section('content')
    <section class="panel">
        <div class="panel__head"><h2>Bukti pembayaran</h2><nav><a href="{{ route('admin.payments.index', ['status' => 'pending']) }}">Pending</a> · <a href="{{ route('admin.payments.index', ['status' => 'all']) }}">Semua</a></nav></div>
        <div class="panel__body">
            @forelse ($proofs as $proof)
                <article class="card-row">
                    <div><strong><a href="{{ route('admin.payments.show', $proof->id) }}">{{ $proof->order_number }}</a></strong><p class="muted">{{ $proof->full_name }} · {{ $proof->payment_method_label }}</p></div>
                    <div><span class="badge">{{ $proof->status }}</span><p>Rp{{ number_format($proof->amount_idr, 0, ',', '.') }}</p></div>
                </article>
            @empty
                <p class="muted">Tidak ada bukti pembayaran.</p>
            @endforelse
            {{ $proofs->links() }}
        </div>
    </section>
@endsection
