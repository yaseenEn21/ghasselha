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
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();

            // نوع البند (بدون discount/coupon لأن الخصم على مستوى الفاتورة)
            $table->enum('item_type', ['service', 'product', 'fee', 'custom'])->default('custom');

            // ربط البند بالعنصر الحقيقي (Service/Product/...) - Snapshot يبقى حتى لو تغير الاسم لاحقاً
            $table->string('itemable_type')->nullable();
            $table->unsignedBigInteger('itemable_id')->nullable();

            // متعدد لغات JSON
            $table->json('title')->nullable();        // {"ar":"...", "en":"..."}
            $table->json('description')->nullable();  // {"ar":"...", "en":"..."}

            $table->decimal('qty', 10, 2)->default(1);
            $table->decimal('unit_price', 12, 2)->default(0);

            // ضريبة سطر (اختياري)
            $table->decimal('line_tax', 12, 2)->default(0);

            // إجمالي السطر قبل خصم الفاتورة (Snapshot)
            $table->decimal('line_total', 12, 2)->default(0); // = qty*unit_price + line_tax

            $table->json('meta')->nullable();
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            $table->index(['invoice_id', 'item_type']);
            $table->index(['itemable_type', 'itemable_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};
