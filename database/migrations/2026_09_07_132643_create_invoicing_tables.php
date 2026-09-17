<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Invoicing / accounting module. "Customer" reuses the existing Client model
// (the agency invoices the businesses it manages) instead of a duplicate
// concept — two extra billing-only columns are added to `clients` for that.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('gstin')->nullable()->after('email');
            $table->string('billing_address')->nullable()->after('gstin');
        });

        Schema::create('service_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->decimal('price', 12, 2)->default(0);
            $table->decimal('gst_percent', 5, 2)->default(18);
            $table->enum('status', ['ACTIVE', 'INACTIVE'])->default('ACTIVE');
            $table->timestamps();
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('invoice_number')->unique();
            $table->date('issue_date');
            $table->date('due_date')->nullable();
            $table->enum('status', ['DRAFT', 'SENT', 'PAID', 'OVERDUE', 'CANCELLED'])->default('DRAFT');
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('gst_total', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->nullable()->constrained()->nullOnDelete();
            $table->string('description');
            $table->decimal('quantity', 10, 2)->default(1);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('gst_percent', 5, 2)->default(18);
            $table->decimal('amount', 12, 2)->default(0); // line total incl. GST
            $table->timestamps();
        });

        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->string('description');
            $table->string('category')->nullable();
            $table->enum('method', ['CASH', 'BANK', 'UPI', 'CARD', 'OTHER'])->default('BANK');
            $table->decimal('amount', 12, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('billing_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('company_name')->nullable();
            $table->string('gstin')->nullable();
            $table->string('address')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_account')->nullable();
            $table->string('ifsc')->nullable();
            $table->string('invoice_prefix')->default('INV');
            $table->unsignedInteger('next_invoice_number')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('billing_settings');
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('services');
        Schema::dropIfExists('service_categories');
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['gstin', 'billing_address']);
        });
    }
};
