<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('package_subscriptions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // customer user
            $table->foreignId('package_id')->constrained('packages')->cascadeOnDelete();

            $table->date('starts_at');
            $table->date('ends_at');

            $table->enum('status', ['active', 'expired', 'cancelled'])->default('active');

            // Snapshots
            $table->decimal('price_snapshot', 10, 2)->default(0);
            $table->decimal('discounted_price_snapshot', 10, 2)->nullable();
            $table->decimal('final_price_snapshot', 10, 2)->default(0);
            $table->unsignedInteger('total_washes_snapshot')->default(0);
            $table->unsignedInteger('remaining_washes')->default(0);

            $table->timestamp('purchased_at')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status', 'ends_at']);
            $table->index(['package_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('package_subscriptions');
    }
};
