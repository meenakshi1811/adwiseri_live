<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('client_accounts')) {
            return;
        }

        Schema::create('client_accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subscriber_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('application_id')->nullable();
            $table->enum('trans_type', ['Credit', 'Debit']);
            $table->decimal('amount', 12, 2);
            $table->string('description', 255);
            $table->decimal('prev_balance', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->date('transaction_date');
            $table->string('trans_by', 150)->nullable();
            $table->timestamps();

            $table->index(['subscriber_id', 'client_id', 'application_id'], 'client_accounts_scope_idx');
            $table->index(['subscriber_id', 'transaction_date'], 'client_accounts_date_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_accounts');
    }
};
