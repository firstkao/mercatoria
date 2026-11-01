<?php

namespace App\Console\Commands;

use App\Models\OrderStatus;
use App\Models\Payment;
use Illuminate\Console\Command;

class PurgePaymentProofs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payments:purge-proofs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Hapus file bukti transfer order Selesai/Refunded/Hangus/Hangus Permanen/Pembayaran Gagal (PRD 17.7)';

    /**
     * Status terminal yang menutup riwayat pembayaran (PRD 17.7 + Hangus Permanen).
     */
    private const PURGE_STATUSES = [
        OrderStatus::Selesai->value,
        OrderStatus::DanaDikembalikan->value,
        OrderStatus::Hangus->value,
        OrderStatus::HangusPermanen->value,
        OrderStatus::PembayaranGagal->value,
    ];

    public function handle(): int
    {
        if (! (bool) setting('auto_delete_payment_proof', true)) {
            $this->info('Auto-delete bukti transfer nonaktif (setting auto_delete_payment_proof).');

            return self::SUCCESS;
        }

        $payments = Payment::query()
            ->whereNotNull('file_path')
            ->whereHas('order', fn ($q) => $q->whereIn('status', self::PURGE_STATUSES))
            ->get();

        $deleted = 0;
        foreach ($payments as $payment) {
            $absolute = storage_path('app/private/'.$payment->file_path);
            if (is_file($absolute) && @unlink($absolute)) {
                $deleted++;
            }
        }

        $this->info("File bukti transfer dihapus: {$deleted}.");

        return self::SUCCESS;
    }
}
