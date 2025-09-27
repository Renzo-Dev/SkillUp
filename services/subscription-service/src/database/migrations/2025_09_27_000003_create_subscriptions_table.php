<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('subscription.subscriptions', function (Blueprint $table) {
      $table->uuid('id')->primary();
      $table->uuid('user_id');
      $table->uuid('plan_id');
      $table->enum('status', ['trial', 'active', 'grace', 'cancelled', 'expired']);
      $table->timestampTz('started_at')->nullable();
      $table->timestampTz('expires_at')->nullable();
      $table->timestampTz('trial_ends_at')->nullable();
      $table->timestampTz('cancelled_at')->nullable();
      $table->string('cancellation_reason')->nullable();
      $table->boolean('auto_renew')->default(true);
      $table->uuid('last_payment_id')->nullable();
      $table->enum('source', ['payment_service', 'manual', 'admin'])->default('payment_service');
      $table->timestampsTz();

      $table->foreign('plan_id')->references('id')->on('subscription.plans');
      $table->index(['user_id']);
    });

    DB::statement("CREATE UNIQUE INDEX subscriptions_user_active_unique ON subscription.subscriptions (user_id) WHERE status IN ('trial','active','grace')");
  }

  public function down(): void
  {
    DB::statement('DROP INDEX IF EXISTS subscriptions_user_active_unique');
    Schema::dropIfExists('subscription.subscriptions');
  }
};

