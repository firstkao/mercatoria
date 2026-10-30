@extends('admin.layouts.admin')

@section('title', 'Activity Log')

@section('content')
    <h1 class="text-xl font-semibold">Activity Log</h1>
    <p class="mt-1 text-sm text-gray-500">Instrumentasi aksi user menyusul (PRD 39.5) &mdash; tabel & halaman sudah siap.</p>

    <div class="mt-4 overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-100">
        <table class="w-full text-sm">
            <thead class="border-b border-gray-100 text-left text-gray-500">
                <tr>
                    <th class="px-3 py-2">Waktu</th>
                    <th class="px-3 py-2">User</th>
                    <th class="px-3 py-2">Aksi</th>
                    <th class="px-3 py-2">Deskripsi</th>
                    <th class="px-3 py-2">URL</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($logs as $log)
                    <tr class="border-b border-gray-50">
                        <td class="px-3 py-2">{{ $log->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-3 py-2">{{ $log->user->name ?? '-' }}</td>
                        <td class="px-3 py-2">{{ $log->action }}</td>
                        <td class="px-3 py-2">{{ $log->description }}</td>
                        <td class="px-3 py-2">{{ $log->url }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-3 py-6 text-center text-gray-400">Belum ada data.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $logs->links() }}</div>
@endsection
