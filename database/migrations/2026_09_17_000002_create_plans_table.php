<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Plans are now managed by the Super Admin (create/edit, per-plan access,
 * GST-inclusive pricing).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();       // STARTER / GROWTH / AGENCY / custom
            $table->unsignedInteger('price');        // GST-INCLUSIVE price in rupees
            $table->unsignedInteger('gst_rate')->default(18); // GST %
            $table->unsignedInteger('credits')->default(0);   // monthly AI credits
            $table->json('features')->nullable();    // bullet list shown on the card
            $table->json('permissions')->nullable(); // module access keys enabled
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
