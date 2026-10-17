@auth
    @unless (auth()->user()->hasVerifiedEmail())
        <div class="mb-4 flex flex-col gap-3 rounded-lg border-l-4 border-[#FB8C00] bg-[#FFF8E1] px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="font-semibold text-gray-900">⚠️ Verifikasi email dulu biar bisa order</p>
                <p class="text-sm text-gray-600">
                    @if (session('status') === 'verification-link-sent')
                        Link verifikasi udah dikirim. Cek inbox kamu.
                    @else
                        Email kamu belum diverifikasi. Klik tombol buat kirim link ke inbox.
                    @endif
                </p>
            </div>
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="rounded-lg bg-[#FB8C00] px-4 py-2 text-sm font-semibold text-white hover:opacity-90">
                    {{ session('status') === 'verification-link-sent' ? 'Kirim Ulang' : 'Verifikasi Sekarang' }}
                </button>
            </form>
        </div>
    @endunless
@endauth