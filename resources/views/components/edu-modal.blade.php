@auth
    @if (auth()->user()->hasVerifiedEmail() && ! auth()->user()->isAdmin() && ! auth()->user()->has_seen_edu_modal)
        <div x-data="{
                show: true,
                async seen(url = null) {
                    this.show = false;
                    try {
                        await fetch('{{ route('edu-modal.seen') }}', {
                            method: 'POST',
                            keepalive: true,
                            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, Accept: 'application/json' },
                        });
                    } catch (error) {
                        console.error('Edu modal state could not be saved.', error);
                    }
                    if (url) window.location.href = url;
                },
            }"
            x-show="show" x-cloak x-on:keydown.escape.window="seen()"
            id="edu-modal" class="fixed inset-0 z-[60] flex items-center justify-center bg-black/60 p-4">
            <div class="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-lg bg-white p-6" role="dialog" aria-modal="true" aria-labelledby="edu-modal-title">
                <h2 id="edu-modal-title" class="text-lg font-bold text-text">🎉 Baru di Mercatoria? Ini Cara Belanja di Mercatoria</h2>
                <hr class="my-3 border-border">
                <ol class="space-y-3 text-sm text-text">
                    <li><strong>1. Pilih Merch IP Favoritmu</strong> — Semua harga yang tertera adalah HARGA BERSIH (sudah termasuk ongkir CN-ID &amp; bea).</li>
                    <li><strong>2. Pilih Skema Pembayaran</strong> — Bisa Full Payment (100%) atau Down Payment (DP 50%) via BCA, QRIS, atau ShopeePay.</li>
                    <li><strong>3. Pilih Platform Pengiriman Lokal</strong> — Toco atau Shopee.</li>
                    <li><strong>4. Pantau Status Real-time</strong> — Barang diproses dari WH China hingga sampai WH Indonesia (pelunasan &amp; ambil ≤ 7 hari).</li>
                    <li><strong>5. Kuota Akun Baru (Crush)</strong> — Kamu punya kuota melihat 10 detail produk. Order pertamamu langsung upgrade ke MVP!</li>
                </ol>
                <p class="mt-3 text-xs text-text-muted">Lihat juga <a href="/tnc" class="text-navy-500 underline">Syarat &amp; Ketentuan</a>.</p>
                <div class="mt-5 flex flex-wrap justify-between gap-2">
                    <x-button variant="secondary" @click="seen('/cara-belanja')">Baca Selengkapnya</x-button>
                    <x-button variant="primary" @click="seen()">Mengerti</x-button>
                </div>
            </div>
        </div>
    @endif
@endauth
