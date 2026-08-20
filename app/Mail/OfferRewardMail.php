<?php

namespace App\Mail;

use App\Support\BrandedMail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OfferRewardMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(protected array $subscriber)
    {
    }

    public function build()
    {
        $offerLabel = $this->subscriber['offer_label'] ?? 'a special offer';
        $headerTitle = 'Congratulations!';
        $subject = 'Congratulations! You have been rewarded';

        $payload = array_merge($this->subscriber, [
            'name' => $this->subscriber['name'] ?? 'Subscriber',
            'offer_label' => $offerLabel,
            'offer_description' => $this->subscriber['offer_description'] ?? '',
            'credit_label' => $this->subscriber['credit_label'] ?? null,
            'credit_value' => $this->subscriber['credit_value'] ?? null,
            'credit_is_html' => $this->subscriber['credit_is_html'] ?? false,
        ]);

        $content = BrandedMail::renderBody('emails.bodies.offer_reward', $payload);

        return BrandedMail::applyPlatformEnvelope(
            $this->subject($subject)->view(BrandedMail::LAYOUT, compact('content', 'headerTitle'))
        );
    }
}
