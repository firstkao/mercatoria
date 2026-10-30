@extends('admin.layouts.admin')

@section('title', 'Admin Log')

@section('content')
    <h1 class="text-xl font-semibold">Admin Log</h1>

    <div class="mt-4 overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-100">
        <table class="w-full text-sm">
            <thead class="border-b border-gray-100 text-left text-gray-500">
                <tr>
                    <th class="px-3 py-2">Waktu</th>
                    <th class="px-3 py-2">Admin</th>
                    <th class="px-3 py-2">Aksi</th>
                    <th class="px-3 py-2">Model</th>
                    <th class="px-3 py-2">Deskripsi</th>
                    <th class="px-3 py-2">Old → New</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($logs as $log)
                    <tr class="border-b border-gray-50 align-top">
                        <td class="px-3 py-2">{{ $log->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-3 py-2">{{ $log->admin->name ?? '-' }}</td>
                        <td class="px-3 py-2"><x-log-label :action="$log->action" /></td>
                        <td class="px-3 py-2">{{ $log->model }} #{{ $log->model_id }}</td>
                        <td class="px-3 py-2">{{ $log->description }}</td>
                        <td class="px-3 py-2 text-xs">
                            @if ($log->old_value || $log->new_value)
                                <span class="text-red-600">{{ json_encode($log->old_value) }}</span>
                                →
                                <span class="text-green-700">{{ json_encode($log->new_value) }}</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $logs->links() }}</div>
@endsection
