<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Apps (ERP, client sites ...) that collect money through this hub.
        Schema::create('merchant_apps', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('app_key', 64)->unique();
            $table->text('app_secret'); // encrypted cast
            $table->string('webhook_url', 500)->nullable();
            $table->json('allowed_hosts')->nullable();
            $table->decimal('fee_percent', 5, 2)->default(0);
            $table->string('contact_email')->nullable();
            $table->text('payout_details')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
        });

        Schema::create('disbursements', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 40)->unique();
            $table->foreignId('merchant_app_id')->constrained()->restrictOnDelete();
            $table->decimal('amount', 12, 2);
            $table->unsignedInteger('payments_count')->default(0);
            $table->string('method', 30);
            $table->text('account_details')->nullable();
            $table->string('transaction_reference')->nullable();
            $table->text('note')->nullable();
            $table->string('status', 20)->default('pending')->index();
            $table->timestamp('disbursed_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 40)->unique();
            $table->foreignId('merchant_app_id')->constrained()->restrictOnDelete();
            $table->string('order_id', 100);
            $table->decimal('amount', 12, 2);
            $table->decimal('fee_amount', 12, 2)->default(0);
            $table->decimal('net_amount', 12, 2)->default(0);
            $table->char('currency', 3)->default('BDT');
            $table->string('description');
            $table->string('status', 20)->default('pending')->index();

            $table->string('customer_name')->nullable();
            $table->string('customer_email')->nullable();
            $table->string('customer_phone', 30)->nullable();
            $table->string('customer_address')->nullable();
            $table->string('customer_city', 100)->nullable();
            $table->string('customer_state', 100)->nullable();
            $table->string('customer_postcode', 20)->nullable();
            $table->string('customer_country', 60)->nullable();

            $table->string('gateway', 20)->default('eps');
            $table->string('merchant_transaction_id', 40)->nullable()->index();
            $table->string('gateway_transaction_id')->nullable();
            $table->string('payment_method')->nullable(); // bKash, Nagad, VISA ... as reported by EPS
            $table->string('failure_reason')->nullable();

            $table->text('return_url');
            $table->text('cancel_url')->nullable();
            $table->text('webhook_url')->nullable();
            $table->json('metadata')->nullable();

            $table->string('webhook_status', 20)->default('none')->index(); // none, pending, delivered, failed
            $table->unsignedSmallInteger('webhook_attempts')->default(0);
            $table->timestamp('webhook_last_attempt_at')->nullable();
            $table->timestamp('webhook_next_attempt_at')->nullable();
            $table->text('webhook_last_response')->nullable();

            $table->foreignId('disbursement_id')->nullable()->constrained()->nullOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('paid_at')->nullable()->index();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->unique(['merchant_app_id', 'order_id']);
        });

        // One row per trip to EPS. A customer may retry after a failure, so a
        // payment can have several attempts; at most one of them succeeds.
        Schema::create('payment_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained()->cascadeOnDelete();
            $table->string('merchant_transaction_id', 40)->unique();
            $table->string('gateway_transaction_id')->nullable();
            $table->text('redirect_url')->nullable();
            $table->string('status', 20)->default('initiated')->index(); // initiated, success, failed, cancelled, error
            $table->string('gateway_status')->nullable();
            $table->string('return_result', 20)->nullable(); // success / fail / cancel as EPS redirected
            $table->json('init_response')->nullable();
            $table->json('verify_response')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_attempts');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('disbursements');
        Schema::dropIfExists('merchant_apps');
    }
};
