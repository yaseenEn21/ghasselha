<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('otp_code', 6); // 348921
            $table->timestamp('otp_expires_at');  // 2025-08-01 15:05:00
            $table->string('reset_token', 64)->nullable()->unique(); 
            $table->timestamp('reset_token_expires_at')->nullable(); // 2025-08-01 15:15:00
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('password_reset_tokens');
    }
};
