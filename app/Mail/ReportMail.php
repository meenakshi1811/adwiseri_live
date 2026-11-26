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
            // The Reports template says "Your {{consultancy_name}}'s Report(s) are ready"; this email
            // never supplied it, so the placeholder went out as literal text.
            if (!$owner && !empty($payload['id'])) {
                $owner = \App\Models\User::find($payload['id']);
            }
            if (empty($payload['consultancy_name'])) {
                $payload['consultancy_name'] = trim((string) ($owner->organization ?? '')) ?: ($payload['name'] ?? 'your consultancy');
            }
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
