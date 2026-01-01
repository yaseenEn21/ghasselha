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
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            // نوع العنوان (كما بالصور)
            $table->enum('type', ['home', 'work', 'other'])->default('home');

            // تفاصيل العنوان
            $table->string('country')->nullable();  // "Saudi Arabia"
            $table->string('city')->nullable();     // "Jeddah"
            $table->string('area')->nullable();     // "Al Hamdaniyah"
            $table->string('address_line')->nullable(); // عنوان مختصر/مقروء
            $table->string('building_name')->nullable();
            $table->string('building_number')->nullable();
            $table->text('landmark')->nullable();   // أقرب معلم

            // ✅ الأهم
            $table->decimal('lat', 10, 7);
            $table->decimal('lng', 10, 7);

            // عنوان أساسي واحد فقط لكل مستخدم (نضمنه بالتطبيق + Transaction)
            $table->boolean('is_default')->default(false);

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'is_default']);
            $table->index(['user_id', 'type']);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
