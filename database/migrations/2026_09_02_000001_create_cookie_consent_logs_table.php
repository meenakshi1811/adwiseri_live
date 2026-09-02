<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cookie_consent_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('subscriber_id')->nullable();
            $table->string('consent_action', 32);
            $table->string('page_url', 512)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 512)->nullable();
            $table->timestamp('accepted_at')->useCurrent();
            $table->timestamps();

            $table->index(['user_id', 'accepted_at']);
            $table->index(['subscriber_id', 'accepted_at']);
            $table->index(['ip_address', 'accepted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cookie_consent_logs');
    }
};
