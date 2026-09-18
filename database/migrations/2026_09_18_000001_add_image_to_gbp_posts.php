<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gbp_posts', function (Blueprint $table) {
            $table->longText('image')->nullable()->after('body');
        });
    }

    public function down(): void
    {
        Schema::table('gbp_posts', function (Blueprint $table) {
            $table->dropColumn('image');
        });
    }
};
