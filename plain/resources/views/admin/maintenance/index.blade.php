@extends('admin.layouts.app', ['title' => 'Maintenance'])

@section('actions')
    <form method="POST" action="{{ route('admin.maintenance.backup') }}" class="inline-form">
        @csrf
        <x-admin.button variant="primary" type="submit" onclick="return confirm('Jalankan backup database sekarang?');">
            Backup Sekarang
        </x-admin.button>
    </form>
    <form method="POST" action="{{ route('admin.maintenance.cleanup') }}" class="inline-form">
        @csrf
        <x-admin.button type="submit" onclick="return confirm('Bersihkan file orphan + log lama?');">
            Cleanup Storage
        </x-admin.button>
    </form>
@endsection

@section('content')

    {{-- Storage overview --}}
    <x-admin.metric-strip class="mb-6">
        <x-admin.metric :value="number_format($totalStorage / 1048576, 2).' MB'" label="Total Storage" />
        <x-admin.metric :value="number_format($storageInfo['product_images'] / 1048576, 2).' MB'" label="Foto Produk" />
        <x-admin.metric :value="number_format($storageInfo['payment_proofs'] / 1048576, 2).' MB'" label="Bukti Bayar" />
        <x-admin.metric :value="number_format($storageInfo['backups'] / 1048576, 2).' MB'" label="Backup" />
    </x-admin.metric-strip>

    {{-- Detail storage --}}
    <x-admin.card class="mb-6">
        <h2>Detail Storage</h2>
        <x-admin.table>
            <x-slot:head>
                <tr>
                    <th>Folder</th>
                    <th>Ukuran</th>
                </tr>
            </x-slot:head>
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
        </x-admin.table>
    </x-admin.card>

    {{-- Backup list --}}
    <x-admin.card padding="flush" class="mb-6">
        <h2 class="panel-title">Riwayat Backup</h2>
        @if ($backups->isEmpty())
            <p class="muted p-4">Belum ada backup.</p>
        @else
            <x-admin.table>
                <x-slot:head>
                    <tr>
                        <th>Nama File</th>
                        <th>Ukuran</th>
                        <th>Dibuat</th>
                        <th>Aksi</th>
                    </tr>
                </x-slot:head>
                @foreach ($backups as $backup)
                    <tr>
                        <td class="mono small">{{ $backup['name'] }}</td>
                        <td>{{ number_format($backup['size'] / 1048576, 2) }} MB</td>
                        <td class="muted small">{{ $backup['created_at']->timezone('Asia/Jakarta')->translatedFormat('j M Y, H:i') }} WIB</td>
                        <td class="nowrap">
                            <div class="table__actions">
                                <a href="{{ route('admin.maintenance.download', $backup['name']) }}" class="link">Download</a>
                                <form method="POST" action="{{ route('admin.maintenance.destroy', $backup['name']) }}"
                                      onsubmit="return confirm('Hapus backup ini?');"
                                      class="inline-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="link text-danger">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-admin.table>
        @endif
    </x-admin.card>

    {{-- Info scheduler --}}
    <x-admin.card>
        <h2>Jadwal Otomatis</h2>
        <ul class="list-bullets">
            <li><strong>Backup Database</strong> — tiap hari 02:00 WIB (simpan 7 terakhir)</li>
            <li><strong>Cleanup Storage</strong> — tiap Minggu 03:00 WIB</li>
            <li><strong>Prune Activity Logs</strong> — tiap hari 03:00 WIB</li>
            <li><strong>Prune Spammer Expired</strong> — tiap jam</li>
            <li><strong>Cancel Unpaid Orders</strong> — tiap 15 menit</li>
            <li><strong>Send Payment Reminders</strong> — tiap jam</li>
            <li><strong>Coin Lifecycle</strong> — tiap hari 01:00 WIB</li>
            <li><strong>Birthday Vouchers</strong> — tiap hari 00:05 WIB</li>
        </ul>
        <p class="hint mt-3">
            Pastikan cron sudah disetup: <code>* * * * * cd /path/ke/project && php artisan schedule:run >> /dev/null 2>&1</code>
        </p>
    </x-admin.card>
@endsection
