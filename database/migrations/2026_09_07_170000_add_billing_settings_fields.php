<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('billing_settings', function (Blueprint $table) {
            $table->string('business_type', 60)->nullable()->after('company_name');
            $table->string('phone', 30)->nullable()->after('business_type');
            $table->string('email')->nullable()->after('phone');
            $table->string('state', 60)->nullable()->after('address');
            $table->longText('logo')->nullable()->after('state');
            $table->decimal('default_gst', 5, 2)->default(18)->after('ifsc');
            $table->string('currency', 10)->default('INR')->after('default_gst');
            $table->boolean('round_total')->default(false)->after('currency');
            $table->string('tally_company_name')->nullable()->after('round_total');
            $table->string('customer_ledger_group')->default('Sundry Debtors')->after('tally_company_name');
        });

        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone', 30)->nullable()->after('email');
            }
            if (!Schema::hasColumn('users', 'avatar')) {
                $table->longText('avatar')->nullable()->after('phone');
            }
        });
    }

    public function down(): void
    {
        Schema::table('billing_settings', function (Blueprint $table) {
            $table->dropColumn(['business_type', 'phone', 'email', 'state', 'logo', 'default_gst', 'currency', 'round_total', 'tally_company_name', 'customer_ledger_group']);
        });
    }
};
