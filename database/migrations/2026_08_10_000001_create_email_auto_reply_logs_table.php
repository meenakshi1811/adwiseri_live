<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmailAutoReplyLogsTable extends Migration
{
    public function up()
    {
        Schema::create('email_auto_reply_logs', function (Blueprint $table) {
            $table->id();
            $table->string('mailbox', 191);
            $table->string('sender_email', 191);
            $table->string('incoming_message_id', 255)->nullable();
            $table->timestamp('sent_at');
            $table->timestamps();

            $table->index(['mailbox', 'sender_email', 'sent_at'], 'email_auto_reply_logs_lookup');
        });
    }

    public function down()
    {
        Schema::dropIfExists('email_auto_reply_logs');
    }
}
