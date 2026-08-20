<?php

namespace App\Mail;

use App\Services\EmailTemplateService;
use App\Support\BrandedMail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function build()
    {
        $data = $this->data;
        $owner = app(EmailTemplateService::class)->resolveTemplateOwner($data);
        $template = app(EmailTemplateService::class)->getTemplateForUser($owner, 'subscriber', 'reports');

        $defaultSubject = 'Adwiseri Monthly Reports';
        $headerTitle = 'Monthly Reports';

        if ($template && !empty(trim((string) $template->body))) {
            $payload = BrandedMail::dataFromObject($data);
            $content = BrandedMail::replacePlaceholders($template->body, $payload);
            $subject = BrandedMail::replacePlaceholders($template->subject ?: $defaultSubject, $payload);
        } else {
            $content = BrandedMail::renderBody('emails.bodies.report_monthly', compact('data'));
            $subject = $defaultSubject;
        }

        return BrandedMail::applyPlatformEnvelope(
            $this->subject($subject)->view(BrandedMail::LAYOUT, compact('content', 'headerTitle'))
        );
    }
}
