<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\Order;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PaymentProofController extends Controller
{
    public function index(Request $request): View
    {
        $status = in_array($request->query('status'), ['pending', 'approved', 'rejected', 'all'], true)
            ? $request->query('status')
            : 'pending';

        // ✅ BUG FIX: pakai LEFT JOIN untuk payment_methods. Migration
            // create_payment_proofs membuat payment_method_id sebagai FK NOT NULL,
            // tapi di DB produksi kolom ini nullable — jika admin menghapus metode
            // pembayaran yang dipakai sebuah bukti, INNER JOIN membuat baris bukti
            // tersebut hilang dari daftar (bukti "menghilang" tanpa jejak).
        $proofs = DB::table('payment_proofs')
            ->join('orders', 'orders.id', '=', 'payment_proofs.order_id')
            ->join('users', 'users.id', '=', 'orders.user_id')
            ->leftJoin('payment_methods', 'payment_methods.id', '=', 'payment_proofs.payment_method_id')
            ->when($status !== 'all', fn ($query) => $query->where('payment_proofs.status', $status))
            // ✅ BUG FIX: orders pakai kolom `status` (bukan `order_status`) —
            // query lama melempar "Unknown column" → halaman bukti bayar error 500.
            ->select('payment_proofs.id', 'payment_proofs.status', 'payment_proofs.amount_idr', 'payment_proofs.uploaded_at', 'orders.order_number', 'orders.pay_now_idr', 'orders.status as order_status', 'users.full_name', 'users.email', DB::raw("COALESCE(payment_methods.label, '(metode dihapus)') as payment_method_label"))
            ->latest('payment_proofs.uploaded_at')
            ->paginate(25)
            // BUG FIX: hasil DB::table() adalah stdClass, jadi kolom tanggal
            // berupa STRING mentah. View memanggil ->timezone() → 500
            // "Call to a member function timezone() on string".
            ->through(function (object $proof): object {
                $proof->uploaded_at = $proof->uploaded_at
                    ? Carbon::parse($proof->uploaded_at)
                    : null;

                return $proof;
            })
            ->withQueryString();

        return view('admin.payments.index', compact('proofs', 'status'));
    }

    public function show(int $proof): View
    {
        // ✅ PERAPIAN: sama seperti index(), LEFT JOIN agar detail bukti tetap
        // bisa dibuka walau payment method-nya sudah dihapus.
        $proofRecord = DB::table('payment_proofs')
            ->join('orders', 'orders.id', '=', 'payment_proofs.order_id')
            ->join('users', 'users.id', '=', 'orders.user_id')
            ->leftJoin('payment_methods', 'payment_methods.id', '=', 'payment_proofs.payment_method_id')
            ->where('payment_proofs.id', $proof)
            // ✅ BUG FIX: sama seperti index(), kolom orders adalah `status`.
            ->select('payment_proofs.*', 'orders.order_number', 'orders.pay_now_idr', 'orders.status as order_status', 'users.full_name', 'users.email', DB::raw("COALESCE(payment_methods.label, '(metode dihapus)') as payment_method_label"))
            ->firstOrFail();

        // BUG FIX: sama seperti index(), baris DB::table() adalah stdClass dengan
        // tanggal berupa string, sedangkan view memanggil ->timezone() pada
        // uploaded_at & reviewed_at → 500.
        $proofRecord->uploaded_at = $proofRecord->uploaded_at
            ? Carbon::parse($proofRecord->uploaded_at)
            : null;
        $proofRecord->reviewed_at = $proofRecord->reviewed_at
            ? Carbon::parse($proofRecord->reviewed_at)
            : null;

        return view('admin.payments.show', [
            'proof' => $proofRecord,
            'imageUrl' => Storage::disk('public')->url($proofRecord->proof_path),
        ]);
    }

    public function approve(int $proof): RedirectResponse
    {
        $order = null;

        DB::transaction(function () use ($proof, &$order): void {
            $record = DB::table('payment_proofs')->where('id', $proof)->lockForUpdate()->firstOrFail();
            abort_unless($record->status === 'pending', 422, 'Bukti pembayaran sudah diproses.');

            $orderRow = DB::table('orders')->where('id', $record->order_id)->lockForUpdate()->firstOrFail();

            // ✅ BUG FIX: Guard transisi. Kalau admin sudah menggeser status
            // manual (mis. langsung ke sedang_diproses) sementara bukti masih
            // pending, approve TIDAK BOLEH memaksa status kembali ke
            // pembayaran_diterima — itu memundurkan pipeline dan memicu
            // cashback/bonus/referral ganda saat order digeser ke 'selesai'
            // lagi (updateStatus hanya idempotent utk source 'cashback',
            // tidak utk bonus_pertama/referral). Bukti tetap ditandai
            // approved + paid_at diisi; status order dibiarkan.
            $forceOrderStatus = in_array($orderRow->status, ['menunggu_pembayaran', 'ditahan'], true);

            $fromStatus = $orderRow->status;

            DB::table('payment_proofs')->where('id', $record->id)->update([
                'status' => 'approved',
                'reviewed_at' => now(),
                'updated_at' => now(),
            ]);

            if ($forceOrderStatus) {
                DB::table('orders')->where('id', $orderRow->id)->update([
                    'status' => 'pembayaran_diterima',
                    'paid_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                DB::table('orders')->where('id', $orderRow->id)->update([
                    'paid_at' => DB::raw('COALESCE(paid_at, NOW())'),
                    'updated_at' => now(),
                ]);
            }

            // ✅ BUG FIX: Catat history dua langkah (ditahan → pembayaran_diterima
            // melewati satu status). Tanpa ini, timeline customer bolong: order
            // tiba-tiba "lompat" dari Menunggu Pembayaran ke Pembayaran Diterima.
            // Hanya backfill jika memang belum ada record masuk-keluar 'ditahan'
            // untuk order ini — supaya proof kedua setelah reject tidak membuat
            // entri duplikat / membanjiri timeline.
            if ($fromStatus === 'ditahan') {
                $alreadyLogged = DB::table('order_status_history')
                    ->where('order_id', $orderRow->id)
                    ->where(function ($q): void {
                        $q->where('to_status', 'ditahan')->orWhere('from_status', 'ditahan');
                    })
                    ->exists();

                if (! $alreadyLogged) {
                    DB::table('order_status_history')->insert([
                        'order_id' => $orderRow->id,
                        'from_status' => 'menunggu_pembayaran',
                        'to_status' => 'ditahan',
                        // ✅ 'user' (bukan 'customer'): konvensi changed_by di codebase
                        // ini adalah admin|user|system — lihat timeline.blade.php dan
                        // OrderManagementController::recordHistory(). Kalau 'customer',
                        // atribusi "oleh pembeli" tidak muncul di timeline.
                        'changed_by' => 'user',
                        'admin_id' => null,
                        'note' => 'Bukti pembayaran diunggah.',
                        'created_at' => $record->uploaded_at ?? now(),
                    ]);
                }
            }

            DB::table('order_status_history')->insert([
                'order_id' => $orderRow->id,
                'from_status' => $fromStatus,
                'to_status' => $forceOrderStatus ? 'pembayaran_diterima' : $fromStatus,
                'changed_by' => 'admin',
                'admin_id' => auth('admin')->id(),
                'note' => $forceOrderStatus
                    ? null
                    : 'Bukti pembayaran disetujui (status order sudah diubah manual, tidak diotomatisasi).',
                'created_at' => now(),
            ]);

            // ✅ Update user role kalau masih spammer
            $user = DB::table('users')->where('id', $orderRow->user_id)->lockForUpdate()->first();
            if ($user && $user->role === 'spammer') {
                DB::table('users')->where('id', $user->id)->update([
                    'role' => 'customer',
                    'became_customer_at' => now(),
                    'view_quota_used' => 0,
                    'updated_at' => now(),
                ]);
            }

            // ❌ REVERT: Koin TIDAK diberikan di sini. Di codebase ini koin
            // customer adalah CASHBACK yang cair saat order 'selesai'
            // (OrderManagementController::updateStatus). Memberikan lot kedua
            // ('purchase') saat approve = menggandakan saldo koin customer.
            // Kolom coin_estimate di orders memang bernama "estimasi", dan
            // email notifikasi order_status juga menjanjikan koin saat selesai.

            AdminLog::record('approve_payment', null, [
                'order_id' => $orderRow->id,
                'proof_id' => $record->id,
            ]);

            $order = $orderRow;
        });

        // Notifikasi in-app di luar transaction
        if ($order) {
            $user = \App\Models\User::find($order->user_id);
            if ($user) {
                try {
                    $orderModel = Order::find($order->id);
                    NotificationService::payment($user, $orderModel, 'approved');
                } catch (\Throwable $e) {
                    // ✅ Rapikan: jangan telan diam-diam — approval tetap sukses,
                    // tapi kegagalan notifikasi dicatat agar bisa ditelusuri.
                    Log::warning('Gagal kirim notifikasi pembayaran disetujui', [
                        'order_id' => $order->id,
                        'user_id' => $user->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        return redirect()->route('admin.payments.index')->with('status', 'Bukti pembayaran disetujui dan status order diperbarui.');
    }

    public function reject(Request $request, int $proof): RedirectResponse
    {
        $data = $request->validate(['reject_reason' => ['required', 'string', 'max:1000']]);
        $order = null;

        DB::transaction(function () use ($proof, $data, &$order): void {
            $record = DB::table('payment_proofs')->where('id', $proof)->lockForUpdate()->firstOrFail();
            abort_unless($record->status === 'pending', 422, 'Bukti pembayaran sudah diproses.');

            DB::table('payment_proofs')->where('id', $record->id)->update([
                'status' => 'rejected',
                'reject_reason' => $data['reject_reason'],
                'reviewed_at' => now(),
                'resubmit_deadline_at' => now()->addHours(24),
                'updated_at' => now(),
            ]);

            $currentOrder = DB::table('orders')->where('id', $record->order_id)->lockForUpdate()->firstOrFail();

            // ✅ BUG FIX: Reset payment_deadline_at. Order dg status 'ditahan'
            // TIDAK dicek oleh scheduler pembatalan (CancelUnpaidOrders hanya
            // memindai menunggu_pembayaran/pembayaran_gagal). Selama ini saat
            // reject, order langsung bisa dibatalkan otomatis padahal email
            // notifikasi menjanjikan "jendela resubmit 24 jam" — karena
            // payment_deadline_at lama sudah lewat. Jendela resubmit di proof
            // (resubmit_deadline_at) dan di order kini disamakan: now + 24 jam.
            $newDeadline = now()->addHours(24);

            // ✅ BUG FIX (guard transisi): menolak bukti TIDAK BOLEH memundurkan
            // order yang sudah jalan/selesai. Dulu status dipaksa jadi
            // 'pembayaran_gagal' apa pun status order saat ini — order
            // 'selesai'/'dikirim_ke_indonesia' tiba-tiba keluar dari omzet
            // (OrderStatus::revenueValues()) lalu dibatalkan otomatis oleh
            // CancelUnpaidOrders 24 jam kemudian, beserta voucher-nya.
            $canDowngrade = in_array($currentOrder->status, [
                OrderStatus::MenungguPembayaran->value,
                OrderStatus::Ditahan->value,
                OrderStatus::PembayaranGagal->value,
            ], true);

            if ($canDowngrade) {
                DB::table('orders')->where('id', $record->order_id)->update([
                    'status' => OrderStatus::PembayaranGagal->value,
                    'payment_deadline_at' => $newDeadline,
                    'updated_at' => now(),
                ]);

                DB::table('order_status_history')->insert([
                    'order_id' => $record->order_id,
                    'from_status' => $currentOrder->status,
                    'to_status' => OrderStatus::PembayaranGagal->value,
                    'changed_by' => 'admin',
                    'admin_id' => auth('admin')->id(),
                    'note' => $data['reject_reason'],
                    'created_at' => now(),
                ]);
            } else {
                // Bukti tetap ditandai rejected, tapi status order dibiarkan.
                Log::warning('Bukti pembayaran ditolak, status order tidak diturunkan', [
                    'proof_id' => $record->id,
                    'order_id' => $record->order_id,
                    'order_status' => $currentOrder->status,
                ]);
            }

            AdminLog::record('reject_payment', null, [
                'order_id' => $record->order_id,
                'proof_id' => $record->id,
                'reason' => $data['reject_reason'],
            ]);

            $order = DB::table('orders')->where('id', $record->order_id)->first();
        });

        // Notifikasi in-app + email di luar transaction
        if ($order) {
            $user = \App\Models\User::find($order->user_id);

            if ($user) {
                try {
                    $orderModel = Order::find($order->id);
                    NotificationService::payment($user, $orderModel, 'rejected', $data['reject_reason']);
                } catch (\Throwable $e) {
                    // ✅ Rapikan: catat kegagalan notifikasi (bukan ditelan diam-diam)
                    Log::warning('Gagal kirim notifikasi pembayaran ditolak', [
                        'order_id' => $order->id,
                        'user_id' => $user->id,
                        'error' => $e->getMessage(),
                    ]);
                }

                if ($user->email) {
                    try {
                        Mail::to($user->email)->send(new \App\Mail\PaymentRejected(Order::find($order->id), $data['reject_reason']));
                    } catch (\Throwable $e) {
                        Log::warning('Gagal kirim email penolakan pembayaran', [
                            'order_id' => $order->id,
                            'user_email' => $user->email,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }
        }

        return redirect()->route('admin.payments.index')->with('status', 'Bukti pembayaran ditolak.');
    }
}
