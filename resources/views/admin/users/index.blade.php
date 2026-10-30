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
                            @if (\Illuminate\Support\Facades\Route::has('office.users.notify'))
                                <button type="button" class="ml-2 text-link underline decoration-link/35 underline-offset-4"
                                        onclick="document.getElementById('notify-{{ $user->id }}').showModal()">Notif</button>
                            @endif
                            @if (\App\Models\Spammer::blocksIdentity($user->email, $user->no_hp))
                                <button type="button" class="ml-2 text-red-600 hover:underline"
                                        onclick="document.getElementById('del-{{ $user->id }}').showModal()">Hard Delete</button>
                                <button type="button" class="ml-2 text-red-700 hover:underline"
                                        onclick="document.getElementById('fdel-{{ $user->id }}').showModal()">Forever Delete</button>

                                <dialog id="del-{{ $user->id }}" class="rounded-xl border border-gray-200 p-0">
                                    <form method="POST" action="{{ route('office.users.destroy', $user) }}" class="p-5">
                                        @csrf @method('DELETE')
                                        <p class="text-sm font-semibold">Hard Delete &ldquo;{{ $user->name }}&rdquo;?</p>
                                        <p class="mt-1 text-xs text-gray-500">Ketik email persis untuk konfirmasi.</p>
                                        <input type="text" name="confirm_email" placeholder="{{ $user->email }}" required
                                               oninput="this.form.querySelector('[type=submit]').disabled = this.value !== '{{ $user->email }}'"
                                               class="mt-2 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                                        <div class="mt-3 flex justify-end gap-2">
                                            <button type="button" class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs" onclick="document.getElementById('del-{{ $user->id }}').close()">Batal</button>
                                            <button type="submit" disabled class="rounded-lg bg-red-600 px-3 py-1.5 text-xs text-white">Hapus</button>
                                        </div>
                                    </form>
                                </dialog>

                                <dialog id="fdel-{{ $user->id }}" class="rounded-xl border border-gray-200 p-0">
                                    <form method="POST" action="{{ route('office.users.forever-delete', $user) }}" class="p-5">
                                        @csrf
                                        <p class="text-sm font-semibold">Forever Delete &ldquo;{{ $user->name }}&rdquo;?</p>
                                        <p class="mt-1 text-xs text-gray-500">Tidak dapat dibatalkan. Ketik email persis untuk konfirmasi.</p>
                                        <input type="text" name="confirm_email" placeholder="{{ $user->email }}" required
                                               oninput="this.form.querySelector('[type=submit]').disabled = this.value !== '{{ $user->email }}'"
                                               class="mt-2 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                                        <div class="mt-3 flex justify-end gap-2">
                                            <button type="button" class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs" onclick="document.getElementById('fdel-{{ $user->id }}').close()">Batal</button>
                                            <button type="submit" disabled class="rounded-lg bg-red-700 px-3 py-1.5 text-xs text-white">Hapus Permanen</button>
                                        </div>
                                    </form>
                                </dialog>
                            @endif

                            @if (\Illuminate\Support\Facades\Route::has('office.users.notify'))
                                <dialog id="notify-{{ $user->id }}" class="rounded-xl border border-rule p-0">
                                    <form method="POST" action="{{ route('office.users.notify', $user) }}" class="p-5">
                                        @csrf
                                        <p class="font-mono text-[10px] uppercase tracking-[0.14em] text-ink/70">Kirim Notifikasi</p>
                                        <input type="text" name="title" placeholder="Judul notifikasi" required maxlength="255"
                                               class="mt-2 w-full rounded-lg border border-rule px-3 py-2 text-sm text-ink">
                                        <textarea name="message" rows="3" required maxlength="1000" placeholder="Pesan..."
                                                  class="mt-2 w-full rounded-lg border border-rule px-3 py-2 text-sm text-ink"></textarea>
                                        <input type="text" name="link" placeholder="Link (opsional)" maxlength="255"
                                               class="mt-2 w-full rounded-lg border border-rule px-3 py-2 text-sm text-ink">
                                        <div class="mt-3 flex justify-end gap-2">
                                            <button type="button" class="rounded-lg border border-rule px-3 py-1.5 text-xs font-semibold text-ink" onclick="document.getElementById('notify-{{ $user->id }}').close()">Batal</button>
                                            <button type="submit" class="rounded-lg bg-black px-3 py-1.5 text-xs font-semibold tracking-[0.12em] text-white">Kirim</button>
                                        </div>
                                    </form>
                                </dialog>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $users->links() }}</div>
@endsection
