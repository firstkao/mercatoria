<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\View\View;

class ReportController extends Controller
{
    /**
     * Kartu dasar laporan (PRD 28). "Denda Diterima" menyusul di bagian (h).
     */
    public function index(): View
    {
        $verified = fn () => Payment::where('status', Payment::STATUS_VERIFIED);

        $totalDp = (clone $verified())->where('type', Payment::TYPE_DP)->sum('amount');
        $totalSettlement = (clone $verified())->where('type', Payment::TYPE_SETTLEMENT)->sum('amount');
        $totalPenjualan = Order::where('status', 'selesai')->sum('total');
        $outstanding = Order::where('scheme', 'dp')
            ->whereNotIn('status', ['selesai', 'pembayaran_gagal', 'dana_dikembalikan', 'hangus', 'hangus_permanen'])
            ->sum('tagihan_web');
        $hangus = (clone $verified())
            ->whereHas('order', fn ($q) => $q->whereIn('status', ['hangus', 'hangus_permanen']))
            ->sum('amount');
        // PRD 51.2: pendapatan denda, terpisah dari DP/Pelunasan.
        $dendaDiterima = (clone $verified())->where('type', Payment::TYPE_PENALTY)->sum('amount');

        return view('admin.reports.index', [
            'totalDp' => $totalDp,
            'totalSettlement' => $totalSettlement,
            'totalPenjualan' => $totalPenjualan,
            'outstanding' => $outstanding,
            'hangus' => $hangus,
            'dendaDiterima' => $dendaDiterima,
        ]);
    }
}
