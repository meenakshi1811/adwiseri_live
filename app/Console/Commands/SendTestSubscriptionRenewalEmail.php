<?php

namespace App\Console\Commands;

use App\Mail\PlanSubscriptionMail;
use App\Models\Internal_Invoices;
use App\Models\Membership;
use App\Models\User;
use App\Services\InternalInvoicePdfDataFactory;
use App\Services\SubscriptionTermPricing;
use App\Support\EmailAddress;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendTestSubscriptionRenewalEmail extends Command
{
    protected $signature = 'subscription:send-renewal-test
                            {subscriber : Subscriber user ID or email}
                            {--to= : Override recipient email for testing}
                            {--duration=1 : Renewal term in years (1, 2, 3, or 5)}
                            {--invoice= : Optional internal invoice ID or number for the PDF attachment}';

    protected $description = 'Send a test subscription renewal confirmation email (with invoice PDF)';

    public function handle(): int
    {
        $subscriber = $this->resolveSubscriber((string) $this->argument('subscriber'));
        if (!$subscriber) {
            $this->error('Subscriber not found.');

            return self::FAILURE;
        }

        $plan = Membership::where('plan_name', '=', $subscriber->membership)->first();
        if (!$plan) {
            $this->error('Membership plan not found for subscriber: ' . $subscriber->membership);

            return self::FAILURE;
        }

        $company = User::where('user_type', '=', 'admin')->first();
        if (!$company) {
            $this->error('Admin company account not found.');

            return self::FAILURE;
        }

        $durationYears = SubscriptionTermPricing::normalizeDuration((int) $this->option('duration'));
        $recipient = trim((string) ($this->option('to') ?: $subscriber->email));
        if (!EmailAddress::isValidRecipient($recipient)) {
            $this->error('No valid recipient email available. Pass --to=email@example.com');

            return self::FAILURE;
        }

        $internalInvoice = $this->resolveInvoice($subscriber, $plan->plan_name, $durationYears);
        $paidAmount = (float) ($internalInvoice->total ?? $internalInvoice->amount ?? 0);
        $invoicePdfData = InternalInvoicePdfDataFactory::make($internalInvoice, $subscriber, $company);

        $this->line('Mailer   : ' . config('mail.default'));
        $this->line('From     : ' . config('mail.from.address'));
        $this->line('To       : ' . $recipient);
        $this->line('Subject  : Your subscription has been renewed');
        $this->line('Plan     : ' . $plan->plan_name);
        $this->line('Duration : ' . SubscriptionTermPricing::label($durationYears));
        $this->line('Amount   : USD ' . number_format($paidAmount, 2));
        $this->line('Invoice  : ' . $internalInvoice->invoice_no . ($internalInvoice->exists ? '' : ' (preview only)'));
        $this->newLine();

        try {
            Mail::to($recipient)->send(new PlanSubscriptionMail(
                $subscriber->name,
                $plan->plan_name,
                $plan->validity ?? 'N/A',
                'Your Subscription Plan Has Been Updated',
                $invoicePdfData,
                $paidAmount,
                $plan->plan_name,
                $durationYears,
                'renewal'
            ));

            if (Mail::failures()) {
                $this->error('Mail transport reported delivery failures.');

                return self::FAILURE;
            }

            $this->info('Subscription renewal confirmation email sent successfully.');
            $this->line('Tip: set MAIL_MAILER=log in .env to capture emails in storage/logs/laravel.log while testing locally.');

            return self::SUCCESS;
        } catch (\Throwable $exception) {
            $this->error('Failed to send email: ' . $exception->getMessage());
            $this->line('Tip: set MAIL_MAILER=log in .env to capture emails in storage/logs/laravel.log while testing locally.');

            return self::FAILURE;
        }
    }

    private function resolveSubscriber(string $lookup): ?User
    {
        if (ctype_digit($lookup)) {
            return User::where('id', (int) $lookup)
                ->where('user_type', 'Subscriber')
                ->first();
        }

        return User::where('email', $lookup)
            ->where('user_type', 'Subscriber')
            ->first();
    }

    private function resolveInvoice(User $subscriber, string $planName, int $durationYears): Internal_Invoices
    {
        $lookup = trim((string) $this->option('invoice'));
        if ($lookup !== '') {
            $invoice = ctype_digit($lookup)
                ? Internal_Invoices::find($lookup)
                : Internal_Invoices::where('invoice_no', $lookup)->first();

            if ($invoice) {
                return $invoice;
            }

            $this->warn('Invoice not found: ' . $lookup . '. Using a preview invoice instead.');
        }

        $existingInvoice = Internal_Invoices::where('subscriber_id', $subscriber->id)
            ->where(function ($query) {
                $query->where('detail', 'like', '%Subscription Fees%')
                    ->orWhere('detail', 'like', '%subscription%');
            })
            ->orderByDesc('id')
            ->first();

        if ($existingInvoice) {
            return $existingInvoice;
        }

        return $this->buildPreviewInvoice($subscriber, $planName, $durationYears);
    }

    private function buildPreviewInvoice(User $subscriber, string $planName, int $durationYears): Internal_Invoices
    {
        $plan = Membership::where('plan_name', '=', $planName)->first();
        $amount = SubscriptionTermPricing::calculate((float) ($plan->price_per_year ?? 0), $durationYears);

        $invoice = new Internal_Invoices();
        $invoice->invoice_no = 'TEST-RENEWAL-' . now()->format('Ymd-His');
        $invoice->subscriber_id = $subscriber->id;
        $invoice->to_email = $subscriber->email;
        $invoice->to_address = $subscriber->address_line;
        $invoice->to_city = $subscriber->city;
        $invoice->to_state = $subscriber->state;
        $invoice->to_country = $subscriber->country;
        $invoice->to_pincode = $subscriber->pincode;
        $invoice->detail = SubscriptionTermPricing::subscriptionFeeDetail($planName, $durationYears);
        $invoice->amount = $amount;
        $invoice->discount = 0;
        $invoice->tax = 0;
        $invoice->total = $amount;
        $invoice->status = 'Paid';
        $invoice->due_date = now()->toDateString();
        $invoice->created_at = now();
        $invoice->token = 'TEST-RENEWAL';

        return $invoice;
    }
}
