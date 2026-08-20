<?php

use App\Support\BrandedMail;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Fix the default "Welcome Email" template so it shows the actual plan name
     * and paid amount instead of the hard-coded "Free Plan" / "$0" text.
     *
     * Only the seeded default row (owner_user_id IS NULL) that still contains the
     * old hard-coded markup is updated, so admin-customised templates are untouched.
     */
    public function up(): void
    {
        $signature = '<p style="margin:16px 0 0 0;">' . BrandedMail::emailSignatureHtml() . '</p>';

        $newBody = '<p style="margin-bottom:16px;line-height:1.9;"><strong>Hello {{name}},</strong></p>'
            . '<p style="margin-bottom:16px;line-height:1.9;">Welcome to adwiseri. Your registration is successful.</p>'
            . '<p style="margin-bottom:16px;line-height:1.9;">Your <strong>{{plan_name}}</strong> plan is activated successfully. The plan details are as follows:</p>'
            . '<p style="margin-bottom:16px;line-height:1.9;"><strong>Plan Name</strong> : {{plan_name}}<br>'
            . '<strong>Duration</strong> : {{duration}}<br>'
            . '<strong>Paid Amount</strong> : USD {{paid_amount}}</p>'
            . '<p style="margin-bottom:16px;line-height:1.9;">You can contact support team via email '
            . '<a href="mailto:care@adwiseri.com">care@adwiseri.com</a> or by raising ticket.</p>'
            . $signature;

        DB::table('email_templates')
            ->where('template_key', 'welcome_email_admin_to_subscriber')
            ->whereNull('owner_user_id')
            ->where(function ($query) {
                $query->where('body', 'like', '%Free Plan%')
                    ->orWhere('body', 'like', '%Paid Amount</strong> : $0%')
                    ->orWhere('body', 'like', '%live chat or email%')
                    ->orWhere('body', 'like', '%Hello {{name}}</strong>%');
            })
            ->update(['body' => $newBody]);
    }

    public function down(): void
    {
        // No-op: the previous hard-coded copy is intentionally not restored.
    }
};
