<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\PaymentMethod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PaymentMethodController extends Controller
{
    /** Cache kolom fisik tabel payment_methods dalam satu request. */
    private array $columnCache = [];

    /**
     * Tiga jenis pembayaran yang didukung aplikasi:
     * - bank    : transfer rekening -> wajib nomor rekening + atas nama
     * - qris    : scan QR  -> cukup upload foto QR code
     * - barcode : scan barcode -> cukup upload foto barcode
     */
    private const TYPES = [
        'bank'    => 'Transfer Bank',
        'qris'    => 'Scan QR (QRIS)',
        'barcode' => 'Scan Barcode',
    ];

    public function index(): View
    {
        // Catatan: kolom `type` sudah punya DEFAULT 'bank' di skema baru,
        // sehingga error MySQL 1364 lama tidak bisa terjadi lagi.
        $methods = PaymentMethod::query()->orderBy('sort_order')->orderBy('id')->get();

        // Kalau migration rebuild belum sempat dijalankan (mis. status migrasi
        // korup), coba perbaiki skema secara otomatis sekali saja — tabel lama
        // di-drop & dibuat ulang dengan data yang disalin, lalu method dibaca
        // ulang agar isian opsional (instructions/qr_image) tersedia.
        if ($methods->contains(fn ($m) => ! array_key_exists('instructions', $m->getAttributes()))) {
            try {
                Artisan::call('migrate', [
                    '--path' => 'database/migrations/2026_10_02_000000_rebuild_payment_methods_table.php',
                    '--force' => true,
                ]);
                $methods = PaymentMethod::query()->orderBy('sort_order')->orderBy('id')->get();
            } catch (\Throwable $e) {
                Log::warning('Auto-rebuild payment_methods gagal: ' . $e->getMessage());
            }
        }

        return view('admin.payment-methods.index', ['methods' => $methods]);
    }

    public function create(): View
    {
        return view('admin.payment-methods.form', [
            'method' => new PaymentMethod(['is_active' => true, 'sort_order' => 0, 'type' => 'bank']),
            'types'  => self::TYPES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $input = $request->except('_token', '_method');

        try {
            $data = $this->validated($request);
        } catch (ValidationException $e) {
            return redirect()->back()->withInput($input)->withErrors($e->errors());
        }

        // Upload gambar untuk qris/barcode.
        if ($request->hasFile('qr_image')) {
            $data['qr_image'] = $request->file('qr_image')->store('qris', 'public');
        }

        // Rapikan sesuai jenis: bank tidak pakai gambar, qris/barcode tidak pakai rekening.
        if ($data['type'] === 'bank') {
            $data['qr_image'] = null;
        } else {
            $data['account_number'] = null;
            $data['account_name'] = null;
        }

        try {
            $method = PaymentMethod::create($data);
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Gagal menyimpan metode pembayaran: ' . $e->getMessage());

            return redirect()->back()
                ->withInput($input)
                ->withErrors(['label' => 'Gagal menyimpan ke database (' . $this->shortDbError($e) . '). Jalankan `php artisan migrate` — migrasi rebuild payment_methods belum terpasang.']);
        }

        AdminLog::record('create_payment_method', $method, ['label' => $method->label]);

        return redirect()->route('admin.payment-methods.index')->with('status', 'Metode pembayaran ditambahkan.');
    }

    public function edit(PaymentMethod $paymentMethod): View
    {
        return view('admin.payment-methods.form', [
            'method' => $paymentMethod,
            'types'  => self::TYPES,
        ]);
    }

    public function update(Request $request, PaymentMethod $paymentMethod): RedirectResponse
    {
        $input = $request->except('_token', '_method');

        try {
            $data = $this->validated($request, $paymentMethod);
        } catch (ValidationException $e) {
            return redirect()->back()->withInput($input)->withErrors($e->errors());
        }

        // Jangan timpa qr_image dengan null jika user tidak upload gambar baru.
        unset($data['qr_image']);

        if ($request->hasFile('qr_image')) {
            if ($paymentMethod->qr_image) {
                Storage::disk('public')->delete($paymentMethod->qr_image);
            }
            $data['qr_image'] = $request->file('qr_image')->store('qris', 'public');
        }

        // qris/barcode tidak memakai data rekening.
        if (($data['type'] ?? $paymentMethod->type) !== 'bank') {
            $data['account_number'] = null;
            $data['account_name'] = null;
        }

        try {
            $paymentMethod->update($data);
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Gagal memperbarui metode pembayaran: ' . $e->getMessage());

            return redirect()->back()
                ->withInput($input)
                ->withErrors(['label' => 'Gagal menyimpan ke database (' . $this->shortDbError($e) . '). Jalankan `php artisan migrate`.']);
        }

        AdminLog::record('update_payment_method', $paymentMethod, ['label' => $paymentMethod->label]);

        return redirect()->route('admin.payment-methods.index')->with('status', 'Metode pembayaran diperbarui.');
    }

    public function destroy(PaymentMethod $paymentMethod): RedirectResponse
    {
        if ($paymentMethod->proofs()->exists()) {
            return back()->withErrors(['method' => 'Metode ini sudah dipakai di bukti pembayaran, tidak bisa dihapus. Nonaktifkan saja.']);
        }

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
    private function validated(Request $request, ?PaymentMethod $method = null): array
    {
        $data = $request->validate([
            'type'           => ['required', Rule::in(array_keys(self::TYPES))],
            'label'          => ['required', 'string', 'max:100'],
            'account_number' => ['nullable', 'string', 'max:100'],
            'account_name'   => ['nullable', 'string', 'max:100'],
            'instructions'   => ['nullable', 'string', 'max:2000'],
            'qr_image'       => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'sort_order'     => ['nullable', 'integer', 'min:0', 'max:999'],
            'is_active'      => ['nullable', 'boolean'],
        ]);

        $type = $data['type'];

        // Aturan bisnis: bank butuh rekening + atas nama; qris/barcode butuh gambar.
        if ($type === 'bank') {
            $extra = $request->validate([
                'account_number' => ['required', 'string', 'max:100'],
                'account_name'   => ['required', 'string', 'max:100'],
            ]);
            $data['account_number'] = $extra['account_number'];
            $data['account_name'] = $extra['account_name'];
        } elseif (! $request->hasFile('qr_image') && ($method === null || ! $method->qr_image)) {
            throw ValidationException::withMessages([
                'qr_image' => 'Metode ' . self::TYPES[$type] . ' wajib menyertakan foto QR/Barcode.',
            ]);
        }

        $data['is_active'] = ! empty($data['is_active']);
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        return $this->filterColumns($data);
    }

    /** Buang key yang kolomnya benar-benar tidak ada di tabel (pengaman). */
    private function filterColumns(array $data): array
    {
        $known = $this->existingColumns();
        if ($known === []) {
            return $data;
        }

        return array_filter(
            $data,
            fn ($k) => in_array($k, $known, true),
            ARRAY_FILTER_USE_KEY
        );
    }

    /**
     * Daftar kolom fisik tabel payment_methods.
     * Mengembalikan [] bila tabel/koneksi tidak bisa dibaca.
     *
     * Catatan penting: kalau hasil SHOW COLUMNS TIDAK mengandung `type`
     * (tabel lama masih berformat legacy), cache sengaja dikosongkan agar
     * filterColumns() tidak membuang field `type` dari data insert — kalau
     * tidak, INSERT akan menabrak kolom NOT NULL tanpa default dan memunculkan
     * error MySQL 1364 "Field 'type' doesn't have a default value".
     *
     * @return array<int, string>
     */
    private function existingColumns(): array
    {
        if ($this->columnCache !== []) {
            return $this->columnCache;
        }

        try {
            $rows = DB::select('SHOW COLUMNS FROM payment_methods');
            $cols = array_values(array_map(fn ($r) => (string) $r->Field, $rows));
        } catch (\Throwable) {
            return [];
        }

        // Struktur belum di-rebuild oleh migration -> jangan batasi kolom.
        if (! in_array('type', $cols, true)) {
            return [];
        }

        $this->columnCache = $cols;

        return $this->columnCache;
    }

    /** Pesan error DB pendek & aman ditampilkan ke admin. */
    private function shortDbError(\Illuminate\Database\QueryException $e): string
    {
        $clean = preg_replace('/\s*\(Connection:.*$/is', '', $e->getMessage());
        $clean = preg_replace('/^SQLSTATE\[[^\]]+\]:\s*(SQLSTATE\[[^\]]+\]:\s*)?/', '', (string) $clean);
        $clean = preg_replace('/SQL:.*$/s', '', (string) $clean);

        return mb_substr(trim((string) $clean), 0, 160);
    }
}
