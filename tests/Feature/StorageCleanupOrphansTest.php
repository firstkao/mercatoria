<?php

use App\Models\Payment;
use App\Models\Product;
use App\Services\Image\ImageOptimizerService;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Storage;

test('dry-run menampilkan file orphan tanpa memindah', function () {
    Storage::fake('local');
    Storage::disk('local')->put('products/orphan-dry.jpg', 'x');

    $this->artisan('storage:cleanup-orphans', ['--dry-run' => true])
        ->expectsOutputToContain('products/orphan-dry.jpg')
        ->assertSuccessful();

    expect(Storage::disk('local')->exists('products/orphan-dry.jpg'))->toBeTrue()
        ->and(Storage::disk('local')->exists('orphan-backup/products/orphan-dry.jpg'))->toBeFalse();
});

test('file tanpa referensi DB dipindah ke orphan-backup, file terreferensi tetap', function () {
    Storage::fake('local');
    $disk = Storage::disk('local');

    // Terreferensi: produk + bukti bayar, beserta versi & master turunannya.
    $product = Product::factory()->create(['image_path' => 'products/ref.jpg']);
    $ref = $product->image_path;
    $refCompressed = ImageOptimizerService::variantPath($ref, ImageOptimizerService::COMPRESSED);
    $refThumb = ImageOptimizerService::variantPath($ref, ImageOptimizerService::THUMB);
    foreach ([$ref, $refCompressed, $refThumb, 'originals/'.$ref] as $p) {
        $disk->put($p, 'x');
    }

    $proof = 'payment-proofs/ref.jpg';
    Payment::factory()->create(['file_path' => $proof]);
    $disk->put($proof, 'x');

    // Orphan: file + versi + master tanpa referensi sama sekali.
    $orphan = 'products/orphan.jpg';
    $orphanCompressed = ImageOptimizerService::variantPath($orphan, ImageOptimizerService::COMPRESSED);
    $orphanThumb = ImageOptimizerService::variantPath($orphan, ImageOptimizerService::THUMB);
    foreach ([$orphan, $orphanCompressed, $orphanThumb, 'originals/'.$orphan] as $p) {
        $disk->put($p, 'x');
    }

    $this->artisan('storage:cleanup-orphans')->assertSuccessful();

    foreach ([$ref, $refCompressed, $refThumb, 'originals/'.$ref, $proof] as $p) {
        expect($disk->exists($p))->toBeTrue("{$p} harus tetap ada");
    }

    foreach ([$orphan, $orphanCompressed, $orphanThumb, 'originals/'.$orphan] as $p) {
        expect($disk->exists($p))->toBeFalse("{$p} harus dipindah");
        expect($disk->exists('orphan-backup/'.$p))->toBeTrue("orphan-backup/{$p} harus ada");
    }
});

test('force menghapus orphan permanen tanpa backup', function () {
    Storage::fake('local');
    Storage::disk('local')->put('products/orphan-force.jpg', 'x');

    $this->artisan('storage:cleanup-orphans', ['--force' => true])->assertSuccessful();

    expect(Storage::disk('local')->exists('products/orphan-force.jpg'))->toBeFalse()
        ->and(Storage::disk('local')->exists('orphan-backup/products/orphan-force.jpg'))->toBeFalse();
});

test('storage:cleanup-orphans terjadwal mingguan jam 03:00 WIB', function () {
    $this->artisan('schedule:list')
        ->expectsOutputToContain('storage:cleanup-orphans')
        ->assertSuccessful();

    $events = collect(app(Schedule::class)->events());
    $event = $events->first(fn ($e) => str_contains((string) $e->command, 'storage:cleanup-orphans'));

    expect($event->expression)->toBe('0 3 * * 0')
        ->and($event->timezone)->toBe('Asia/Jakarta');
});
