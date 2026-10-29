@extends('admin.layouts.admin')

@section('title', 'Metode Bayar')

@section('content')
    <p class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink/45">Pengaturan</p>
    <h1 class="mt-2 font-display text-[1.5rem] font-semibold text-ink">Metode Bayar</h1>

    <form method="POST" action="{{ route('office.payment-methods.store') }}" class="mt-4 flex flex-wrap items-end gap-2 rounded-xl border border-rule bg-white p-4">
        @csrf
        <input type="text" name="code" placeholder="Code (bca)" required class="w-28 rounded-lg border border-rule px-3 py-2 text-sm text-ink">
        <input type="text" name="name" placeholder="Nama" required class="w-40 rounded-lg border border-rule px-3 py-2 text-sm text-ink">
        <select name="type" class="rounded-lg border border-rule px-3 py-2 text-sm text-ink">
            <option value="bank_transfer">Bank Transfer</option>
            <option value="qris">QRIS</option>
            <option value="ewallet">E-Wallet</option>
        </select>
        <input type="number" step="0.01" name="fee" placeholder="Fee" class="w-24 rounded-lg border border-rule px-3 py-2 text-sm text-ink">
        <input type="text" name="instructions" placeholder="Instruksi" class="flex-1 rounded-lg border border-rule px-3 py-2 text-sm text-ink">
        <label class="flex items-center gap-1 py-2 text-sm text-ink/80"><input type="checkbox" name="is_active" value="1" checked class="rounded border-rule"> Aktif</label>
        <button class="rounded-lg bg-black px-4 py-2 text-sm font-semibold tracking-[0.12em] text-white transition-colors hover:bg-ink">+ Tambah</button>
    </form>

    <div class="mt-4 space-y-3">
        @forelse ($methods as $method)
            <div class="rounded-xl border border-rule bg-white p-4">
                <form method="POST" action="{{ route('office.payment-methods.update', $method) }}" class="flex flex-wrap items-end gap-2">
                    @csrf @method('PUT')
                    <input type="text" name="code" value="{{ $method->code }}" required class="w-28 rounded-lg border border-rule px-3 py-2 text-sm text-ink">
                    <input type="text" name="name" value="{{ $method->name }}" required class="w-40 rounded-lg border border-rule px-3 py-2 text-sm text-ink">
                    <select name="type" class="rounded-lg border border-rule px-3 py-2 text-sm text-ink">
                        @foreach (['bank_transfer', 'qris', 'ewallet'] as $t)
                            <option value="{{ $t }}" @selected($method->type === $t)>{{ $t }}</option>
                        @endforeach
                    </select>
                    <input type="number" step="0.01" name="fee" value="{{ $method->fee }}" class="w-24 rounded-lg border border-rule px-3 py-2 text-sm text-ink">
                    <input type="text" name="instructions" value="{{ $method->instructions }}" class="flex-1 rounded-lg border border-rule px-3 py-2 text-sm text-ink">
                    <label class="flex items-center gap-1 py-2 text-sm text-ink/80"><input type="checkbox" name="is_active" value="1" @checked($method->is_active) class="rounded border-rule"> Aktif</label>
                    <button class="rounded-lg border border-rule px-4 py-2 text-sm font-semibold text-ink transition-colors hover:border-ink">Simpan</button>
                </form>
                <form method="POST" action="{{ route('office.payment-methods.destroy', $method) }}" class="mt-2 text-right">
                    @csrf @method('DELETE')
                    <button class="text-xs text-danger hover:underline" onclick="return confirm('Hapus metode bayar ini?')">Hapus</button>
                </form>
            </div>
        @empty
            <div class="rounded-xl border border-rule bg-white p-8 text-center text-sm text-ink/45">Belum ada metode bayar.</div>
        @endforelse
    </div>
@endsection
