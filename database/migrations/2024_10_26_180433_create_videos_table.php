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
        Schema::create('videos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Привязка к таблице users
            $table->string('title'); // Название видео
            $table->text('description')->nullable(); // Описание видео
            $table->string('url'); // Ссылка на видео
            $table->decimal('price', 10, 2)->nullable(); // Цена за видео
            $table->string('sizes')->nullable(); // Размеры видео (можно хранить в формате JSON, если несколько)
            $table->boolean('is_fixed')->default(false); // Фиксированное видео или нет
            $table->unsignedBigInteger('views_count')->default(0); // Счётчик просмотров
            $table->string('preview_url')->nullable(); // Ссылка на превью
            $table->boolean('is_visible')->default(true); // Видимость видео
            $table->unsignedBigInteger('tapped_count')->default(0); // Счётчик нажатий на видео
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('videos');
    }
};
