@extends('admin.layouts.admin')

@section('title', 'Marketing')

@section('content')
    <p class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink/45">Pengaturan</p>
    <h1 class="mt-2 font-display text-[1.5rem] font-semibold text-ink">Marketing</h1>

    <form method="POST" action="{{ route('office.marketing.update') }}" class="mt-4 max-w-lg space-y-3 rounded-xl border border-rule bg-white p-4">
        @csrf
        <div>
            <label for="promo_text" class="block font-mono text-[10px] font-medium uppercase tracking-[0.14em] text-ink/70">Teks Promo Bar</label>
            <input type="text" name="promo_bar_text" id="promo_text" value="{{ $promo_bar_text }}" maxlength="255"
                   class="mt-2 w-full rounded-lg border border-rule px-3 py-2 text-sm text-ink">
        </div>
        <div>
            <label for="promo_link" class="block font-mono text-[10px] font-medium uppercase tracking-[0.14em] text-ink/70">Link Promo (opsional)</label>
            <input type="text" name="promo_bar_link" id="promo_link" value="{{ $promo_bar_link }}" maxlength="255"
                   class="mt-2 w-full rounded-lg border border-rule px-3 py-2 text-sm text-ink">
        </div>
        <label class="flex items-center gap-2 text-sm text-ink/80">
            <input type="checkbox" name="promo_bar_active" value="1" @checked($promo_bar_active !== '0') class="rounded border-rule">
            Promo bar aktif
        </label>
        <button class="rounded-lg bg-black px-4 py-2 text-sm font-semibold tracking-[0.12em] text-white transition-colors hover:bg-ink">Simpan</button>
    </form>
@endsection
