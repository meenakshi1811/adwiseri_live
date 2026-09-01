<?php

namespace App\Mail;

use App\Support\BrandedMail;
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

        $mail = $this->subject($subject)
            ->from(BrandedMail::alertsFromAddress(), BrandedMail::alertsFromName($subscriberName))
            ->view(BrandedMail::LAYOUT, compact('content', 'headerTitle'))
            ->attachData($this->pdfContents, $this->fileName, [
                'mime' => 'application/pdf',
            ]);

        if ($subscriberEmail !== '') {
            BrandedMail::applySubscriberReplyTo($mail, $subscriberEmail, $subscriberName);
        } else {
            BrandedMail::applyDefaultReplyTo($mail);
        }

        return $mail;
    }
}
