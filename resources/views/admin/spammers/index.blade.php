@extends('admin.layouts.admin')

@section('title', 'Spammers')

@section('content')
    <p class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink/45">Keamanan</p>
    <h1 class="mt-2 font-display text-[1.5rem] font-semibold text-ink">Spammers</h1>

    <form method="POST" action="{{ route('office.spammers.store') }}" class="mt-4 flex flex-wrap items-end gap-2 rounded-xl border border-rule bg-white p-4">
        @csrf
        <input type="email" name="email" placeholder="Email" class="w-56 rounded-lg border border-rule px-3 py-2 text-sm text-ink">
        <input type="text" name="no_hp" placeholder="No HP" class="w-40 rounded-lg border border-rule px-3 py-2 text-sm text-ink">
        <input type="text" name="reason" placeholder="Alasan" class="flex-1 rounded-lg border border-rule px-3 py-2 text-sm text-ink">
        <button class="rounded-lg bg-black px-4 py-2 text-sm font-semibold tracking-[0.12em] text-white transition-colors hover:bg-ink">+ Tambah</button>
    </form>

    <div class="mt-4 overflow-x-auto rounded-xl border border-rule bg-white">
        <table class="w-full text-sm">
            <thead class="border-b border-rule text-left">
                <tr>
                    <th class="px-3 py-2 font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">Email</th>
                    <th class="px-3 py-2 font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">No HP</th>
                    <th class="px-3 py-2 font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">Alasan</th>
                    <th class="px-3 py-2 font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">Status</th>
                    <th class="px-3 py-2 font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">Diblokir</th>
                    <th class="px-3 py-2"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($spammers as $spammer)
                    <tr class="border-b border-rule/50">
                        <td class="px-3 py-2 text-ink">{{ $spammer->email ?? '—' }}</td>
                        <td class="px-3 py-2 text-ink/80">{{ $spammer->no_hp ?? '—' }}</td>
                        <td class="px-3 py-2 text-ink/70">{{ $spammer->reason ?? '—' }}</td>
                        <td class="px-3 py-2">
                            <span class="rounded px-2 py-0.5 text-[10px] font-semibold {{ $spammer->status === 'blocked' ? 'bg-[#E53935] text-white' : 'bg-paper text-ink/60' }}">
                                {{ strtoupper($spammer->status ?? '-') }}
                            </span>
                        </td>
                        <td class="px-3 py-2 font-mono text-xs text-ink/55">{{ $spammer->banned_at?->format('d/m/Y') ?? '—' }}</td>
                        <td class="px-3 py-2 text-right">
                            @if ($spammer->status === 'blocked')
                                <form method="POST" action="{{ route('office.spammers.unblock', $spammer) }}" class="inline">
                                    @csrf
                                    <button class="text-sm font-medium text-link underline decoration-link/35 underline-offset-4"
                                            onclick="return confirm('Unblock identitas ini?')">
                                        Unblock
                                    </button>
                                </form>
                            @endif
                            @php($spammerUser = \App\Models\User::where('email', $spammer->email)->first())
                            @if ($spammerUser && \Illuminate\Support\Facades\Route::has('office.users.forever-delete'))
                                <button type="button" class="ml-2 text-sm font-semibold text-danger hover:underline"
                                        onclick="document.getElementById('fdel-{{ $spammer->id }}').showModal()">
                                    Forever Delete
                                </button>
                                <dialog id="fdel-{{ $spammer->id }}" class="rounded-xl border border-rule p-0">
                                    <form method="POST" action="{{ route('office.users.forever-delete', $spammerUser) }}" class="p-5">
                                        @csrf
                                        <p class="text-sm font-semibold text-ink">Forever Delete &ldquo;{{ $spammerUser->name }}&rdquo;?</p>
                                        <p class="mt-1 text-xs text-ink/60">Tidak dapat dibatalkan. Ketik email persis untuk konfirmasi.</p>
                                        <input type="text" name="confirm_email" placeholder="{{ $spammerUser->email }}" required
                                               oninput="this.form.querySelector('[type=submit]').disabled = this.value !== '{{ $spammerUser->email }}'"
                                               class="mt-2 w-full rounded-lg border border-rule px-3 py-2 text-sm text-ink">
                                        <div class="mt-3 flex justify-end gap-2">
                                            <button type="button" class="rounded-lg border border-rule px-3 py-1.5 text-xs font-semibold text-ink" onclick="document.getElementById('fdel-{{ $spammer->id }}').close()">Batal</button>
                                            <button type="submit" disabled class="rounded-lg bg-[#E53935] px-3 py-1.5 text-xs font-semibold text-white">Hapus Permanen</button>
                                        </div>
                                    </form>
                                </dialog>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="py-8 text-center text-sm text-ink/45">Belum ada data spammer.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $spammers->links() }}</div>
@endsection
