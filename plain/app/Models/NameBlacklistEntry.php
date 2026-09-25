<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

#[Fillable(['name', 'name_normalized'])]
class NameBlacklistEntry extends Model
{
    protected $table = 'name_blacklist';

    /**
     * Normalize a name for exact, case-insensitive comparison.
     */
    public static function normalize(string $name): string
    {
        return Str::of($name)->squish()->lower()->toString();
    }

    /**
     * Determine whether the name exactly matches a blacklisted name.
     */
    public static function matches(string $name): bool
    {
        return static::query()->where('name_normalized', static::normalize($name))->exists();
    }
}