<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Module 1: Users with roles (Super Admin, Agency Owner, Client Owner, Marketing Manager, Staff)
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('email');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->enum('role', ['SUPER_ADMIN', 'AGENCY_OWNER', 'CLIENT_OWNER', 'MARKETING_MANAGER', 'STAFF'])->default('STAFF');
            $table->foreignId('client_id')->nullable();
            $table->string('phone')->nullable();
            $table->longText('avatar')->nullable(); // base64 data URL
            $table->rememberToken();
            $table->timestamps();

            $table->unique(['agency_id', 'email']);
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
    }
};
