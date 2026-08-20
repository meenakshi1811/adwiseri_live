<?php

namespace App\Mail;

use App\Support\BrandedMail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

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
        $headerTitle = $isClientCareLetter ? 'Client Care Letter' : 'Service Agreement';
        $content = BrandedMail::renderBody('emails.bodies.client_care_letter', ['data' => $this->data]);

        $mail = $this->subject($subject)
            ->from(BrandedMail::alertsFromAddress(), BrandedMail::alertsFromName($subscriberName))
            ->view(BrandedMail::LAYOUT, compact('content', 'headerTitle'))
            ->attach($this->attachmentPath, [
                'as' => str_replace(' ', '-', $this->data['document_title']) . '.pdf',
                'mime' => 'application/pdf',
            ]);

        if (!empty($subscriberEmail)) {
            BrandedMail::applySubscriberReplyTo($mail, $subscriberEmail, $subscriberName);
            $mail->cc($subscriberEmail);
        } else {
            BrandedMail::applyDefaultReplyTo($mail);
        }

        return $mail;
    }
}
