<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private string $oldBody = '<p style="margin-bottom:16px;line-height:1.9;"><strong>Hello {{name}}</strong></p><p style="margin-bottom:16px;line-height:1.9;">Welcome to adwiseri. Your registration is successful.</p><p style="margin-bottom:16px;line-height:1.9;">Your <strong>Free Plan</strong> is activated successfully. The plan details are as follows:</p><p style="margin-bottom:16px;line-height:1.9;"><strong>Plan Name</strong> : {{plan_name}}<br><strong>Duration</strong> : {{duration}}<br><strong>Paid Amount</strong> : $0</p><p style="margin-bottom:16px;line-height:1.9;">You can always contact our support team via live chat or email.</p><p style="margin-bottom:16px;line-height:1.9;">Thanks,<br><strong>The Adwiseri Team</strong></p>';

    private string $newBody = '<p style="margin-bottom:16px;line-height:1.9;"><strong>Hello {{name}}</strong></p><p style="margin-bottom:16px;line-height:1.9;">Welcome to adwiseri. Your registration is successful.</p><p style="margin-bottom:16px;line-height:1.9;">{{plan_activation_line}}</p><p style="margin-bottom:16px;line-height:1.9;"><strong>Plan Name</strong> : {{plan_name}}<br><strong>Duration</strong> : {{duration}}<br><strong>Start Date</strong> : {{start_date}}<br><strong>End Date</strong> : {{end_date}}<br><strong>Paid Amount</strong> : {{currency_symbol}}{{paid_amount}}</p>{{invoice_link_section}}<p style="margin-bottom:16px;line-height:1.9;"><strong>Have a question?</strong></p><p style="margin-bottom:16px;line-height:1.9;">Check our <strong><a href="https://adwiseri.com/faqs">FAQ Page</a></strong> for a quick answer.</p><p style="margin-bottom:16px;line-height:1.9;">You can always contact our support team via email or by raising a ticket from the support section.</p><p style="margin-bottom:16px;line-height:1.9;">Thanks,<br><strong>The Adwiseri Team</strong></p>';

    public function up(): void
    {
        DB::table('email_templates')
            ->where('template_key', 'welcome_email_admin_to_subscriber')
            ->where(function ($query) {
                $query->whereNull('body')
                    ->orWhere('body', '')
                    ->orWhere('body', 'like', '%Your <strong>Free Plan</strong> is activated successfully%')
                    ->orWhere('body', 'like', '%<strong>Paid Amount</strong> : $0%');
            })
            ->update(['body' => $this->newBody]);
    }

    public function down(): void
    {
        DB::table('email_templates')
            ->where('template_key', 'welcome_email_admin_to_subscriber')
            ->where('body', $this->newBody)
            ->update(['body' => $this->oldBody]);
    }
};
