<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class Game extends Model
{
    use HasFactory;

    public const NAV_CACHE_KEY = 'nav_games_active';

    protected static function booted(): void
    {
        $flush = function (): void {
            Cache::forget(self::NAV_CACHE_KEY);
            request()->attributes->remove('nav_games');
        };
        static::saved($flush);
        static::deleted($flush);
    }

    protected $fillable = [
        'name',
        'slug',
        'description',
        'logo_path',
        'is_active',
        'default_store_id',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function defaultStore(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'default_store_id');
    }

    public function developers(): BelongsToMany
    {
        return $this->belongsToMany(Developer::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
