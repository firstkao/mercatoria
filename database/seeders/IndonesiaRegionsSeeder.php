<?php

namespace Database\Seeders;

use App\Models\IndonesiaRegion;
use Illuminate\Database\Seeder;

class IndonesiaRegionsSeeder extends Seeder
{
    /**
     * 38 provinsi (lengkap) + kota/kabupaten subset awal per provinsi.
     * Kecamatan & kode_pos: contoh kecil untuk alur uji — import penuh dari
     * dataset Kemendagri menyusul (struktur & API sudah siap menerima).
     *
     * @var array<string, string> provinsi => "Nama|tipe, ..."
     */
    private const REGENCIES = [
        'Aceh' => 'Kota Banda Aceh|kota, Kabupaten Aceh Besar|kabupaten',
        'Sumatera Utara' => 'Kota Medan|kota, Kabupaten Deli Serdang|kabupaten',
        'Sumatera Barat' => 'Kota Padang|kota, Kabupaten Agam|kabupaten',
        'Riau' => 'Kota Pekanbaru|kota, Kabupaten Siak|kabupaten',
        'Kepulauan Riau' => 'Kota Batam|kota, Kabupaten Bintan|kabupaten',
        'Jambi' => 'Kota Jambi|kota, Kabupaten Muaro Jambi|kabupaten',
        'Bengkulu' => 'Kota Bengkulu|kota, Kabupaten Bengkulu Utara|kabupaten',
        'Sumatera Selatan' => 'Kota Palembang|kota, Kabupaten Ogan Ilir|kabupaten',
        'Kepulauan Bangka Belitung' => 'Kota Pangkal Pinang|kota, Kabupaten Bangka|kabupaten',
        'Lampung' => 'Kota Bandar Lampung|kota, Kabupaten Lampung Selatan|kabupaten',
        'DKI Jakarta' => 'Kota Jakarta Pusat|kota, Kota Jakarta Selatan|kota, Kabupaten Kepulauan Seribu|kabupaten',
        'Banten' => 'Kota Tangerang|kota, Kabupaten Tangerang|kabupaten, Kota Serang|kota',
        'Jawa Barat' => 'Kota Bandung|kota, Kabupaten Bandung|kabupaten, Kota Bekasi|kota',
        'Jawa Tengah' => 'Kota Semarang|kota, Kabupaten Semarang|kabupaten, Kota Surakarta|kota',
        'DI Yogyakarta' => 'Kota Yogyakarta|kota, Kabupaten Sleman|kabupaten, Kabupaten Bantul|kabupaten',
        'Jawa Timur' => 'Kota Surabaya|kota, Kabupaten Sidoarjo|kabupaten, Kota Malang|kota',
        'Bali' => 'Kota Denpasar|kota, Kabupaten Badung|kabupaten',
        'Nusa Tenggara Barat' => 'Kota Mataram|kota, Kabupaten Lombok Barat|kabupaten',
        'Nusa Tenggara Timur' => 'Kota Kupang|kota, Kabupaten Kupang|kabupaten',
        'Kalimantan Barat' => 'Kota Pontianak|kota, Kabupaten Kubu Raya|kabupaten',
        'Kalimantan Tengah' => 'Kota Palangka Raya|kota, Kabupaten Kapuas|kabupaten',
        'Kalimantan Selatan' => 'Kota Banjarmasin|kota, Kabupaten Banjar|kabupaten',
        'Kalimantan Timur' => 'Kota Balikpapan|kota, Kota Samarinda|kota, Kabupaten Kutai Kartanegara|kabupaten',
        'Kalimantan Utara' => 'Kota Tarakan|kota, Kabupaten Malinau|kabupaten',
        'Sulawesi Utara' => 'Kota Manado|kota, Kabupaten Minahasa|kabupaten',
        'Gorontalo' => 'Kota Gorontalo|kota, Kabupaten Gorontalo|kabupaten',
        'Sulawesi Tengah' => 'Kota Palu|kota, Kabupaten Donggala|kabupaten',
        'Sulawesi Barat' => 'Kota Mamuju|kota, Kabupaten Mamuju|kabupaten',
        'Sulawesi Selatan' => 'Kota Makassar|kota, Kabupaten Gowa|kabupaten',
        'Sulawesi Tenggara' => 'Kota Kendari|kota, Kabupaten Konawe|kabupaten',
        'Maluku' => 'Kota Ambon|kota, Kabupaten Maluku Tengah|kabupaten',
        'Maluku Utara' => 'Kota Ternate|kota, Kabupaten Halmahera Barat|kabupaten',
        'Papua' => 'Kota Jayapura|kota, Kabupaten Jayapura|kabupaten, Kabupaten Sarmi|kabupaten',
        'Papua Barat' => 'Kabupaten Manokwari|kabupaten, Kabupaten Teluk Wondama|kabupaten',
        'Papua Barat Daya' => 'Kota Sorong|kota, Kabupaten Sorong|kabupaten',
        'Papua Selatan' => 'Kabupaten Merauke|kabupaten, Kabupaten Asmat|kabupaten',
        'Papua Tengah' => 'Kabupaten Nabire|kabupaten, Kabupaten Mimika|kabupaten',
        'Papua Pegunungan' => 'Kabupaten Jayawijaya|kabupaten, Kabupaten Lanny Jaya|kabupaten',
    ];

    /**
     * Contoh kecamatan + kode pos (untuk alur uji & autocomplete awal).
     *
     * @var array<string, array{districts: array<int, string>, codes: array<string, string>}>
     */
    private const DISTRICTS = [
        'Kota Jakarta Pusat' => [
            'districts' => ['Menteng', 'Senen', 'Kemayoran'],
            'codes' => ['Menteng' => '10310', 'Senen' => '10410', 'Kemayoran' => '10610'],
        ],
        'Kota Medan' => [
            'districts' => ['Medan Kota', 'Medan Baru'],
            'codes' => ['Medan Kota' => '20111', 'Medan Baru' => '20151'],
        ],
    ];

    public function run(): void
    {
        foreach (array_keys(self::REGENCIES) as $namaProvinsi) {
            $provinsi = IndonesiaRegion::updateOrCreate(
                ['type' => IndonesiaRegion::TYPE_PROVINSI, 'name' => $namaProvinsi],
                ['parent_id' => null, 'subtype' => null]
            );

            foreach (explode(', ', self::REGENCIES[$namaProvinsi]) as $entry) {
                [$namaKota, $tipe] = explode('|', $entry);

                IndonesiaRegion::updateOrCreate(
                    ['type' => IndonesiaRegion::TYPE_KOTA, 'name' => $namaKota, 'parent_id' => $provinsi->id],
                    ['subtype' => $tipe]
                );
            }
        }

        foreach (self::DISTRICTS as $namaKota => $data) {
            $kota = IndonesiaRegion::query()
                ->where('type', IndonesiaRegion::TYPE_KOTA)
                ->where('name', $namaKota)
                ->firstOrFail();

            foreach ($data['districts'] as $namaKecamatan) {
                $kecamatan = IndonesiaRegion::updateOrCreate(
                    ['type' => IndonesiaRegion::TYPE_KECAMATAN, 'name' => $namaKecamatan, 'parent_id' => $kota->id],
                    ['subtype' => null]
                );

                if (isset($data['codes'][$namaKecamatan])) {
                    IndonesiaRegion::updateOrCreate(
                        ['type' => IndonesiaRegion::TYPE_KODE_POS, 'name' => $data['codes'][$namaKecamatan], 'parent_id' => $kecamatan->id],
                        ['subtype' => null]
                    );
                }
            }
        }
    }
}
