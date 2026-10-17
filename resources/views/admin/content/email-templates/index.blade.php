@extends('layouts.admin')

@section('title', 'Template Email')

@section('content')
    <div class="flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-navy-500">Template Email</h1>
            <p class="mt-1 text-sm text-text-muted">Edit template, lihat preview, kirim email tes, dan periksa log.</p>
        </div>
        <x-button href="{{ route('office.email-templates.create') }}">+ Template Baru</x-button>
    </div>
    <div class="mt-5 overflow-x-auto rounded-xl border border-border bg-white">
        <table class="w-full min-w-[900px] text-left text-sm">
            <thead class="bg-navy-500 text-white">
                <tr><th class="px-4 py-3">Kode</th><th class="px-4 py-3">Nama / Subject</th><th class="px-4 py-3">Pengirim</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Log</th><th class="px-4 py-3">Aksi</th></tr>
            </thead>
            <tbody class="divide-y divide-border">
                @forelse ($templates as $template)
                    <tr>
                        <td class="px-4 py-3 font-mono">{{ $template->code }}</td>
                        <td class="px-4 py-3"><span class="block font-semibold">{{ $template->name }}</span><span class="text-xs text-text-muted">{{ $template->subject }}</span></td>
                        <td class="px-4 py-3">{{ $template->from_email }}</td>
                        <td class="px-4 py-3">{{ $template->is_active ? 'Aktif' : 'Nonaktif' }}</td>
                        <td class="px-4 py-3">{{ $template->logs_count }}</td>
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap gap-3">
                                <a class="text-navy-500 hover:underline" href="{{ route('office.email-templates.edit', $template) }}">Edit</a>
                                <form method="POST" action="{{ route('office.email-templates.preview', $template) }}" target="_blank">
                                    @csrf <button class="text-navy-500 hover:underline">Preview</button>
                                </form>
                                <form method="POST" action="{{ route('office.email-templates.test-send', $template) }}" class="flex gap-2">
                                    @csrf
                                    <input type="email" name="recipient_email" required placeholder="email tes"
                                           class="w-36 rounded border border-border px-2 py-1 text-xs">
                                    <button class="text-navy-500 hover:underline">Kirim tes</button>
                                </form>
                                <form method="POST" action="{{ route('office.email-templates.destroy', $template) }}" onsubmit="return confirm('Hapus template ini?')">
                                    @csrf @method('DELETE') <button class="text-danger-500 hover:underline">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-text-muted">Belum ada template email.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-5">{{ $templates->links() }}</div>
@endsection
