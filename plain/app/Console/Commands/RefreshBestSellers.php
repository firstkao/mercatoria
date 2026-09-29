<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\Setting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RefreshBestSellers extends Command
{
    protected $signature = 'products:refresh-best-sellers';
    protected $description = 'Hitung ulang ranking produk terlaris dari data pembelian';

    /**
     * Status order yang dihitung sebagai penjualan sah.
     */
    private const PAID_STATUSES = [
        'pembayaran_diterima',
        'sedang_diproses',
        'sampai_wh_cn',
        'dikirim_ke_indonesia',
        'bea_cukai',
        'sampai_wh_indonesia',
        'selesai',
    ];

    /**
     * Ukuran chunk untuk bulk update.
     * Kalau kepanjangan, MySQL bisa error "max_allowed_packet".
     */
    private const CHUNK_SIZE = 500;

    public function handle(): int
    {
        $periodDays = Setting::integer('best_seller_period_days', 365);
        $limit = Setting::integer('best_seller_limit', 8);
        $minSales = Setting::integer('best_seller_min_sales', 1);
        $since = now()->subDays($periodDays);

        $this->info("Menghitung best seller periode {$periodDays} hari terakhir...");

        // ============================================================
        // 1 QUERY: aggregate qty terjual per product_id
        // ============================================================
        $rows = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('product_variants', 'product_variants.id', '=', 'order_items.product_variant_id')
            ->whereIn('orders.status', self::PAID_STATUSES)
            ->where('orders.created_at', '>=', $since)
            ->select('product_variants.product_id', DB::raw('SUM(order_items.quantity) as total_qty'))
            ->groupBy('product_variants.product_id')
            ->orderByDesc('total_qty')
            ->get();

        // ============================================================
        // 1 QUERY: reset semua produk
        // ============================================================
        Product::query()->update([
            'best_seller_score' => 0,
            'best_seller_rank' => null,
        ]);

        if ($rows->isEmpty()) {
            $this->warn('Belum ada penjualan dalam periode ini.');
            return self::SUCCESS;
        }

        // ============================================================
        // 1 QUERY: ambil set produk yang di-exclude (biar loop gak query lagi)
        // ============================================================
        $excludedSet = Product::query()
            ->whereIn('id', $rows->pluck('product_id')->all())
            ->where('exclude_best_seller', true)
            ->pluck('id')
            ->flip() // jadikan Set untuk lookups O(1)
            ->all();

        // ============================================================
        // Build data update (di memori, tanpa query)
        // ============================================================
        $rank = 0;
        $assigned = 0;
        $updates = []; // [id => ['score' => int, 'rank' => int|null]]

        foreach ($rows as $row) {
            $qty = (int) $row->total_qty;

            if ($qty < $minSales) {
                continue;
            }

            // Produk yang di-exclude: score tetap di-update, rank = null
            if (isset($excludedSet[$row->product_id])) {
                $updates[$row->product_id] = ['score' => $qty, 'rank' => null];
                continue;
            }

            $rank++;

            $updates[$row->product_id] = [
                'score' => $qty,
                'rank' => $rank <= $limit ? $rank : null,
            ];

            if ($rank <= $limit) {
                $assigned++;
            }
        }

        // ============================================================
        // Bulk update dalam chunk (3-N query total, tergantung jumlah chunk)
        // ============================================================
        $chunks = array_chunk($updates, self::CHUNK_SIZE, true);
        $this->info("Melakukan bulk update untuk " . count($updates) . " produk dalam " . count($chunks) . " batch…");

        foreach ($chunks as $chunk) {
            $this->bulkUpdate($chunk);
        }

        $this->info("✓ {$assigned} produk teratas ditandai best seller dari {$rows->count()} produk terjual.");

        return self::SUCCESS;
    }

    /**
     * Bulk UPDATE pakai CASE WHEN — 1 query untuk banyak produk.
     *
     * @param  array<int, array{score: int, rank: int|null}>  $updates  [product_id => ['score' => int, 'rank' => int|null]]
     */
    private function bulkUpdate(array $updates): void
    {
        if (empty($updates)) {
            return;
        }

        $ids = array_keys($updates);
        $idPlaceholders = implode(',', array_fill(0, count($ids), '?'));

        $scoreCases = [];
        $rankCases = [];
        $scoreBindings = [];
        $rankBindings = [];

        foreach ($updates as $id => $data) {
            // CASE WHEN id = ? THEN score
            $scoreCases[] = 'WHEN ? THEN ?';
            $scoreBindings[] = $id;
            $scoreBindings[] = $data['score'];

            // CASE WHEN id = ? THEN rank
            $rankCases[] = 'WHEN ? THEN ?';
            $rankBindings[] = $id;
            $rankBindings[] = $data['rank'];
        }

        $sql = "UPDATE products SET
            best_seller_score = CASE id " . implode(' ', $scoreCases) . " END,
            best_seller_rank  = CASE id " . implode(' ', $rankCases) . " END
            WHERE id IN ({$idPlaceholders})";

        $bindings = array_merge($scoreBindings, $rankBindings, $ids);

        DB::update($sql, $bindings);
    }
}