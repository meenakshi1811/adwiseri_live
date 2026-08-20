<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use App\Support\BrandedMail;
use Illuminate\Database\Seeder;

class EmailTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $signature = '<p style="margin:16px 0 0 0;">' . BrandedMail::emailSignatureHtml() . '</p>';
        $welcomeSignature = '<p style="margin:16px 0 24px 0;">' . BrandedMail::emailSignatureHtml() . '</p>';

        $templates = [
            ['audience' => 'admin', 'template_key' => 'otp_email', 'template_name' => 'OTP Email', 'subject' => 'adwiseri OTP', 'body' => '<p><strong>Hello {{name}}</strong></p><p>Your OTP is <strong>{{otp}}</strong>.</p>' . $signature],
            ['audience' => 'admin', 'template_key' => 'forgot_password_email', 'template_name' => 'Forgot Password Email', 'subject' => 'adwiseri Password Recovery OTP', 'body' => '<p><strong>Hello {{name}}</strong></p><p>Your password recovery OTP is <strong>{{otp}}</strong>.</p>' . $signature],
            ['audience' => 'admin', 'template_key' => 'welcome_email_admin_to_subscriber', 'template_name' => 'Welcome Email (Admin to Subscriber version)', 'subject' => 'Welcome to adwiseri', 'body' => '<p style="margin-bottom:16px;line-height:1.9;"><strong>Hello {{name}},</strong></p><p style="margin-bottom:16px;line-height:1.9;">Welcome to adwiseri. Your registration is successful.</p><p style="margin-bottom:16px;line-height:1.9;"><strong>{{plan_name}}</strong> plan is activated on your account.</p><p style="margin-bottom:16px;line-height:1.9;">Subscription details are as follows:</p><p style="margin-bottom:16px;line-height:1.9;"><strong>Plan Name</strong> : {{plan_name}}<br><strong>Duration</strong> : {{duration}}<br><strong>Paid Amount</strong> : USD {{paid_amount}}</p><p style="margin-bottom:16px;line-height:1.9;">You can contact support team via email <a href="mailto:care@adwiseri.com">care@adwiseri.com</a> or by raising ticket.</p>' . $welcomeSignature],
            ['audience' => 'admin', 'template_key' => 'demo_request_notification_email', 'template_name' => 'Demo Request Email Notification Email', 'subject' => 'Demo Request from adwiseri.com', 'body' => '<p>A demo request was submitted on adwiseri.com.</p>' . $signature],
            ['audience' => 'admin', 'template_key' => 'contact_us_notification_email', 'template_name' => 'Contact Us Form Data Notification Email', 'subject' => 'New Contact Us Submission from {{name}}', 'body' => '<p style="margin-bottom:12px;">A new Contact Us message has been submitted.</p><p style="margin-bottom:6px;"><strong>Name:</strong> {{name}}</p><p style="margin-bottom:6px;"><strong>Email:</strong> {{email}}</p><p style="margin-bottom:6px;"><strong>Phone:</strong> {{phone}}</p><p style="margin-bottom:6px;"><strong>Country:</strong> {{country}}</p><p style="margin-bottom:12px;"><strong>City:</strong> {{city}}</p><p style="margin-bottom:0;"><strong>Message:</strong><br>{{message}}</p>' . $signature],
            ['audience' => 'admin', 'template_key' => 'support_ticket_notification_email', 'template_name' => 'Support Ticket Notification Email', 'subject' => 'New Support Ticket Raised ({{ticket_id}})', 'body' => '<p>A new support ticket has been raised.</p><p>Ticket Raiser: {{ticket_raiser}}</p><p>Ticket ID: {{ticket_id}}</p><p>Department: {{department}}</p><p>Issue: {{issue}}</p>' . $signature],
            ['audience' => 'subscriber', 'template_key' => 'newsletter', 'template_name' => 'Newsletter', 'subject' => 'Newsletter', 'body' => '<p>Hello {{name}},</p><p>{{message}}</p>' . $signature],
            ['audience' => 'subscriber', 'template_key' => 'payment_reminder', 'template_name' => 'Payment Reminder', 'subject' => 'Outstanding Payment Reminder - {{client_name}} (Invoice {{invoice_no}})', 'body' => '<p>Dear {{client_name}},</p><p>This is a friendly reminder for outstanding payment for the invoice {{invoice_id}}.</p><p><strong>Application/Service :</strong> {{application_service}}<br><strong>Outstanding Amount :</strong> {{currency_symbol}} {{outstanding_amount}}<br><strong>Due Date :</strong> {{due_date}}</p>{{payment_link_section}}<p>Please clear the outstanding amount to avoid delays in service and/or late payment charges.</p><p>Sincerely,<br>{{subscriber_name}}</p>'],
            ['audience' => 'subscriber', 'template_key' => 'document_reminder', 'template_name' => 'Documents Reminder', 'subject' => 'Documents Required - {{client_name}} ({{application_name}})', 'body' => '<p>Hello {{client_name}},</p><p>Kindly send below listed Documents which are needed to prepare your application.</p>{{missing_documents_list}}<p>Sincerely,<br>{{subscriber_name}}</p>'],
            ['audience' => 'subscriber', 'template_key' => 'application_reminder', 'template_name' => 'Application Reminder', 'subject' => 'Application Reminder - {{subject}} (Deadline: {{deadline}})', 'body' => '<p>Hello {{user_name}},</p><p>This is a reminder for the following application task:</p><p><strong>Client:</strong> {{client_name}}<br><strong>Application:</strong> {{application_name}}<br><strong>Subject:</strong> {{subject}}<br><strong>Deadline:</strong> {{deadline}}</p><p><strong>Description:</strong><br>{{description}}</p><p>Sincerely,<br>{{subscriber_name}}</p>'],
            ['audience' => 'subscriber', 'template_key' => 'subscription_expiry_reminder', 'template_name' => 'Subscription Expiry Reminder', 'subject' => 'Subscription Expiry Reminder', 'body' => '<p>Hello {{name}},</p><p>Your subscription expires in {{daysRemaining}} days.</p>' . $signature],
            ['audience' => 'subscriber', 'template_key' => 'subscription_termination', 'template_name' => 'Subscription Termination', 'subject' => 'Subscription Termination', 'body' => '<p>Hello {{name}},</p><p>Your subscription has been terminated.</p>' . $signature],
            ['audience' => 'subscriber', 'template_key' => 'wallet_credit_alert', 'template_name' => 'Wallet Credit Alert', 'subject' => 'Congratulations! You have been rewarded', 'body' => '<p><strong>Dear {{name}},</strong></p><p>Congratulations! You have been rewarded with <strong>{{offer_label}}</strong> on your Adwiseri subscription account.</p><p><strong>Offer:</strong> {{offer_label}}<br><strong>Details:</strong> {{offer_description}}</p>' . $signature],
            ['audience' => 'subscriber', 'template_key' => 'wallet_debit_alert', 'template_name' => 'Wallet Debit Alert', 'subject' => 'Wallet Debit Alert', 'body' => '<p><strong>Dear {{name}},</strong></p><p>Your Adwiseri wallet was debited.</p><p><strong>Amount:</strong> USD {{credit_amount}}<br><strong>Description:</strong> {{description}}</p>' . $signature],
            ['audience' => 'subscriber', 'template_key' => 'reports', 'template_name' => 'Reports', 'subject' => 'Adwiseri Reports', 'body' => '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="width:100%;max-width:100%;"><tr><td style="padding:0;word-wrap:break-word;overflow-wrap:break-word;word-break:break-word;"><p style="margin:0 0 14px 0;font-size:14px;line-height:1.7;color:#1f2937;"><strong>Hello {{name}},</strong></p></td></tr><tr><td style="padding:0;word-wrap:break-word;overflow-wrap:break-word;word-break:break-word;"><p style="margin:0 0 14px 0;font-size:14px;line-height:1.7;color:#1f2937;">Your {{consultancy_name}}\'s Report(s) are ready.</p><p style="margin:0 0 14px 0;font-size:14px;line-height:1.7;color:#1f2937;">Please find attached file or download via link given in this email.</p></td></tr><tr><td style="padding:0;">' . $signature . '</td></tr></table>'],
            ['audience' => 'subscriber', 'template_key' => 'other', 'template_name' => 'Other', 'subject' => 'Notification', 'body' => '<p>Hello {{name}},</p><p>{{message}}</p>' . $signature],
        ];

        foreach ($templates as $template) {
            EmailTemplate::updateOrCreate(
                [
                    'owner_user_id' => null,
                    'audience' => $template['audience'],
                    'template_key' => $template['template_key'],
                ],
                $template
            );
        }
    }
}
