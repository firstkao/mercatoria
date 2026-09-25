@extends('admin.layouts.app', ['title' => $user->displayName(), 'back' => route('admin.users.index')])

@section('content')
    <div class="form-grid">
        <div class="form-grid__main">
            <section class="panel stack">
                <div class="panel__head">
                    <h2>Profil</h2>
                    @include('admin.users.role-badge', ['user' => $user])
                </div>
                <dl class="deflist">
                    <div><dt>Nama</dt><dd>{{ $user->full_name ?? '—' }}</dd></div>
                    <div><dt>Email</dt><dd>{{ $user->email ?? '—' }}</dd></div>
                    <div><dt>WhatsApp</dt><dd>{{ $user->whatsapp ? '+'.$user->whatsapp : '—' }}</dd></div>
                    <div><dt>Tanggal lahir</dt><dd>{{ $user->birth_date->translatedFormat('j F Y') }} ({{ $user->birth_date->age }} tahun)</dd></div>
                    <div><dt>Alamat</dt><dd>{{ $user->street_address ? "{$user->street_address}, {$user->district}, {$user->city}, {$user->province} {$user->postal_code}" : '—' }}</dd></div>
                    <div><dt>Terdaftar</dt><dd>{{ $user->registered_at->timezone('Asia/Jakarta')->translatedFormat('j F Y, H:i') }} WIB</dd></div>
                    @if ($user->became_customer_at)
                        <div><dt>Jadi customer</dt><dd>{{ $user->became_customer_at->timezone('Asia/Jakarta')->translatedFormat('j F Y, H:i') }} WIB</dd></div>
                    @endif
                    @if ($user->parental_consent)
                        <div><dt>Persetujuan ortu</dt><dd>Ya</dd></div>
                    @endif
                </dl>
            </section>

            <section class="panel stack">
                <div class="panel__head">
                    <h2>Aktivitas terakhir</h2>
                    @if ($user->email)
                        <a href="{{ route('admin.logs.index', ['q' => $user->email]) }}" class="link">Lihat semua</a>
                    @endif
                </div>
                @forelse ($activities as $log)
                    <div class="timeline__item">
                        <span class="timeline__time">{{ $log->created_at->timezone('Asia/Jakarta')->format('d/m H:i') }}</span>
                        <span>
                            <strong>{{ $log->label() }}</strong>
                            @foreach ($log->details() as $value)
                                <span class="muted">· {{ $value }}</span>
                            @endforeach
                        </span>
                    </div>
                @empty
                    <p class="muted">Belum ada aktivitas.</p>
                @endforelse
            </section>
        </div>

        <aside class="form-grid__side">
            <section class="panel stack">
                <h2>Pelacakan</h2>
                @include('admin.users.tracking', ['user' => $user])
            </section>

            @if ($identities->isNotEmpty())
                <section class="panel stack">
                    <h2>Riwayat pendaftaran</h2>
                    @foreach ($identities as $identity)
                        <div class="identity-line">
                            <span class="muted small">{{ $identity->kind === 'email' ? 'Email' : 'WhatsApp' }}</span>
                            <span>Terhapus {{ $identity->deletion_count }}/{{ $limit }}×
                                @if ($identity->blocked_at)
                                    <span class="badge badge--danger">Diblokir</span>
                                @endif
                            </span>
                        </div>
                    @endforeach
                    <a href="{{ route('admin.identities.index', ['q' => $user->email, 'status' => 'semua']) }}" class="link">Kelola di Blokir & banding</a>
                </section>
            @endif

            @if ($user->isSpammer())
                <section class="panel stack">
                    <h2>Kuota lihat produk</h2>
                    <p class="muted">Terpakai {{ $user->view_quota_used }} dari {{ $viewQuota }}.</p>
                    <form method="POST" action="{{ route('admin.users.reset-quota', $user) }}" onsubmit="return confirm('Reset kuota lihat produk pengguna ini?');">
                        @csrf
                        <button type="submit" class="btn btn--block" @disabled($user->view_quota_used === 0)>Reset kuota</button>
                    </form>
                </section>
            @elseif (! $user->anonymized_at)
                <section class="panel stack">
                    <h2>Hapus data pribadi</h2>
                    <p class="muted small">Nama, alamat, WhatsApp, email, dan kata sandi dihapus, dan akun tidak bisa login lagi. Order tetap tersimpan untuk laporan. Tidak bisa dibatalkan.</p>
                    <form method="POST" action="{{ route('admin.users.anonymize', $user) }}" onsubmit="return confirm('Hapus data pribadi customer ini? Tindakan ini tidak bisa dibatalkan.');">
                        @csrf
                        <button type="submit" class="btn btn--danger btn--block">Hapus data pribadi</button>
                    </form>
                </section>
            @endif
        </aside>
    </div>
@endsection