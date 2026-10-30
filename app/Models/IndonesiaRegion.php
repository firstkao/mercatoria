<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IndonesiaRegion extends Model
{
    use HasFactory;

    public const TYPE_PROVINSI = 'provinsi';

    public const TYPE_KOTA = 'kota';

    public const TYPE_KECAMATAN = 'kecamatan';

    public const TYPE_KODE_POS = 'kode_pos';

    protected $fillable = [
        'parent_id',
        'type',
        'name',
        'subtype',
    ];

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function scopeType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Validasi rantai alamat (provinsi -> kota -> kecamatan) bila id dikirim.
     *
     * @return array<string, array<int, string>>
     */
    public static function chainErrors(int|string|null $provinsiId, int|string|null $kotaId, int|string|null $kecamatanId): array
    {
        $errors = [];

        if ($provinsiId && ! static::query()->where('id', $provinsiId)->where('type', self::TYPE_PROVINSI)->exists()) {
            $errors['provinsi_id'][] = 'Provinsi tidak valid.';
        }

        if ($kotaId) {
            $kota = static::query()->where('id', $kotaId)->where('type', self::TYPE_KOTA)->first();

            if (! $kota) {
                $errors['kota_id'][] = 'Kota/kabupaten tidak valid.';
            } elseif ($provinsiId && (int) $kota->parent_id !== (int) $provinsiId) {
                $errors['kota_id'][] = 'Kota/kabupaten tidak sesuai dengan provinsi.';
            }
        }

        if ($kecamatanId) {
            $kec = static::query()->where('id', $kecamatanId)->where('type', self::TYPE_KECAMATAN)->first();

            if (! $kec) {
                $errors['kecamatan_id'][] = 'Kecamatan tidak valid.';
            } elseif ($kotaId && (int) $kec->parent_id !== (int) $kotaId) {
                $errors['kecamatan_id'][] = 'Kecamatan tidak sesuai dengan kota/kabupaten.';
            }
        }

        return $errors;
    }

    /**
     * Nama wilayah dari id (untuk mengisi kolom string provinsi/kota/kecamatan).
     *
     * @return array{provinsi: ?string, kota: ?string, kecamatan: ?string}
     */
    public static function namesFor(int|string|null $provinsiId, int|string|null $kotaId, int|string|null $kecamatanId): array
    {
        $find = fn ($id, $type) => $id ? static::query()->where('id', $id)->where('type', $type)->value('name') : null;

        return [
            'provinsi' => $find($provinsiId, self::TYPE_PROVINSI),
            'kota' => $find($kotaId, self::TYPE_KOTA),
            'kecamatan' => $find($kecamatanId, self::TYPE_KECAMATAN),
        ];
    }
}
