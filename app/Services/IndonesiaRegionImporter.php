<?php

namespace App\Services;

use App\Models\IndonesiaRegion;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Impor data wilayah administratif Indonesia ke tabel indonesia_regions.
 *
 * Sumber: mrayhanfadil/kodepos-id (Kepmendagri No. 100.1.1-6117/2022),
 * file CSV di database/seeders/data (provinsi_v2, kota_v2, kecamatan_v2, kodepos_parsed).
 */
class IndonesiaRegionImporter
{
    private const CHUNK = 500;

    private const DATA_DIR = __DIR__.'/../../database/seeders/data';

    /**
     * Timpa isi tabel dengan dataset CSV lengkap (idempoten).
     *
     * @return array{provinsi: int, kota: int, kecamatan: int, kode_pos: int}
     */
    public function import(): array
    {
        ['provinsi' => $provinsi, 'kota' => $kota, 'kota_provinsi' => $kotaProvinsi, 'kecamatan' => $kecamatan, 'kode_pos' => $kodePos] = $this->load();

        $connection = DB::connection();
        $wasLogging = $connection->logging();
        $connection->disableQueryLog();

        try {
            DB::transaction(function () use ($provinsi, $kota, $kotaProvinsi, $kecamatan, $kodePos): void {
                $this->wipe();

                $now = now();

                $this->insertStream($this->provinsiRows($provinsi, $now));
                $provinsiIds = DB::table('indonesia_regions')
                    ->where('type', IndonesiaRegion::TYPE_PROVINSI)
                    ->pluck('id', 'name');

                $this->insertStream($this->kotaRows($kota, $kotaProvinsi, $provinsiIds, $now));
                $kotaIds = DB::table('indonesia_regions')
                    ->where('type', IndonesiaRegion::TYPE_KOTA)
                    ->pluck('id', 'code');

                $this->insertStream($this->kecamatanRows($kecamatan, $kotaIds, $now));
                $kecamatanIds = DB::table('indonesia_regions')
                    ->where('type', IndonesiaRegion::TYPE_KECAMATAN)
                    ->pluck('id', 'code');

                $this->insertStream($this->kodePosRows($kodePos, $kecamatanIds, $now));
            });
        } finally {
            if ($wasLogging) {
                $connection->enableQueryLog();
            }
        }

        return [
            'provinsi' => DB::table('indonesia_regions')->where('type', IndonesiaRegion::TYPE_PROVINSI)->count(),
            'kota' => DB::table('indonesia_regions')->where('type', IndonesiaRegion::TYPE_KOTA)->count(),
            'kecamatan' => DB::table('indonesia_regions')->where('type', IndonesiaRegion::TYPE_KECAMATAN)->count(),
            'kode_pos' => DB::table('indonesia_regions')->where('type', IndonesiaRegion::TYPE_KODE_POS)->count(),
        ];
    }

    /**
     * @param  array<int, array{0: string, 1: string}>  $provinsi
     * @param  \DateTimeInterface  $now
     * @return \Generator<int, array<string, mixed>>
     */
    private function provinsiRows(array $provinsi, $now): \Generator
    {
        foreach ($provinsi as [$code, $name]) {
            yield $this->row(null, IndonesiaRegion::TYPE_PROVINSI, $name, $code, null, $now);
        }
    }

    /**
     * Provinsi induk dikunci per nama: dataset memakai kode "92" untuk dua
     * provinsi (Papua Barat & Papua Barat Daya).
     *
     * @param  array<int, array{0: string, 1: string, 2: string}>  $kota
     * @param  array<string, string>  $kotaProvinsi
     * @param  Collection<string, int>  $provinsiIds
     * @param  \DateTimeInterface  $now
     * @return \Generator<int, array<string, mixed>>
     */
    private function kotaRows(array $kota, array $kotaProvinsi, $provinsiIds, $now): \Generator
    {
        foreach ($kota as [$fullCode, $tipe, $name]) {
            yield $this->row(
                $provinsiIds[$kotaProvinsi[$fullCode]],
                IndonesiaRegion::TYPE_KOTA,
                $tipe.' '.$name,
                $fullCode,
                strtolower($tipe),
                $now
            );
        }
    }

    /**
     * @param  array<int, array{0: string, 1: string}>  $kecamatan
     * @param  Collection<string, int>  $kotaIds
     * @param  \DateTimeInterface  $now
     * @return \Generator<int, array<string, mixed>>
     */
    private function kecamatanRows(array $kecamatan, $kotaIds, $now): \Generator
    {
        foreach ($kecamatan as [$fullCode, $name]) {
            yield $this->row(
                $kotaIds[substr($fullCode, 0, 4)],
                IndonesiaRegion::TYPE_KECAMATAN,
                $name,
                $fullCode,
                null,
                $now
            );
        }
    }

    /**
     * @param  array<int, string>  $kodePos
     * @param  Collection<string, int>  $kecamatanIds
     * @param  \DateTimeInterface  $now
     * @return \Generator<int, array<string, mixed>>
     */
    private function kodePosRows(array $kodePos, $kecamatanIds, $now): \Generator
    {
        foreach ($kodePos as $packed) {
            [$zip, $fullCode, $kecCode] = explode('|', $packed);

            yield $this->row(
                $kecamatanIds[$kecCode],
                IndonesiaRegion::TYPE_KODE_POS,
                $zip,
                $fullCode,
                null,
                $now
            );
        }
    }

    /**
     * @param  \DateTimeInterface  $now
     * @return array<string, mixed>
     */
    private function row(int|string|null $parentId, string $type, string $name, string $code, ?string $subtype, $now): array
    {
        return [
            'parent_id' => $parentId,
            'type' => $type,
            'name' => $name,
            'code' => $code,
            'subtype' => $subtype,
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }

    /**
     * @param  \Generator<int, array<string, mixed>>  $rows
     */
    private function insertStream(\Generator $rows): void
    {
        $batch = [];

        foreach ($rows as $row) {
            $batch[] = $row;

            if (count($batch) >= self::CHUNK) {
                DB::table('indonesia_regions')->insert($batch);
                $batch = [];
            }
        }

        if ($batch !== []) {
            DB::table('indonesia_regions')->insert($batch);
        }
    }

    private function wipe(): void
    {
        // Urut anak-ke-induk agar FK diri sendiri tetap konsisten.
        foreach ([
            IndonesiaRegion::TYPE_KODE_POS,
            IndonesiaRegion::TYPE_KECAMATAN,
            IndonesiaRegion::TYPE_KOTA,
            IndonesiaRegion::TYPE_PROVINSI,
        ] as $type) {
            DB::table('indonesia_regions')->where('type', $type)->delete();
        }
    }

    /**
     * @return array{
     *     provinsi: array<int, array{0: string, 1: string}>,
     *     kota: array<int, array{0: string, 1: string, 2: string}>,
     *     kota_provinsi: array<string, string>,
     *     kecamatan: array<int, array{0: string, 1: string}>,
     *     kode_pos: array<int, string>
     * }
     */
    private function load(): array
    {
        $provinsi = [];
        foreach ($this->readCsv('provinsi_v2.csv') as $row) {
            $provinsi[] = [$row[0], $row[1]];
        }

        $kota = [];
        foreach ($this->readCsv('kota_v2.csv') as $row) {
            $kota[] = [$row[2], $row[3], $row[4]];
        }

        $kecamatan = [];
        foreach ($this->readCsv('kecamatan_v2.csv') as $row) {
            $kecamatan[] = [$row[3], $row[4]];
        }

        // Satu peta kabupaten -> nama provinsi dari data desa: memisahkan Papua Barat
        // vs Papua Barat Daya yang berbagi kode "92" di file provinsi/kota.
        // Baris kode pos disimpan padat ("zip|kodeWilayah|kodeKecamatan"): 83k baris
        // sebagai string kecil jauh lebih hemat memori daripada array-of-arrays.
        $kotaProvinsi = [];
        $kodePos = [];
        foreach ($this->readCsv('kodepos_parsed.csv') as $row) {
            $kotaProvinsi[$row[3].$row[4]] = $row[14];
            $kodePos[] = $row[1].'|'.$row[7].'|'.$row[3].$row[4].$row[5];
        }

        return [
            'provinsi' => $provinsi,
            'kota' => $kota,
            'kota_provinsi' => $kotaProvinsi,
            'kecamatan' => $kecamatan,
            'kode_pos' => $kodePos,
        ];
    }

    /**
     * @return \Generator<int, array<int, string>>
     */
    private function readCsv(string $filename): \Generator
    {
        $path = self::DATA_DIR.'/'.$filename;
        $handle = fopen($path, 'rb');

        if ($handle === false) {
            throw new RuntimeException("File data wilayah tidak ditemukan: {$path}");
        }

        try {
            $isFirst = true;

            while (($row = fgetcsv($handle)) !== false) {
                if ($isFirst) {
                    $isFirst = false;

                    continue;
                }

                if ($row === [null]) {
                    continue;
                }

                yield $row;
            }
        } finally {
            fclose($handle);
        }
    }
}
