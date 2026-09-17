<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Credit balance lives on subscriptions — one row per agency.
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->unsignedInteger('credit_balance')->default(0)->after('plan');
            $table->unsignedInteger('monthly_credits')->default(0)->after('credit_balance');
            $table->timestamp('credits_reset_at')->nullable()->after('monthly_credits');
        });

        // Every credit change (deduction or top-up) is logged here.
        Schema::create('credit_ledger', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->smallInteger('amount'); // negative = deduction, positive = top-up / monthly reset
            $table->unsignedInteger('balance_after');
            $table->string('action', 60); // e.g. ai_review_reply, ai_media_generate, monthly_reset, manual_topup
            $table->string('description')->nullable();
            $table->timestamps();

            $table->index(['agency_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credit_ledger');
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn(['credit_balance', 'monthly_credits', 'credits_reset_at']);
        });
    }
};
