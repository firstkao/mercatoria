<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['event'])]
class RegistrationEvent extends Model
{
    public const UPDATED_AT = null;

    /**
     * @return BelongsTo<IdentityRecord, $this>
     */
    public function identityRecord(): BelongsTo
    {
        return $this->belongsTo(IdentityRecord::class);
    }
}