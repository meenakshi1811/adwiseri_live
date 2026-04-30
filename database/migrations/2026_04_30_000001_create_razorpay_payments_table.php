<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('razorpay_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('dr_id');
            $table->unsignedBigInteger('appointment_id');
            $table->string('razorpay_payment_id');
            $table->string('razorpay_order_id')->nullable();
            $table->string('razorpay_signature')->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 10)->default('INR');
            $table->string('status', 30)->default('captured');
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('contact', 30)->nullable();
            $table->json('notes')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['patient_id', 'dr_id', 'appointment_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('razorpay_payments');
    }
};
