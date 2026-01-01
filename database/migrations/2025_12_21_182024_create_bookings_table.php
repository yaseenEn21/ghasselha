<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            $table->foreignId('car_id')->constrained('cars')->cascadeOnDelete();
            $table->foreignId('address_id')->constrained('addresses')->cascadeOnDelete();

            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();

            // resolved context
            $table->unsignedBigInteger('zone_id')->nullable();
            $table->enum('time_period', ['morning', 'evening', 'all'])->default('all');

            // price snapshot (before "package cover")
            $table->decimal('service_unit_price_snapshot', 12, 2)->default(0);

            // what customer should pay for service line (0 if covered by package)
            $table->decimal('service_charge_amount_snapshot', 12, 2)->default(0);

            $table->enum('service_pricing_source', ['base', 'zone', 'group', 'package'])
                ->default('base');

            $table->json('service_pricing_meta')->nullable();

            // الموظف (بسبب التوفر)
            $table->foreignId('employee_id')->nullable()->constrained('employees')->nullOnDelete();

            // في حال استخدم باقة
            $table->foreignId('package_subscription_id')
                ->nullable()
                ->constrained('package_subscriptions')
                ->nullOnDelete();

            $table->enum('status', ['pending', 'confirmed', 'moving', 'arrived', 'completed', 'cancelled'])
                ->default('pending');

            // تاريخ وتوقيت الحجز
            $table->date('booking_date');
            $table->time('start_time');
            $table->time('end_time');

            $table->unsignedInteger('duration_minutes');

            // Snapshots للأسعار
            $table->decimal('service_price_snapshot', 12, 2)->default(0);
            $table->decimal('service_discounted_price_snapshot', 12, 2)->nullable();
            $table->decimal('service_final_price_snapshot', 12, 2)->default(0);

            $table->decimal('products_subtotal_snapshot', 12, 2)->default(0);

            $table->decimal('subtotal_snapshot', 12, 2)->default(0);
            $table->decimal('discount_snapshot', 12, 2)->default(0);
            $table->decimal('tax_snapshot', 12, 2)->default(0);
            $table->decimal('total_snapshot', 12, 2)->default(0);

            $table->char('currency', 3)->default('SAR');

            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            // سبب الإلغاء
            $table->string('cancel_reason')->nullable();
            $table->text('cancel_note')->nullable();

            $table->json('meta')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status', 'booking_date']);
            $table->index(['employee_id', 'booking_date', 'start_time']);
            $table->index(['zone_id', 'time_period']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};