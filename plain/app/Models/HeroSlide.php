<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HeroSlide extends Model
{
    use HasFactory;

    // Tentukan nama tabel jika tidak menggunakan bentuk jamak standar (opsional)
    protected $table = 'hero_slides';

    // Kolom yang bisa diisi (mass assignable)
    protected $fill = [
        'title',
        'subtitle',
        'image',
        'button_text',
        'button_link',
        'order',
        'is_active',
    ];
}
