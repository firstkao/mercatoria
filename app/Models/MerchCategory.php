<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MerchCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'priority',
        'keywords',
    ];

    protected function casts(): array
    {
        return [
            'priority' => 'integer',
            'keywords' => 'array',
        ];
    }
}
