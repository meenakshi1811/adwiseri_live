<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * adwiseri's own emails (newsletter sign-up, subscription expiry / win-back / termination, wallet
 * alerts, automatic report emails) become admin templates: listed and edited under Admin > Settings > Email Templates, no
 * longer under the subscriber's templates. Code already treats these keys as adwiseri's (see
 * EmailTemplateService::PLATFORM_TEMPLATE_KEYS); this only relabels the default rows.
 */
return new class extends Migration
{
    private const KEYS = [
        'newsletter',
        'subscription_expiry_reminder',
        'subscription_winback_reminder',
        'subscription_winback_final_reminder',
        'subscription_termination',
        'wallet_credit_alert',
        'wallet_debit_alert',
        'reports',
    ];

    public function up(): void
    {
        if (!Schema::hasTable('email_templates')) {
            return;
        }

        DB::table('email_templates')
            ->whereNull('owner_user_id')
            ->whereIn('template_key', self::KEYS)
            ->update(['audience' => 'admin', 'updated_at' => now()]);
    }

    public function down(): void
    {
        if (!Schema::hasTable('email_templates')) {
            return;
        }

        DB::table('email_templates')
            ->whereNull('owner_user_id')
            ->whereIn('template_key', self::KEYS)
            ->update(['audience' => 'subscriber', 'updated_at' => now()]);
    }
};
