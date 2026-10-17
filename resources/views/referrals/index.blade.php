@extends('layouts.app')

@section('title', 'Undang Teman')

@section('content')
<div class="mx-auto max-w-7xl space-y-6 px-4 py-8">
    <div>
        <h1 class="text-3xl font-bold">Undang Teman</h1>
        <p class="mt-1 text-sm text-text-muted">Dapatkan 3.000 koin setelah temanmu menyelesaikan order pertamanya.</p>
    </div>

    <section class="rounded-xl border border-border bg-white p-6">
        <h2 class="font-semibold">Kode undanganmu</h2>
        @if ($inviteUrl)
            <div class="mt-3 flex flex-wrap items-center gap-3">
                <code id="referral-code" class="rounded bg-surface-2 px-4 py-2 text-2xl font-bold tracking-widest">{{ auth()->user()->referral_code }}</code>
                <button type="button" data-copy-value="{{ auth()->user()->referral_code }}" class="rounded-lg border border-border px-3 py-2 text-sm">Salin kode</button>
                <button type="button" data-copy-value="{{ $inviteUrl }}" class="rounded-lg bg-navy-500 px-3 py-2 text-sm text-white">Salin link undangan</button>
            </div>
        @else
            <p class="mt-3 text-sm text-text-muted">Kode undangan belum tersedia untuk akun ini.</p>
        @endif
    </section>

    <section class="grid grid-cols-2 gap-3 md:grid-cols-5">
        @foreach ([
            ['Total diundang', $totalReferrals],
            ['Order pertama selesai', $completedReferrals],
            ['Menunggu order', $pendingReferrals],
            ['Koin referral', number_format($referralCoins, 0, ',', '.')],
            ['Share / koin share', $shareCount.' / '.number_format($shareCoins, 0, ',', '.')],
        ] as [$label, $value])
            <article class="rounded-xl border border-border bg-white p-4">
                <p class="text-xs text-text-muted">{{ $label }}</p><p class="mt-1 text-xl font-bold">{{ $value }}</p>
            </article>
        @endforeach
    </section>

    <section class="overflow-hidden rounded-xl border border-border bg-white">
        <h2 class="border-b border-border p-5 text-lg font-bold">Riwayat undangan</h2>
        <div class="divide-y divide-border">
            @forelse ($referrals as $referral)
                <div class="flex flex-wrap items-center justify-between gap-3 p-4">
                    <div><strong>{{ $referral->referee?->name ?? 'Akun dihapus' }}</strong><p class="text-xs text-text-muted">Terdaftar {{ $referral->referee_registered_at?->format('d-m-Y') ?: '—' }}</p></div>
                    <span class="rounded-full bg-surface-2 px-3 py-1 text-xs">{{ $referral->referee_first_order_completed_at ? 'Berhasil' : 'Menunggu order pertama' }}</span>
                </div>
            @empty
                <p class="p-8 text-center text-sm text-text-muted">Belum ada teman yang mendaftar melalui kode undanganmu.</p>
            @endforelse
        </div>
        <div class="border-t border-border p-4">{{ $referrals->links() }}</div>
    </section>
</div>
@endsection

@push('scripts')
    <script>
        document.querySelectorAll('[data-copy-value]').forEach(button => button.addEventListener('click', async () => {
            try {
                await navigator.clipboard.writeText(button.dataset.copyValue);
                button.textContent = 'Tersalin';
            } catch (error) {
                console.error('Referral value could not be copied.', error);
                button.textContent = 'Gagal menyalin';
            }
        }));
    </script>
@endpush
