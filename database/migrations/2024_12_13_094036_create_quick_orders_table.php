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
        Schema::create('quick_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Связь с таблицей пользователей
            $table->integer('group_iter')->default(0); // Итерация группы
            $table->timestamp('refresh_time')->nullable(); // Время обновления
            $table->integer('masters_left')->default(10); // или просто ->integer('masters_left')
            $table->boolean('responded')->default(false); // Ответил ли мастер
            $table->timestamps(); // Поля created_at и updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quick_orders');
    }
};
