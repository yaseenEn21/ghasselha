<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();

            $table->string('number')->unique(); // INV-...

            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            // يربط الفاتورة بالكيان الأساسي: Booking / PackageSubscription / StoreOrder ...
            $table->string('invoiceable_type')->nullable();
            $table->unsignedBigInteger('invoiceable_id')->nullable();

            // نوع الفاتورة
            $table->enum('type', ['invoice', 'adjustment', 'credit_note'])->default('invoice');

            // فاتورة ناتجة عن فاتورة سابقة (مثلاً تعديل بعد الدفع)
            $table->foreignId('parent_invoice_id')->nullable()
                ->constrained('invoices')->nullOnDelete();

            // رقم نسخة لكل invoiceable (حجز) -> 1,2,3...
            $table->unsignedInteger('version')->default(1);

            // الحالة
            $table->enum('status', ['unpaid', 'paid', 'cancelled', 'refunded'])->default('unpaid');

            // Totals (Snapshot)
            $table->decimal('subtotal', 12, 2)->default(0); // مجموع البنود قبل الخصم
            $table->decimal('discount', 12, 2)->default(0); // ✅ خصم على مستوى الفاتورة فقط
            $table->decimal('tax', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);    // total = subtotal + tax - discount

            $table->char('currency', 3)->default('SAR');

            $table->timestamp('issued_at')->useCurrent();
            $table->timestamp('paid_at')->nullable();

            // لمنع تعديل فاتورة مدفوعة (سياسة)
            $table->boolean('is_locked')->default(false);

            // بيانات إضافية (مثلاً coupon_code، نسبة خصم...)
            $table->json('meta')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status']);
            $table->index(['invoiceable_type', 'invoiceable_id', 'version']);
            $table->index(['parent_invoice_id']);
            // $table->unique(['invoiceable_type', 'invoiceable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
