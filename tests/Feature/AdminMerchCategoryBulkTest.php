<?php

use App\Models\MerchCategory;
use App\Models\Product;
use App\Models\User;

test('bulk kategori merch memperbarui yang ada dan membuat yang baru', function () {
    $admin = User::factory()->admin()->create();
    $existing = MerchCategory::factory()->create(['name' => 'Kategori Lama', 'priority' => 5]);

    $this->actingAs($admin)->post('/office/merch-categories/bulk', [
        'categories' => [
            ['id' => $existing->id, 'name' => 'Kategori Lama Baru', 'priority' => 3, 'keywords' => 'kaos, baju'],
            ['name' => 'Kategori Baru Bulk', 'priority' => 9, 'keywords' => 'stiker'],
        ],
    ])->assertRedirect(route('office.merch-categories.index'));

    expect(MerchCategory::count())->toBe(2)
        ->and($existing->fresh()->name)->toBe('Kategori Lama Baru')
        ->and($existing->fresh()->priority)->toBe(3)
        ->and(MerchCategory::where('name', 'Kategori Baru Bulk')->exists())->toBeTrue();
});

test('bulk delete kategori merch menolak kategori dengan produk dan menghapus yang kosong', function () {
    $admin = User::factory()->admin()->create();
    $bersih = MerchCategory::factory()->create();
    $bermasalah = MerchCategory::factory()->create();
    Product::factory()->create(['merch_category_id' => $bermasalah->id]);

    $this->actingAs($admin)->post('/office/merch-categories/bulk-delete', ['ids' => [$bersih->id, $bermasalah->id]])
        ->assertSessionHasErrors('ids');

    expect(MerchCategory::count())->toBe(2);

    $this->actingAs($admin)->post('/office/merch-categories/bulk-delete', ['ids' => [$bersih->id]])
        ->assertRedirect(route('office.merch-categories.index'));

    expect(MerchCategory::find($bersih->id))->toBeNull()
        ->and(MerchCategory::count())->toBe(1);
});
