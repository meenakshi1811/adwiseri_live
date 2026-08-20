<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('associate_invoices')) {
            Schema::create('associate_invoices', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('subscriber_id')->comment('Owning subscriber (users.id)');
                $table->string('invoice_no', 100)->nullable();
                $table->unsignedBigInteger('associate_id');
                $table->unsignedBigInteger('client_id')->nullable();
                $table->string('client_name', 255)->nullable();
                $table->text('service_provided')->nullable()->comment('Comma-separated services (legacy; prefer services column)');
                $table->decimal('fees', 12, 2)->default(0);
                // Paid | UnPaid | PartiallyPaid | Cancelled  (mirrors internal_invoices)
                $table->string('status', 20)->default('UnPaid');
                $table->date('due_date')->nullable();
                $table->decimal('paid', 12, 2)->default(0);
                $table->timestamps();

                $table->index('subscriber_id');
                $table->index('associate_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('associate_invoices');
    }
};
