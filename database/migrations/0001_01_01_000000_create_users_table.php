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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            // $table->timestamps();

            $table->string('phone')->unique(); // Номер телефона
            $table->string('username')->unique(); // Ник пользователя
            $table->string('city_id'); // Город
            // $table->string('role')->default('user'); // Роль: user, master, admin
            $table->string('firstname'); // ФИО мастера
            $table->string('lastname'); // ФИО мастера
            $table->string('iin',12)->unique(); // ИИН мастера
            // $table->string('store_name')->nullable(); // Название магазина (для мастеров)
            // $table->string('company_type_id')->nullable(); // ИП или ТОО
            // $table->decimal('balance', 8, 2)->default(0); // Баланс
            $table->boolean('is_visible')->default(1)->nullable(); // ИИН мастера
            $table->string('photo_url')->nullable(); // ФИО мастера

            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
