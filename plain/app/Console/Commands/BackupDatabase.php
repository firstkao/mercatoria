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

            if ($driver === 'mysql') {
                $this->backupMysql($path);
            } elseif ($driver === 'pgsql') {
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

    private function backupMysql(string $path): void
    {
        $config = config('database.connections.mysql');
        $command = sprintf(
            'mysqldump --user=%s --password=%s --host=%s --port=%s --single-transaction --quick --lock-tables=false %s > %s 2>&1',
            escapeshellarg($config['username']),
            escapeshellarg($config['password'] ?? ''),
            escapeshellarg($config['host']),
            escapeshellarg($config['port'] ?? '3306'),
            escapeshellarg($config['database']),
            escapeshellarg($path),
        );

        exec($command, $output, $exitCode);

        if ($exitCode !== 0 || ! File::exists($path) || File::size($path) === 0) {
            throw new \RuntimeException('mysqldump gagal. Cek apakah mysqldump tersedia. Output: ' . implode("\n", $output));
        }
    }

    private function backupPostgres(string $path): void
    {
        $config = config('database.connections.pgsql');
        $env = 'PGPASSWORD=' . escapeshellarg($config['password'] ?? '');
        $command = "{$env} pg_dump -h " . escapeshellarg($config['host']) .
            " -p " . escapeshellarg($config['port'] ?? '5432') .
            " -U " . escapeshellarg($config['username']) .
            " " . escapeshellarg($config['database']) .
            " > " . escapeshellarg($path) . " 2>&1";

        exec($command, $output, $exitCode);

        if ($exitCode !== 0) {
            throw new \RuntimeException('pg_dump gagal: ' . implode("\n", $output));
        }
    }

    /**
     * Fallback: dump tabel satu-satu via query (lambat, tapi portable).
     */
    private function backupGeneric(string $path): void
    {
        $handle = fopen($path, 'w');
        fwrite($handle, "-- Backup generated at " . now()->toDateTimeString() . "\n\n");

        $tables = DB::select('SHOW TABLES');
        $key = 'Tables_in_' . config('database.connections.mysql.database');

        foreach ($tables as $table) {
            $tableName = $table->$key ?? null;
            if (! $tableName) continue;

            fwrite($handle, "-- Table: {$tableName}\n");
            fwrite($handle, "DROP TABLE IF EXISTS `{$tableName}`;\n");
            $create = DB::select("SHOW CREATE TABLE `{$tableName}`");
            fwrite($handle, ($create[0]->{'Create Table'} ?? '') . ";\n\n");

            $rows = DB::table($tableName)->get();
            foreach ($rows as $row) {
                $values = array_map(fn ($v) => $v === null ? 'NULL' : DB::getPdo()->quote((string) $v), (array) $row);
                fwrite($handle, "INSERT INTO `{$tableName}` VALUES (" . implode(',', $values) . ");\n");
            }
            fwrite($handle, "\n");
        }

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