<?php

use App\Support\BrandedMail;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Refresh the default Welcome Email copy and signature spacing.
     *
     * Only the seeded default row (owner_user_id IS NULL) that still contains the
     * previous activation/details wording is updated, so admin-customised templates
     * are untouched.
     */
    public function up(): void
    {
        $welcomeSignature = '<p style="margin:16px 0 24px 0;">' . BrandedMail::emailSignatureHtml() . '</p>';

        $newBody = '<p style="margin-bottom:16px;line-height:1.9;"><strong>Hello {{name}},</strong></p>'
            . '<p style="margin-bottom:16px;line-height:1.9;">Welcome to adwiseri. Your registration is successful.</p>'
            . '<p style="margin-bottom:16px;line-height:1.9;"><strong>{{plan_name}}</strong> plan is activated on your account.</p>'
            . '<p style="margin-bottom:16px;line-height:1.9;">Subscription details are as follows:</p>'
            . '<p style="margin-bottom:16px;line-height:1.9;"><strong>Plan Name</strong> : {{plan_name}}<br>'
            . '<strong>Duration</strong> : {{duration}}<br>'
            . '<strong>Paid Amount</strong> : USD {{paid_amount}}</p>'
            . '<p style="margin-bottom:16px;line-height:1.9;">You can contact support team via email '
            . '<a href="mailto:care@adwiseri.com">care@adwiseri.com</a> or by raising ticket.</p>'
            . $welcomeSignature;

        DB::table('email_templates')
            ->where('template_key', 'welcome_email_admin_to_subscriber')
            ->whereNull('owner_user_id')
            ->where(function ($query) {
                $query->where('body', 'like', '%plan is activated successfully%')
                    ->orWhere('body', 'like', '%The plan details are as follows%')
                    ->orWhere('body', 'like', '%margin:16px 0 0 0;%Sincerely%');
            })
            ->update(['body' => $newBody]);
    }

    public function down(): void
    {
        // No-op: the previous copy is intentionally not restored.
    }
};
