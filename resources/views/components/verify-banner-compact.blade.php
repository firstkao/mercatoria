@auth
    @unless (auth()->user()->hasVerifiedEmail() || request()->routeIs('dashboard', 'verification.*'))
        <div class="sticky top-16 z-30 flex items-center justify-between gap-3 border-b-2 border-warning-500 bg-warning-50 px-5 py-3 text-sm">
            <span class="text-text">
                ⚠️
                @if (session('status') === 'verification-link-sent')
                    Link verifikasi udah dikirim. Cek inbox kamu.
                @else
                    Verifikasi email untuk unlock semua fitur
                @endif
            </span>

            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="rounded-lg bg-warning-500 px-3 py-1 text-xs font-semibold text-white hover:opacity-90">
                    {{ session('status') === 'verification-link-sent' ? 'Kirim Ulang' : 'Verifikasi' }}
                </button>
            </form>
        </div>
    @endunless
@endauth