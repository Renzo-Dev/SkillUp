<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('subscription.plans', function (Blueprint $table) {
      $table->uuid('id')->primary();
      $table->string('code')->unique();
      $table->string('name');
      $table->text('description')->nullable();
      $table->integer('price_cents');
      $table->char('currency', 3)->default('USD');
      $table->enum('billing_cycle', ['monthly', 'yearly', 'lifetime']);
      $table->integer('trial_period_days')->nullable();
      $table->boolean('is_active')->default(true);
      $table->timestampsTz();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('subscription.plans');
  }
};

