<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable(['title', 'slug', 'content', 'is_active'])]
class PreorderPage extends Model
{
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * @return HasMany<PreorderBanner, $this>
     */
    public function banners(): HasMany
    {
        return $this->hasMany(PreorderBanner::class)->orderBy('sort_order')->orderBy('id');
    }

    /**
     * @return HasMany<PreorderBanner, $this>
     */
    public function activeBanners(): HasMany
    {
        return $this->banners()->where('is_active', true);
    }

    #[Scope]
    protected function active(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Halaman PO yang aktif saat ini (yang tampil di /pre-order-baru).
     */
    public static function current(): ?self
    {
        return static::query()->active()->latest('updated_at')->first();
    }

    public function html(): string
    {
        return Str::markdown((string) $this->content, [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);
    }
}