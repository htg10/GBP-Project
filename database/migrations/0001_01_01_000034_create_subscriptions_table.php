<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Module 8: Subscription + billing (Razorpay).
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->constrained()->cascadeOnDelete()->unique();
            $table->enum('plan', ['STARTER', 'GROWTH', 'AGENCY'])->default('STARTER');
            $table->enum('status', ['ACTIVE', 'PAST_DUE', 'CANCELLED', 'TRIALING'])->default('TRIALING');
            $table->string('provider')->nullable();      // razorpay
            $table->string('external_id')->nullable();    // gateway order/sub id
            $table->timestamp('renews_at')->nullable();
            $table->timestamps();
        });

        // Payment records for invoices / history.
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->constrained()->cascadeOnDelete();
            $table->enum('plan', ['STARTER', 'GROWTH', 'AGENCY']);
            $table->unsignedInteger('amount'); // in paise
            $table->string('currency')->default('INR');
            $table->string('razorpay_order_id')->nullable();
            $table->string('razorpay_payment_id')->nullable();
            $table->enum('status', ['CREATED', 'PAID', 'FAILED'])->default('CREATED');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('subscriptions'); Schema::dropIfExists('payments'); }
};
