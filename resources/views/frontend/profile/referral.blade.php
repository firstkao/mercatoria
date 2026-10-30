@extends('layouts.app')

@section('title', 'Undang Teman — Mercatoria')

@section('content')
    <p class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink/45">Akun</p>
    <h1 class="mt-2 font-display text-[1.75rem] leading-tight font-semibold text-ink sm:text-3xl">Undang Teman</h1>
    <p class="mt-1 text-sm text-ink/65">Ajak teman berbelanja dan dapatkan koin.</p>

    <div class="mt-5 flex flex-wrap gap-1 border-b border-rule">
        <a href="{{ route('dashboard') }}" class="border-b-2 border-transparent px-4 py-2.5 text-sm text-ink/55 transition-colors hover:text-ink">Pesanan Saya</a>
        <a href="{{ route('profile.notifications') }}" class="border-b-2 border-transparent px-4 py-2.5 text-sm text-ink/55 transition-colors hover:text-ink">Notifikasi</a>
        <a href="{{ route('profile.coins') }}" class="border-b-2 border-transparent px-4 py-2.5 text-sm text-ink/55 transition-colors hover:text-ink">Koin</a>
        <a href="{{ route('profile.wallet') }}" class="border-b-2 border-transparent px-4 py-2.5 text-sm text-ink/55 transition-colors hover:text-ink">Voucher</a>
        <a href="{{ route('profile.referral') }}" class="border-b-2 border-ink px-4 py-2.5 text-sm font-semibold text-ink">Undang Teman</a>
        <a href="{{ route('profile.edit') }}" class="border-b-2 border-transparent px-4 py-2.5 text-sm text-ink/55 transition-colors hover:text-ink">Edit Profil</a>
    </div>

    <div class="mt-5 rounded-xl border border-rule bg-white p-6">
        <p class="font-mono text-[10px] uppercase tracking-[0.18em] text-ink/55">KODE UNDANGANMU</p>
        <p class="mt-2 font-mono text-2xl font-semibold tracking-[0.28em] text-ink">{{ $user->referral_code }}</p>
        <p class="mt-2 text-xs text-ink/50" id="referral-link">
            Link: {{ url('/register?ref='.$user->referral_code) }}
        </p>
        <div class="mt-4 flex flex-wrap gap-3">
            <button type="button" data-copy="{{ $user->referral_code }}"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-rule px-4 py-2 text-xs font-semibold text-ink transition-colors hover:border-ink">
                <x-icons.clipboard width="14" height="14" />
                Copy Kode
            </button>
            <button type="button" data-copy="{{ url('/register?ref='.$user->referral_code) }}"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-rule px-4 py-2 text-xs font-semibold text-ink transition-colors hover:border-ink">
                <x-icons.link width="14" height="14" />
                Copy Link Undangan
            </button>
        </div>
    </div>

    {{-- Reward Program (Design Spec 10.3 / PRD 9.2) --}}
    <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-2">
        <div class="rounded-xl border border-rule bg-white p-4">
            <p class="font-mono text-[10px] uppercase tracking-[0.16em] text-ink/55">UNDANG TEMAN</p>
            <p class="mt-1 text-lg font-semibold text-ink">+3.000 Koin</p>
            <p class="mt-1 text-xs text-ink/60">Saat temanmu menyelesaikan order pertama. Teman mendapat +1.000 koin saat pembayaran pertama terverifikasi.</p>
        </div>
        <div class="rounded-xl border border-rule bg-white p-4">
            <p class="font-mono text-[10px] uppercase tracking-[0.16em] text-ink/55">SHARE PRODUK</p>
            <p class="mt-1 text-lg font-semibold text-ink">+5 Koin / Klik</p>
            <p class="mt-1 text-xs text-ink/60">Per IP unik Indonesia saat membuka link produk yang kamu bagikan.</p>
        </div>
    </div>

    {{-- Stats share (data BE menyusul) --}}
    @isset($shareStats)
        <div class="mt-5 grid grid-cols-2 gap-3 lg:grid-cols-3">
            <div class="rounded-xl border border-rule bg-white p-4">
                <p class="font-mono text-[10px] uppercase tracking-[0.16em] text-ink/55">LINK DIBUKA</p>
                <p class="mt-1 text-2xl font-semibold text-ink">{{ $shareStats['opens'] ?? 0 }}</p>
            </div>
            <div class="rounded-xl border border-rule bg-white p-4">
                <p class="font-mono text-[10px] uppercase tracking-[0.16em] text-ink/55">KOIN DARI SHARE</p>
                <p class="mt-1 text-2xl font-semibold text-ink">{{ number_format($shareStats['coins'] ?? 0, 0, ',', '.') }}</p>
            </div>
            <div class="rounded-xl border border-rule bg-white p-4">
                <p class="font-mono text-[10px] uppercase tracking-[0.16em] text-ink/55">REWARD</p>
                <p class="mt-1 text-2xl font-semibold text-ink">5 / klik</p>
            </div>
        </div>
    @endisset

    {{-- Stats Undang Teman (DS 10.3) --}}
    @isset($referralStats)
        <div class="mt-5 grid grid-cols-2 gap-3 lg:grid-cols-4">
            <div class="rounded-xl border border-rule bg-white p-4">
                <p class="font-mono text-[10px] uppercase tracking-[0.16em] text-ink/55">Total Diundang</p>
                <p class="mt-1 text-2xl font-semibold text-ink">{{ $referralStats['total_diundang'] ?? 0 }}</p>
            </div>
            <div class="rounded-xl border border-rule bg-white p-4">
                <p class="font-mono text-[10px] uppercase tracking-[0.16em] text-ink/55">Berhasil</p>
                <p class="mt-1 text-2xl font-semibold text-ink">{{ $referralStats['berhasil'] ?? 0 }}</p>
            </div>
            <div class="rounded-xl border border-rule bg-white p-4">
                <p class="font-mono text-[10px] uppercase tracking-[0.16em] text-ink/55">Menunggu Order</p>
                <p class="mt-1 text-2xl font-semibold text-ink">{{ $referralStats['menunggu_order'] ?? 0 }}</p>
            </div>
            <div class="rounded-xl border border-rule bg-white p-4">
                <p class="font-mono text-[10px] uppercase tracking-[0.16em] text-ink/55">Koin</p>
                <p class="mt-1 text-2xl font-semibold text-ink">{{ number_format($referralStats['koin_didapat'] ?? 0, 0, ',', '.') }}</p>
            </div>
        </div>
    @endisset

    <div class="mt-5 rounded-xl border border-rule bg-white p-4">
        <h2 class="font-mono text-[11px] font-medium uppercase tracking-[0.16em] text-ink">Riwayat Undangan</h2>
        @if ($referrals->isEmpty())
            <p class="mt-2 text-sm text-ink/45">Belum ada yang bergabung lewat kode kamu.</p>
        @else
            <div class="mt-3 overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="border-b border-rule text-left">
                        <tr>
                            <th class="py-2 font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">Nama / Email</th>
                            <th class="py-2 font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">Tgl Daftar</th>
                            <th class="py-2 font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">Status</th>
                            <th class="py-2 font-mono text-[10px] uppercase tracking-[0.14em] text-ink/55">Koin</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($referrals as $referral)
                            @php
                                $hasOrder = $referral->orders()->exists();
                                $koin = $hasOrder ? 3000 : 0;
                            @endphp
                            <tr class="border-b border-rule/50">
                                <td class="py-2 text-ink/80">{{ $referral->name }}</td>
                                <td class="py-2 font-mono text-xs text-ink/55">{{ $referral->created_at->format('d/m/Y') }}</td>
                                <td class="py-2">
                                    @if ($hasOrder)
                                        <span class="rounded px-2 py-0.5 text-[10px] font-semibold bg-[#43A047] text-white">Order Pertama Lunas</span>
                                    @else
                                        <span class="rounded px-2 py-0.5 text-[10px] font-semibold bg-amber-100 text-amber-800">Menunggu Order</span>
                                    @endif
                                </td>
                                <td class="py-2 text-ink/80">+{{ number_format($koin) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <script>
        (function () {
            document.querySelectorAll('[data-copy]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var value = btn.getAttribute('data-copy');
                    if (navigator.clipboard && navigator.clipboard.writeText) {
                        navigator.clipboard.writeText(value);
                    }
                });
            });
        })();
    </script>
@endsection
