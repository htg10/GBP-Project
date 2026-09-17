<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gbp_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gbp_location_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['OFFER', 'EVENT', 'UPDATE']);
            $table->text('body');
            $table->string('cta_url')->nullable();
            $table->enum('status', ['DRAFT', 'SCHEDULED', 'PUBLISHED', 'FAILED'])->default('DRAFT');
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
        Schema::create('gbp_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gbp_location_id')->constrained()->cascadeOnDelete();
            $table->longText('image'); // base64 or URL
            $table->string('caption')->nullable();
            $table->enum('status', ['DRAFT', 'SCHEDULED', 'PUBLISHED', 'FAILED'])->default('DRAFT');
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('gbp_posts'); Schema::dropIfExists('gbp_photos'); }
};
