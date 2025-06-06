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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Привязка к таблице users
            $table->decimal('amount', 10, 2); // Сумма платежа
            $table->foreignId('status_id')->constrained('payment_statuses')->onDelete('cascade'); // Привязка к payment_statuses
            $table->string('check_url'); // Сумма платежа
            $table->text('description')->nullable(); // Сумма платежа

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
