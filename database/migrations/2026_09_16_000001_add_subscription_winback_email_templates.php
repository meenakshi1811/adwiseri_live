<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('email_templates')) {
            return;
        }

        $now = now();
        $signature = '<p style="margin:16px 0 0 0;">Sincerely,<br><strong>Adwiseri</strong></p>';
        $renewButton = '<p><a href="{{renewalLink}}" class="email-cta" style="background:#695EEE;color:#ffffff;padding:12px 20px;border-radius:6px;text-decoration:none;display:inline-block;">Renew Now</a></p>';

        $templates = [
            [
                'template_key' => 'subscription_winback_reminder',
                'template_name' => 'Subscription Win-back Reminder',
                'subject' => 'Renew Your Subscription - Expired {{daysExpired}} Days Ago',
                'body' => '<p>Hello {{name}},</p><p>Your Adwiseri subscription expired <strong>{{daysExpired}} days ago</strong>.</p><p>You are still in the 30-day win-back period. Please renew within <strong>{{daysLeft}} days</strong> to keep your account active.</p>' . $renewButton . $signature,
            ],
            [
                'template_key' => 'subscription_winback_final_reminder',
                'template_name' => 'Subscription Win-back Final Reminder',
                'subject' => 'Final Reminder - Renew Today Before Wallet Credit Expires',
                'body' => '<p>Hello {{name}},</p><p>Your Adwiseri subscription expired <strong>30 days ago</strong>. This is your <strong>final reminder</strong>.</p><p>Today is the last day of your 30-day win-back period. After today, your account will lapse.</p><p>Your <strong>wallet credit of USD {{walletAmount}}</strong> will also expire if you do not renew today.</p><p>Please renew now to keep your subscription and wallet credit.</p>' . $renewButton . $signature,
            ],
        ];

        foreach ($templates as $template) {
            $existing = DB::table('email_templates')
                ->whereNull('owner_user_id')
                ->where('audience', 'subscriber')
                ->where('template_key', $template['template_key'])
                ->first();

            $payload = [
                'template_name' => $template['template_name'],
                'subject' => $template['subject'],
                'body' => $template['body'],
                'updated_at' => $now,
            ];

            if ($existing) {
                DB::table('email_templates')->where('id', $existing->id)->update($payload);
                continue;
            }

            DB::table('email_templates')->insert(array_merge($payload, [
                'owner_user_id' => null,
                'audience' => 'subscriber',
                'template_key' => $template['template_key'],
                'custom_name' => null,
                'created_at' => $now,
            ]));
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('email_templates')) {
            return;
        }

        DB::table('email_templates')
            ->whereNull('owner_user_id')
            ->where('audience', 'subscriber')
            ->whereIn('template_key', [
                'subscription_winback_reminder',
                'subscription_winback_final_reminder',
            ])
            ->delete();
    }
};
