<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SpammerLog extends Model
{
    protected $fillable = ['spammer_id', 'action', 'ip_address', 'note'];

    public function spammer(): BelongsTo
    { return $this->belongsTo(Spammer::class); }
}
