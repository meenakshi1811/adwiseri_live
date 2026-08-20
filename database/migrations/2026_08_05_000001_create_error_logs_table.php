<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateErrorLogsTable extends Migration
{
    public function up()
    {
        Schema::create('error_logs', function (Blueprint $table) {
            $table->id();
            $table->string('error_type', 191)->index();
            $table->string('page_screen', 512)->nullable()->index();
            $table->text('message');
            $table->unsignedSmallInteger('status_code')->nullable();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->longText('stack_trace')->nullable();
            $table->timestamps();

            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('error_logs');
    }
}
