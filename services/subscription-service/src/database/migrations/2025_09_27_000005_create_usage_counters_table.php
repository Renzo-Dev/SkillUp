<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription.usage_counters', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('subscription_id');
            $table->string('feature_key');
            $table->timestampTz('period_start');
            $table->timestampTz('period_end');
            $table->integer('used_amount');
            $table->integer('limit_value');
            $table->timestampsTz();

            $table->foreign('subscription_id')->references('id')->on('subscription.subscriptions')->onDelete('cascade');
            $table->unique(['subscription_id', 'feature_key', 'period_start', 'period_end'], 'usage_counters_unique_period');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription.usage_counters');
    }
};

