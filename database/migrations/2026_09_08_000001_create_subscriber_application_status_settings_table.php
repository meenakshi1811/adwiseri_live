<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('subscriber_application_status_settings')) {
            return;
        }

        Schema::create('subscriber_application_status_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subscriber_id');
            $table->string('visa_category')->nullable();
            $table->json('statuses');
            $table->json('end_date_required')->nullable();
            $table->timestamps();

            $table->foreign('subscriber_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['subscriber_id', 'visa_category'], 'subscriber_app_status_category_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriber_application_status_settings');
    }
};
