<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('payment_reminder_settings')) {
            return;
        }

        DB::table('payment_reminder_settings')
            ->where('email_to', 'associate_bcc_subscriber_alerts')
            ->update(['email_to' => 'associate_bcc_subscriber']);
    }

    public function down(): void
    {
        if (!Schema::hasTable('payment_reminder_settings')) {
            return;
        }

        DB::table('payment_reminder_settings')
            ->where('email_to', 'associate_bcc_subscriber')
            ->update(['email_to' => 'associate_bcc_subscriber_alerts']);
    }
};
