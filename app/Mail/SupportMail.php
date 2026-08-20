<?php

namespace App\Mail;

use App\Services\EmailTemplateService;
use App\Support\BrandedMail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SupportMail extends Mailable
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
        $owner = $templateService->resolveTemplateOwner($data);
        $template = $templateService->getTemplateForUser($owner, 'admin', 'support_ticket_notification_email');

        $defaultSubject = 'New Support Ticket Raised (' . ($data->ticket_id ?? '') . ')';
        $headerTitle = 'Support Ticket Notification';
        $placeholderData = BrandedMail::dataFromObject($data);

        if ($template && !empty(trim((string) $template->body))) {
            $content = BrandedMail::replacePlaceholders($template->body, $placeholderData);
            $subject = BrandedMail::replacePlaceholders($template->subject ?: $defaultSubject, $placeholderData);
        } else {
            $content = BrandedMail::renderBody('emails.bodies.support_fallback', compact('data'));
            $subject = $defaultSubject;
        }

        $mail = BrandedMail::applyPlatformEnvelope(
            $this->subject($subject)->view(BrandedMail::LAYOUT, compact('content', 'headerTitle'))
        );

        if (!empty($data->attachment)) {
            $mail->attach('web_assets/users/ticket_images/' . $data->attachment);
        }

        return $mail;
    }
}
