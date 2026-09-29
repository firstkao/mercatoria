@extends('admin.layouts.app', ['title' => 'Pengaturan'])

@section('tabs')
    @include('admin.settings.tabs')
@endsection

@section('content')
    <form method="POST" action="{{ route('admin.settings.general.update') }}" class="panel stack" novalidate>
        @csrf
        @method('PUT')
        <h2>Kontak & Sosial Media</h2>
        <p class="hint">Info ini tampil di footer toko dan halaman legal. Kosongkan untuk menyembunyikan.</p>

        <div class="field-row">
            <label class="field">
                <span>Email CS</span>
                <input type="email" name="contact_email" value="{{ old('contact_email', $settings['contact_email']) }}" maxlength="150" placeholder="cs@mercatoria.id">
                @include('admin.partials.error', ['name' => 'contact_email'])
            </label>
            <label class="field">
                <span>WhatsApp CS</span>
                <input type="text" name="contact_whatsapp" value="{{ old('contact_whatsapp', $settings['contact_whatsapp']) }}" maxlength="30" placeholder="+62 812-1968-3709">
                @include('admin.partials.error', ['name' => 'contact_whatsapp'])
            </label>
        </div>

        <label class="field">
            <span>Jam operasional (opsional)</span>
            <input type="text" name="contact_hours" value="{{ old('contact_hours', $settings['contact_hours']) }}" maxlength="150" placeholder="Senin–Jumat, 09:00–17:00 WIB">
            @include('admin.partials.error', ['name' => 'contact_hours'])
        </label>

        <label class="field">
            <span>Alamat toko (opsional)</span>
            <textarea name="store_address" rows="3" maxlength="500" placeholder="Alamat lengkap...">{{ old('store_address', $settings['store_address']) }}</textarea>
            @include('admin.partials.error', ['name' => 'store_address'])
        </label>

        <h3 style="margin-top:20px;">Sosial Media</h3>

        <div class="field-row">
            <label class="field">
                <span>Instagram (URL)</span>
                <input type="url" name="social_instagram" value="{{ old('social_instagram', $settings['social_instagram']) }}" maxlength="255" placeholder="https://instagram.com/mercatoria.id">
                @include('admin.partials.error', ['name' => 'social_instagram'])
            </label>
            <label class="field">
                <span>TikTok (URL)</span>
                <input type="url" name="social_tiktok" value="{{ old('social_tiktok', $settings['social_tiktok']) }}" maxlength="255" placeholder="https://tiktok.com/@mercatoria">
                @include('admin.partials.error', ['name' => 'social_tiktok'])
            </label>
        </div>

        <div class="field-row">
            <label class="field">
                <span>Facebook (URL)</span>
                <input type="url" name="social_facebook" value="{{ old('social_facebook', $settings['social_facebook']) }}" maxlength="255">
                @include('admin.partials.error', ['name' => 'social_facebook'])
            </label>
            <label class="field">
                <span>X / Twitter (URL)</span>
                <input type="url" name="social_x" value="{{ old('social_x', $settings['social_x']) }}" maxlength="255">
                @include('admin.partials.error', ['name' => 'social_x'])
            </label>
        </div>

        <div class="savebar">
            <button type="submit" class="btn btn--primary">Simpan pengaturan</button>
        </div>
    </form>
@endsection