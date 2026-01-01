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
        Schema::create('cars', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            $table->foreignId('vehicle_make_id')->constrained('vehicle_makes');
            $table->foreignId('vehicle_model_id')->constrained('vehicle_models');

            // لون كـ key ثابت
            $table->string('color', 30)->nullable();

            // لوحة (مرنة) — حسب تصميمكم (حروف + أرقام)
            $table->string('plate_number', 10);
            $table->string('plate_letters', 10);      // ممكن عربي/إنجليزي
            $table->string('plate_letters_ar', 10)->nullable(); // اختياري لو الموبايل يرسل العربي منفصل

            $table->boolean('is_default')->default(false);

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['user_id', 'plate_number', 'plate_letters']);
            $table->index(['user_id', 'is_default']);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cars');
    }
};
