@extends('layouts.admin')

@section('title', $announcement->exists ? 'Edit Announcement' : 'Tambah Announcement')

@section('content')
<h1 class="mb-5 text-2xl font-bold">{{ $announcement->exists ? 'Edit Announcement' : 'Tambah Announcement' }}</h1>
<form method="POST" action="{{ $announcement->exists ? route('office.announcements.update', $announcement) : route('office.announcements.store') }}" class="max-w-2xl space-y-4 rounded-xl border border-border bg-white p-6">
    @csrf
    @if ($announcement->exists) @method('PUT') @endif
    <label class="block text-sm">Pesan promo
        <input name="text" value="{{ old('text', $announcement->text) }}" maxlength="255" required class="mt-1 w-full rounded-lg border border-border px-3 py-2">
        @error('text')<span class="text-xs text-danger-500">{{ $message }}</span>@enderror
    </label>
    <label class="block text-sm">URL tujuan (opsional)
        <input name="link_url" value="{{ old('link_url', $announcement->link_url) }}" maxlength="500" class="mt-1 w-full rounded-lg border border-border px-3 py-2">
        @error('link_url')<span class="text-xs text-danger-500">{{ $message }}</span>@enderror
    </label>
    <div class="grid gap-4 sm:grid-cols-2">
        <label class="block text-sm">Mulai
            <input name="starts_at" type="datetime-local" value="{{ old('starts_at', $announcement->starts_at?->format('Y-m-d\TH:i')) }}" class="mt-1 w-full rounded-lg border border-border px-3 py-2">
            @error('starts_at')<span class="text-xs text-danger-500">{{ $message }}</span>@enderror
        </label>
        <label class="block text-sm">Berakhir
            <input name="ends_at" type="datetime-local" value="{{ old('ends_at', $announcement->ends_at?->format('Y-m-d\TH:i')) }}" class="mt-1 w-full rounded-lg border border-border px-3 py-2">
            @error('ends_at')<span class="text-xs text-danger-500">{{ $message }}</span>@enderror
        </label>
    </div>
    <input type="hidden" name="is_active" value="0">
    <label class="flex items-center gap-2 text-sm"><input name="is_active" type="checkbox" value="1" @checked(old('is_active', $announcement->is_active ?? true))> Aktif</label>
    @error('is_active')<span class="text-xs text-danger-500">{{ $message }}</span>@enderror
    <div class="flex gap-3"><button class="rounded-lg bg-navy-500 px-4 py-2 text-sm font-medium text-white">Simpan</button><a href="{{ route('office.announcements.index') }}" class="rounded-lg border border-border px-4 py-2 text-sm">Batal</a></div>
</form>
@endsection
