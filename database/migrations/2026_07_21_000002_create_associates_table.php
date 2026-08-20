<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('associates')) {
            Schema::create('associates', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('added_by')->comment('Subscriber (users.id) who owns this associate');
                $table->string('associate_code', 50)->nullable();
                $table->string('name', 255);
                $table->string('email', 255);
                $table->string('phone', 50)->nullable();
                $table->string('organization', 255)->nullable();
                $table->string('country', 100)->nullable();
                $table->string('state', 100)->nullable();
                $table->string('city', 100)->nullable();
                $table->string('pincode', 20)->nullable();
                $table->string('home_country', 100)->nullable();
                $table->string('visa_country', 100)->nullable();
                $table->string('application_type', 100)->nullable();
                $table->string('currency', 30)->nullable();
                $table->string('status', 20)->default('true');
                $table->timestamps();

                $table->index('added_by');
                $table->index('email');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('associates');
    }
};
