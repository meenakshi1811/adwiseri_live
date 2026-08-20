<?php

namespace App\Mail;

use App\Support\BrandedMail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmailBroadcastMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        protected string $emailSubject,
        protected string $content,
        protected string $senderEmail,
        protected string $senderName,
        protected ?array $subscriberFooter = null
    ) {
    }

    public function build()
    {
        $headerTitle = $this->emailSubject;
        $content = $this->content;
        $footerMode = $this->subscriberFooter ? 'subscriber' : 'platform';
        $subscriberFooter = $this->subscriberFooter;

        $mail = $this->subject($this->emailSubject)
            ->from($this->senderEmail, $this->senderName)
            ->view(BrandedMail::LAYOUT, compact('content', 'headerTitle', 'footerMode', 'subscriberFooter'));

        return BrandedMail::applySubscriberReplyTo($mail, $this->senderEmail, $this->senderName);
    }
}
