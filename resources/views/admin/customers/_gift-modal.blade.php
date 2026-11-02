{{-- Modal Kirim Gift (PRD 27). Variabel: $user (id, name), $vouchers --}}
<div x-data="{ open: false, type: 'koin' }" class="inline-block">
    <button type="button" @click="open = true"
            class="rounded-lg border border-rule px-3 py-1.5 text-xs font-semibold text-ink transition-colors hover:border-ink">
        <i class="bi bi-gift me-1"></i> Kirim Gift
    </button>
    <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" @keydown.escape.window="open = false">
        <form method="POST" action="{{ route('office.users.gift', $user->id) }}" @click.outside="open = false"
              class="w-full max-w-md max-h-[90vh] space-y-3 overflow-y-auto rounded-2xl border border-rule bg-white p-6 shadow-2xl">
            @csrf
            <h3 class="text-sm font-semibold text-ink">Kirim Gift ke {{ $user->name }}</h3>
            <select name="type" x-model="type" class="w-full rounded-lg border border-rule px-3 py-2 text-sm">
                <option value="koin">Koin</option>
                <option value="voucher">Voucher</option>
                <option value="gift_box">Gift Box Fisik</option>
            </select>
            <input x-show="type === 'koin'" type="number" name="coin_amount" min="1" placeholder="Nominal koin"
                   class="w-full rounded-lg border border-rule px-3 py-2 text-sm">
            <select x-show="type === 'voucher'" name="voucher_id" class="w-full rounded-lg border border-rule px-3 py-2 text-sm">
                <option value="">— Pilih voucher —</option>
                @foreach ($vouchers as $voucher)
                    <option value="{{ $voucher->id }}">{{ $voucher->name }} ({{ $voucher->code }})</option>
                @endforeach
            </select>
            <textarea name="note" rows="2" maxlength="500" placeholder="Catatan (opsional)"
                      class="w-full rounded-lg border border-rule px-3 py-2 text-sm"></textarea>
            <p class="text-[11px] text-ink/55">Notifikasi bell dan email (info@) otomatis dikirim ke user.</p>
            <div class="flex justify-end gap-2">
                <button type="button" @click="open = false" class="rounded-lg border border-rule px-4 py-2 text-sm">Batal</button>
                <button type="submit" class="rounded-lg bg-black px-4 py-2 text-sm font-semibold text-white">Kirim</button>
            </div>
        </form>
    </div>
</div>
