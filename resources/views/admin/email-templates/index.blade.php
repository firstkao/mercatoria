@extends('admin.layouts.admin')

@section('title', 'Template Email')

@section('content')
    <p class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink/45">Konten</p>
    <h1 class="mt-2 font-display text-[1.5rem] font-semibold text-ink">Template Email</h1>

    <div class="mt-4 rounded-lg border border-rule bg-paper px-4 py-3">
        <p class="font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">Placeholder tersedia</p>
        <p class="mt-1 text-xs text-ink/65">@{{nama_user}} · @{{email_user}} · @{{no_order}} · @{{kode_otp}} · @{{link_verifikasi}} · @{{judul}}</p>
    </div>

    <form method="POST" action="{{ route('office.email-templates.store') }}" class="mt-4 space-y-2 rounded-xl border border-rule bg-white p-4">
        @csrf
        <div class="flex flex-wrap gap-2">
            <input type="text" name="code" placeholder="Code (order-created)" required class="w-48 rounded-lg border border-rule px-3 py-2 text-sm text-ink">
            <input type="text" name="subject" placeholder="Subjek" required class="flex-1 rounded-lg border border-rule px-3 py-2 text-sm text-ink">
            <label class="flex items-center gap-1 py-2 text-sm text-ink/80"><input type="checkbox" name="is_active" value="1" checked class="rounded border-rule"> Aktif</label>
        </div>
        <textarea name="body" rows="3" placeholder="Isi email (HTML/plain)" class="w-full rounded-lg border border-rule px-3 py-2 text-sm text-ink"></textarea>
        <button class="rounded-lg bg-black px-4 py-2 text-sm font-semibold tracking-[0.12em] text-white transition-colors hover:bg-ink">+ Tambah</button>
    </form>

    <div class="mt-4 space-y-3">
        @forelse ($templates as $template)
            <div class="rounded-xl border border-rule bg-white p-4">
                <form method="POST" action="{{ route('office.email-templates.update', $template) }}" class="space-y-2">
                    @csrf @method('PUT')
                    <div class="flex flex-wrap gap-2">
                        <input type="text" name="code" value="{{ $template->code }}" required class="w-48 rounded-lg border border-rule px-3 py-2 text-sm text-ink">
                        <input type="text" name="subject" value="{{ $template->subject }}" required class="flex-1 rounded-lg border border-rule px-3 py-2 text-sm text-ink">
                        <label class="flex items-center gap-1 py-2 text-sm text-ink/80"><input type="checkbox" name="is_active" value="1" @checked($template->is_active) class="rounded border-rule"> Aktif</label>
                    </div>
                    <textarea name="body" rows="3" class="w-full rounded-lg border border-rule px-3 py-2 text-sm text-ink">{{ $template->body }}</textarea>
                    <div class="flex items-center justify-between">
                        <button class="rounded-lg border border-rule px-4 py-2 text-sm font-semibold text-ink transition-colors hover:border-ink">Simpan</button>
                        <form method="POST" action="{{ route('office.email-templates.destroy', $template) }}" class="inline">
                            @csrf @method('DELETE')
                            <button class="text-xs text-danger hover:underline" onclick="return confirm('Hapus template ini?')">Hapus</button>
                        </form>
                    </div>
                </form>
            </div>
        @empty
            <div class="rounded-xl border border-rule bg-white p-8 text-center text-sm text-ink/45">Belum ada template email.</div>
        @endforelse
    </div>
@endsection
