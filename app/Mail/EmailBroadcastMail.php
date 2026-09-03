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
        $footerMode = $this->subscriberFooter ? 'subscriber' : 'platform';
        $subscriberFooter = $this->subscriberFooter;
        $content = $this->content;
        $pageTitle = $this->emailSubject;
        $headerTitle = null;
        $headerLogoUrl = null;
        $headerLogoAlt = null;

        if ($this->subscriberFooter) {
            $headerLogoUrl = $this->subscriberFooter['logo_url'] ?? null;
            $headerLogoAlt = $this->subscriberFooter['organization'] ?? 'Logo';
        }

        $mail = $this->subject($this->emailSubject)
            ->from($this->senderEmail, $this->senderName)
            ->view(BrandedMail::LAYOUT, compact(
                'content',
                'headerTitle',
                'headerLogoUrl',
                'headerLogoAlt',
                'pageTitle',
                'footerMode',
                'subscriberFooter'
            ));

        return BrandedMail::applySubscriberReplyTo($mail, $this->senderEmail, $this->senderName);
    }
}
