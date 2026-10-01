<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $orders = $request->user()->orders()->latest()->paginate(20);

        return view('orders.index', compact('orders'));
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

        return view('orders.show', compact('order', 'paymentMethods'));
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

        // Simpan file ke storage (setelah nama & konten final ditentukan)
        Storage::disk('public')->put($filename, $storedContent);

        // ✅ PERBAIKAN: Buat record DB. Jika gagal, hapus file yang sudah
        // diupload agar tidak ada file sampah (orphaned file) di server.
        try {
            $order->paymentProofs()->create([
                'payment_method_id' => $request->input('payment_method_id'),
                // ✅ BUG FIX: isi payment_stage sesuai skema order — dulu
                // di-hardcode 'lunas' walau kolomnya varchar(10) dan order DP
                // akan salah label. FP -> lunas, selain itu dp.
                'payment_stage' => $order->payment_scheme === 'FP' ? 'lunas' : 'dp',
                'amount_idr' => $order->pay_now_idr,
                'proof_path' => $filename,
                'status' => 'pending',
                'uploaded_at' => now(),
            ]);

            $order->update(['status' => 'ditahan']);
        } catch (\Throwable $e) {
            Storage::disk('public')->delete($filename);

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
