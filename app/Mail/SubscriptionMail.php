<?php

namespace App\Mail;

use App\Services\EmailTemplateService;
use App\Support\BrandedMail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SubscriptionMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public ?string $email = null)
    {
    }

    public function build()
    {
        $template = app(EmailTemplateService::class)->getTemplateForUser(null, 'subscriber', 'newsletter');
        $defaultSubject = 'Adwiseri - Email Subscribed';
        $headerTitle = 'Newsletter Subscription';

        if ($template && !empty(trim((string) $template->body))) {
            $payload = [
                'name' => 'Subscriber',
                'email' => $this->email ?? '',
                'message' => 'Your email has been added to our Subscribers list.',
            ];
            $content = BrandedMail::replacePlaceholders($template->body, $payload);
            $subject = BrandedMail::replacePlaceholders($template->subject ?: $defaultSubject, $payload);
        } else {
            $content = BrandedMail::renderBody('emails.bodies.newsletter_confirm');
            $subject = $defaultSubject;
        }

        return BrandedMail::applyPlatformEnvelope(
            $this->subject($subject)->view(BrandedMail::LAYOUT, compact('content', 'headerTitle'))
        );
    }
}
