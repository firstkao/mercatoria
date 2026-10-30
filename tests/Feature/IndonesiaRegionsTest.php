<?php

use App\Models\IndonesiaRegion;
use App\Models\User;
use Database\Seeders\IndonesiaRegionsSeeder;

test('api regions memberi data berantai sesuai parent', function () {
    $this->seed(IndonesiaRegionsSeeder::class);

    $provinces = $this->getJson('/api/regions/provinces')->assertOk()->json();
    expect(count($provinces))->toBe(38);

    $jakarta = collect($provinces)->firstWhere('nama', 'DKI Jakarta');
    expect($jakarta)->not->toBeNull();

    $regencies = $this->getJson('/api/regions/regencies/'.$jakarta['id'])->assertOk()->json();
    $namaKota = collect($regencies)->pluck('nama');
    expect($namaKota)->toContain('Kota Jakarta Pusat')
        ->and(collect($regencies)->every(fn ($r) => in_array($r['tipe'], ['kota', 'kabupaten'], true)))->toBeTrue();

    $jakpus = collect($regencies)->firstWhere('nama', 'Kota Jakarta Pusat');
    $districts = $this->getJson('/api/regions/districts/'.$jakpus['id'])->assertOk()->json();
    expect(collect($districts)->pluck('nama'))->toContain('Menteng');

    $menteng = collect($districts)->firstWhere('nama', 'Menteng');
    $codes = $this->getJson('/api/regions/postal-codes/'.$menteng['id'])->assertOk()->json();
    expect(collect($codes)->pluck('kode'))->toContain('10310');

    // Banyak desa dalam satu kecamatan berbagi zip yang sama: API cukup mengembalikannya sekali.
    $bakongan = IndonesiaRegion::type('kecamatan')->where('code', '110101')->firstOrFail();
    $kodesBakongan = collect($this->getJson('/api/regions/postal-codes/'.$bakongan->id)->assertOk()->json())->pluck('kode');
    expect($kodesBakongan->unique()->count())->toBe($kodesBakongan->count())
        ->and($kodesBakongan)->toContain('23773');
});

test('register dengan data dropdown menyimpan id dan nama alamat', function () {
    $this->seed(IndonesiaRegionsSeeder::class);

    $prov = IndonesiaRegion::type('provinsi')->where('name', 'DKI Jakarta')->firstOrFail();
    $kota = IndonesiaRegion::type('kota')->where('name', 'Kota Jakarta Pusat')->firstOrFail();
    $kec = IndonesiaRegion::type('kecamatan')->where('name', 'Menteng')->firstOrFail();

    $this->post('/register', registerPayload([
        'email' => 'dropdown@example.com',
        'no_hp' => '081234567041',
        'provinsi' => 'DKI Jakarta',
        'kota' => 'Kota Jakarta Pusat',
        'kecamatan' => 'Menteng',
        'kode_pos' => '10310',
        'provinsi_id' => $prov->id,
        'kota_id' => $kota->id,
        'kecamatan_id' => $kec->id,
    ]))->assertRedirect();

    $user = User::where('email', 'dropdown@example.com')->firstOrFail();

    expect($user->provinsi)->toBe('DKI Jakarta')
        ->and($user->kota)->toBe('Kota Jakarta Pusat')
        ->and($user->kecamatan)->toBe('Menteng')
        ->and($user->provinsi_id)->toBe($prov->id)
        ->and($user->kota_id)->toBe($kota->id)
        ->and($user->kecamatan_id)->toBe($kec->id);
});

test('rantai alamat tidak valid ditolak saat register dan edit profil', function () {
    $this->seed(IndonesiaRegionsSeeder::class);

    $jakarta = IndonesiaRegion::type('provinsi')->where('name', 'DKI Jakarta')->firstOrFail();
    $medan = IndonesiaRegion::type('kota')->where('name', 'Kota Medan')->firstOrFail();

    $this->post('/register', registerPayload([
        'email' => 'ranti-salah@example.com',
        'no_hp' => '081234567042',
        'provinsi_id' => $jakarta->id,
        'kota_id' => $medan->id,
    ]))->assertSessionHasErrors('kota_id');

    // Edit profil: kota tidak sesuai provinsi → ditolak
    $user = User::factory()->create();
    $this->actingAs($user)->put('/profile/edit', [
        'name' => 'Ranti Salah',
        'email' => $user->email,
        'no_hp' => $user->no_hp,
        'provinsi' => 'Sumatera Utara',
        'kota' => 'Kota Medan',
        'kecamatan' => 'Medan Kota',
        'alamat' => 'Jl. A',
        'kode_pos' => '20111',
        'provinsi_id' => $jakarta->id,
        'kota_id' => $medan->id,
    ])->assertSessionHasErrors('kota_id');
});

test('edit profil dengan rantai valid memperbarui id dan nama alamat', function () {
    $this->seed(IndonesiaRegionsSeeder::class);

    $prov = IndonesiaRegion::type('provinsi')->where('name', 'Sumatera Utara')->firstOrFail();
    $kota = IndonesiaRegion::type('kota')->where('name', 'Kota Medan')->firstOrFail();
    $kec = IndonesiaRegion::type('kecamatan')->where('name', 'Medan Kota')->firstOrFail();

    $user = User::factory()->create();
    $this->actingAs($user)->put('/profile/edit', [
        'name' => 'Profil Baru',
        'email' => $user->email,
        'no_hp' => $user->no_hp,
        'provinsi' => 'Sumatera Utara',
        'kota' => 'Kota Medan',
        'kecamatan' => 'Medan Kota',
        'alamat' => 'Jl. Baru 1',
        'kode_pos' => '20111',
        'provinsi_id' => $prov->id,
        'kota_id' => $kota->id,
        'kecamatan_id' => $kec->id,
    ])->assertRedirect();

    $user->refresh();

    expect($user->provinsi)->toBe('Sumatera Utara')
        ->and($user->kota)->toBe('Kota Medan')
        ->and($user->kecamatan)->toBe('Medan Kota')
        ->and($user->provinsi_id)->toBe($prov->id)
        ->and($user->kota_id)->toBe($kota->id)
        ->and($user->kecamatan_id)->toBe($kec->id)
        ->and($user->kode_pos)->toBe('20111');
});

test('data wilayah lengkap: 38 provinsi, lebih dari 500 kota, lebih dari 5000 kecamatan', function () {
    $this->seed(IndonesiaRegionsSeeder::class);

    expect(IndonesiaRegion::type('provinsi')->count())->toBe(38)
        ->and(IndonesiaRegion::type('kota')->count())->toBeGreaterThan(500)
        ->and(IndonesiaRegion::type('kecamatan')->count())->toBeGreaterThan(5000)
        ->and(IndonesiaRegion::type('kode_pos')->count())->toBeGreaterThan(80000);
});

test('chain check data lengkap menolak kota Jakarta dengan kecamatan Medan', function () {
    $this->seed(IndonesiaRegionsSeeder::class);

    $jakarta = IndonesiaRegion::type('provinsi')->where('name', 'DKI Jakarta')->firstOrFail();
    $jakpus = IndonesiaRegion::type('kota')->where('name', 'Kota Jakarta Pusat')->firstOrFail();
    $medanKota = IndonesiaRegion::type('kecamatan')->where('name', 'Medan Kota')->firstOrFail();

    expect(IndonesiaRegion::chainErrors($jakarta->id, $jakpus->id, $medanKota->id))
        ->toHaveKey('kecamatan_id');

    $this->post('/register', registerPayload([
        'email' => 'rantai-berantai@example.com',
        'no_hp' => '081234567043',
        'provinsi_id' => $jakarta->id,
        'kota_id' => $jakpus->id,
        'kecamatan_id' => $medanKota->id,
    ]))->assertSessionHasErrors('kecamatan_id');
});
