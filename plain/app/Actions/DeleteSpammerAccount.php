<?php

namespace App\Actions;

use App\Models\IdentityRecord;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Deletes a spammer account and counts it against its email and WhatsApp number.
 */
class DeleteSpammerAccount
{
    /**
     * @param  string  $reason  Registration event name, e.g. "deleted_expired" or "deleted_unpaid".
     */
    public function handle(User $user, string $reason): void
    {
        if (! $user->isSpammer()) {
            return;
        }

        DB::transaction(function () use ($user, $reason): void {
            $limit = Setting::integer('registration_limit', 3);
            $records = collect([
                IdentityRecord::KIND_EMAIL => $user->email,
                IdentityRecord::KIND_WHATSAPP => $user->whatsapp,
            ])
                ->filter()
                ->map(fn (string $value, string $kind): IdentityRecord => IdentityRecord::firstOrCreate(['kind' => $kind, 'value' => $value]));

            foreach ($records as $record) {
                $record->increment('deletion_count');
                $record->registrationEvents()->create(['event' => $reason]);
            }

            // Reaching the limit on either identity blocks both.
            if ($records->contains(fn (IdentityRecord $record): bool => $record->deletion_count >= $limit)) {
                foreach ($records as $record) {
                    $record->forceFill(['blocked_at' => now()])->save();
                    $record->registrationEvents()->create(['event' => 'blocked']);
                }
            }

            // Orders, cart items, product views, and activity logs cascade with the user row.
            $user->delete();
        });
    }
}