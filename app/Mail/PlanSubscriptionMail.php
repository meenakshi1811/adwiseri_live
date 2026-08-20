<?php

namespace App\Mail;

use App\Models\Membership;
use App\Support\BrandedMail;
use App\Support\InvoiceMailAttachment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PlanSubscriptionMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public $subscriberName,
        public $planDetails,
        public $validityDuration,
        public $title,
        public $invoicePdfData = null,
        public $paidAmount = null,
        public ?string $previousPlanName = null,
        public ?int $durationYears = null,
        public ?string $purchaseCategory = null,
    ) {
    }

    public function build()
    {
        $isUpgrade = $this->isUpgrade();
        $isRenewal = $this->isRenewal();
        $view = $isUpgrade
            ? 'emails.bodies.subscription_plan_upgrade'
            : ($isRenewal ? 'emails.bodies.subscription_plan_renewal' : 'emails.bodies.subscription_plan');
        $subject = $isUpgrade
            ? 'Your subscription (plan) has been upgraded'
            : ($isRenewal ? 'Your subscription has been renewed' : $this->title);
        $headerTitle = $isUpgrade
            ? 'Subscription Upgraded'
            : ($isRenewal ? 'Subscription Renewed' : 'Subscription Update');
        $validityLabel = $this->validityLabel();

        $content = BrandedMail::renderBody($view, [
            'subscriberName' => $this->subscriberName,
            'planDetails' => $this->planDetails,
            'validityDuration' => $validityLabel,
            'title' => $this->title,
            'paidAmount' => $this->paidAmount,
            'oldPlanName' => $this->previousPlanName,
        ]);

        $mail = BrandedMail::applyPlatformEnvelope(
            $this->subject($subject)
                ->view(BrandedMail::LAYOUT, compact('content', 'headerTitle'))
        );

        BrandedMail::applyAlertsBcc($mail);

        if (!empty($this->invoicePdfData)) {
            $invoiceData = is_array($this->invoicePdfData)
                ? (object) $this->invoicePdfData
                : $this->invoicePdfData;

            $mail = InvoiceMailAttachment::attachInvoicePdf($mail, $invoiceData);
        }

        return $mail;
    }

    protected function isUpgrade(): bool
    {
        if (empty($this->previousPlanName)) {
            return false;
        }

        $newPlanName = (string) $this->planDetails;
        if (strcasecmp($this->previousPlanName, $newPlanName) === 0) {
            return false;
        }

        $oldPlan = Membership::where('plan_name', $this->previousPlanName)->first();
        $newPlan = Membership::where('plan_name', $newPlanName)->first();

        if (!$oldPlan || !$newPlan) {
            return false;
        }

        return (float) $newPlan->price_per_year > (float) $oldPlan->price_per_year;
    }

    protected function isRenewal(): bool
    {
        if ($this->purchaseCategory === 'renewal') {
            return true;
        }

        if (empty($this->previousPlanName)) {
            return false;
        }

        return strcasecmp(trim($this->previousPlanName), trim((string) $this->planDetails)) === 0;
    }

    protected function validityLabel(): string
    {
        if ($this->durationYears !== null && (int) $this->durationYears > 0) {
            $years = (int) $this->durationYears;

            return $years . ' ' . ($years === 1 ? 'Year' : 'Years');
        }

        if (is_numeric($this->validityDuration)) {
            return (int) $this->validityDuration . ' Days';
        }

        return (string) $this->validityDuration;
    }
}
