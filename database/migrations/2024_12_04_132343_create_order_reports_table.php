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
        Schema::create('order_reports', function (Blueprint $table) {
            $table->id(); // Уникальный идентификатор
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade'); // Привязка к таблице orders
            $table->json('photo_urls'); // Массив ссылок на фото
            $table->text('description')->nullable(); // Описание (может быть пустым)
            $table->timestamps(); // Поля created_at и updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_reports');
    }
};
