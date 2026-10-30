@extends('admin.layouts.admin')

@section('title', 'Users')

@section('content')
    <div class="flex items-center justify-between">
        <div>
            <p class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink/45">Pengguna</p>
            <h1 class="mt-2 font-display text-[1.5rem] font-semibold text-ink">Users</h1>
        </div>
        <a href="{{ route('office.users.create') }}" class="rounded-lg bg-black px-4 py-2 text-sm font-semibold tracking-[0.12em] text-white transition-colors hover:bg-ink">+ Tambah User</a>
    </div>

    <form method="GET" class="mt-4 flex gap-2">
        <input type="text" name="q" value="{{ $q }}" placeholder="Cari nama / email / HP"
               class="w-72 rounded-lg border border-rule px-3 py-2 text-sm text-ink">
        <button class="rounded-lg border border-rule px-4 py-2 text-sm font-semibold text-ink transition-colors hover:border-ink">Cari</button>
    </form>

    <div class="mt-4 flex flex-wrap items-center gap-2">
        <label class="flex items-center gap-2 text-sm text-ink/70">
            <input type="checkbox" id="user-select-all" class="rounded border-rule"> Pilih Semua
        </label>
        <button type="button" id="user-bulk-save" class="rounded-lg bg-black px-4 py-2 text-sm font-semibold tracking-[0.12em] text-white transition-colors hover:bg-ink">Simpan Semua</button>
        <span class="text-xs text-ink/45">Edit nama / email / role langsung di tabel, lalu Simpan Semua.</span>
    </div>

    <div class="mt-3 overflow-hidden rounded-xl border border-rule bg-white">
        <table class="w-full text-sm">
            <thead class="border-b border-rule text-left font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">
                <tr>
                    <th class="px-3 py-2"></th>
                    <th class="px-3 py-2">Nama</th>
                    <th class="px-3 py-2">Email</th>
                    <th class="px-3 py-2">No HP</th>
                    <th class="px-3 py-2">Role</th>
                    <th class="px-3 py-2"></th>
                </tr>
            </thead>
            <tbody id="user-list">
                @foreach ($users as $user)
                    <tr class="border-b border-rule/50" data-user-row>
                        <td class="px-3 py-2">
                            <input type="checkbox" class="user-checkbox rounded border-rule" data-id="{{ $user->id }}">
                        </td>
                        <td class="px-3 py-2">
                            <input type="text" value="{{ $user->name }}" data-field="name" data-orig="{{ $user->name }}"
                                   class="w-full rounded-lg border border-rule px-2 py-1 text-sm text-ink">
                        </td>
                        <td class="px-3 py-2">
                            <input type="text" value="{{ $user->email }}" data-field="email" data-orig="{{ $user->email }}"
                                   class="w-full rounded-lg border border-rule px-2 py-1 text-sm text-ink">
                        </td>
                        <td class="px-3 py-2 text-ink/75">{{ $user->no_hp }}</td>
                        <td class="px-3 py-2">
                            <select data-field="role" data-orig="{{ $user->role }}" class="rounded-lg border border-rule px-2 py-1 text-sm text-ink">
                                <option value="customer" @selected($user->role === 'customer')>customer</option>
                                <option value="admin" @selected($user->role === 'admin')>admin</option>
                            </select>
                        </td>
                        <td class="px-3 py-2 text-right">
                            <a href="{{ route('office.users.edit', $user) }}" class="text-sm font-medium text-link underline decoration-link/35 underline-offset-4">Edit</a>
                            @if (\Illuminate\Support\Facades\Route::has('office.users.notify'))
                                <button type="button" class="ml-2 text-link underline decoration-link/35 underline-offset-4"
                                        onclick="document.getElementById('notify-{{ $user->id }}').showModal()">Notif</button>
                            @endif
                            @if (\App\Models\Spammer::blocksIdentity($user->email, $user->no_hp))
                                <button type="button" class="ml-2 text-danger hover:underline"
                                        onclick="document.getElementById('del-{{ $user->id }}').showModal()">Hard Delete</button>
                                <button type="button" class="ml-2 text-danger hover:underline"
                                        onclick="document.getElementById('fdel-{{ $user->id }}').showModal()">Forever Delete</button>

                                <dialog id="del-{{ $user->id }}" class="m-auto rounded-xl border border-rule p-0">
                                    <form method="POST" action="{{ route('office.users.destroy', $user) }}" class="p-5">
                                        @csrf @method('DELETE')
                                        <p class="text-sm font-semibold text-ink">Hard Delete &ldquo;{{ $user->name }}&rdquo;?</p>
                                        <p class="mt-1 text-xs text-ink/55">Ketik email persis untuk konfirmasi.</p>
                                        <input type="text" name="confirm_email" placeholder="{{ $user->email }}" required
                                               oninput="this.form.querySelector('[type=submit]').disabled = this.value !== '{{ $user->email }}'"
                                               class="mt-2 w-full rounded-lg border border-rule px-3 py-2 text-sm text-ink">
                                        <div class="mt-3 flex justify-end gap-2">
                                            <button type="button" class="rounded-lg border border-rule px-3 py-1.5 text-xs font-semibold text-ink" onclick="document.getElementById('del-{{ $user->id }}').close()">Batal</button>
                                            <button type="submit" disabled class="rounded-lg bg-danger px-3 py-1.5 text-xs font-semibold text-white">Hapus</button>
                                        </div>
                                    </form>
                                </dialog>

                                <dialog id="fdel-{{ $user->id }}" class="m-auto rounded-xl border border-rule p-0">
                                    <form method="POST" action="{{ route('office.users.forever-delete', $user) }}" class="p-5">
                                        @csrf
                                        <p class="text-sm font-semibold text-ink">Forever Delete &ldquo;{{ $user->name }}&rdquo;?</p>
                                        <p class="mt-1 text-xs text-ink/55">Tidak dapat dibatalkan. Ketik email persis untuk konfirmasi.</p>
                                        <input type="text" name="confirm_email" placeholder="{{ $user->email }}" required
                                               oninput="this.form.querySelector('[type=submit]').disabled = this.value !== '{{ $user->email }}'"
                                               class="mt-2 w-full rounded-lg border border-rule px-3 py-2 text-sm text-ink">
                                        <div class="mt-3 flex justify-end gap-2">
                                            <button type="button" class="rounded-lg border border-rule px-3 py-1.5 text-xs font-semibold text-ink" onclick="document.getElementById('fdel-{{ $user->id }}').close()">Batal</button>
                                            <button type="submit" disabled class="rounded-lg bg-danger px-3 py-1.5 text-xs font-semibold text-white">Hapus Permanen</button>
                                        </div>
                                    </form>
                                </dialog>
                            @endif

                            @if (\Illuminate\Support\Facades\Route::has('office.users.notify'))
                                <dialog id="notify-{{ $user->id }}" class="m-auto rounded-xl border border-rule p-0">
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

    <script>
        (function () {
            var list = document.getElementById('user-list');
            if (!list) return;
            var saveBtn = document.getElementById('user-bulk-save');

            document.getElementById('user-select-all').addEventListener('change', function () {
                list.querySelectorAll('.user-checkbox').forEach(function (cb) { cb.checked = this.checked; }.bind(this));
            });

            saveBtn.addEventListener('click', function () {
                var rows = list.querySelectorAll('[data-user-row]');
                var pending = [];
                rows.forEach(function (row) {
                    var fields = {};
                    var changed = false;
                    row.querySelectorAll('[data-field]').forEach(function (el) {
                        var orig = el.getAttribute('data-orig');
                        if (el.value !== orig) changed = true;
                        fields[el.getAttribute('data-field')] = el.value;
                    });
                    if (changed) pending.push({ row: row, id: row.querySelector('[data-id]').getAttribute('data-id'), fields: fields });
                });
                if (!pending.length) { alert('Tidak ada perubahan untuk disimpan.'); return; }
                saveBtn.disabled = true; saveBtn.textContent = 'Menyimpan...';
                var done = 0;
                pending.forEach(function (item) {
                    var form = new FormData();
                    form.append('_token', '{{ csrf_token() }}');
                    form.append('_method', 'PUT');
                    form.append('name', item.fields.name);
                    form.append('email', item.fields.email);
                    form.append('role', item.fields.role);
                    fetch('{{ route("office.users.index") }}/' + item.id, { method: 'POST', body: form })
                        .then(function () {
                            item.row.querySelectorAll('[data-field]').forEach(function (el) { el.setAttribute('data-orig', el.value); });
                        })
                        .catch(function () {})
                        .finally(function () {
                            done++;
                            if (done === pending.length) { saveBtn.disabled = false; saveBtn.textContent = 'Simpan Semua'; alert('Selesai.'); }
                        });
                });
            });
        })();
    </script>
@endsection
