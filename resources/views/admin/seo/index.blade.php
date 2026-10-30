@extends('admin.layouts.admin')

@section('title', 'SEO')

@section('content')
    <p class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink/45">Pengaturan</p>
    <h1 class="mt-2 font-display text-[1.5rem] font-semibold text-ink">SEO</h1>

    <form method="POST" action="{{ route('office.seo.update') }}" class="mt-4 max-w-lg rounded-xl border border-rule bg-white p-4">
        @csrf
        <label for="ga4" class="block font-mono text-[10px] font-medium uppercase tracking-[0.14em] text-ink/70">GA4 Measurement ID</label>
        <input type="text" name="ga4_measurement_id" id="ga4" value="{{ $ga4 }}" maxlength="40" placeholder="G-XXXXXXXXXX"
               class="mt-2 w-full rounded-lg border border-rule px-3 py-2 text-sm text-ink">
        <button class="mt-3 rounded-lg bg-black px-4 py-2 text-sm font-semibold tracking-[0.12em] text-white transition-colors hover:bg-ink">Simpan</button>
    </form>
@endsection
