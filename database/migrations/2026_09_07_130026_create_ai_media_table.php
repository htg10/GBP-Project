<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// AI Generated Media — images (video is out of scope for now) produced from a
// text prompt, stored as a data: URI so no S3/storage setup is required.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type')->default('IMAGE'); // IMAGE (VIDEO reserved for later)
            $table->text('prompt');
            $table->longText('image_data'); // data:image/...;base64,... or a URL
            $table->string('model')->nullable();
            $table->string('source')->default('ai'); // ai | fallback
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_media');
    }
};
