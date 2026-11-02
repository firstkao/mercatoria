<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Wajib ada minimal 1 super_admin: jadikan admin tertua super_admin bila belum ada.
     */
    public function up(): void
    {
        if (DB::table('users')->where('role', 'super_admin')->exists()) {
            return;
        }

        $firstAdminId = DB::table('users')->where('role', 'admin')->orderBy('id')->value('id');

        if ($firstAdminId) {
            DB::table('users')->where('id', $firstAdminId)->update(['role' => 'super_admin']);
        }
    }

    public function down(): void
    {
        DB::table('users')->where('role', 'super_admin')->update(['role' => 'admin']);
    }
};
