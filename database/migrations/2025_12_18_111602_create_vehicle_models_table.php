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
        Schema::create('vehicle_models', function (Blueprint $table) {
            $table->id();

            $table->foreignId('vehicle_make_id')->constrained('vehicle_makes')->cascadeOnDelete();

            // من ملف الإكسل: CarBrandModels → ID
            $table->unsignedInteger('external_id');

            // i18n JSON: {"ar":"كامري","en":"Camry"}
            $table->json('name');

            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['vehicle_make_id', 'external_id']);
            $table->index(['vehicle_make_id', 'is_active', 'sort_order']);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_models');
    }
};
