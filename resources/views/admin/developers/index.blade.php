@extends('admin.layouts.admin')

@section('title', 'Developer')

@section('content')
    <p class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink/45">Katalog</p>
    <h1 class="mt-2 font-display text-[1.5rem] font-semibold text-ink">Developer</h1>

    {{-- FORM TAMBAH BARU --}}
    <form method="POST" action="{{ route('office.developers.store') }}" enctype="multipart/form-data" class="mt-4 rounded-xl border border-rule bg-white p-4">
        @csrf
        <div class="flex flex-wrap items-end gap-2">
            <div>
                <label class="block text-xs text-ink/55">Nama <span class="text-danger">*</span></label>
                <input type="text" name="name" id="dev-name-input" placeholder="Nama developer" required
                       class="mt-1 w-56 rounded-lg border border-rule px-3 py-2 text-sm text-ink">
            </div>
            <div>
                <label class="block text-xs text-ink/55">Slug <span class="text-[10px] text-ink/40">otomatis</span></label>
                <input type="text" name="slug" id="dev-slug-input" placeholder="slug (opsional)"
                       class="mt-1 w-40 rounded-lg border border-rule bg-paper/30 px-3 py-2 text-sm text-ink">
            </div>
            <div>
                <label class="block text-xs text-ink/55">Logo</label>
                <input type="file" name="logo" accept=".jpg,.jpeg,.png,.webp"
                       class="mt-1 block rounded-lg border border-rule px-2 py-1.5 text-xs text-ink">
            </div>
            <label class="flex items-center gap-1 pb-2 text-sm text-ink/80">
                <input type="checkbox" name="is_active" value="1" checked class="rounded border-rule"> Aktif
            </label>
            <button class="rounded-lg bg-black px-4 py-2 text-sm font-semibold tracking-[0.12em] text-white transition-colors hover:bg-ink">
                + Tambah
            </button>
        </div>
    </form>

    {{-- BULK ACTIONS --}}
    <div class="mt-4 flex flex-wrap items-center gap-2">
        <label class="flex items-center gap-2 text-sm text-ink/70">
            <input type="checkbox" id="dev-select-all" class="rounded border-rule"> Pilih Semua
        </label>
        <button type="button" id="dev-bulk-save"
                class="rounded-lg bg-black px-4 py-2 text-sm font-semibold tracking-[0.12em] text-white transition-colors hover:bg-ink">
            Simpan Semua
        </button>
        <button type="button" id="dev-bulk-delete"
                class="rounded-lg border border-danger/40 px-4 py-2 text-sm font-semibold text-danger transition-colors hover:border-danger">
            Hapus Terpilih
        </button>
        <span class="text-xs text-ink/45">Centang baris untuk bulk edit / hapus.</span>
    </div>

    {{-- LIST --}}
    <div class="mt-3 space-y-3" id="dev-list">
        @foreach ($developers as $developer)
            <div class="rounded-xl border border-rule bg-white p-4" data-dev-row>
                <div class="flex flex-wrap items-center gap-2">
                    <input type="checkbox" class="dev-checkbox rounded border-rule" data-id="{{ $developer->id }}">

                    @if ($developer->logo_path)
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($developer->logo_path) }}"
                             alt="{{ $developer->name }}"
                             class="h-10 w-10 rounded border border-rule object-cover">
                    @else
                        <div class="flex h-10 w-10 items-center justify-center rounded border border-rule bg-paper text-[10px] text-ink/30">N/A</div>
                    @endif

                    <input type="text" value="{{ $developer->name }}" data-field="name" data-orig="{{ $developer->name }}"
                           class="w-48 rounded-lg border border-rule px-2 py-1.5 text-sm text-ink">

                    <input type="text" value="{{ $developer->slug }}" data-field="slug" data-orig="{{ $developer->slug }}" placeholder="slug"
                           class="w-36 rounded-lg border border-rule px-2 py-1.5 text-sm text-ink">

                    <div>
                        <label class="block text-[9px] text-ink/40">Logo baru</label>
                        <input type="file" data-field="logo" accept=".jpg,.jpeg,.png,.webp"
                               class="block w-32 rounded border border-rule px-1 py-0.5 text-[10px] text-ink">
                    </div>

                    <label class="flex items-center gap-1 py-2 text-sm text-ink/80">
                        <input type="checkbox" data-field="is_active" data-orig="{{ $developer->is_active ? '1' : '0' }}"
                               @checked($developer->is_active) class="rounded border-rule"> Aktif
                    </label>

                    <span class="ml-auto text-xs text-ink/45 dev-status" data-id="{{ $developer->id }}"></span>
                </div>
            </div>
        @endforeach

        @if ($developers->isEmpty())
            <div class="rounded-xl border border-rule bg-white p-8 text-center">
                <i class="bi bi-code-slash text-ink/20" style="font-size:2.5rem;"></i>
                <p class="mt-3 text-sm text-ink/50">Belum ada developer.</p>
                <p class="mt-1 text-xs text-ink/40">Tambahkan developer pertama di form atas.</p>
            </div>
        @endif
    </div>

    {{-- CONFIG UNTUK JS EKSTERNAL --}}
    @php
        $devConfig = [
            'csrf'  => csrf_token(),
            'baseUrl' => route('office.developers.index'),
        ];
    @endphp
    <script type="application/json" id="dev-page-config">{!! json_encode($devConfig) !!}</script>
    <script src="{{ asset('js/admin/developer-index.js') }}"></script>
@endsection