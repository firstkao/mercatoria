<?php

namespace App\Console\Commands;

use App\Models\AdminLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class BackupDatabase extends Command
{
    protected $signature = 'backup:database {--keep=7 : Jumlah backup yang disimpan}';
    protected $description = 'Backup database ke storage/app/backups (default: simpan 7 terakhir)';

    public function handle(): int
    {
        $backupDir = storage_path('app/backups');
        File::ensureDirectoryExists($backupDir);

        $filename = 'db-' . now()->format('Y-m-d_His') . '.sql';
        $path = $backupDir . DIRECTORY_SEPARATOR . $filename;

        $this->info("Membuat backup: {$filename}");

        try {
            $driver = DB::connection()->getDriverName();

            // Shared hosting (mis. Hostinger) sering menonaktifkan exec(): pakai dump via PHP.
            if ($driver === 'mysql' && $this->canExec()) {
                $this->backupMysql($path);
            } elseif ($driver === 'pgsql' && $this->canExec()) {
                $this->backupPostgres($path);
            } else {
                $this->backupGeneric($path);
            }

            $size = File::size($path);
            $this->info("✓ Backup selesai: " . $this->humanSize($size));

            // Hapus backup lama
            $this->pruneOld($backupDir, (int) $this->option('keep'));

            // Catat di admin log
            try {
                AdminLog::record('backup_database', null, [
                    'filename' => $filename,
                    'size' => $size,
                ]);
            } catch (\Throwable $e) {
                // ignore (mungkin belum login admin)
            }

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("✗ Backup gagal: " . $e->getMessage());
            return self::FAILURE;
        }
    }

    private function canExec(): bool
    {
        if (! function_exists('exec')) {
            return false;
        }
        $disabled = array_map('trim', explode(',', (string) ini_get('disable_functions')));

        return ! in_array('exec', $disabled, true);
    }

    private function backupMysql(string $path): void
    {
        $config = config('database.connections.mysql');
        $errorPath = $path . '.err';

        // ✅ BUG FIX 1: password tadinya dikirim sebagai argumen `--password=...`,
        // sehingga siapa pun yang menjalankan `ps aux` (atau melihat history proses)
        // di server bisa membacanya. Sekarang kredensial ditulis ke file opsi
        // sementara ber-mode 0600, lalu dihapus di blok finally.
        // ✅ BUG FIX 2: `2>&1` membuat pesan error mysqldump ikut tertulis ke dalam
        // file .sql, jadi file backup jadi rusak tapi tetap terlihat "berhasil".
        // stderr sekarang dialihkan ke file .err terpisah.
        $defaultsFile = $this->writeMysqlDefaultsFile($config);

        try {
            // --defaults-extra-file WAJIB jadi argumen pertama mysqldump.
            $command = sprintf(
                'mysqldump --defaults-extra-file=%s --host=%s --port=%s --single-transaction --quick --lock-tables=false %s > %s 2> %s',
                escapeshellarg($defaultsFile),
                escapeshellarg((string) $config['host']),
                escapeshellarg((string) ($config['port'] ?? '3306')),
                escapeshellarg((string) $config['database']),
                escapeshellarg($path),
                escapeshellarg($errorPath),
            );

            exec($command, $output, $exitCode);

            $stderr = File::exists($errorPath) ? trim(File::get($errorPath)) : '';

            if ($exitCode !== 0 || ! File::exists($path) || File::size($path) === 0) {
                throw new \RuntimeException(
                    'mysqldump gagal. Cek apakah mysqldump tersedia. '
                    . 'Output: ' . implode("\n", $output)
                    . ($stderr !== '' ? ' | Stderr: ' . $stderr : '')
                );
            }
        } finally {
            if (File::exists($errorPath)) {
                File::delete($errorPath);
            }

            if (is_file($defaultsFile)) {
                @unlink($defaultsFile);
            }
        }
    }

    /**
     * Tulis kredensial MySQL ke file opsi sementara (format .cnf) yang hanya
     * bisa dibaca user sendiri.
     *
     * @param  array<string, mixed>  $config
     */
    private function writeMysqlDefaultsFile(array $config): string
    {
        $tmpPath = tempnam(sys_get_temp_dir(), 'mercatoria_dump_');

        if ($tmpPath === false) {
            throw new \RuntimeException('Gagal membuat file kredensial sementara untuk mysqldump.');
        }

        $content = "[client]\n"
            . "user=" . $this->mysqlOptionValue((string) ($config['username'] ?? '')) . "\n";

        if (($config['password'] ?? null) !== null && $config['password'] !== '') {
            $content .= "password=" . $this->mysqlOptionValue((string) $config['password']) . "\n";
        }

        file_put_contents($tmpPath, $content);
        chmod($tmpPath, 0600);

        return $tmpPath;
    }

    /**
     * Escape nilai untuk file opsi MySQL: baris baru harus di-escape,
     * backslash & double-quote juga.
     */
    private function mysqlOptionValue(string $value): string
    {
        $escaped = str_replace(
            ['\\', '"', "\n", "\r"],
            ['\\\\', '\\"', '\\n', '\\r'],
            $value,
        );

        return '"' . $escaped . '"';
    }

    private function backupPostgres(string $path): void
    {
        $config = config('database.connections.pgsql');
        $errorPath = $path . '.err';

        // ✅ BUG FIX: PGPASSWORD tadinya ditulis di depan perintah
        // (`PGPASSWORD=... pg_dump ...`) yang juga terlihat di `ps aux`.
        // Sekarang dilewatkan lewat environment proses dan dibersihkan setelahnya.
        $previous = getenv('PGPASSWORD');
        putenv('PGPASSWORD=' . (string) ($config['password'] ?? ''));

        try {
            $command = 'pg_dump -h ' . escapeshellarg((string) $config['host']) .
                ' -p ' . escapeshellarg((string) ($config['port'] ?? '5432')) .
                ' -U ' . escapeshellarg((string) $config['username']) .
                ' ' . escapeshellarg((string) $config['database']) .
                ' > ' . escapeshellarg($path) . ' 2> ' . escapeshellarg($errorPath);

            exec($command, $output, $exitCode);

            $stderr = File::exists($errorPath) ? trim(File::get($errorPath)) : '';

            if ($exitCode !== 0) {
                throw new \RuntimeException(
                    'pg_dump gagal: ' . implode("\n", $output)
                    . ($stderr !== '' ? ' | Stderr: ' . $stderr : '')
                );
            }
        } finally {
            if ($previous === false) {
                putenv('PGPASSWORD');
            } else {
                putenv('PGPASSWORD=' . $previous);
            }

            if (File::exists($errorPath)) {
                File::delete($errorPath);
            }
        }
    }

    /**
     * Fallback: dump tabel satu-satu via query (lambat, tapi portable).
     */
    private function backupGeneric(string $path): void
    {
        $pdo = DB::getPdo();
        $handle = fopen($path, 'w');
        fwrite($handle, "-- Backup generated at " . now()->toDateTimeString() . "\n\n");
        // Tanpa ini hasil dump tidak bisa di-restore: urutan tabel vs foreign key.
        fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\nSET NAMES utf8mb4;\n\n");

        $key = 'Tables_in_' . DB::connection()->getDatabaseName();

        foreach (DB::select('SHOW FULL TABLES WHERE Table_type = "BASE TABLE"') as $table) {
            $tableName = $table->$key ?? null;
            if (! $tableName) {
                continue;
            }

            fwrite($handle, "-- Table: {$tableName}\n");
            fwrite($handle, "DROP TABLE IF EXISTS `{$tableName}`;\n");
            $create = DB::select("SHOW CREATE TABLE `{$tableName}`");
            fwrite($handle, ($create[0]->{'Create Table'} ?? '') . ";\n\n");

            // cursor() agar tabel besar tidak dimuat sekaligus ke memori
            foreach (DB::table($tableName)->cursor() as $row) {
                $values = array_map(fn ($v) => $v === null ? 'NULL' : $pdo->quote((string) $v), (array) $row);
                fwrite($handle, "INSERT INTO `{$tableName}` VALUES (" . implode(',', $values) . ");\n");
            }
            fwrite($handle, "\n");
        }

        fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
        fclose($handle);
    }

    private function pruneOld(string $dir, int $keep): void
    {
        $files = collect(File::files($dir))
            ->filter(fn ($file) => $file->getExtension() === 'sql')
            ->sortByDesc(fn ($file) => $file->getMTime())
            ->values();

        $toDelete = $files->slice($keep);

        foreach ($toDelete as $file) {
            File::delete($file->getPathname());
            $this->line("  Hapus backup lama: " . $file->getFilename());
        }
    }

    private function humanSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
