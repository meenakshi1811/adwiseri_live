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
        protected ?array $subscriberFooter = null,
        protected bool $bccSubscriber = false,
        protected ?string $fallbackSenderEmail = null,
        protected ?string $fallbackSenderName = null
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

        if ($this->subscriberFooter) {
            $organization = trim((string) ($this->subscriberFooter['organization'] ?? 'Subscriber'));
            $replyEmail = trim((string) ($this->subscriberFooter['email'] ?? ''));

            $mail = $this->subject($this->emailSubject)
                ->from(BrandedMail::alertsFromAddress(), $organization)
                ->view(BrandedMail::LAYOUT, compact(
                    'content',
                    'headerTitle',
                    'headerLogoUrl',
                    'headerLogoAlt',
                    'pageTitle',
                    'footerMode',
                    'subscriberFooter'
                ));

            BrandedMail::applyReplyToEmailOnly($mail, $replyEmail);

            if ($this->bccSubscriber && $replyEmail !== '') {
                $mail->bcc($replyEmail);
            }

            return $mail;
        }

        $mail = $this->subject($this->emailSubject)
            ->from(
                $this->fallbackSenderEmail ?: BrandedMail::alertsFromAddress(),
                $this->fallbackSenderName ?: 'Adwiseri'
            )
            ->view(BrandedMail::LAYOUT, compact(
                'content',
                'headerTitle',
                'headerLogoUrl',
                'headerLogoAlt',
                'pageTitle',
                'footerMode',
                'subscriberFooter'
            ));

        return BrandedMail::applySubscriberReplyTo(
            $mail,
            $this->fallbackSenderEmail,
            $this->fallbackSenderName
        );
    }
}
