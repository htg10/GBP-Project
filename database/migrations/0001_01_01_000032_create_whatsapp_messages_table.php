<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Module 6/9: WhatsApp messages log.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->constrained()->cascadeOnDelete();
            $table->string('to_phone');
            $table->string('from_phone')->nullable();
            $table->enum('direction', ['INBOUND', 'OUTBOUND']);
            $table->string('template')->nullable();
            $table->text('body')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('whatsapp_messages'); }
};
