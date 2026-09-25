<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

#[Fillable(['image_path', 'sort_order'])]
class ProductImage extends Model
{
    public function url(): string
    {
        return Storage::disk('public')->url($this->image_path);
    }
}