@extends('layouts.admin')

@section('title', $template->exists ? 'Edit Template Email' : 'Template Email Baru')

@section('content')
    <div>
        <a href="{{ route('office.email-templates.index') }}" class="text-sm text-navy-500 hover:underline">← Template Email</a>
        <h1 class="mt-2 text-2xl font-bold text-navy-500">{{ $template->exists ? 'Edit Template Email' : 'Template Email Baru' }}</h1>
    </div>
    @if ($errors->any())
        <div role="alert" class="mt-4 rounded-lg bg-danger-50 p-4 text-sm text-danger-700">
            <ul class="list-inside list-disc">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif
    <form method="POST" action="{{ $template->exists ? route('office.email-templates.update', $template) : route('office.email-templates.store') }}"
          class="mt-5 space-y-4 rounded-xl border border-border bg-white p-5">
        @csrf
        @if ($template->exists) @method('PUT') @endif
        <div class="grid gap-4 md:grid-cols-2">
            <label class="text-sm font-medium">Kode
                <input name="code" value="{{ old('code', $template->code) }}" required maxlength="64" pattern="[a-zA-Z0-9_-]+"
                       class="mt-1 w-full rounded-lg border border-border px-3 py-2">
            </label>
            <label class="text-sm font-medium">Nama
                <input name="name" value="{{ old('name', $template->name) }}" required maxlength="255"
                       class="mt-1 w-full rounded-lg border border-border px-3 py-2">
            </label>
            <label class="text-sm font-medium">Email pengirim
                <input type="email" name="from_email" value="{{ old('from_email', $template->from_email) }}" required maxlength="255"
                       class="mt-1 w-full rounded-lg border border-border px-3 py-2">
            </label>
            <label class="text-sm font-medium">Subject
                <input name="subject" value="{{ old('subject', $template->subject) }}" required maxlength="255"
                       class="mt-1 w-full rounded-lg border border-border px-3 py-2">
            </label>
        </div>
        <label class="block text-sm font-medium">Body HTML
            <textarea name="body" rows="16" required maxlength="100000"
                      class="mt-1 w-full rounded-lg border border-border px-3 py-2 font-mono text-sm">{{ old('body', $template->body) }}</textarea>
        </label>
        <label class="block text-sm font-medium">Variables (satu nama per baris)
            <textarea name="variables_text" rows="3" class="mt-1 w-full rounded-lg border border-border px-3 py-2">{{ old('variables_text', implode("\n", $template->variables ?? [])) }}</textarea>
            <span class="mt-1 block text-xs text-text-light">Placeholder tersedia: @{{nama_user}}, @{{email}}, @{{no_hp}}, @{{no_order}}, @{{total}}, @{{status}}, @{{state}}, @{{link}}, @{{tanggal}}, @{{nama_toko}}.</span>
        </label>
        <label class="flex items-center gap-2 text-sm font-medium">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $template->is_active ?? true))>
            Template aktif
        </label>
        <div class="flex gap-3">
            <x-button type="submit">Simpan</x-button>
            <x-button variant="ghost" href="{{ route('office.email-templates.index') }}">Batal</x-button>
        </div>
    </form>
@endsection
