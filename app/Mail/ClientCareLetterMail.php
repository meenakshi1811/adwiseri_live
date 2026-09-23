<?php

namespace App\Mail;

use App\Support\BrandedMail;
use App\Support\DocumentMailAttachment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use RuntimeException;

class ClientCareLetterMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public array $data,
        protected string $attachmentPath
    ) {
    }

    public function build()
    {
        $subscriberName = $this->data['subscriber']->name ?? 'Subscriber';
        $subscriberEmail = $this->data['subscriber']->email ?? null;
        $isClientCareLetter = ($this->data['letter_type'] ?? null) === 'oisc_iaa';
        $subject = $isClientCareLetter ? 'Client Care Letter' : 'Service Agreement';
        $headerTitle = $subject;
        $content = BrandedMail::ensureResponsiveEmailHtml(
            BrandedMail::renderBody('emails.bodies.client_care_letter', ['data' => $this->data])
        );

        $attachmentName = str_replace(' ', '-', (string) ($this->data['document_title'] ?? 'Document')) . '.pdf';
        $pdfContents = is_readable($this->attachmentPath) ? file_get_contents($this->attachmentPath) : false;
        if (!is_string($pdfContents) || strlen($pdfContents) < 100 || substr($pdfContents, 0, 4) !== '%PDF') {
            throw new RuntimeException('Client care / service agreement PDF attachment is missing or invalid.');
        }

        $mail = $this->subject($subject)
            ->from(BrandedMail::alertsFromAddress(), BrandedMail::alertsFromName($subscriberName))
            ->view(BrandedMail::LAYOUT, compact('content', 'headerTitle'));

        DocumentMailAttachment::attachChecklistPdf($mail, $pdfContents, $attachmentName);

        if (!empty($subscriberEmail)) {
            BrandedMail::applySubscriberReplyTo($mail, $subscriberEmail, $subscriberName);
            $mail->cc($subscriberEmail);
        } else {
            BrandedMail::applyDefaultReplyTo($mail);
        }

        return $mail;
    }
}
