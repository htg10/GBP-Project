<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gbp_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('google_name'); // accounts/123/locations/456
            $table->string('title');
            $table->string('address')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('gbp_locations'); }
};
