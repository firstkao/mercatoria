<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['key', 'value'])]
class Setting extends Model
{
    protected $primaryKey = 'key';

    protected $keyType = 'string';

    public $incrementing = false;

    /**
     * Read a business setting, loading all settings once per request.
     */
    public static function get(string $key, ?string $default = null): ?string
    {
        $settings = once(fn (): array => static::query()->pluck('value', 'key')->all());

        return $settings[$key] ?? $default;
    }

    /**
     * Read a business setting as an integer.
     */
    public static function integer(string $key, int $default): int
    {
        return (int) static::get($key, (string) $default);
    }
}