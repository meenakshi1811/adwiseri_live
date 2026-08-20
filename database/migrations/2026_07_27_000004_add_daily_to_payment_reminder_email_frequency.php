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

        DB::statement("ALTER TABLE payment_reminder_settings MODIFY email_frequency ENUM('daily', 'weekly', 'monthly', 'quarterly') NOT NULL DEFAULT 'weekly'");
    }

    public function down(): void
    {
        if (!Schema::hasTable('payment_reminder_settings')) {
            return;
        }

        DB::statement("ALTER TABLE payment_reminder_settings MODIFY email_frequency ENUM('weekly', 'monthly', 'quarterly') NOT NULL DEFAULT 'weekly'");
    }
};
