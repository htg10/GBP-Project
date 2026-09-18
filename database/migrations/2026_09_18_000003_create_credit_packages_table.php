<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('credit_packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedInteger('credits');
            $table->unsignedInteger('price');            // GST-inclusive rupees
            $table->unsignedInteger('gst_rate')->default(18);
            $table->boolean('is_active')->default(true); // active / paused
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credit_packages');
    }
};
