@extends('layouts.admin')

@section('title', 'Pengaturan SEO')

@section('content')
    <div>
        <h1 class="text-2xl font-bold text-navy-500">Pengaturan SEO</h1>
        <p class="mt-1 text-sm text-text-muted">Metadata default dipakai ketika halaman tidak memiliki metadata khusus.</p>
    </div>
    @if ($errors->any())
        <div role="alert" class="mt-4 rounded-lg bg-danger-50 p-4 text-sm text-danger-700">
            <ul class="list-inside list-disc">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif
    <form method="POST" action="{{ route('office.seo.update') }}" class="mt-5 space-y-4 rounded-xl border border-border bg-white p-5">
        @csrf
        @method('PATCH')
        <label class="block text-sm font-medium">Default SEO title
            <input name="seo_default_title" value="{{ old('seo_default_title', $settings['seo_default_title'] ?? 'Mercatoria — Marketplace Merchandise IP Cina') }}" maxlength="255" required
                   class="mt-1 w-full rounded-lg border border-border px-3 py-2">
        </label>
        <label class="block text-sm font-medium">Default SEO description
            <textarea name="seo_default_description" rows="3" maxlength="500" required
                      class="mt-1 w-full rounded-lg border border-border px-3 py-2">{{ old('seo_default_description', $settings['seo_default_description'] ?? 'Temukan dan beli merchandise game, anime, donghua, dan novel dari Tiongkok melalui Mercatoria.') }}</textarea>
        </label>
        <label class="block text-sm font-medium">Default Open Graph image URL
            <input type="url" name="seo_default_og_image" value="{{ old('seo_default_og_image', $settings['seo_default_og_image'] ?? '') }}" maxlength="2048"
                   class="mt-1 w-full rounded-lg border border-border px-3 py-2">
        </label>
        <x-button type="submit">Simpan Metadata</x-button>
    </form>
@endsection
