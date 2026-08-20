<?php

namespace App\Mail;

use App\Services\EmailTemplateService;
use App\Support\BrandedMail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DocumentReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public $subscriber,
        public array $payload
    ) {
    }

    public function build()
    {
        $template = app(EmailTemplateService::class)->getTemplateForUser($this->subscriber, 'subscriber', 'document_reminder');

        $defaultSubject = 'Documents Required - {{client_name}} ({{application_name}})';
        $defaultBody = '<p>Hello {{client_name}},</p>'
            . '<p>Kindly send below listed Documents which are needed to prepare your application.</p>'
            . '{{missing_documents_list}}'
            . '<p>Sincerely,<br>{{subscriber_name}}</p>';

        $subjectTemplate = $template?->subject ?: $defaultSubject;
        $bodyTemplate = $template?->body ?: $defaultBody;
        $resolvedPayload = $this->withDynamicAliases($this->payload);

        $subject = BrandedMail::replacePlaceholders($subjectTemplate, $resolvedPayload);
        $content = BrandedMail::replacePlaceholders($bodyTemplate, $resolvedPayload);

        $headerTitle = 'Documents Reminder';
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
        $missingDocuments = $data['missing_documents'] ?? [];
        if (!is_array($missingDocuments)) {
            $missingDocuments = array_filter(array_map('trim', explode("\n", (string) $missingDocuments)));
        }

        $listHtml = '<ul>';
        foreach ($missingDocuments as $document) {
            $document = trim((string) $document);
            if ($document === '') {
                continue;
            }
            $listHtml .= '<li>' . e($document) . '</li>';
        }
        $listHtml .= '</ul>';

        $clientName = $data['client_name'] ?? $data['client_first_name'] ?? '';

        return array_merge($data, [
            'client_name' => $clientName,
            'client_first_name' => $data['client_first_name'] ?? $clientName,
            'missing_documents_list' => $data['missing_documents_list'] ?? $listHtml,
            'missing_documents_text' => $data['missing_documents_text'] ?? implode("\n", $missingDocuments),
            'subscriber_name' => $data['subscriber_name'] ?? ($this->subscriber->name ?? ''),
        ]);
    }
}
