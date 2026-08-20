<?php

namespace App\Mail;

use App\Services\EmailTemplateService;
use App\Services\OfferBenefitService;
use App\Support\BrandedMail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WalletAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        protected array $subscriber,
        protected string $alertType = 'credit'
    ) {
    }

    public function build()
    {
        $offerType = (string) ($this->subscriber['type'] ?? '');
        $allOfferTypes = array_merge(OfferBenefitService::MANUAL_TYPES, OfferBenefitService::AUTOMATED_TYPES);

        if (in_array($offerType, $allOfferTypes, true)) {
            return $this->buildOfferRewardStyleMail();
        }

        $templateKey = $this->alertType === 'debit' ? 'wallet_debit_alert' : 'wallet_credit_alert';
        $template = app(EmailTemplateService::class)->getTemplateForUser(null, 'subscriber', $templateKey);

        $defaultSubject = $this->alertType === 'debit'
            ? 'Wallet Debit / Offer Applied'
            : 'Wallet Credit / Offer Applied';
        $headerTitle = 'Wallet Update';

        $payload = array_merge($this->subscriber, [
            'name' => $this->subscriber['name'] ?? 'Subscriber',
            'type' => $offerType,
            'credit_amount' => $this->subscriber['credit_amount'] ?? ($this->subscriber['value'] ?? 0),
            'description' => $this->subscriber['description'] ?? '',
        ]);

        if ($template && !empty(trim((string) $template->body))) {
            $content = BrandedMail::replacePlaceholders($template->body, $payload);
            $subject = BrandedMail::replacePlaceholders($template->subject ?: $defaultSubject, $payload);
        } else {
            $content = BrandedMail::renderBody('emails.bodies.wallet_alert', $payload);
            $subject = $defaultSubject;
        }

        return BrandedMail::applyPlatformEnvelope(
            $this->subject($subject)->view(BrandedMail::LAYOUT, compact('content', 'headerTitle'))
        );
    }

    protected function buildOfferRewardStyleMail()
    {
        $offerBenefitService = app(OfferBenefitService::class);
        $type = (string) ($this->subscriber['type'] ?? '');

        if (!empty($this->subscriber['offer_label']) && !empty($this->subscriber['credit_label'])) {
            $payload = [
                'name' => $this->subscriber['name'] ?? 'Subscriber',
                'offer_label' => $this->subscriber['offer_label'],
                'offer_description' => $this->subscriber['offer_description'] ?? '',
                'credit_label' => $this->subscriber['credit_label'] ?? null,
                'credit_value' => $this->subscriber['credit_value'] ?? null,
                'credit_is_html' => $this->subscriber['credit_is_html'] ?? false,
            ];
        } elseif (!empty($this->subscriber['userid'])) {
            $user = \App\Models\User::find($this->subscriber['userid']);
            $payload = $user
                ? array_merge(
                    ['name' => $this->subscriber['name'] ?? $user->name],
                    $offerBenefitService->buildOfferEmailDetails($type, $user, [
                        'discount_value' => $this->subscriber['discount_value'] ?? $this->subscriber['value'] ?? 0,
                        'credit_amount' => $this->subscriber['credit_amount'] ?? $this->subscriber['amount_added'] ?? 0,
                    ])
                )
                : [
                    'name' => $this->subscriber['name'] ?? 'Subscriber',
                    'offer_label' => $this->subscriber['offer_label'] ?? $offerBenefitService->offerTypeLabel($type),
                    'offer_description' => $this->subscriber['offer_description'] ?? $this->subscriber['description'] ?? '',
                ];
        } else {
            $payload = [
                'name' => $this->subscriber['name'] ?? 'Subscriber',
                'offer_label' => $this->subscriber['offer_label'] ?? $offerBenefitService->offerTypeLabel($type),
                'offer_description' => $this->subscriber['offer_description'] ?? $this->subscriber['description'] ?? '',
            ];
        }

        $content = BrandedMail::renderBody('emails.bodies.offer_reward', $payload);
        $headerTitle = 'Congratulations!';
        $subject = 'Congratulations! You have been rewarded';

        return BrandedMail::applyPlatformEnvelope(
            $this->subject($subject)->view(BrandedMail::LAYOUT, compact('content', 'headerTitle'))
        );
    }
}
