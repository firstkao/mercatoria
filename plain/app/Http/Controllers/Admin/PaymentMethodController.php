<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\PaymentMethod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class PaymentMethodController extends Controller
{
    /** Cache kolom fisik tabel payment_methods dalam satu request. */
    private array $columnCache = [];

    public function index(): View
    {
        // Perbaiki skema secara proaktif (idempoten, murah) supaya admin tidak
        // pernah lagi melihat error 1364 "Field 'type' doesn't have a default
        // value" — perbaikan yang sebelumnya hanya jalan saat INSERT gagal
        // ternyata tidak cukup karena insert tetap memakai daftar kolom hasil
        // cache lama. Setelah repair sukses, model di-refresh agar tahu kolom
        // barunya.
        if ($this->repairSchema()) {
            unset($this->columnCache);
            $this->columnCache = [];
            PaymentMethod::clearResolvedInstances();
        }

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
        // Simpan input mentah agar field opsional tidak hilang saat retry form.
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

        // Perbaikan preventif: kolom warisan NOT NULL tanpa default (mis.
        // `type` dari SQL dump lama) membuat INSERT gagal dengan error MySQL
        // 1364 bahkan sebelum repair pasca-gagal sempat menolong, karena
        // repair hanya mengubah DB — bukan daftar kolom yang dikirim. Jadi:
        // kalau masih ada kolom wajib tanpa default yang tidak ikut diisi,
        // perbaiki skema DULU, lalu bangun ulang $data dari kolom fisik terbaru.
        if ($this->hasUntypedRequiredColumn(array_keys($data))) {
            if ($this->repairSchema()) {
                $data = $this->filterColumns($this->normalizeData($data));
            }
        }

        // Lapis pengaman terakhir: kalau setelah semua itu masih ada kolom
        // wajib tanpa default yang belum terisi (kasus enum yang ditolak
        // DEFAULT '' oleh MySQL), isi otomatis dengan nilai enum pertama agar
        // insert tetap sukses dan admin tidak perlu menyentuh SQL manual.
        foreach ($this->notNullColumnsWithoutDefault() as $col) {
            $name = $col['name'];
            if (in_array($name, ['id', 'created_at', 'updated_at'], true) || array_key_exists($name, $data)) {
                continue;
            }
            $first = $this->enumFirstValue($name);
            if ($first !== null) {
                $data[$name] = $first;
            }
        }

        try {
            $method = PaymentMethod::unguarded(fn () => PaymentMethod::create($data));
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Gagal menyimpan metode pembayaran: ' . $e->getMessage());

            // Percobaan terakhir sebelum menyerah: sinkronkan struktur tabel
            // secara langsung (kolom hilang / NOT NULL tanpa default), lalu
            // ulangi insert satu kali. Ini memperbaiki akar masalah "Nothing
            // to migrate" — migration lama terlanjur tercatat DONE padahal
            // gagal sebagian, sehingga `php artisan migrate` tidak lagi
            // mencoba menambah kolom yang sebenarnya belum ada.
            if ($this->repairSchema()) {
                try {
                    $retryData = $this->filterColumns($this->normalizeData($data));

                    // Kalau penyebabnya kolom wajib tanpa default (mis. `type`)
                    // dan repair gagal menambalnya (sering terjadi pada kolom
                    // enum: MODIFY enum ... DEFAULT '' ditolak MySQL), isi
                    // manual dengan nilai enum pertama supaya insert bisa jalan.
                    foreach ($this->notNullColumnsWithoutDefault() as $col) {
                        $name = $col['name'];
                        if (in_array($name, ['id', 'created_at', 'updated_at'], true)) {
                            continue;
                        }
                        if (array_key_exists($name, $retryData)) {
                            continue;
                        }
                        $enumValues = $this->enumFirstValue($name);
                        if ($enumValues !== null) {
                            $retryData[$name] = $enumValues;
                        } elseif (in_array($name, $this->existingColumns(), true)) {
                            return redirect()->back()
                                ->withInput($input)
                                ->withErrors(['label' => "Kolom wajib '{$name}' di tabel payment_methods belum terisi dan tidak punya nilai default. Hubungi developer untuk menjalankan: ALTER TABLE payment_methods MODIFY {$name} {$col['type']} NULL"]);
                        }
                    }

                    $method = PaymentMethod::unguarded(fn () => PaymentMethod::create($retryData));
                    AdminLog::record('create_payment_method', $method, ['label' => $method->label]);

                    return redirect()->route('admin.payment-methods.index')->with('status', 'Metode pembayaran ditambahkan.');
                } catch (\Throwable $retry) {
                    Log::error('Retry simpan metode pembayaran setelah repair: ' . $retry->getMessage());
                }
            }

            return redirect()->back()
                ->withInput($input)
                ->withErrors(['label' => 'Gagal menyimpan ke database (' . $this->shortDbError($e) . '). Struktur tabel payment_methods belum cocok dengan aplikasi — hubungi developer untuk memperbaiki skema.']);
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

        // Jangan timpa qr_image dengan null jika user tidak upload gambar baru.
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

            if ($this->repairSchema()) {
                try {
                    $paymentMethod->refresh();
                    $paymentMethod->update($this->filterColumns($data));
                    AdminLog::record('update_payment_method', $paymentMethod, ['label' => $paymentMethod->label]);

                    return redirect()->route('admin.payment-methods.index')->with('status', 'Metode pembayaran diperbarui.');
                } catch (\Throwable) {
                    // jatuh ke pesan error di bawah
                }
            }

            return redirect()->back()
                ->withInput($input)
                ->withErrors(['label' => 'Gagal menyimpan ke database (' . $this->shortDbError($e) . '). Struktur tabel payment_methods belum cocok dengan aplikasi — hubungi developer untuk memperbaiki skema.']);
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

        return $this->filterColumns($data);
    }

    /** Buang key yang kolomnya benar-benar tidak ada di tabel. */
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
     * @return array<int, string>
     */
    private function existingColumns(): array
    {
        if ($this->columnCache !== []) {
            return $this->columnCache;
        }

        try {
            $conn = DB::connection()->getDriverName();
            $db = DB::connection()->getDatabaseName();

            $rows = match ($conn) {
                'sqlite' => DB::select("PRAGMA table_info(payment_methods)"),
                'mysql'  => DB::select("SHOW COLUMNS FROM payment_methods"),
                default  => DB::select(
                    "SELECT column_name AS name FROM information_schema.columns WHERE table_schema = ? AND table_name = 'payment_methods'",
                    [$db]
                ),
            };

            $this->columnCache = array_values(array_filter(array_map(
                fn ($r) => (string) ($r->name ?? $r->Field ?? ''),
                $rows
            )));
        } catch (\Throwable) {
            return [];
        }

        return $this->columnCache;
    }

    private function tableHasQrImage(): bool
    {
        try {
            return Schema::hasColumn('payment_methods', 'qr_image');
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Sinkronkan skema secara langsung (idempoten, aman dipanggil berkali-kali):
     * - tambahkan kolom yang hilang (instructions, qr_image),
     * - pindahkan data qris_image_path -> qr_image lalu buang kolom lama,
     * - beri DEFAULT pada kolom NOT NULL tanpa default (mis. `type` warisan
     *   SQL dump lama) supaya INSERT tanpa nilai itu tidak gagal.
     *
     * Catatan penting: di MySQL versi lama / MariaDB < 10.2, klausa
     * `DEFAULT ''` untuk kolom TEXT tidak didukung dan akan gagal. Karena itu
     * kolom teks/enum lebih dulu diubah menjadi NULL-able (cara paling aman),
     * baru numeric/boolean diberi default 0 dan string biasa diberi ''.
     */
    private function repairSchema(): bool
    {
        try {
            if (! Schema::hasTable('payment_methods')) {
                return false;
            }

            $cols = $this->existingColumns();
            if ($cols === []) {
                return false;
            }

            if (! in_array('instructions', $cols, true)) {
                DB::statement('ALTER TABLE payment_methods ADD COLUMN instructions TEXT NULL');
            }
            if (! in_array('qr_image', $cols, true)) {
                DB::statement('ALTER TABLE payment_methods ADD COLUMN qr_image VARCHAR(255) NULL');
            }
            if (in_array('qris_image_path', $cols, true)) {
                DB::statement('UPDATE payment_methods SET qr_image = qris_image_path WHERE qr_image IS NULL AND qris_image_path IS NOT NULL');
                DB::statement('ALTER TABLE payment_methods DROP COLUMN qris_image_path');
            }

            // Kolom NOT NULL tanpa default (penyebab error MySQL 1364
            // "Field 'x' doesn't have a default value") → buat NULL-able atau
            // beri default netral sesuai tipenya.
            foreach ($this->notNullColumnsWithoutDefault() as $col) {
                if (in_array($col['name'], ['id', 'created_at', 'updated_at'], true)) {
                    continue;
                }
                $type = (string) $col['type'];
                $default = match (true) {
                    in_array($type, ['int', 'bigint', 'smallint', 'mediumint', 'decimal', 'float', 'double'], true) => '0',
                    in_array($type, ['varchar', 'char'], true) => "''",
                    // text & enum/set: hindari DEFAULT (tidak didukung sebagian
                    // versi MySQL) — cukup jadikan NULL-able.
                    default => null,
                };
                if ($default === null) {
                    DB::statement("ALTER TABLE payment_methods MODIFY {$col['name']} {$type} NULL");
                } else {
                    DB::statement("ALTER TABLE payment_methods MODIFY {$col['name']} {$type} NOT NULL DEFAULT {$default}");
                }
            }

            $this->columnCache = [];

            return true;
        } catch (\Throwable $e) {
            Log::error('Gagal memperbaiki skema payment_methods: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Ada kolom NOT NULL tanpa default yang TIDAK ikut diisi dalam insert?
     * (mis. `type` warisan dump lama) → INSERT pasti kena error 1364.
     *
     * @param  array<int, string>  $plannedColumns
     */
    private function hasUntypedRequiredColumn(array $plannedColumns): bool
    {
        foreach ($this->notNullColumnsWithoutDefault() as $col) {
            if (in_array($col['name'], ['id', 'created_at', 'updated_at'], true)) {
                continue;
            }
            if (! in_array($col['name'], $plannedColumns, true)) {
                return true;
            }
        }

        return false;
    }

    /** Pastikan key yang dibuang filterColumns tetap ada isinya saat retry. */
    private function normalizeData(array $data): array
    {
        $data['is_active'] ??= true;
        $data['sort_order'] ??= 0;

        return $data;
    }

    /**
     * Kalau kolom adalah enum, kembalikan nilai enum PERTAMA sebagai default
     * darurat (mis. `type` enum('bank','qris','ewallet') → 'bank'). Ini
     * diperlukan karena MySQL menolak `DEFAULT ''` pada enum NOT NULL, sehingga
     * repairSchema() tidak bisa memberi default untuk kolom enum — satu-satunya
     * jalan agar INSERT dari aplikasi lama tetap sukses tanpa drop data.
     */
    private function enumFirstValue(string $column): ?string
    {
        try {
            if (DB::connection()->getDriverName() !== 'mysql') {
                return null;
            }

            $rows = DB::select(
                "SELECT COLUMN_TYPE AS ctype FROM information_schema.columns
                 WHERE table_schema = DATABASE() AND table_name = 'payment_methods'
                   AND COLUMN_NAME = ?",
                [$column]
            );

            if ($rows === []) {
                return null;
            }

            $ctype = strtolower((string) $rows[0]->ctype);
            if (! str_starts_with($ctype, 'enum(')) {
                return null;
            }

            if (preg_match("/enum\\((.*)\\)/s", $ctype, $m)) {
                $parts = preg_split("/','/", trim($m[1], "'"));
                $first = trim((string) ($parts[0] ?? ''), "' ");
                if ($first !== '') {
                    return $first;
                }
            }

            return null;
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Kolom NOT NULL tanpa default pada MySQL.
     *
     * @return array<int, array{name: string, type: string}>
     */
    private function notNullColumnsWithoutDefault(): array
    {
        try {
            if (DB::connection()->getDriverName() !== 'mysql') {
                return [];
            }

            $rows = DB::select(
                "SELECT COLUMN_NAME AS name, DATA_TYPE AS type
                 FROM information_schema.columns
                 WHERE table_schema = DATABASE() AND table_name = 'payment_methods'
                   AND IS_NULLABLE = 'NO' AND COLUMN_DEFAULT IS NULL"
            );

            $out = [];
            foreach ($rows as $r) {
                $out[] = ['name' => (string) $r->name, 'type' => (string) $r->type];
            }

            return $out;
        } catch (\Throwable) {
            return [];
        }
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
