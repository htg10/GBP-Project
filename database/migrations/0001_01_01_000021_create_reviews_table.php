<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Module 2: Google reviews + AI sentiment + replies.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->constrained()->cascadeOnDelete();
            $table->foreignId('gbp_location_id')->constrained()->cascadeOnDelete();
            $table->string('google_review_id')->unique();
            $table->string('reviewer_name');
            $table->string('reviewer_photo')->nullable();
            $table->unsignedTinyInteger('star_rating');
            $table->text('comment')->nullable();
            $table->enum('sentiment', ['POSITIVE', 'NEUTRAL', 'NEGATIVE'])->nullable();
            $table->text('reply_text')->nullable();
            $table->timestamp('replied_at')->nullable();
            $table->foreignId('replied_by')->nullable();
            $table->timestamp('review_time')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('reviews'); }
};
