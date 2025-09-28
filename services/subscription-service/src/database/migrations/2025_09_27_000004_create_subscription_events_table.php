<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription.subscription_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('subscription_id');
            $table->string('event_type');
            $table->jsonb('payload');
            $table->timestampTz('processed_at')->nullable();
            $table->uuid('correlation_id')->nullable();
            $table->uuid('event_id');
            $table->timestampsTz();

            $table->foreign('subscription_id')->references('id')->on('subscription.subscriptions')->onDelete('cascade');
            $table->unique('correlation_id');
            $table->unique('event_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription.subscription_events');
    }
};

