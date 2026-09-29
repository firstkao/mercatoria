<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;

class MaintenanceController extends Controller
{
    public function index(): View
    {
        $backupDir = storage_path('app/backups');
        File::ensureDirectoryExists($backupDir);

        $backups = collect(File::files($backupDir))
            ->filter(fn ($file) => $file->getExtension() === 'sql')
            ->sortByDesc(fn ($file) => $file->getMTime())
            ->map(fn ($file) => [
                'name' => $file->getFilename(),
                'size' => $file->getSize(),
                'created_at' => \Illuminate\Support\Carbon::createFromTimestamp($file->getMTime()),
                'path' => $file->getPathname(),
            ])
            ->values();

        // Info storage
        $storageInfo = [
            'product_images' => $this->folderSize(storage_path('app/public/products')),
            'variants' => $this->folderSize(storage_path('app/public/products/variants')),
            'hero_slides' => $this->folderSize(storage_path('app/public/hero-slides')),
            'payment_proofs' => $this->folderSize(storage_path('app/public/payment-proofs')),
            'logs' => $this->folderSize(storage_path('logs')),
            'backups' => $this->folderSize($backupDir),
        ];

        return view('admin.maintenance.index', [
            'backups' => $backups,
            'storageInfo' => $storageInfo,
            'totalStorage' => array_sum($storageInfo),
        ]);
    }

    public function backupNow(): RedirectResponse
    {
        Artisan::call('backup:database', ['--keep' => 7]);
        $output = Artisan::output();

        return back()->with('status', 'Backup dimulai. ' . trim($output));
    }

    public function cleanupNow(): RedirectResponse
    {
        Artisan::call('storage:cleanup');
        $output = Artisan::output();

        return back()->with('status', 'Cleanup selesai. ' . trim($output));
    }

    public function download(string $filename)
    {
        // Sanitize: hanya boleh file .sql di folder backups
        $filename = basename($filename);
        abort_unless(str_ends_with($filename, '.sql'), 404);

        $path = storage_path('app/backups/' . $filename);
        abort_unless(File::exists($path), 404);

        AdminLog::record('download_backup', null, ['filename' => $filename]);

        return response()->download($path);
    }

    public function destroy(string $filename): RedirectResponse
    {
        $filename = basename($filename);
        abort_unless(str_ends_with($filename, '.sql'), 404);

        $path = storage_path('app/backups/' . $filename);
        abort_unless(File::exists($path), 404);

        File::delete($path);
        AdminLog::record('delete_backup', null, ['filename' => $filename]);

        return back()->with('status', 'Backup dihapus.');
    }

    private function folderSize(string $path): int
    {
        if (! File::isDirectory($path)) {
            return 0;
        }

        return collect(File::allFiles($path))->sum(fn ($file) => $file->getSize());
    }
}