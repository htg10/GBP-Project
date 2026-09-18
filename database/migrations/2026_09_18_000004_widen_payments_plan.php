<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * payments.plan was an enum limited to plan codes — widen it to a string so it
 * can also record credit-pack purchases (e.g. "CREDITS-2000").
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE payments MODIFY plan VARCHAR(60) NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE payments MODIFY plan ENUM('STARTER','GROWTH','AGENCY') NOT NULL");
    }
};
