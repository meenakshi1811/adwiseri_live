<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('email_broadcasts', function (Blueprint $table) {
            $table->string('status', 20)->default('queued')->after('body');
            $table->unsignedInteger('total_recipients')->default(0)->after('status');
            $table->json('recipient_payload')->nullable()->after('recipient_labels');
            $table->timestamp('queued_at')->nullable()->after('failed_count');
            $table->timestamp('started_at')->nullable()->after('queued_at');
            $table->timestamp('completed_at')->nullable()->after('started_at');
            $table->text('error_message')->nullable()->after('completed_at');
        });
    }

    public function down(): void
    {
        Schema::table('email_broadcasts', function (Blueprint $table) {
            $table->dropColumn([
                'status',
                'total_recipients',
                'recipient_payload',
                'queued_at',
                'started_at',
                'completed_at',
                'error_message',
            ]);
        });
    }
};
