<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('service_zone_prices', function (Blueprint $table) {
            $table->id();

            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->foreignId('zone_id')->constrained('zones')->cascadeOnDelete();

            // all = طوال اليوم، morning = صباحي، evening = مسائي
            $table->enum('time_period', ['all', 'morning', 'evening'])->default('all');

            $table->decimal('price', 10, 2);
            $table->decimal('discounted_price', 10, 2)->nullable();

            $table->boolean('is_active')->default(true);

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['service_id', 'zone_id', 'time_period']);
            $table->index(['zone_id', 'time_period', 'is_active']);
            $table->index(['service_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_zone_prices');
    }
};