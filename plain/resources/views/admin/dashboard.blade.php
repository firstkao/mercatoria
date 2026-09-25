@extends('admin.layouts.app', ['title' => 'Ringkasan'])

@section('content')
    @unless ($ratesConfigured)
        <div class="alert alert--warning">
            Kurs dan tarif ongkir belum diisi, jadi harga produk belum bisa dihitung.
            <a href="{{ route('admin.settings.pricing') }}">Isi di Pengaturan</a>
        </div>
    @endunless

    <div class="stats">
        @foreach ($stats as $label => $value)
            <div class="stat">
                <span class="stat__label">{{ $label }}</span>
                <strong class="stat__value">{{ number_format($value, 0, ',', '.') }}</strong>
            </div>
        @endforeach
    </div>

    <section class="panel">
        <h2>Mulai dari sini</h2>
        <ol class="steps">
            <li><a href="{{ route('admin.settings.pricing') }}">Isi kurs, tarif ongkir, dan margin</a></li>
            <li><a href="{{ route('admin.products.create') }}">Tambah produk pertama</a></li>
        </ol>
    </section>
@endsection