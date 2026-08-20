<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('associate_payments')) {
            Schema::create('associate_payments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('subscriber_id')->comment('Owning subscriber (users.id)');
                $table->unsignedBigInteger('associate_invoice_id');
                $table->string('invoice_no', 100)->nullable();
                $table->unsignedBigInteger('associate_id');
                $table->unsignedBigInteger('client_id')->nullable();
                $table->string('client_name', 255)->nullable();
                $table->text('service_provided')->nullable();
                $table->decimal('fees', 12, 2)->default(0)->comment('Invoice fees at time of payment');
                $table->decimal('paying', 12, 2)->default(0)->comment('Amount paid in this record');
                $table->string('payment_mode', 100)->nullable();
                $table->date('payment_date')->nullable();
                $table->timestamps();

                $table->index('subscriber_id');
                $table->index('associate_invoice_id');
                $table->index('associate_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('associate_payments');
    }
};
