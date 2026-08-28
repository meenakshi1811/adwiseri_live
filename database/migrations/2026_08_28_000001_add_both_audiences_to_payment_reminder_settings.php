<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('payment_reminder_settings')) {
            return;
        }

        if (!Schema::hasColumn('payment_reminder_settings', 'email_to_associates')) {
            Schema::table('payment_reminder_settings', function (Blueprint $table) {
                $table->string('email_to_associates', 50)->nullable()->after('email_to');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('payment_reminder_settings') || !Schema::hasColumn('payment_reminder_settings', 'email_to_associates')) {
            return;
        }

        Schema::table('payment_reminder_settings', function (Blueprint $table) {
            $table->dropColumn('email_to_associates');
        });
    }
};
