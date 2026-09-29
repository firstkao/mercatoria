@extends('layouts.app', ['title' => 'Hubungi Kami'])

@section('content')
    <section class="card" style="max-width: 720px;">
        <p class="eyebrow">Kontak</p>
        <h1>Hubungi Kami</h1>
        <p class="muted">Punya pertanyaan tentang produk, pesanan, reseller, atau kerja sama? Kami siap membantu.</p>

        {{-- Info kontak --}}
        <div class="contact-info">
            @if ($contactWhatsapp)
                <a href="https://wa.me/{{ preg_replace('/\D/', '', $contactWhatsapp) }}" target="_blank" rel="noopener" class="contact-card">
                    <span class="contact-card__icon">💬</span>
                    <div>
                        <strong>WhatsApp</strong>
                        <span>{{ $contactWhatsapp }}</span>
                        <small>Balasan paling cepat</small>
                    </div>
                </a>
            @endif

            @if ($contactEmail)
                <a href="mailto:{{ $contactEmail }}" class="contact-card">
                    <span class="contact-card__icon">✉️</span>
                    <div>
                        <strong>Email</strong>
                        <span>{{ $contactEmail }}</span>
                        <small>Untuk hal resmi</small>
                    </div>
                </a>
            @endif

            @if ($contactHours)
                <div class="contact-card contact-card--static">
                    <span class="contact-card__icon">🕐</span>
                    <div>
                        <strong>Jam Operasional</strong>
                        <span>{{ $contactHours }}</span>
                    </div>
                </div>
            @endif

            @if ($storeAddress)
                <div class="contact-card contact-card--static">
                    <span class="contact-card__icon">📍</span>
                    <div>
                        <strong>Alamat</strong>
                        <span>{{ $storeAddress }}</span>
                    </div>
                </div>
            @endif
        </div>

        {{-- Form --}}
        <h2 style="margin-top:32px;">Kirim Pesan</h2>

        @if (session('status'))
            <div class="notice" role="status">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('contact.store') }}" class="form" novalidate>
            @csrf

            <div class="field-row">
                <label class="field">
                    <span>Nama</span>
                    <input type="text" name="name" value="{{ old('name', auth()->user()?->full_name) }}" maxlength="100" required>
                    @include('partials.field-error', ['name' => 'name'])
                </label>
                <label class="field">
                    <span>Email</span>
                    <input type="email" name="email" value="{{ old('email', auth()->user()?->email) }}" maxlength="150" required>
                    @include('partials.field-error', ['name' => 'email'])
                </label>
            </div>

            <div class="field-row">
                <label class="field">
                    <span>WhatsApp (opsional)</span>
                    <input type="tel" name="whatsapp" value="{{ old('whatsapp', auth()->user()?->whatsapp ? '+'.auth()->user()->whatsapp : '') }}" maxlength="30">
                    @include('partials.field-error', ['name' => 'whatsapp'])
                </label>
                <label class="field">
                    <span>Subjek</span>
                    <input type="text" name="subject" value="{{ old('subject') }}" maxlength="150" required>
                    @include('partials.field-error', ['name' => 'subject'])
                </label>
            </div>

            <label class="field">
                <span>Pesan</span>
                <textarea name="message" rows="6" maxlength="3000" required placeholder="Tulis pesan kamu di sini...">{{ old('message') }}</textarea>
                @include('partials.field-error', ['name' => 'message'])
            </label>

            @include('partials.turnstile')

            <button type="submit" class="button button--block">Kirim Pesan</button>
        </form>
    </section>
@endsection