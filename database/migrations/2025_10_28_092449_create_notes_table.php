<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('notes', function (Blueprint $table) {
            $table->id();

            $table->string('title')->nullable();
            $table->text('body');
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->enum('status', ['pending', 'inprogress', 'completed'])->default('pending');

            $table->timestamps();
            $table->softDeletes();

            $table->index(['title']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notes');
    }
};

