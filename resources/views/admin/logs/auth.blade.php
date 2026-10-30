@extends('admin.layouts.admin')

@section('title', 'Auth Log')

@section('content')
    <h1 class="text-xl font-semibold">Auth Log</h1>

    <div class="mt-4 overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-100">
        <table class="w-full text-sm">
            <thead class="border-b border-gray-100 text-left text-gray-500">
                <tr>
                    <th class="px-3 py-2">Waktu</th>
                    <th class="px-3 py-2">Email</th>
                    <th class="px-3 py-2">Aksi</th>
                    <th class="px-3 py-2">Alasan</th>
                    <th class="px-3 py-2">IP</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($logs as $log)
                    <tr class="border-b border-gray-50 {{ $log->action === 'login_failed' ? 'bg-red-50' : '' }}">
                        <td class="px-3 py-2">{{ $log->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-3 py-2">{{ $log->email_attempt }}</td>
                        <td class="px-3 py-2"><x-log-label :action="$log->action" /></td>
                        <td class="px-3 py-2"><x-log-label :reason="$log->reason" /></td>
                        <td class="px-3 py-2">{{ $log->ip_address }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $logs->links() }}</div>
@endsection
