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
        Schema::create('promotion_coupon_redemptions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('promotion_coupon_id')->constrained('promotion_coupons')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();

            $table->decimal('discount_amount', 10, 2)->default(0);

            $table->enum('status', ['applied', 'voided'])->default('applied');

            $table->timestamp('applied_at')->nullable();
            $table->timestamp('voided_at')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->index(['user_id', 'status']);
            // $table->index(['promotion_coupon_id', 'user_id', 'status']);

        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotion_coupon_redemptions');
    }
};
