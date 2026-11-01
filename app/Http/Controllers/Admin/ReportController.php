<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
        // PRD 28: Outstanding = DP belum lunas → total order dikurangi pembayaran DP/pelunasan terverifikasi.
        $outstanding = Order::where('scheme', 'dp')
            ->whereNotIn('status', ['selesai', 'pembayaran_gagal', 'dana_dikembalikan', 'hangus', 'hangus_permanen'])
            ->withSum(['payments as paid_amount' => fn ($q) => $q
                ->where('status', Payment::STATUS_VERIFIED)
                ->whereIn('type', [Payment::TYPE_DP, Payment::TYPE_SETTLEMENT, Payment::TYPE_FULL])], 'amount')
            ->get()
            ->sum(fn (Order $order) => max(0, (float) $order->total - (float) $order->paid_amount));
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

    /**
     * AMANDEMEN-v1: export laporan inline via stream CSV (tanpa maatwebsite/excel).
     */
    public function export(): StreamedResponse
    {
        return response()->streamDownload(function (): void {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['no_order', 'status', 'scheme', 'total', 'tagihan_web', 'created_at']);

            Order::query()->orderBy('id')->chunk(500, function ($orders) use ($out): void {
                foreach ($orders as $order) {
                    fputcsv($out, [
                        $order->no_order, $order->status, $order->scheme,
                        $order->total, $order->tagihan_web, $order->created_at,
                    ]);
                }
            });

            fclose($out);
        }, 'laporan-penjualan-'.now()->format('Ymd').'.csv', ['Content-Type' => 'text/csv']);
    }

    /**
     * PRD 22.3: data grafik penjualan 30 hari terakhir (revenue per hari).
     * SUM(total) order per hari (created_at), kecuali pembayaran_gagal.
     * Zero-filled 30 elemen — visualisasi menyusul dari UI.
     */
    public function salesChart(): JsonResponse
    {
        $start = Carbon::today()->subDays(29);

        $rows = Order::query()
            ->where('created_at', '>=', $start)
            ->where('status', '!=', 'pembayaran_gagal')
            ->groupByRaw('DATE(created_at)')
            ->selectRaw('DATE(created_at) as date, SUM(total) as revenue')
            ->pluck('revenue', 'date');

        $data = collect(range(29, 0))
            ->map(function (int $ago) use ($rows) {
                $date = Carbon::today()->subDays($ago)->toDateString();

                return [
                    'date' => $date,
                    'revenue' => (float) ($rows[$date] ?? 0),
                ];
            })
            ->values();

        return response()->json($data);
    }
}
