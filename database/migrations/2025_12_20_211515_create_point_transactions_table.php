<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('point_transactions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            $table->enum('type', ['earn', 'redeem', 'adjust', 'refund'])->index();

            // عدد النقاط (ممكن تكون + أو -)
            $table->bigInteger('points');

            // قيمة الريال الناتجة عن التحويل (مهم لحركات redeem)
            $table->decimal('money_amount', 10, 2)->nullable();
            $table->char('currency', 3)->default('SAR');

            // مرجع للحركة (Booking/Invoice/... إلخ)
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->index(['reference_type', 'reference_id']);

            $table->string('note')->nullable();
            $table->json('meta')->nullable();

            // أرشفة اختيارية
            $table->boolean('is_archived')->default(false);
            $table->timestamp('archived_at')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('point_transactions');
    }
};
