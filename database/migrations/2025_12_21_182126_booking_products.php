<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('booking_products', function (Blueprint $table) {
            $table->id();

            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();

            $table->unsignedInteger('qty')->default(1);

            // snapshot
            $table->decimal('unit_price_snapshot', 12, 2)->default(0);
            $table->json('title')->nullable(); // {"ar": "...", "en": "..."}
            $table->decimal('line_total', 12, 2)->default(0);

            $table->timestamps();

            $table->unique(['booking_id', 'product_id']);
            $table->index(['booking_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_products');
    }
};