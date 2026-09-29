@extends('admin.layouts.app', ['title' => 'Pengaturan'])

@section('tabs')
    @include('admin.settings.tabs')
@endsection

@section('content')
    <form method="POST" action="{{ route('admin.settings.seo.update') }}" class="panel stack" novalidate>
        @csrf
        @method('PUT')
        <h2>SEO Default</h2>
        <p class="hint">Info ini dipakai untuk homepage & halaman yang tidak punya meta sendiri.</p>

        <label class="field">
            <span>Judul default (max 60 karakter ideal)</span>
            <input type="text" name="meta_title" value="{{ old('meta_title', $settings['meta_title']) }}" maxlength="150" placeholder="MERCATORIA — Merchandise Game Original">
            @include('admin.partials.error', ['name' => 'meta_title'])
        </label>

        <label class="field">
            <span>Deskripsi default (max 160 karakter ideal)</span>
            <textarea name="meta_description" rows="3" maxlength="300" placeholder="Group Order Manager untuk merchandise game original dari Tmall & Taobao...">{{ old('meta_description', $settings['meta_description']) }}</textarea>
            @include('admin.partials.error', ['name' => 'meta_description'])
        </label>

        <label class="field">
            <span>Kata kunci (opsional, dipisah koma)</span>
            <input type="text" name="meta_keywords" value="{{ old('meta_keywords', $settings['meta_keywords']) }}" maxlength="300" placeholder="merchandise game, tmall, genshin impact, hsr">
            @include('admin.partials.error', ['name' => 'meta_keywords'])
        </label>

        <label class="field">
            <span>URL gambar preview (opsional)</span>
            <input type="url" name="meta_image" value="{{ old('meta_image', $settings['meta_image']) }}" maxlength="500" placeholder="https://...">
            <small class="hint">Gambar yang muncul saat link dibagikan di WhatsApp / Twitter / Facebook. Ukuran ideal 1200×630.</small>
            @include('admin.partials.error', ['name' => 'meta_image'])
        </label>

        <div class="savebar">
            <button type="submit" class="btn btn--primary">Simpan SEO</button>
        </div>
    </form>
@endsection