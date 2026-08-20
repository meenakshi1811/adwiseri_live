<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('internal_communication_reads')) {
            return;
        }

        Schema::create('internal_communication_reads', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('communication_id');
            $table->timestamp('read_at');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['user_id', 'communication_id'], 'user_communication_read_unique');
            $table->index('communication_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('internal_communication_reads');
    }
};
