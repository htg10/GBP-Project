<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Module 4: Social media posts.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('social_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->enum('platform', ['FACEBOOK', 'INSTAGRAM', 'LINKEDIN', 'X']);
            $table->text('body');
            $table->json('media_urls')->nullable();
            $table->enum('status', ['DRAFT', 'SCHEDULED', 'PUBLISHED', 'FAILED'])->default('DRAFT');
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('social_posts'); }
};
