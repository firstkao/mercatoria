@extends('admin.layouts.app', ['title' => 'Maintenance'])

@section('actions')
    <form method="POST" action="{{ route('admin.maintenance.backup') }}" style="display:inline;">
        @csrf
        <button type="submit" class="btn btn--primary" onclick="return confirm('Jalankan backup database sekarang?');">
            Backup Sekarang
        </button>
    </form>
    <form method="POST" action="{{ route('admin.maintenance.cleanup') }}" style="display:inline;">
        @csrf
        <button type="submit" class="btn" onclick="return confirm('Bersihkan file orphan + log lama?');">
            Cleanup Storage
        </button>
    </form>
@endsection

@section('content')

    {{-- Storage overview --}}
    <div class="stats">
        <div class="stat">
            <span class="stat__label">Total Storage</span>
            <strong class="stat__value">{{ number_format($totalStorage / 1048576, 2) }} MB</strong>
        </div>
        <div class="stat">
            <span class="stat__label">Foto Produk</span>
            <strong class="stat__value">{{ number_format($storageInfo['product_images'] / 1048576, 2) }} MB</strong>
        </div>
        <div class="stat">
            <span class="stat__label">Bukti Bayar</span>
            <strong class="stat__value">{{ number_format($storageInfo['payment_proofs'] / 1048576, 2) }} MB</strong>
        </div>
        <div class="stat">
            <span class="stat__label">Backup</span>
            <strong class="stat__value">{{ number_format($storageInfo['backups'] / 1048576, 2) }} MB</strong>
        </div>
    </div>

    {{-- Detail storage --}}
    <section class="panel">
        <h2>Detail Storage</h2>
        <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Folder</th>
                    <th>Ukuran</th>
                </tr>
            </thead>
            <tbody>
                @foreach ([
                    'product_images' => 'Foto Produk Utama',
                    'variants' => 'Foto Varian',
                    'hero_slides' => 'Hero Slide',
                    'payment_proofs' => 'Bukti Pembayaran',
                    'logs' => 'Log Laravel',
                    'backups' => 'Backup Database',
                ] as $key => $label)
                    <tr>
                        <td>{{ $label }}</td>
                        <td>{{ number_format($storageInfo[$key] / 1048576, 2) }} MB</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    </section>

    {{-- Backup list --}}
    <section class="panel panel--flush">
        <h2 style="padding:1rem 1rem 0;">Riwayat Backup</h2>
        @if ($backups->isEmpty())
            <p class="muted" style="padding:1rem;">Belum ada backup.</p>
        @else
            <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nama File</th>
                        <th>Ukuran</th>
                        <th>Dibuat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($backups as $backup)
                        <tr>
                            <td class="mono small">{{ $backup['name'] }}</td>
                            <td>{{ number_format($backup['size'] / 1048576, 2) }} MB</td>
                            <td class="muted small">{{ $backup['created_at']->timezone('Asia/Jakarta')->translatedFormat('j M Y, H:i') }} WIB</td>
                            <td class="nowrap">
                                <a href="{{ route('admin.maintenance.download', $backup['name']) }}" class="link">Download</a>
                                <form method="POST" action="{{ route('admin.maintenance.destroy', $backup['name']) }}"
                                      onsubmit="return confirm('Hapus backup ini?');"
                                      style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="link" style="color:var(--danger);">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        @endif
    </section>

    {{-- Info scheduler --}}
    <section class="panel">
        <h2>Jadwal Otomatis</h2>
        <ul style="margin:0;padding-left:20px;">
            <li><strong>Backup Database</strong> — tiap hari 02:00 WIB (simpan 7 terakhir)</li>
            <li><strong>Cleanup Storage</strong> — tiap Minggu 03:00 WIB</li>
            <li><strong>Prune Activity Logs</strong> — tiap hari 03:00 WIB</li>
            <li><strong>Prune Spammer Expired</strong> — tiap jam</li>
            <li><strong>Cancel Unpaid Orders</strong> — tiap 15 menit</li>
            <li><strong>Send Payment Reminders</strong> — tiap jam</li>
            <li><strong>Coin Lifecycle</strong> — tiap hari 01:00 WIB</li>
            <li><strong>Birthday Vouchers</strong> — tiap hari 00:05 WIB</li>
        </ul>
        <p class="hint" style="margin-top:12px;">
            Pastikan cron sudah disetup: <code>* * * * * cd /path/ke/project && php artisan schedule:run >> /dev/null 2>&1</code>
        </p>
    </section>
@endsection