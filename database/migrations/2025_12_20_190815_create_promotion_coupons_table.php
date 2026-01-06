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
        Schema::create('promotion_coupons', function (Blueprint $table) {
            $table->id();

            $table->foreignId('promotion_id')->constrained('promotions')->cascadeOnDelete();

            $table->string('code', 30)->unique(); // نخزّنها uppercase
            $table->boolean('is_active')->default(true);

            // صلاحية الكوبون (اختياري فوق صلاحية الحملة)
            $table->date('starts_at')->nullable();
            $table->date('ends_at')->nullable();

             // نطاق التطبيق
            $table->enum('applies_to', ['service', 'package', 'both'])->default('both');
            $table->boolean('apply_all_services')->default(false);
            $table->boolean('apply_all_packages')->default(false);

            // نوع الخصم
            $table->enum('discount_type', ['percent', 'fixed'])->default('percent');
            $table->decimal('discount_value', 10, 2)->default(0); // 10% أو 20 SAR


            // حدود الاستخدام
            $table->unsignedInteger('usage_limit_total')->nullable();     // إجمالي
            $table->unsignedInteger('usage_limit_per_user')->nullable();  // لكل مستخدم
            $table->unsignedInteger('used_count')->default(0);            // للتسريع (نحدّثه مع redeem)

            // شروط
            $table->decimal('min_invoice_total', 10, 2)->nullable(); // أقل إجمالي فاتورة قبل الخصم
            $table->decimal('max_discount', 10, 2)->nullable();      // override على max_discount الخاص بالحملة

            $table->json('meta')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['promotion_id', 'is_active']);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotion_coupons');
    }
};
