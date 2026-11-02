<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Hanya super_admin yang boleh mengganti role user lain (bukan dirinya sendiri).
     */
    public function changeRole(User $actor, User $target): bool
    {
        return $actor->isSuperAdmin() && ! $actor->is($target);
    }

    /**
     * Super_admin lain tidak boleh dihapus; akun sendiri juga tidak.
     */
    public function delete(User $actor, User $target): bool
    {
        return $actor->isAdmin() && ! $actor->is($target) && ! $target->isSuperAdmin();
    }
}
