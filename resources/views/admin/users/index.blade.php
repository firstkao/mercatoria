@extends('admin.layouts.admin')

@section('title', 'Users')

@section('content')
    <div class="flex items-center justify-between">
        <h1 class="text-xl font-semibold">Users</h1>
        <a href="{{ route('office.users.create') }}" class="rounded-lg bg-black px-4 py-2 text-sm font-semibold text-white">+ Tambah User</a>
    </div>

    <form method="GET" class="mt-4 flex gap-2">
        <input type="text" name="q" value="{{ $q }}" placeholder="Cari nama / email / HP"
               class="w-72 rounded-lg border border-gray-200 px-3 py-2 text-sm">
        <button class="rounded-lg border border-gray-300 px-4 py-2 text-sm hover:border-black">Cari</button>
    </form>

    <div class="mt-4 overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-100">
        <table class="w-full text-sm">
            <thead class="border-b border-gray-100 text-left text-gray-500">
                <tr>
                    <th class="px-3 py-2">Nama</th>
                    <th class="px-3 py-2">Email</th>
                    <th class="px-3 py-2">No HP</th>
                    <th class="px-3 py-2">Role</th>
                    <th class="px-3 py-2"></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                    <tr class="border-b border-gray-50">
                        <td class="px-3 py-2 font-medium">{{ $user->name }}</td>
                        <td class="px-3 py-2">{{ $user->email }}</td>
                        <td class="px-3 py-2">{{ $user->no_hp }}</td>
                        <td class="px-3 py-2">{{ $user->role }}</td>
                        <td class="px-3 py-2 text-right">
                            <a href="{{ route('office.users.edit', $user) }}" class="text-sky-600 hover:underline">Edit</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $users->links() }}</div>
@endsection
