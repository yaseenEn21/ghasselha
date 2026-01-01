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
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('wallet_id')->constrained('wallets')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            $table->enum('direction', ['credit', 'debit']); // + / -
            $table->enum('type', [
                'topup',           // شحن
                'refund',          // استرجاع
                'adjustment',      // تعديل إداري
                'booking_charge',  // خصم لحجز
                'package_purchase' // خصم لشراء باقة (لو عبر wallet)
            ]);

            $table->decimal('amount', 12, 2);
            $table->decimal('balance_before', 12, 2);
            $table->decimal('balance_after', 12, 2);

            // متعدد لغات (JSON): {"ar":"...", "en":"..."}
            $table->json('description')->nullable();
            $table->json('meta')->nullable();

            // ربط بالحجز/الباقة/الخ... (Polymorphic)
            $table->string('referenceable_type')->nullable();
            $table->unsignedBigInteger('referenceable_id')->nullable();

            // ربط بالدفع إن وجد
            $table->unsignedBigInteger('payment_id')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->index(['user_id']);
            $table->index(['wallet_id']);
            $table->index(['referenceable_type', 'referenceable_id']);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};
