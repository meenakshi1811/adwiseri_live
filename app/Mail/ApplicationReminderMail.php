<?php

namespace App\Mail;

use App\Services\EmailTemplateService;
use App\Support\BrandedMail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ApplicationReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public $subscriber,
        public array $payload
    ) {
    }

    public function build()
    {
        $template = app(EmailTemplateService::class)->getTemplateForUser($this->subscriber, 'subscriber', 'application_reminder');

        $defaultSubject = 'Application Reminder - {{subject}} (Deadline: {{deadline}})';
        $defaultBody = '<p>Hello {{user_name}},</p>'
            . '<p>This is a reminder for the following application task:</p>'
            . '<p><strong>Client:</strong> {{client_name}}<br>'
            . '<strong>Application:</strong> {{application_name}}<br>'
            . '<strong>Subject:</strong> {{subject}}<br>'
            . '<strong>Deadline:</strong> {{deadline}}</p>'
            . '<p><strong>Description:</strong><br>{{description}}</p>'
            . '<p>Sincerely,<br>{{subscriber_name}}</p>';

        $subjectTemplate = $template?->subject ?: $defaultSubject;
        $bodyTemplate = $template?->body ?: $defaultBody;
        $resolvedPayload = $this->withDynamicAliases($this->payload);

        $subject = BrandedMail::replacePlaceholders($subjectTemplate, $resolvedPayload);
        $content = BrandedMail::replacePlaceholders($bodyTemplate, $resolvedPayload);

        $headerTitle = 'Application Reminder';
        $subscriberName = trim((string) ($this->subscriber->name ?? ''));
        $subscriberEmail = trim((string) ($this->subscriber->email ?? ''));

        $mail = $this->subject($subject)
            ->from(
                BrandedMail::alertsFromAddress(),
                BrandedMail::alertsFromName($subscriberName !== '' ? $subscriberName : 'Subscriber')
            )
            ->view(BrandedMail::LAYOUT, compact('content', 'headerTitle'));

        if ($subscriberEmail !== '') {
            BrandedMail::applySubscriberReplyTo($mail, $subscriberEmail, $subscriberName);
        } else {
            BrandedMail::applyDefaultReplyTo($mail);
        }

        return $mail;
    }

    private function withDynamicAliases(array $data): array
    {
        return array_merge($data, [
            'user_name' => $data['user_name'] ?? $data['recipient_name'] ?? '',
            'description' => nl2br(e((string) ($data['description'] ?? ''))),
            'subscriber_name' => $data['subscriber_name'] ?? ($this->subscriber->name ?? ''),
        ]);
    }
}
