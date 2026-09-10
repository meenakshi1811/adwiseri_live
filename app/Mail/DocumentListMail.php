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
        $subscriberName = trim((string) ($this->data['subscriber_name'] ?? '')) ?: 'Subscriber';
        $subscriberEmail = trim((string) ($this->data['subscriber_email'] ?? ''));
        $headerTitle = 'Documents Checklist';
        $country = trim((string) ($this->data['country'] ?? ''));
        $category = trim((string) ($this->data['category'] ?? ''));
        $subject = 'Documents Checklist - ' . $country . ' - ' . $category;
        $content = BrandedMail::renderBody('emails.bodies.document_list', ['data' => $this->data]);

        $fromEmail = $subscriberEmail !== '' ? $subscriberEmail : BrandedMail::alertsFromAddress();

        $mail = $this->subject($subject)
            ->from($fromEmail, BrandedMail::alertsFromName($subscriberName))
            ->view(BrandedMail::LAYOUT, compact('content', 'headerTitle'));

        BrandedMail::applyDefaultReplyTo($mail);

        if ($subscriberEmail !== '') {
            $mail->bcc($subscriberEmail);
        }

        // Attach the PDF after envelope headers so MIME parts are not dropped by mail clients.
        return DocumentMailAttachment::attachChecklistPdf($mail, $this->pdfContents, $this->fileName);
    }
}
