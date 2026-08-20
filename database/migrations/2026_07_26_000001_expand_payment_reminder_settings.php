<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('payment_reminder_settings')) {
            return;
        }

        DB::statement("ALTER TABLE payment_reminder_settings MODIFY client_group VARCHAR(30) NOT NULL DEFAULT 'all'");
        DB::statement("ALTER TABLE payment_reminder_settings MODIFY email_to VARCHAR(50) NOT NULL DEFAULT 'client_only'");
    }

    public function down(): void
    {
        if (!Schema::hasTable('payment_reminder_settings')) {
            return;
        }

        DB::statement("ALTER TABLE payment_reminder_settings MODIFY client_group ENUM('all','over_500','over_100') NOT NULL DEFAULT 'all'");
        DB::statement("ALTER TABLE payment_reminder_settings MODIFY email_to ENUM('client_only','client_bcc_subscriber') NOT NULL DEFAULT 'client_only'");
    }
};
