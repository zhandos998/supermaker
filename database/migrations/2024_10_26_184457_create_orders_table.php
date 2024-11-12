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
        Schema::create('orders', function (Blueprint $table) {
            $table->id(); // Уникальный идентификатор
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Привязка к таблице users
            $table->foreignId('master_id')->constrained('users')->onDelete('cascade'); // Привязка к мастерам (также пользователи)
            $table->foreignId('video_id')->constrained('videos')->onDelete('cascade'); // Привязка к таблице videos
            $table->decimal('master_price', 10, 2); // Цена мастера
            $table->unsignedInteger('master_time'); // Время, связанное с заказом (например, в минутах)
            $table->foreignId('status_id')->constrained('order_statuses')->onDelete('cascade'); // Привязка к таблице order_statuses
            $table->timestamps(); // Поля created_at и updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
