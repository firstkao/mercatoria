@auth
    @if (auth()->user()->hasVerifiedEmail() && ! auth()->user()->hasCompletedProfile())
        <div class="mb-4 flex flex-col gap-3 rounded-lg border-l-4 border-[#FB8C00] bg-[#FFF8E1] px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="font-semibold text-gray-900">⚠️ Lengkapi profil dulu</p>
                <p class="text-sm text-gray-600">Isi tanggal lahir untuk bisa order.</p>
            </div>
            <a href="{{ route('profile.complete') }}"
               class="rounded-lg bg-[#FB8C00] px-4 py-2 text-center text-sm font-semibold text-white hover:opacity-90">
                Lengkapi Profil →
            </a>
        </div>
    @endif
@endauth