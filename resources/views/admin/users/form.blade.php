@extends('admin.layouts.admin')

@section('title', $user->exists ? 'Edit User' : 'Tambah User')

@section('content')
    <style>
        .form-input {
            border: 2px solid #f5f5f5;
            background: #fff;
            border-radius: 6px;
            outline: none;
            transition: border-color .15s, box-shadow .15s;
        }
        .form-input:hover { border-color: #e0e0e0; }
        .form-input:focus { border-color: #aaa; box-shadow: 0 0 0 2px rgba(0,0,0,0.05); }
    </style>

    {{-- PAGE HEADER --}}
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <p class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink/45">
                <a href="{{ route('office.users.index') }}" class="transition-colors hover:text-ink">Pengguna</a>
                <span class="mx-1 text-ink/25">/</span>
                {{ $user->exists ? 'Edit' : 'Tambah' }}
            </p>
            <h1 class="mt-2 font-display text-[1.5rem] font-semibold text-ink">
                {{ $user->exists ? 'Edit: '.$user->name : 'Tambah User' }}
            </h1>
        </div>
        <div class="flex items-center gap-2">
            @if ($user->exists)
                <span class="rounded-full {{ $user->role === 'admin' ? 'bg-[#43A047]' : 'bg-[#9E9E9E]' }} px-2.5 py-1 text-[10px] font-bold text-white">
                    {{ ucfirst($user->role) }}
                </span>
            @endif
            <a href="{{ route('office.users.index') }}"
               class="rounded-lg border border-rule px-3.5 py-2 text-xs font-semibold text-ink/70 transition-colors hover:border-ink hover:text-ink">
                <i class="bi bi-arrow-left me-1" style="font-size:0.85rem;"></i> Kembali
            </a>
        </div>
    </div>

    {{-- ═══ FORM ═══ --}}
    <form method="POST"
          action="{{ $user->exists ? route('office.users.update', $user) : route('office.users.store') }}"
          class="mt-4 rounded-xl border border-rule bg-white">
        @csrf
        @if ($user->exists) @method('PUT') @endif

        <div class="border-b border-rule px-5 py-2">
            <h2 class="font-mono text-[10px] font-medium uppercase tracking-[0.16em] text-ink/70">
                {{ $user->exists ? 'Edit Data User' : 'Data User Baru' }}
            </h2>
        </div>

        <div class="px-5 py-4">
            @if ($errors->any())
                <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-3">
                    <p class="flex items-center gap-2 text-xs font-semibold text-red-700">
                        <i class="bi bi-exclamation-circle"></i> Gagal menyimpan:
                    </p>
                    <ul class="mt-1.5 space-y-0.5 text-xs text-red-600">
                        @foreach ($errors->all() as $error)
                            <li>&bull; {{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Info Akun --}}
            <p class="mb-3 font-mono text-[10px] font-medium uppercase tracking-[0.16em] text-ink/45">Info Akun</p>

            <div class="flex flex-wrap items-end gap-3">
                <div class="w-[220px] shrink-0">
                    <label class="block text-xs font-medium text-ink/70 mb-1">Nama <span class="text-danger">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required placeholder="Nama lengkap"
                           class="h-11 w-full rounded-lg border border-rule px-3 text-sm text-ink">
                </div>
                <div class="w-[250px] shrink-0">
                    <label class="block text-xs font-medium text-ink/70 mb-1">Email <span class="text-danger">*</span></label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required placeholder="user@email.com"
                           class="h-11 w-full rounded-lg border border-rule px-3 text-sm text-ink">
                </div>
                <div class="w-[200px] shrink-0">
                    <label class="block text-xs font-medium text-ink/70 mb-1">
                        Password
                        @if ($user->exists)
                            <span class="text-ink/35">(kosongkan bila tetap)</span>
                        @else
                            <span class="text-danger">*</span>
                        @endif
                    </label>
                    <input type="password" name="password" {{ $user->exists ? '' : 'required' }} placeholder="••••••••"
                           class="h-11 w-full rounded-lg border border-rule px-3 text-sm text-ink">
                </div>
                <div class="w-[150px] shrink-0">
                    <label class="block text-xs font-medium text-ink/70 mb-1">Role</label>
                    <select name="role" class="h-11 w-full rounded-lg border border-rule px-3 text-sm text-ink">
                        <option value="customer" @selected(old('role', $user->role ?: 'customer') === 'customer')>Customer</option>
                        <option value="admin" @selected(old('role') === 'admin' || $user->role === 'admin')>Admin</option>
                    </select>
                </div>
            </div>

            {{-- Alamat (hanya untuk create / inject) --}}
            @unless ($user->exists)
                <div class="mt-5 border-t border-rule pt-4">
                    <p class="mb-3 font-mono text-[10px] font-medium uppercase tracking-[0.16em] text-ink/45">Info Alamat</p>

                    <div class="flex flex-wrap items-end gap-3">
                        <div class="w-[160px] shrink-0">
                            <label class="block text-xs font-medium text-ink/70 mb-1">Tanggal Lahir <span class="text-danger">*</span></label>
                            <input type="date" name="tgl_lahir" value="{{ old('tgl_lahir') }}" required
                                   class="h-11 w-full rounded-lg border border-rule px-3 text-sm text-ink">
                        </div>
                        <div class="w-[180px] shrink-0">
                            <label class="block text-xs font-medium text-ink/70 mb-1">No HP <span class="text-danger">*</span></label>
                            <input type="text" name="no_hp" value="{{ old('no_hp') }}" required placeholder="08xxxxxxxxxx"
                                   inputmode="numeric" oninput="this.value=this.value.replace(/[^0-9]/g,'')"
                                   class="h-11 w-full rounded-lg border border-rule px-3 text-sm text-ink">
                        </div>
                        <div class="w-[170px] shrink-0">
                            <label class="block text-xs font-medium text-ink/70 mb-1">Provinsi <span class="text-danger">*</span></label>
                            <input type="text" name="provinsi" value="{{ old('provinsi') }}" required placeholder="cth: DKI Jakarta"
                                   class="h-11 w-full rounded-lg border border-rule px-3 text-sm text-ink">
                        </div>
                        <div class="w-[170px] shrink-0">
                            <label class="block text-xs font-medium text-ink/70 mb-1">Kota <span class="text-danger">*</span></label>
                            <input type="text" name="kota" value="{{ old('kota') }}" required placeholder="cth: Jakarta Selatan"
                                   class="h-11 w-full rounded-lg border border-rule px-3 text-sm text-ink">
                        </div>
                    </div>

                    <div class="mt-3 flex flex-wrap items-end gap-3">
                        <div class="w-[170px] shrink-0">
                            <label class="block text-xs font-medium text-ink/70 mb-1">Kecamatan <span class="text-danger">*</span></label>
                            <input type="text" name="kecamatan" value="{{ old('kecamatan') }}" required placeholder="cth: Tebet"
                                   class="h-11 w-full rounded-lg border border-rule px-3 text-sm text-ink">
                        </div>
                        <div class="w-[130px] shrink-0">
                            <label class="block text-xs font-medium text-ink/70 mb-1">Kode Pos <span class="text-danger">*</span></label>
                            <input type="text" name="kode_pos" value="{{ old('kode_pos') }}" required placeholder="12xxx"
                                   inputmode="numeric" oninput="this.value=this.value.replace(/[^0-9]/g,'')"
                                   class="h-11 w-full rounded-lg border border-rule px-3 text-sm text-ink">
                        </div>
                        <div class="flex-1 min-w-[200px]">
                            <label class="block text-xs font-medium text-ink/70 mb-1">Alamat <span class="text-danger">*</span></label>
                            <textarea name="alamat" rows="1" required placeholder="Jl. ..."
                                      class="w-full rounded-lg border border-rule px-3 py-2.5 text-sm text-ink resize-none"
                                      style="min-height:44px;"></textarea>
                        </div>
                    </div>
                </div>
            @endunless

            <div class="mt-5 border-t border-rule pt-4">
                <button type="submit"
                        class="h-11 rounded-lg bg-black px-6 text-sm font-semibold tracking-[0.12em] text-white transition-colors hover:bg-ink">
                    <i class="bi bi-check-lg me-1" style="font-size:0.85rem;"></i>
                    {{ $user->exists ? 'Simpan Perubahan' : 'Simpan User' }}
                </button>
                @if ($user->exists)
                    <a href="{{ route('office.users.index') }}"
                       class="ml-2 inline-flex h-11 items-center rounded-lg border border-rule px-5 text-sm font-semibold text-ink/70 transition-colors hover:border-ink hover:text-ink">
                        Batal
                    </a>
                @endif
            </div>
        </div>
    </form>

    {{-- ═══ REFERRAL INFO ═══ --}}
    @isset($visit)
        @if (data_get($visit, 'source') || data_get($visit, 'landing_page'))
            <div class="mt-4 rounded-lg border-l-4 border-l-[#1A4D8F] bg-[#F8F9FA] p-3.5">
                <p class="font-mono text-[10px] font-medium uppercase tracking-[0.16em] text-ink/65">
                    <i class="bi bi-link-45deg me-1" style="font-size:0.85rem;"></i> Sumber Referral
                </p>
                @if (data_get($visit, 'source'))
                    <p class="mt-1.5 text-sm text-ink/75">{{ data_get($visit, 'source') }}</p>
                @endif
                @if (data_get($visit, 'landing_page'))
                    <p class="mt-0.5 text-xs text-ink/45">{{ data_get($visit, 'landing_page') }}</p>
                @endif
            </div>
        @endif
    @endisset
@endsection