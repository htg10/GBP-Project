<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Module 6/7: Google + Meta ad performance reports.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ad_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->enum('network', ['GOOGLE', 'META']);
            $table->string('campaign');
            $table->decimal('spend', 12, 2)->default(0);
            $table->unsignedInteger('clicks')->default(0);
            $table->unsignedInteger('impressions')->default(0);
            $table->unsignedInteger('conversions')->default(0);
            $table->unsignedInteger('leads')->default(0);
            $table->date('period_start');
            $table->date('period_end');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('ad_reports'); }
};
