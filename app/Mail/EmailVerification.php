<?php

namespace App\Mail;

use App\Services\EmailTemplateService;
use App\Support\BrandedMail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmailVerification extends Mailable
{
    use Queueable, SerializesModels;

    protected $data;

    public function __construct($maildata)
    {
        $this->data = $maildata;
    }

    public function build()
    {
        $data = $this->data;
        $templateService = app(EmailTemplateService::class);

        $templateKey = 'otp_email';
        $defaultSubject = 'Adwiseri Email Verification';
        $headerTitle = 'Email Verification';

        if (isset($data->password)) {
            $templateKey = 'forgot_password_email';
            $defaultSubject = 'Adwiseri Password Recovery OTP';
            $headerTitle = 'Password Recovery';
        } elseif (isset($data->message)) {
            $templateKey = 'contact_us_notification_email';
            $defaultSubject = 'New Message from adwiseri.com (Contact Us)';
            $headerTitle = 'Contact Us Message';
        } elseif (isset($data->how_did_hear)) {
            $templateKey = 'demo_request_notification_email';
            $defaultSubject = 'Demo Request from adwiseri.com';
            $headerTitle = 'Demo Request';
        }

        $owner = $templateService->resolveTemplateOwner($data);
        $template = $templateService->getTemplateForUser($owner, 'admin', $templateKey);
        $placeholderData = $this->placeholderData($data);

        if ($template && !empty(trim((string) $template->body))) {
            $content = BrandedMail::replacePlaceholders($template->body, $placeholderData);
            $subject = BrandedMail::replacePlaceholders($template->subject ?: $defaultSubject, $placeholderData);
        } else {
            $content = BrandedMail::renderBody('emails.bodies.verification_fallback', compact('data'));
            $subject = $defaultSubject;
        }

        $mail = $this->subject($subject)
            ->view(BrandedMail::LAYOUT, compact('content', 'headerTitle'));

        // Sender: alerts@adwiseri.com | Reply-To: care@adwiseri.com
        return BrandedMail::applyPlatformEnvelope($mail);
    }

    private function placeholderData($data): array
    {
        $map = BrandedMail::dataFromObject($data);

        if (isset($map['phone']) && !isset($map['contact_no'])) {
            $map['contact_no'] = $map['phone'];
        }

        if (isset($map['country']) && !isset($map['country_name'])) {
            $map['country_name'] = $map['country'];
        }

        if (isset($map['message']) && !isset($map['query'])) {
            $map['query'] = $map['message'];
        }

        return $map;
    }
}
