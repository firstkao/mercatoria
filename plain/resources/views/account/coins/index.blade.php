@extends('layouts.app', ['title' => 'Koin Saya'])

@section('content')
<section class="card" style="max-width: 900px;">
    <h1>Koin Saya</h1>
    
    <div style="background: var(--primary-bg); padding: 20px; border-radius: 8px; margin-bottom: 30px; text-align: center; border: 1px solid #bae6fd;">
        <p class="muted" style="margin: 0 0 5px 0;">Total Koin Aktif</p>
        <h2 style="color: var(--primary); margin: 0; font-size: 2.5rem;">{{ number_format($activeCoins, 0, ',', '.') }}</h2>
        <p style="margin: 5px 0 0 0; font-size: 13px;">1 Koin = Rp1. Dapat digunakan maksimal 5% dari total pesanan.</p>
    </div>

    <h3>Riwayat Koin</h3>
    @if($coinHistory->isEmpty())
        <div class="empty">
            <p>Belum ada riwayat koin.</p>
        </div>
    @else
        <div class="panel panel--flush">
            <table class="table">
                <thead>
                    <tr>
                        <th>Tanggal Didapat</th>
                        <th>Sumber</th>
                        <th>Jumlah Awal</th>
                        <th>Sisa</th>
                        <th>Kedaluwarsa</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($coinHistory as $lot)
                        <tr>
                            <td>{{ $lot->earned_at->timezone('Asia/Jakarta')->translatedFormat('j M Y') }}</td>
                            <td>{{ str_replace('_', ' ', Str::title($lot->source)) }}</td>
                            <td style="color: var(--success); font-weight: bold;">+{{ number_format($lot->amount, 0, ',', '.') }}</td>
                            <td>{{ number_format($lot->remaining, 0, ',', '.') }}</td>
                            <td class="muted small">{{ $lot->expires_at->timezone('Asia/Jakarta')->translatedFormat('j M Y') }}</td>
                            <td>
                                @if($lot->remaining == 0 && $lot->expires_at <= now())
                                    <span class="badge badge--danger">Hangus</span>
                                @elseif($lot->remaining == 0)
                                    <span class="badge badge--muted">Habis Terpakai</span>
                                @else
                                    <span class="badge badge--on">Aktif</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div style="padding-top: 20px;">
            {{ $coinHistory->links('partials.pagination') }}
        </div>
    @endif
</section>
@endsection
