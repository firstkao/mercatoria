@extends('layouts.app', ['title' => 'Akun saya'])

@section('content')
    <section class="card">
        <p class="eyebrow">Akun saya</p>
        <h1>Halo, {{ $user->full_name }}</h1>
        <p><span class="badge badge--{{ $user->role->value }}">{{ $user->role->label() }}</span></p>

        @if ($user->isSpammer())
            @php($remaining = $user->remainingViewQuota())
            <div class="quota {{ $remaining <= 2 ? 'quota--warning' : '' }}" role="status">
                <strong>Sisa lihat produk: {{ $remaining }} dari {{ $viewQuota }}</strong>
                <p>
                    Akun ini akan terhapus otomatis pada
                    {{ $user->expires_at->timezone('Asia/Jakarta')->translatedFormat('j F Y, H:i') }} WIB
                    jika belum ada pembayaran yang terverifikasi.
                </p>
            </div>
        @endif

        <dl class="details">
            <div><dt>Email</dt><dd>{{ $user->email }}</dd></div>
            <div><dt>WhatsApp</dt><dd>+{{ $user->whatsapp }}</dd></div>
            <div><dt>Tanggal lahir</dt><dd>{{ $user->birth_date->translatedFormat('j F Y') }}</dd></div>
            <div><dt>Alamat</dt><dd>{{ $user->street_address }}, {{ $user->district }}, {{ $user->city }}, {{ $user->province }} {{ $user->postal_code }}</dd></div>
        </dl>
    </section>
@endsection