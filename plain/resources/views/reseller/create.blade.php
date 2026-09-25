@extends('layouts.app', ['title' => 'Reseller'])

@section('content')
    <section class="card">
        <h1>Reseller</h1>
        <div class="prose">
            {!! $termsHtml !!}
        </div>

        @if (session('status'))
            <div class="notice" role="status">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('reseller.create') }}" class="form" novalidate>
            @csrf

            <fieldset>
                <legend>Formulir pendaftaran</legend>

                <label class="field">
                    <span>Nama lengkap sesuai identitas</span>
                    <input type="text" name="full_name" value="{{ old('full_name', auth()->user()?->full_name) }}" autocomplete="name" maxlength="100" required>
                    @include('partials.field-error', ['name' => 'full_name'])
                </label>

                <div class="field-row">
                    <label class="field">
                        <span>Nomor WhatsApp</span>
                        <input type="tel" name="whatsapp" value="{{ old('whatsapp', auth()->user()?->whatsapp ? '+'.auth()->user()->whatsapp : null) }}" placeholder="08xxxxxxxxxx" autocomplete="tel" required>
                        @include('partials.field-error', ['name' => 'whatsapp'])
                    </label>
                    <label class="field">
                        <span>Email</span>
                        <input type="email" name="email" value="{{ old('email', auth()->user()?->email) }}" autocomplete="email" required>
                        @include('partials.field-error', ['name' => 'email'])
                    </label>
                </div>

                <label class="field">
                    <span>Kota/Kabupaten</span>
                    <input type="text" name="city" value="{{ old('city', auth()->user()?->city) }}" maxlength="100" required>
                    @include('partials.field-error', ['name' => 'city'])
                </label>

                <div class="field-row">
                    <label class="field">
                        <span>Tempat berjualan</span>
                        <select name="sales_channel" required>
                            <option value="">Pilih</option>
                            @foreach ($salesChannels as $value => $label)
                                <option value="{{ $value }}" @selected(old('sales_channel') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @include('partials.field-error', ['name' => 'sales_channel'])
                    </label>
                    <label class="field">
                        <span>Perkiraan order per bulan</span>
                        <select name="monthly_estimate" required>
                            <option value="">Pilih</option>
                            @foreach ($monthlyEstimates as $value => $label)
                                <option value="{{ $value }}" @selected(old('monthly_estimate') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @include('partials.field-error', ['name' => 'monthly_estimate'])
                    </label>
                </div>

                <label class="field">
                    <span>Link toko atau akun jualan (opsional)</span>
                    <input type="url" name="store_link" value="{{ old('store_link') }}" placeholder="https://instagram.com/namatoko" maxlength="255">
                    @include('partials.field-error', ['name' => 'store_link'])
                </label>

                <label class="field">
                    <span>Catatan (opsional)</span>
                    <textarea name="notes" rows="3" maxlength="1000">{{ old('notes') }}</textarea>
                    @include('partials.field-error', ['name' => 'notes'])
                </label>
            </fieldset>

            <label class="check">
                <input type="checkbox" name="accept_reseller_terms" value="1" @checked(old('accept_reseller_terms'))>
                <span>Saya sudah membaca dan menyetujui ketentuan reseller di atas.</span>
            </label>
            @include('partials.field-error', ['name' => 'accept_reseller_terms'])

            @include('partials.turnstile')

            <button type="submit" class="button button--block">Kirim pendaftaran</button>
        </form>
    </section>
@endsection