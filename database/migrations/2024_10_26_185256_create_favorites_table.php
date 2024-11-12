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
        Schema::create('favorites', function (Blueprint $table) {
            $table->id(); // Уникальный идентификатор
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Привязка к таблице users
            $table->foreignId('video_id')->constrained('videos')->onDelete('cascade'); // Привязка к таблице videos
            $table->timestamps(); // Поля created_at и updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('favorites');
    }
};
