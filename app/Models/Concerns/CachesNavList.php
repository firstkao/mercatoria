<?php

namespace App\Models\Concerns;

use App\Providers\AppServiceProvider;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Daftar model yang jarang berubah: di-cache lintas request, di-memo per request,
 * dan otomatis di-flush saat model disimpan/dihapus.
 * Model wajib mendefinisikan konstanta NAV_CACHE_KEY dan method statis navQuery().
 */
trait CachesNavList
{
    public static function bootCachesNavList(): void
    {
        $flush = function (): void {
            Cache::forget(static::NAV_CACHE_KEY);
            request()->attributes->remove(static::NAV_CACHE_KEY);
        };
        static::saved($flush);
        static::deleted($flush);
    }

    /**
     * @return Collection<int, static>
     */
    public static function navList(): Collection
    {
        return AppServiceProvider::perRequest(static::NAV_CACHE_KEY, function (): Collection {
            // Simpan atribut polos (bukan object model): payload serialize model rusak
            // jadi __PHP_Incomplete_Class bila class pindah/berubah nama antar deploy.
            $rows = Cache::get(static::NAV_CACHE_KEY);

            if (! is_array($rows)) {
                Cache::forget(static::NAV_CACHE_KEY);
                $rows = static::navQuery()->get()->map(fn ($model) => $model->getAttributes())->all();
                Cache::put(static::NAV_CACHE_KEY, $rows, 3600);
            }

            return static::hydrate($rows)->values();
        });
    }

    abstract protected static function navQuery(): Builder;
}
