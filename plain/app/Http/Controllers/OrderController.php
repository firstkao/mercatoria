<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\Setting;
use App\Services\NotificationService;
use App\Support\QrisPayloadReader;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $orders = $request->user()->orders()->latest()->paginate(20);

        // FASE 1: "Tagihan Menunggu" — order yang butuh aksi bayar/unggah bukti.
        // Query TERPISAH dari seluruh order user (bukan hanya halaman pagination
        // saat ini), supaya order lama yang belum dibayar tetap muncul di sini.
        $unpaid = $request->user()->orders()
            ->whereIn('status', [
                OrderStatus::MenungguPembayaran->value,
                OrderStatus::PembayaranGagal->value,
            ])
            ->orderByRaw('COALESCE(payment_deadline_at, created_at) asc')
            ->take(5)
            ->get();

        return view('orders.index', compact('orders', 'unpaid'));
    }

    public function show(Request $request, string $orderNumber): View
    {
        $order = Order::where('user_id', $request->user()->id)
            ->where('order_number', $orderNumber)
            ->firstOrFail();

        $order->load([
            'items.variant.product',
            'paymentProofs.method',
            'marketplace',
            'statusHistory.admin',
        ]);

        $paymentMethods = PaymentMethod::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        // Data metode bayar untuk JS di halaman upload bukti. Disiapkan di
        // controller (bukan di dalam @json(...) di Blade) karena direktif
        // @json() memecah argumennya dengan explode(',') — array literal
        // seperti ini bila ditulis langsung di dalam @json() akan terpotong
        // dan menghasilkan PHP tidak valid → halaman detail pesanan 500.
        $paymentMethodsJs = $paymentMethods
            ->map(fn (PaymentMethod $pm): array => [
                'id' => $pm->id,
                'type' => $pm->type ?? 'bank',
                'account_number' => $pm->account_number,
                'account_name' => $pm->account_name,
                'instructions' => $pm->instructions,
                // QR image tetap di disk 'public' (public/uploads/...),
                // karena itu file publik metode pembayaran, bukan bukti bayar.
                'qr_image' => ! empty($pm->qr_image) ? \Illuminate\Support\Facades\Storage::disk('public')->url($pm->qr_image) : null,
            ])
            ->values();

        return view('orders.show', compact('order', 'paymentMethods', 'paymentMethodsJs'));
    }

    public function proof(Request $request, string $orderNumber): RedirectResponse
    {
        $order = Order::where('user_id', $request->user()->id)
            ->where('order_number', $orderNumber)
            ->firstOrFail();

        abort_unless(
            in_array($order->status, ['menunggu_pembayaran', 'pembayaran_gagal'], true),
            422,
            'Status pesanan tidak memungkinkan upload bukti.'
        );

        // ✅ BUG FIX: dulu tidak ada cek batas waktu. Order yang sudah lewat
        // payment_deadline_at tetap bisa diunggah buktinya (kecuali keburu
        // dibatalkan scheduler), padahal harga & kurs sudah disnapshot untuk
        // jendela waktu tertentu. Sekarang ditolak lebih awal dengan pesan jelas.
        if ($order->payment_deadline_at !== null && $order->payment_deadline_at->lessThan(now())) {
            return back()->withErrors([
                'proof' => 'Batas waktu pembayaran sudah lewat. Pesanan ini menunggu pembatalan otomatis — hubungi admin jika kamu sudah transfer.',
            ]);
        }

        // ✅ BUG FIX: Cegah double-submit — jika masih ada bukti berstatus pending,
        // user harus menunggu review admin sebelum upload lagi.
        abort_unless(
            ! $order->paymentProofs()->where('status', 'pending')->exists(),
            422,
            'Bukti pembayaran kamu masih dalam verifikasi admin.'
        );

        $request->validate([
            // ✅ PERBAIKAN: Cek juga is_active agar user tidak bisa pilih
            // metode pembayaran yang sudah dinonaktifkan admin
            'payment_method_id' => [
                'required',
                Rule::exists('payment_methods', 'id')->where('is_active', true),
            ],
            'proof' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
        ], [
            'payment_method_id.exists' => 'Metode pembayaran tidak valid atau sudah dinonaktifkan.',
        ]);

        // =========================================================
        // QRIS fee: kalau nominal tagihan > Rp500.000 dan metode = QRIS,
        // tambahkan biaya 0,3% dari nominal. Disimpan ke orders.qris_fee_idr
        // supaya: (a) user tahu persis nominal transfer, (b) kalau user
        // re-upload dengan metode lain, fee bisa di-reset.
        // =========================================================
        $method = PaymentMethod::find($request->input('payment_method_id'));
        $isQris = $method !== null && ($method->type ?? 'bank') === 'qris';
        $qrisFee = 0;
        if ($isQris && $order->pay_now_idr > 500_000) {
            $qrisFee = (int) floor($order->pay_now_idr * 0.003);
        }
        $order->update(['qris_fee_idr' => $qrisFee]);

        $file = $request->file('proof');

        // ✅ BUG FIX: jangan paksakan ekstensi '.webp' untuk semua file.
        // Validasi menerima jpeg/png/jpg/webp; sebelumnya file JPEG disimpan
        // dengan nama xxx.webp sehingga admin melihat "bukti" berlabel webp
        // yang isinya data JPEG (membingungkan browser saat preview/download).
        // Format asli dipertahankan; konversi ke webp hanya bila aman dilakukan.
        $extension = strtolower($file->getClientOriginalExtension() ?: ($file->guessExtension() ?? 'jpg'));
        if (! in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            $extension = 'jpg';
        }

        // ✅ PERBAIKAN: Bungkus proses gambar dalam try-catch.
        // Jika GD/Imagick tidak tersedia di server, user dapat pesan error
        // yang jelas, bukan 500 error.
        $storedContent = null;
        try {
            $manager = new ImageManager(new Driver());
            $img = $manager->read($file)->scaleDown(width: 800);
            // Simpan sebagai webp HANYA jika hasilnya benar-benar WebP;
            // jika tidak, gunakan format asli agar ekstensi & isi file cocok.
            $encoded = (string) $img->toWebp(75);
            if (str_starts_with($encoded, 'RIFF') && str_contains($encoded, 'WEBP')) {
                $storedContent = $encoded;
                $extension = 'webp';
            } else {
                $storedContent = (string) (in_array($extension, ['jpg', 'jpeg', 'webp'], true)
                    ? $img->toJpeg(80)
                    : $img->toPng());
            }
        } catch (\Throwable $e) {
            // Pipeline gambar gagal (GD tidak mendukung format, dsb.) —
            // simpan file apa adanya daripada menolak upload.
            try {
                $storedContent = file_get_contents($file->getRealPath());
            } catch (\Throwable $e2) {
                return back()->withErrors([
                    'proof' => 'Gagal memproses gambar. Silakan coba lagi atau hubungi admin.',
                ]);
            }
        }

        $filename = 'payment-proofs/' . Str::random(40) . '.' . $extension;

        // Simpan file ke storage private (setelah nama & konten final ditentukan).
        // Disk 'payment_proofs' → root storage/app/private, jadi file fisik
        // ada di storage/app/private/payment-proofs/xxx.webp. Akses lewat
        // route payment-proof.show (cek owner/admin).
        Storage::disk('payment_proofs')->put($filename, $storedContent);

        // ✅ PERBAIKAN: Buat record DB. Jika gagal, hapus file yang sudah
        // diupload agar tidak ada file sampah (orphaned file) di server.
        // Cek double-submit di sini dibuat ATOMIK (lock baris order + cek ulang
        // di dalam transaksi) supaya dua upload paralel tidak sama-sama lolos.
        try {
            DB::transaction(function () use ($order, $request, $filename, $qrisFee): void {
                $lockedOrder = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();

                if ($lockedOrder->paymentProofs()->where('status', 'pending')->exists()) {
                    throw ValidationException::withMessages([
                        'proof' => 'Bukti pembayaran kamu masih dalam verifikasi admin.',
                    ]);
                }

                $order->paymentProofs()->create([
                    'payment_method_id' => $request->input('payment_method_id'),
                    // ✅ BUG FIX: isi payment_stage sesuai skema order — dulu
                    // di-hardcode 'lunas' walau kolomnya varchar(10) dan order DP
                    // akan salah label. FP -> lunas, selain itu dp.
                    'payment_stage' => $order->payment_scheme === 'FP' ? 'lunas' : 'dp',
                    // Nominal yang sebenarnya ditransfer user = tagihan + biaya QRIS
                    // (kalau ada). Admin akan lihat angka yang cocok dengan mutasi.
                    'amount_idr' => $order->pay_now_idr + $qrisFee,
                    'proof_path' => $filename,
                    'status' => 'pending',
                    'uploaded_at' => now(),
                ]);

                $order->update(['status' => 'ditahan']);
            });

            // =========================================================
            // FASE 2 — Verifikasi otomatis QRIS.
            // Bila metode = qris & auto_verify aktif, coba baca payload QR
            // dari gambar bukti dan cocokkan ID merchant dengan rekening/
            // merchant id metode. Cocok -> langsung approved + status order
            // 'pembayaran_diterima' (admin tidak perlu review manual).
            // Gagal/tidak cocok -> biarkan 'ditahan' (review manual seperti
            // biasa). Keputusan auto-verification dicatat ke payment_proofs
            // lewat kolom status/reviewed_at agar audit trail tetap ada.
            // =========================================================
            if ($method !== null && $method->isAutoVerifiable()) {
                try {
                    $payload = QrisPayloadReader::read(
                        Storage::disk('payment_proofs')->path($filename)
                    );
                    $matched = QrisPayloadReader::matchesMerchant(
                        $payload,
                        $method->account_number ?: $method->account_name
                    );

                    if ($matched) {
                        DB::transaction(function () use ($order, $filename): void {
                            $proofRow = DB::table('payment_proofs')
                                ->where('order_id', $order->id)
                                ->where('proof_path', $filename)
                                ->lockForUpdate()
                                ->first();

                            if ($proofRow === null) {
                                return;
                            }

                            DB::table('payment_proofs')->where('id', $proofRow->id)->update([
                                'status' => 'approved',
                                'reviewed_at' => now(),
                                'updated_at' => now(),
                            ]);

                            $fromStatus = $order->status;

                            DB::table('orders')->where('id', $order->id)->update([
                                'status' => 'pembayaran_diterima',
                                'paid_at' => now(),
                                'updated_at' => now(),
                            ]);

                            DB::table('order_status_history')->insert([
                                'order_id' => $order->id,
                                'from_status' => $fromStatus,
                                'to_status' => 'pembayaran_diterima',
                                'changed_by' => 'system',
                                'admin_id' => null,
                                'note' => 'Verifikasi otomatis QRIS (merchant ID cocok)',
                                'created_at' => now(),
                                // BUG FIX: tabel order_status_history tidak punya
                                // kolom updated_at (lihat migration
                                // 2024_01_01_000013) → insert ini melempar
                                // "Unknown column 'updated_at'" dan seluruh
                                // transaksi di-rollback (order nyangkut).
                            ]);
                        });

                        try {
                            NotificationService::payment(
                                $order->user,
                                $order->fresh(),
                                'approved'
                            );
                        } catch (\Throwable $e) {
                            Log::warning('Gagal kirim notifikasi auto-approve QRIS', [
                                'order' => $order->order_number,
                                'error' => $e->getMessage(),
                            ]);
                        }

                        ActivityLog::record($request->user(), 'auto_verify_payment', $request, [
                            'order_number' => $order->order_number,
                            'payment_method' => $method->label,
                        ]);

                        return redirect()
                            ->route('account.orders.show', $orderNumber)
                            ->with('status', 'Pembayaran kamu terverifikasi otomatis. Pesanan sedang diproses.');
                    }
                } catch (\Throwable $e) {
                    // Decoder gagal total — aman, jatuh ke review manual.
                    Log::info('Auto-verify QRIS dilewati (decoder gagal/tidak cocok)', [
                        'order' => $order->order_number,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        } catch (ValidationException $e) {
            Storage::disk('payment_proofs')->delete($filename);
            throw $e;
        } catch (\Throwable $e) {
            Storage::disk('payment_proofs')->delete($filename);

            return back()->withErrors([
                'proof' => 'Gagal menyimpan bukti pembayaran. Silakan coba lagi.',
            ]);
        }

        ActivityLog::record($request->user(), 'upload_payment_proof', $request, [
            'order_number' => $order->order_number,
        ]);

        return redirect()
            ->route('account.orders.show', $orderNumber)
            ->with('status', 'Bukti pembayaran berhasil diunggah. Menunggu verifikasi admin.');
    }

    public function invoice(Request $request, string $orderNumber): View
    {
        $order = Order::where('user_id', $request->user()->id)
            ->where('order_number', $orderNumber)
            ->firstOrFail();

        $order->load(['items.variant', 'user', 'marketplace', 'paymentProofs.method']);

        return view('orders.print.invoice', [
            'order' => $order,
            'branding' => [
                'store_name' => 'MERCATORIA',
                'store_address' => Setting::get('store_address'),
                'contact_email' => Setting::get('contact_email'),
                'contact_whatsapp' => Setting::get('contact_whatsapp'),
                'has_logo' => file_exists(public_path('images/logo.png')),
                'logo_url' => asset('images/logo.png'),
            ],
            'generatedAt' => now(),
        ]);
    }
}
