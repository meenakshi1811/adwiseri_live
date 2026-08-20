<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('subscriber_cc_settings')) {
            return;
        }

        Schema::create('subscriber_cc_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subscriber_id')->unique();
            $table->json('countries')->nullable();
            $table->json('visa_categories')->nullable();
            $table->json('document_lists')->nullable();
            $table->timestamps();

            $table->foreign('subscriber_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriber_cc_settings');
    }
};
