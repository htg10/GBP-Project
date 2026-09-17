<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Merge AGENCY_OWNER into SUPER_ADMIN — they are now one role.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')->where('role', 'AGENCY_OWNER')->update(['role' => 'SUPER_ADMIN']);
        DB::statement("ALTER TABLE users MODIFY role ENUM('SUPER_ADMIN','CLIENT_OWNER','MARKETING_MANAGER','STAFF') NOT NULL DEFAULT 'STAFF'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY role ENUM('SUPER_ADMIN','AGENCY_OWNER','CLIENT_OWNER','MARKETING_MANAGER','STAFF') NOT NULL DEFAULT 'STAFF'");
    }
};
