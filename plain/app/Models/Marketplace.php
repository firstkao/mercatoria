<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'fp_fee_idr', 'dp_fee_percent', 'is_active'])]
class Marketplace extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fp_fee_idr' => 'integer',
            'dp_fee_percent' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }
}