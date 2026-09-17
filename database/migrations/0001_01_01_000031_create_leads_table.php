<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Module 5: Lead CRM with pipeline stages.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->enum('source', ['FACEBOOK', 'INSTAGRAM', 'WEBSITE', 'WHATSAPP', 'GOOGLE_FORM']);
            $table->enum('stage', ['NEW', 'CONTACTED', 'FOLLOW_UP', 'APPOINTMENT', 'CONVERTED', 'LOST'])->default('NEW');
            $table->unsignedSmallInteger('score')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('leads'); }
};
