@extends('layouts.app')

@section('title', 'Undang Teman')

@section('content')
<div class='mx-auto max-w-7xl space-y-6 px-4 py-8'>
    <x-dashboard-tabs />
    <header><h1 class='text-3xl font-bold'>Undang Teman</h1><p class='mt-1 text-sm text-text-muted'>Ajak teman belanja bersama dan kumpulkan koin.</p></header>
    <section class='rounded-xl border border-border bg-white p-6'>
        <h2 class='font-semibold'>Kode Undanganmu</h2>
        @if ($inviteUrl)
            <div class='mt-3 flex flex-wrap items-center gap-3'><code class='rounded bg-surface-2 px-4 py-2 text-2xl font-bold tracking-[0.25em]'>{{ implode(' ', mb_str_split(auth()->user()->referral_code)) }}</code><button type='button' data-copy-value='{{ auth()->user()->referral_code }}' class='rounded-lg border border-border px-3 py-2 text-sm font-semibold'>Copy Kode</button><button type='button' data-copy-value='{{ $inviteUrl }}' class='rounded-lg bg-navy-500 px-3 py-2 text-sm font-semibold text-white'>Copy Link Undang</button></div>
        @else<p class='mt-3 text-sm text-text-muted'>Kode undangan belum tersedia untuk akun ini.</p>@endif
    </section>
    <section class='grid gap-4 md:grid-cols-3'>
        <article class='rounded-xl border border-border bg-white p-5'><p class='text-2xl'>🎁</p><h2 class='mt-2 font-bold'>Undang Teman: +3.000 Koin</h2><p class='mt-1 text-sm text-text-muted'>Untuk pengundang, saat teman menyelesaikan pesanan pertamanya.</p></article>
        <article class='rounded-xl border border-border bg-white p-5'><p class='text-2xl'>🪙</p><h2 class='mt-2 font-bold'>Teman Diundang: +1.000 Koin</h2><p class='mt-1 text-sm text-text-muted'>Untuk teman yang diundang, saat pembayaran pertamanya terverifikasi.</p></article>
        <article class='rounded-xl border border-border bg-white p-5'><p class='text-2xl'>🔗</p><h2 class='mt-2 font-bold'>Share Produk: +5 Koin / Klik</h2><p class='mt-1 text-sm text-text-muted'>Per IP unik Indonesia saat membuka link produk yang kamu bagikan.</p></article>
    </section>
    <section><h2 class='mb-3 text-lg font-bold'>Statistik Undangan</h2><div class='grid grid-cols-2 gap-3 md:grid-cols-4'>
        @foreach ([['Total Diundang', $totalReferrals], ['Berhasil', $completedReferrals], ['Menunggu Order', $pendingReferrals], ['Koin dari Undang', number_format($referralCoins, 0, ',', '.')]] as [$label, $value])
            <article class='rounded-xl border border-border bg-white p-4'><p class='text-xs text-text-muted'>{{ $label }}</p><p class='mt-1 text-xl font-bold'>{{ $value }}</p></article>
        @endforeach
    </div></section>
    <section><h2 class='mb-3 text-lg font-bold'>Statistik Share</h2><div class='grid grid-cols-2 gap-3 md:grid-cols-3'>
        @foreach ([['Link Dibuka (IP ID)', $shareCount], ['Koin dari Share', number_format($shareCoins, 0, ',', '.')], ['Reward / Klik', '5 koin']] as [$label, $value])
            <article class='rounded-xl border border-border bg-white p-4'><p class='text-xs text-text-muted'>{{ $label }}</p><p class='mt-1 text-xl font-bold'>{{ $value }}</p></article>
        @endforeach
    </div></section>
    <section class='overflow-hidden rounded-xl border border-border bg-white'>
        <h2 class='border-b border-border p-5 text-lg font-bold'>Riwayat Undangan</h2><div class='overflow-x-auto'>
            <table class='min-w-full divide-y divide-border text-left text-sm'><thead class='bg-surface-2 text-xs uppercase text-text-muted'><tr><th class='px-4 py-3'>Nama / Email Teman</th><th class='px-4 py-3'>Tgl Daftar</th><th class='px-4 py-3'>Status</th><th class='px-4 py-3'>Koin</th></tr></thead>
                <tbody class='divide-y divide-border'>
                    @forelse ($referrals as $referral)
                        <tr><td class='px-4 py-3'>{{ $referral->referee ? \Illuminate\Support\Str::mask($referral->referee->email, '*', 1, max(0, (int) mb_strpos($referral->referee->email, '@') - 1)) : 'Akun dihapus' }}</td><td class='whitespace-nowrap px-4 py-3'>{{ format_tanggal_id($referral->referee_registered_at) }}</td><td class='px-4 py-3'>{{ $referral->referee_first_order_completed_at ? '✅ Order Pertama Lunas' : '⏳ Menunggu order' }}</td><td class='px-4 py-3 font-semibold'>{{ $referral->referee_first_order_completed_at ? '+3.000' : '0' }}</td></tr>
                    @empty<tr><td colspan='4' class='p-8 text-center text-sm text-text-muted'>Belum ada teman yang mendaftar melalui kode undanganmu.</td></tr>@endforelse
                </tbody>
            </table>
        </div><div class='border-t border-border p-4'>{{ $referrals->links() }}</div>
    </section>
</div>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('[data-copy-value]').forEach(button => button.addEventListener('click', async () => {
        try { await navigator.clipboard.writeText(button.dataset.copyValue); button.textContent = 'Tersalin'; }
        catch (error) { console.error('Referral value could not be copied.', error); button.textContent = 'Gagal menyalin'; }
    }));
</script>
@endpush
