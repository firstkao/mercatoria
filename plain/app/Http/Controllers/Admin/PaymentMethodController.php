<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\PaymentMethod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;
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
        // Simpan tanpa validasi ketat dulu: kalau DB belum di-migrate, field
        // opsional (mis. Instructions) tidak boleh hilang saat retry form.
        $input = $request->except('_token', '_method');

        try {
            $data = $this->validated($request);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withInput($input)->withErrors($e->errors());
        }

        // Proses upload gambar saat create.
        if ($request->hasFile('qr_image')) {
            if (! $this->tableHasQrImage()) {
                return redirect()->route('admin.payment-methods.index')
                    ->withInput($input)
                    ->withErrors(['qr_image' => 'Kolom qr_image belum ada di database. Jalankan `php artisan migrate` terlebih dahulu.']);
            }
            $data['qr_image'] = $request->file('qr_image')->store('qris', 'public');
        }

        try {
            $method = PaymentMethod::create($data);
        } catch (\Illuminate\Database\QueryException $e) {
            // Penyebab 500 paling umum: struktur DB produksi (dari SQL dump lama)
            // berbeda dari kode — kolom `type` NOT NULL tanpa default, atau kolom
            // instructions/qr_image belum ada karena migration belum jalan.
            Log::error('Gagal menyimpan metode pembayaran: ' . $e->getMessage());

            return redirect()->back()
                ->withInput($input)
                ->withErrors(['label' => 'Gagal menyimpan ke database: struktur tabel payment_methods belum cocok dengan aplikasi. Jalankan `php artisan migrate` di server, lalu coba simpan lagi.']);
        }

        AdminLog::record('create_payment_method', $method, ['label' => $method->label]);

        return redirect()->route('admin.payment-methods.index')->with('status', 'Metode pembayaran ditambahkan.');
    }

    public function edit(PaymentMethod $paymentMethod): View
    {
        return view('admin.payment-methods.form', ['method' => $paymentMethod]);
    }

    public function update(Request $request, PaymentMethod $paymentMethod): RedirectResponse
    {
        $input = $request->except('_token', '_method');

        try {
            $data = $this->validated($request);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withInput($input)->withErrors($e->errors());
        }

        // ✅ PERBAIKAN: Hapus qr_image dari $data agar tidak menimpa dengan null 
        // jika user tidak mengupload gambar baru saat edit
        unset($data['qr_image']);

        // Proses upload gambar baru dan hapus gambar lama saat update.
        if ($request->hasFile('qr_image')) {
            if (! $this->tableHasQrImage()) {
                return redirect()->back()
                    ->withInput($input)
                    ->withErrors(['qr_image' => 'Kolom qr_image belum ada di database. Jalankan `php artisan migrate` terlebih dahulu.']);
            }
            if ($paymentMethod->qr_image) {
                Storage::disk('public')->delete($paymentMethod->qr_image);
            }
            $data['qr_image'] = $request->file('qr_image')->store('qris', 'public');
        }

        try {
            $paymentMethod->update($data);
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Gagal memperbarui metode pembayaran: ' . $e->getMessage());

            return redirect()->back()
                ->withInput($input)
                ->withErrors(['label' => 'Gagal menyimpan ke database: struktur tabel payment_methods belum cocok dengan aplikasi. Jalankan `php artisan migrate` di server, lalu coba simpan lagi.']);
        }

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

        // Jangan pernah menulis kolom yang belum ada di tabel — mencegah
        // QueryException (error 500) saat migration belum jalan di produksi.
        if (! $this->tableHasQrImage()) {
            unset($data['qr_image']);
        }
        if (! Schema::hasColumn('payment_methods', 'instructions')) {
            unset($data['instructions']);
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
