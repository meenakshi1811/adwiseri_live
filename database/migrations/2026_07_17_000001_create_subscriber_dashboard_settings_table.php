<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('subscriber_dashboard_settings')) {
            return;
        }

        Schema::create('subscriber_dashboard_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subscriber_id')->unique();
            $table->json('headers')->nullable();
            $table->json('charts')->nullable();
            $table->timestamps();

            $table->foreign('subscriber_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriber_dashboard_settings');
    }
};
