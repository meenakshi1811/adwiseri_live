<?php

namespace App\Mail;

use App\Support\BrandedMail;
use App\Support\DocumentMailAttachment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DocumentListMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public array $data,
        protected string $pdfContents,
        protected string $fileName
    ) {
    }

    public function build()
    {
        $subscriberFooter = is_array($this->data['subscriber_footer'] ?? null)
            ? $this->data['subscriber_footer']
            : null;

        $subscriberName = trim((string) ($subscriberFooter['name'] ?? $this->data['subscriber_name'] ?? ''));
        if ($subscriberName === '') {
            $subscriberName = 'Subscriber';
        }

        $subscriberEmail = trim((string) ($subscriberFooter['email'] ?? $this->data['subscriber_email'] ?? ''));

        $country = trim((string) ($this->data['country'] ?? ''));
        $category = trim((string) ($this->data['category'] ?? ''));
        $subject = 'Documents Checklist - ' . $country . ' - ' . $category;
        $content = BrandedMail::renderBody('emails.bodies.document_list', ['data' => $this->data]);

        $footerMode = $subscriberFooter ? 'subscriber' : 'platform';
        $headerTitle = $subscriberFooter ? null : 'Documents Checklist';
        $pageTitle = $subject;
        $headerLogoUrl = $subscriberFooter['logo_url'] ?? null;
        $headerLogoAlt = $subscriberFooter['organization'] ?? $subscriberName;

        $mail = $this->subject($subject)
            ->from(BrandedMail::alertsFromAddress(), BrandedMail::alertsFromName($subscriberName))
            ->view(BrandedMail::LAYOUT, compact(
                'content',
                'headerTitle',
                'headerLogoUrl',
                'headerLogoAlt',
                'pageTitle',
                'footerMode',
                'subscriberFooter'
            ));

        if ($subscriberEmail !== '') {
            BrandedMail::applyReplyToEmailOnly($mail, $subscriberEmail);
            $mail->bcc($subscriberEmail);
        } else {
            BrandedMail::applyDefaultReplyTo($mail);
        }

        return DocumentMailAttachment::attachChecklistPdf($mail, $this->pdfContents, $this->fileName);
    }
}
