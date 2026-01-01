<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('otp_codes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('mobile'); // للتأكد لو المستخدم مش موجود أو صار تغيير

            $table->string('code');   // 6 أرقام (تقدر لاحقاً تخليها هاش)
            $table->string('type')->default('login'); // لو حبيت تستخدمها لأغراض أخرى لاحقاً

            $table->dateTime('expires_at'); // وقت انتهاء الصلاحية
            $table->unsignedSmallInteger('attempts')->default(0);
            $table->boolean('is_used')->default(false);

            $table->timestamps();

            $table->index(['mobile', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('otp_codes');
    }
};
