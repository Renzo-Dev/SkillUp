<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Миграция под структуру User.php
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id(); // PK
            $table->string('name'); // имя пользователя
            $table->string('email')->unique(); // email, уникальный
            $table->timestamp('email_verified_at')->nullable(); // подтверждение email
            $table->string('password'); // пароль
            $table->boolean('is_active')->default(true); // активен ли пользователь
            $table->timestamp('last_login_at')->nullable(); // последнее время входа
            $table->timestamps(); // created_at, updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
