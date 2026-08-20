<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('associate_businesses')) {
            Schema::create('associate_businesses', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('subscriber_id')->comment('Owning subscriber (users.id)');
                $table->unsignedBigInteger('associate_id');
                $table->unsignedBigInteger('client_id')->nullable();
                $table->string('client_name', 255)->nullable();
                $table->text('service_provided')->nullable()->comment('Comma-separated services (legacy; prefer services column)');
                $table->decimal('fees', 12, 2)->default(0);
                $table->string('home_country', 100)->nullable();
                $table->string('visa_country', 100)->nullable();
                $table->string('application_type', 100)->nullable();
                $table->timestamps();

                $table->index('subscriber_id');
                $table->index('associate_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('associate_businesses');
    }
};
