<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription.plan_features', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('plan_id');
            $table->string('feature_key');
            $table->integer('limit_value')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->timestampsTz();

            $table->foreign('plan_id')->references('id')->on('subscription.plans')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription.plan_features');
    }
};

