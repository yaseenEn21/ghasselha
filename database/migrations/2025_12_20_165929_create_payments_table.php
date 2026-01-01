<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();

            // الدفع رايح على إيش؟ (wallet_topup / booking_payment / package_purchase / invoice_payment / refund)
            $table->string('payable_type')->nullable();
            $table->unsignedBigInteger('payable_id')->nullable();

            $table->decimal('amount', 12, 2);
            $table->char('currency', 3)->default('SAR');

            $table->enum('method', ['wallet', 'credit_card', 'apple_pay', 'google_pay', 'cash', 'visa', 'stc']);
            $table->enum('status', ['pending', 'paid', 'failed', 'cancelled', 'refunded'])->default('pending');

            $table->string('gateway')->nullable(); // moyasar
            $table->string('gateway_payment_id')->nullable();
            $table->string('gateway_invoice_id')->nullable();
            $table->string('gateway_status')->nullable()->index();
            $table->text('gateway_transaction_url')->nullable();
            $table->json('gateway_raw')->nullable();
            $table->timestamp('paid_at')->nullable();

            $table->json('meta')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status', 'created_at']);
            $table->index(['invoice_id']);
            $table->index(['payable_type', 'payable_id']);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
