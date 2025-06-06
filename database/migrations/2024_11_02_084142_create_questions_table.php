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
        Schema::create('questions', function (Blueprint $table) {
            $table->id(); // Создаёт поле id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
            $table->foreignId('survey_id') // Создаёт поле survey_id BIGINT UNSIGNED
                ->constrained('surveys')  // Указывает, что это внешний ключ, ссылающийся на таблицу surveys
                ->onDelete('cascade');      // Удаление вопроса при удалении связанного опроса
            $table->string('text');     // Поле для заголовка
            $table->integer('type_id')->default(1); // Поле type_id с дефолтным значением 1
            $table->timestamps();        // Поля created_at и updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
