<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription.webhook_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->enum('direction', ['incoming', 'outgoing']);
            $table->string('endpoint');
            $table->jsonb('request_body')->nullable();
            $table->jsonb('response_body')->nullable();
            $table->integer('status_code')->nullable();
            $table->timestampTz('processed_at')->nullable();
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription.webhook_logs');
    }
};

