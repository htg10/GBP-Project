<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $demoEmails = ['owner@demo.com', 'staff@demo.com', 'client@demo.com'];
        DB::table('users')->whereIn('email', $demoEmails)->delete();
    }

    public function down(): void
    {
        // Demo users are not restored
    }
};
