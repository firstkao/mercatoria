<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\PaymentMethod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class PaymentMethodController extends Controller
{
    public function index(): View
    {
        return view('admin.payment-methods.index', [
            'methods' => PaymentMethod::query()->orderBy('sort_order')->orderBy('id')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.payment-methods.form', [
            'method' => new PaymentMethod(['is_active' => true, 'sort_order' => 0]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        // Proses upload gambar saat create.
        // Simpan hanya jika kolom qr_image benar-benar ada di tabel — kalau
        // migration belum dijalankan, upload gambar tidak boleh memicu error 500.
        if ($request->hasFile('qr_image')) {
            if (! $this->tableHasQrImage()) {
                return redirect()->route('admin.payment-methods.index')
                    ->withErrors(['qr_image' => 'Kolom qr_image belum ada di database. Jalankan `php artisan migrate` terlebih dahulu.']);
            }
            $data['qr_image'] = $request->file('qr_image')->store('qris', 'public');
        }

        $method = PaymentMethod::create($data);
        AdminLog::record('create_payment_method', $method, ['label' => $method->label]);

        return redirect()->route('admin.payment-methods.index')->with('status', 'Metode pembayaran ditambahkan.');
    }

    public function edit(PaymentMethod $paymentMethod): View
    {
        return view('admin.payment-methods.form', ['method' => $paymentMethod]);
    }

    public function update(Request $request, PaymentMethod $paymentMethod): RedirectResponse
    {
        $data = $this->validated($request);

        // ✅ PERBAIKAN: Hapus qr_image dari $data agar tidak menimpa dengan null 
        // jika user tidak mengupload gambar baru saat edit
        unset($data['qr_image']);

        // Proses upload gambar baru dan hapus gambar lama saat update.
        if ($request->hasFile('qr_image')) {
            if (! $this->tableHasQrImage()) {
                return redirect()->route('admin.payment-methods.index')
                    ->withErrors(['qr_image' => 'Kolom qr_image belum ada di database. Jalankan `php artisan migrate` terlebih dahulu.']);
            }
            if ($paymentMethod->qr_image) {
                Storage::disk('public')->delete($paymentMethod->qr_image);
            }
            $data['qr_image'] = $request->file('qr_image')->store('qris', 'public');
        }

        $paymentMethod->update($data);
        AdminLog::record('update_payment_method', $paymentMethod, ['label' => $paymentMethod->label]);

        return redirect()->route('admin.payment-methods.index')->with('status', 'Metode pembayaran diperbarui.');
    }

    public function destroy(PaymentMethod $paymentMethod): RedirectResponse
    {
        if ($paymentMethod->proofs()->exists()) {
            return back()->withErrors(['method' => 'Metode ini sudah dipakai di bukti pembayaran, tidak bisa dihapus. Nonaktifkan saja.']);
        }

        // Hapus file gambar dari storage sebelum data dihapus
        if ($paymentMethod->qr_image) {
            Storage::disk('public')->delete($paymentMethod->qr_image);
        }

        $label = $paymentMethod->label;
        $paymentMethod->delete();
        AdminLog::record('delete_payment_method', null, ['label' => $label]);

        return redirect()->route('admin.payment-methods.index')->with('status', 'Metode pembayaran dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'label'          => ['required', 'string', 'max:100'],
            'account_number' => ['nullable', 'string', 'max:100'],
            'account_name'   => ['nullable', 'string', 'max:100'],
            'instructions'   => ['nullable', 'string', 'max:2000'],
            'qr_image'       => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'sort_order'     => ['nullable', 'integer', 'min:0', 'max:999'],
            'is_active'      => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = ! empty($data['is_active']);
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        // Jangan pernah menulis kolom qr_image kalau kolomnya belum ada di
        // tabel (migration 2026_10_01 belum jalan) — mencegah QueryException.
        if (! $this->tableHasQrImage()) {
            unset($data['qr_image']);
        }

        return $data;
    }

    private function tableHasQrImage(): bool
    {
        try {
            return Schema::hasColumn('payment_methods', 'qr_image');
        } catch (\Throwable) {
            return false;
        }
    }
}
