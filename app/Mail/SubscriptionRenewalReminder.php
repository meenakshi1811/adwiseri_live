<?php

namespace App\Mail;

use App\Services\EmailTemplateService;
use App\Support\BrandedMail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SubscriptionRenewalReminder extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public $subscriber,
        public $daysRemaining
    ) {
    }

    public function build()
    {
        $template = app(EmailTemplateService::class)->getTemplateForUser(
            $this->subscriber,
            'subscriber',
            'subscription_expiry_reminder'
        );

        $defaultSubject = "Renew Your Subscription - {$this->daysRemaining} Days Left";
        $headerTitle = 'Subscription Renewal Reminder';
        $renewalLink = route('price_plans', ['id' => $this->subscriber->id]);

        if ($template && !empty(trim((string) $template->body))) {
            $data = [
                'name' => $this->subscriber->name,
                'daysRemaining' => $this->daysRemaining,
                'renewalLink' => $renewalLink,
            ];
            $content = BrandedMail::replacePlaceholders($template->body, $data);
            $subject = BrandedMail::replacePlaceholders($template->subject ?: $defaultSubject, $data);
        } else {
            $content = BrandedMail::renderBody('emails.bodies.subscription_renewal', [
                'subscriber' => $this->subscriber,
                'daysRemaining' => $this->daysRemaining,
                'renewalLink' => $renewalLink,
            ]);
            $subject = $defaultSubject;
        }

        return BrandedMail::applyPlatformEnvelope(
            $this->subject($subject)->view(BrandedMail::LAYOUT, compact('content', 'headerTitle'))
        );
    }
}
