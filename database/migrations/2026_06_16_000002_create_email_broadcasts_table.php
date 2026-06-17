<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_broadcasts', function (Blueprint $table) {
            $table->id();
            $table->string('broadcast_id', 10)->unique();
            $table->unsignedBigInteger('subscriber_id')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->string('sender_name');
            $table->string('sender_email');
            $table->string('communicate_type', 20);
            $table->string('subject');
            $table->text('body');
            $table->json('recipient_labels')->nullable();
            $table->unsignedInteger('sent_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_broadcasts');
    }
};
