<?php

namespace App\Mail;

use App\Services\EmailTemplateService;
use App\Support\SubscriptionRenewalReminderSchedule;
use App\Support\BrandedMail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SubscriptionRenewalReminder extends Mailable
{
    use Queueable, SerializesModels;

    public const PHASE_PRE_EXPIRY = 'pre_expiry';

    public const PHASE_WINBACK = 'winback';

    public const PHASE_WINBACK_FINAL = 'winback_final';

    public function __construct(
        public $subscriber,
        public $daysRemaining,
        public string $phase = self::PHASE_PRE_EXPIRY
    ) {
    }

    public function build()
    {
        $renewalLink = route('price_plans', ['id' => $this->subscriber->id]);
        $daysExpired = $this->isPostExpiry() ? (int) $this->daysRemaining : 0;
        $daysLeft = $this->isPostExpiry()
            ? max(0, SubscriptionRenewalReminderSchedule::POST_EXPIRY_FINAL_DAY - $daysExpired)
            : (int) $this->daysRemaining;
        $walletAmount = number_format((float) ($this->subscriber->wallet ?? 0), 2);
        $templateKey = $this->templateKey();
        $defaultSubject = $this->defaultSubject($daysExpired);
        $headerTitle = $this->isFinalWinback()
            ? 'Final Renewal Reminder'
            : 'Subscription Renewal Reminder';

        $template = app(EmailTemplateService::class)->getTemplateForUser(
            $this->subscriber,
            'subscriber',
            $templateKey
        );

        $placeholders = [
            'name' => $this->subscriber->name,
            'daysRemaining' => $this->isPostExpiry() ? $daysLeft : $this->daysRemaining,
            'daysExpired' => $daysExpired,
            'daysLeft' => $daysLeft,
            'walletAmount' => $walletAmount,
            'renewalLink' => $renewalLink,
        ];

        if ($template && !empty(trim((string) $template->body))) {
            $content = BrandedMail::replacePlaceholders($template->body, $placeholders);
            $subject = BrandedMail::replacePlaceholders($template->subject ?: $defaultSubject, $placeholders);
        } else {
            $content = BrandedMail::renderBody($this->bodyView(), [
                'subscriber' => $this->subscriber,
                'daysRemaining' => $this->daysRemaining,
                'daysExpired' => $daysExpired,
                'daysLeft' => $daysLeft,
                'walletAmount' => $walletAmount,
                'renewalLink' => $renewalLink,
            ]);
            $subject = $defaultSubject;
        }

        return BrandedMail::applyPlatformEnvelope(
            $this->subject($subject)->view(BrandedMail::LAYOUT, compact('content', 'headerTitle'))
        );
    }

    private function isPostExpiry(): bool
    {
        return in_array($this->phase, [self::PHASE_WINBACK, self::PHASE_WINBACK_FINAL], true);
    }

    private function isFinalWinback(): bool
    {
        return $this->phase === self::PHASE_WINBACK_FINAL;
    }

    private function templateKey(): string
    {
        if ($this->isFinalWinback()) {
            return 'subscription_winback_final_reminder';
        }

        if ($this->phase === self::PHASE_WINBACK) {
            return 'subscription_winback_reminder';
        }

        return 'subscription_expiry_reminder';
    }

    private function bodyView(): string
    {
        if ($this->isFinalWinback()) {
            return 'emails.bodies.subscription_winback_final';
        }

        if ($this->phase === self::PHASE_WINBACK) {
            return 'emails.bodies.subscription_winback';
        }

        return 'emails.bodies.subscription_renewal';
    }

    private function defaultSubject(int $daysExpired): string
    {
        if ($this->isFinalWinback()) {
            return 'Final Reminder - Renew Today Before Wallet Credit Expires';
        }

        if ($this->phase === self::PHASE_WINBACK) {
            return "Renew Your Subscription - Expired {$daysExpired} Days Ago";
        }

        return "Renew Your Subscription - {$this->daysRemaining} Days Left";
    }
}
