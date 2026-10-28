@extends('admin.layouts.admin')

@section('title', 'Tag')

@section('content')
    <h1 class="text-xl font-semibold">Tag</h1>

    <form method="POST" action="{{ route('office.tags.store') }}" class="mt-4 flex items-end gap-2 rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
        @csrf
        <input type="text" name="name" placeholder="Nama tag (mis. PRESALE)" required class="rounded-lg border border-gray-200 px-3 py-2 text-sm">
        <button class="rounded-lg bg-black px-4 py-2 text-sm font-semibold text-white">+ Tambah</button>
    </form>

    <div class="mt-4 space-y-2">
        @foreach ($tags as $tag)
            <div class="flex items-center justify-between rounded-xl bg-white px-4 py-3 shadow-sm ring-1 ring-gray-100">
                <div>
                    <span class="text-sm font-medium">{{ $tag->name }}</span>
                    <span class="ml-2 text-xs text-gray-400">{{ $tag->slug }}</span>
                </div>
                <form method="POST" action="{{ route('office.tags.destroy', $tag) }}" onsubmit="return confirm('Hapus tag?')">
                    @csrf @method('DELETE')
                    <button class="text-xs text-red-600 hover:underline">Hapus</button>
                </form>
            </div>
        @endforeach
    </div>
@endsection
