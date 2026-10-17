<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'type', 'group', 'description'];

    public static function getValue(string $key, $default = null)
    {
        $setting = static::where('key', $key)->first();

        return $setting?->value ?? $default;
    }

    public static function setValue(string $key, $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => (string) $value]);
        Cache::forget("setting.{$key}");
    }
}
