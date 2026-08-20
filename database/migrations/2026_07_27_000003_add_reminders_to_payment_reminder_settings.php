<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('payment_reminder_settings')) {
            return;
        }

        if (!Schema::hasColumn('payment_reminder_settings', 'reminders_to')) {
            Schema::table('payment_reminder_settings', function (Blueprint $table) {
                $table->string('reminders_to', 20)->default('clients')->after('user_id');
            });
        }

        DB::table('payment_reminder_settings')
            ->whereIn('email_to', [
                'associate_only',
                'associate_bcc_subscriber',
                'associate_bcc_subscriber_alerts',
            ])
            ->update(['reminders_to' => 'associates']);
    }

    public function down(): void
    {
        if (!Schema::hasTable('payment_reminder_settings') || !Schema::hasColumn('payment_reminder_settings', 'reminders_to')) {
            return;
        }

        Schema::table('payment_reminder_settings', function (Blueprint $table) {
            $table->dropColumn('reminders_to');
        });
    }
};
