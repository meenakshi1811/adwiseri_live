<?php

namespace App\Services;

use App\Models\Activities;
use App\Models\Internal_Invoices;
use App\Models\Invoices;
use App\Models\Membership;
use App\Models\PaymentARs;
use App\Models\User;
use App\Mail\PlanSubscriptionMail;
use App\Support\EmailAddress;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Prices and completes a subscription purchase made from inside the app (upgrade, renewal or
 * re-subscribing), so every way of paying (card, wallet, credits) charges the same amount,
 * worked out here on the server and never taken from the browser.
 *
 * Upgrades get the unused part of the current paid plan back as a pro-rated credit:
 * the current plan's yearly price (after its multi-year saving) x days left / 365.
 * The credit comes off first, then any wallet balance, and the card pays the rest.
 */
class SubscriptionCheckout
{
    /** Wallet row for unused-plan credit left over after an upgrade is fully paid by it. */
    public const UNUSED_CREDIT_TYPE = 'Unused Plan Credit';

    /**
     * @return array{
     *   plan_name: string, duration: int, plan_amount: float,
     *   credit_amount: float, credit_excess: float, credit_days: int, credit_plan: ?string,
     *   wallet_available: float, wallet_applied: float, payable: float,
     *   category: string, previous_plan: ?string, previous_expiry: ?string
     * }
     */
    public function quote(User $user, Membership $plan, int $durationYears, ?Carbon $now = null): array
    {
        $now = ($now ?? Carbon::now())->copy();
        $duration = SubscriptionTermPricing::normalizeDuration($durationYears);
        $planAmount = $this->isFreePlan($plan)
            ? 0.0
            : SubscriptionTermPricing::calculate((float) $plan->price_per_year, $duration);

        $previousPlanName = $user->membership;
        $previousExpiry = !empty($user->membership_expiry_date)
            ? Carbon::parse($user->membership_expiry_date)
            : null;
        $category = app(UserJourneyLogService::class)->classifySubscriptionPurchase(
            false,
            $previousExpiry,
            $previousPlanName,
            (string) $plan->plan_name,
            $now
        );

        $credit = $this->unusedPlanCredit($user, $plan, $category, $now);
        $creditApplied = round(min($credit['amount'], $planAmount), 2);
        // Credit worth more than the new plan is not lost: the rest goes to the wallet.
        $creditExcess = round($credit['amount'] - $creditApplied, 2);
        $afterCredit = round($planAmount - $creditApplied, 2);

        $walletAvailable = round(max(0.0, SubscriptionTermPricing::walletCreditForSubscriptionPayment($user, $category)), 2);
        $walletApplied = round(min($walletAvailable, $afterCredit), 2);

        return [
            'plan_name' => (string) $plan->plan_name,
            'duration' => $duration,
            'plan_amount' => $planAmount,
            'credit_amount' => $creditApplied,
            'credit_excess' => $creditExcess,
            'credit_days' => $credit['days_left'],
            'credit_plan' => $credit['plan_name'],
            'wallet_available' => $walletAvailable,
            'wallet_applied' => $walletApplied,
            'payable' => round($afterCredit - $walletApplied, 2),
            'category' => $category,
            'previous_plan' => $previousPlanName,
            'previous_expiry' => $previousExpiry?->toDateTimeString(),
        ];
    }

    /** Payable amount for each term (1-5 years), for the term picker on the upgrade page. */
    public function quotesByDuration(User $user, Membership $plan): array
    {
        $quotes = [];
        foreach (SubscriptionTermPricing::allowedDurations() as $years) {
            $quotes[$years] = $this->quote($user, $plan, $years);
        }

        return $quotes;
    }

    /**
     * Credit for the unused days of the subscriber's current paid plan when they move up to a
     * higher plan before it ends. Nothing for renewals, downgrades, Free or expired plans.
     *
     * @return array{amount: float, days_left: int, plan_name: ?string}
     */
    public function unusedPlanCredit(User $user, Membership $targetPlan, string $category, ?Carbon $now = null): array
    {
        $none = ['amount' => 0.0, 'days_left' => 0, 'plan_name' => null];
        $now = ($now ?? Carbon::now())->copy();

        if ($category !== 'upgrade' || empty($user->membership_expiry_date)) {
            return $none;
        }

        $currentPlan = Membership::where('plan_name', $user->membership)->first();
        if (!$currentPlan || $this->isFreePlan($currentPlan)
            || !SubscriptionTermPricing::isUpgradePlan($currentPlan, $targetPlan)) {
            return $none;
        }

        $expiry = Carbon::parse($user->membership_expiry_date);
        $daysLeft = (int) $now->copy()->startOfDay()->diffInDays($expiry->copy()->startOfDay(), false);
        if ($daysLeft <= 0) {
            return $none;
        }

        // The saving the current term was bought with (e.g. 3 years at 20% off) carries into its value.
        $termYears = 1;
        if (!empty($user->membership_start_date)) {
            $termDays = Carbon::parse($user->membership_start_date)->startOfDay()->diffInDays($expiry->copy()->startOfDay());
            $termYears = max(1, (int) round($termDays / 365));
            $daysLeft = min($daysLeft, max(1, $termDays));
        }
        $multiplier = SubscriptionTermPricing::TERM_MULTIPLIERS[SubscriptionTermPricing::normalizeDuration($termYears)] ?? 1.0;
        if ($termYears > 5) {
            $multiplier = 1.0;
        }

        $amount = round((float) $currentPlan->price_per_year * $multiplier * $daysLeft / 365, 2);

        return [
            'amount' => max(0.0, $amount),
            'days_left' => $daysLeft,
            'plan_name' => (string) $currentPlan->plan_name,
        ];
    }

    /**
     * Applies a paid (or fully credited) purchase: the plan and its dates, the wallet, the invoice
     * and payment records, the wallet ledger, referral and renewal commissions, and the email.
     *
     * @return array{internal_invoice: Internal_Invoices, email_sent: bool}
     */
    public function complete(User $user, Membership $plan, array $quote, string $paymentMode, ?string $localTime = null): array
    {
        $duration = SubscriptionTermPricing::normalizeDuration((int) ($quote['duration'] ?? 1));
        $category = (string) ($quote['category'] ?? 'upgrade');
        $previousPlanName = $quote['previous_plan'] ?? $user->membership;
        $previousExpiry = !empty($quote['previous_expiry']) ? Carbon::parse($quote['previous_expiry']) : null;
        $previousWallet = round((float) $user->wallet, 2);
        $forfeitWallet = SubscriptionTermPricing::shouldForfeitWalletOnPurchase($user, $category);
        $walletApplied = $forfeitWallet ? 0.0 : round(min((float) ($quote['wallet_applied'] ?? 0), $previousWallet), 2);
        $planAmount = round((float) ($quote['plan_amount'] ?? 0), 2);
        $creditAmount = round((float) ($quote['credit_amount'] ?? 0), 2);
        $creditExcess = $forfeitWallet ? 0.0 : round(max(0.0, (float) ($quote['credit_excess'] ?? 0)), 2);
        $payable = round(max(0.0, (float) ($quote['payable'] ?? 0)), 2);
        $localTime = $localTime ?: now()->format('d-m-Y H:i:s');

        $user->membership = $plan->plan_name;
        $user->membership_type = 'Subscription';
        if ($this->isFreePlan($plan)) {
            $user->membership_start_date = now();
            $user->membership_expiry_date = now()->addDays(max(1, (int) $plan->validity));
        } else {
            SubscriptionTermPricing::applyMembershipDates($user, $duration, $category, $previousExpiry);
        }
        $walletAfterPayment = $forfeitWallet ? 0.0 : max(0.0, round($previousWallet - $walletApplied, 2));
        $user->wallet = round($walletAfterPayment + $creditExcess, 2);
        $user->save();

        foreach (User::where('added_by', $user->id)->get() as $staff) {
            $staff->membership = $user->membership;
            $staff->membership_type = $user->membership_type;
            $staff->membership_start_date = $user->membership_start_date;
            $staff->membership_expiry_date = $user->membership_expiry_date;
            $staff->wallet = 0;
            $staff->save();
        }

        $this->logActivity($user, 'Subscription Updated', $user->name . ' updates account subscription at ' . $localTime, 'mmbrcp.png', $localTime);
        app(UserJourneyLogService::class)->logSubscriptionPurchase(
            $user,
            $category,
            $plan->plan_name,
            $duration,
            $user->id,
            ['previous_plan' => $previousPlanName]
        );

        $company = User::where('user_type', 'admin')->first();
        $detail = SubscriptionTermPricing::subscriptionFeeDetail($plan->plan_name, $duration);
        $this->recordLegacyInvoice($user, $company, $planAmount, $creditAmount + $walletApplied, $payable, $paymentMode);
        $internalInvoice = $this->createSubscriptionInvoice(
            $user,
            $company,
            $detail,
            $planAmount,
            $payable,
            $paymentMode,
            $this->creditNote($quote, $walletApplied)
        );
        $this->logActivity($user, 'Invoice Generated', 'Invoice generated for subscription update for user ' . $user->name . ' at ' . $localTime, 'invoice.jpg', $localTime);

        if ($walletApplied > 0) {
            $ledger = app(WalletLedgerService::class);
            $ledger->recordSubscriptionDebit(
                $user,
                $walletApplied,
                $previousWallet,
                $walletAfterPayment,
                $ledger->subscriptionDebitDescription($previousPlanName, $plan)
            );
        }
        if ($creditExcess > 0) {
            \App\Models\Referrals::create([
                'referral_code' => $user->referral ?? '',
                'userid' => $user->id,
                'user_name' => $user->name,
                'type' => self::UNUSED_CREDIT_TYPE,
                'total_amount' => $creditExcess,
                'amount_added' => $creditExcess,
                'previous_balance' => $walletAfterPayment,
                'wallet_balance' => (float) $user->wallet,
            ]);
        }

        // Commission is earned on the money actually paid for this purchase.
        $firstPurchase = app(ReferralCommissionService::class)->awardFirstPurchase($user, $payable);
        if (!$firstPurchase) {
            app(RenewalCommissionService::class)->processRenewalCommission($user, $payable, $category, $previousExpiry);
        }

        return [
            'internal_invoice' => $internalInvoice,
            'email_sent' => $this->sendConfirmation($user, $plan, $internalInvoice, $company, $payable, $previousPlanName, $duration, $category),
        ];
    }

    public function isFreePlan(Membership $plan): bool
    {
        return (float) $plan->price_per_year <= 0;
    }

    /** Invoice note lines explaining how the amount due was reduced. */
    public function creditNote(array $quote, ?float $walletApplied = null): string
    {
        $lines = [];
        $credit = round((float) ($quote['credit_amount'] ?? 0), 2);
        if ($credit > 0) {
            $lines[] = 'Pro-rated credit of USD ' . number_format($credit, 2) . ' for '
                . (int) ($quote['credit_days'] ?? 0) . ' unused day(s) of the '
                . strtoupper((string) ($quote['credit_plan'] ?? '')) . ' plan.';
        }
        $excess = round((float) ($quote['credit_excess'] ?? 0), 2);
        if ($excess > 0) {
            $lines[] = 'The remaining unused credit of USD ' . number_format($excess, 2) . ' has been added to your wallet.';
        }
        $wallet = round((float) ($walletApplied ?? $quote['wallet_applied'] ?? 0), 2);
        if ($wallet > 0) {
            $lines[] = 'Wallet credit of USD ' . number_format($wallet, 2) . ' applied.';
        }

        return implode("\n", $lines);
    }

    private function createSubscriptionInvoice(
        User $subscriber,
        ?User $company,
        string $detail,
        float $amount,
        float $total,
        string $paymentMode,
        string $note
    ): Internal_Invoices {
        $invoice = new Internal_Invoices();
        $invoice->invoice_no = $this->uniqueCode(10, fn ($code) => Internal_Invoices::where('invoice_no', $code)->exists());
        $invoice->subscriber_id = $subscriber->id;
        $invoice->name = $company->organization ?? Internal_Invoices::ADWISERI_VENDOR_NAME;
        $invoice->email = $company->email ?? null;
        $invoice->phone = $company->phone ?? null;
        $invoice->country = $company->country ?? null;
        $invoice->state = $company->state ?? null;
        $invoice->city = $company->city ?? null;
        $invoice->pincode = $company->pincode ?? null;
        $invoice->address = $company->address_line ?? null;
        $invoice->logo = $company->organization_logo ?? null;
        $invoice->vendor_id = Internal_Invoices::ADWISERI_VENDOR_ID;
        $invoice->to_name = Internal_Invoices::ADWISERI_VENDOR_NAME;
        $invoice->to_email = $subscriber->email;
        $invoice->to_phone = $subscriber->phone;
        $invoice->to_country = $subscriber->country;
        $invoice->to_state = $subscriber->state;
        $invoice->to_city = $subscriber->city;
        $invoice->to_pincode = $subscriber->pincode;
        $invoice->to_address = $subscriber->address_line;
        $invoice->detail = $detail;
        // Subtotal is the plan price; credits are shown as a line and in the note; total is what was paid.
        $invoice->amount = round(max(0, $amount), 2);
        $invoice->discount = 0;
        $invoice->tax = 0;
        $invoice->total = round(max(0, $total), 2);
        $invoice->invoice_note = $note !== '' ? $note : null;
        $invoice->status = 'Paid';
        $invoice->type = 'ap';
        $invoice->due_date = date('Y-m-d');
        $invoice->token = $this->uniqueCode(20, fn ($code) => Internal_Invoices::where('token', $code)->exists());
        if ($company) {
            app(InvoiceAuditService::class)->markCreatedBySystem($invoice, $company);
        }
        $invoice->save();

        PaymentARs::create([
            'subscriber_id' => $subscriber->id,
            'invoice_no' => $invoice->invoice_no,
            'service_provider' => Internal_Invoices::ADWISERI_VENDOR_NAME,
            'service_taken' => $detail,
            'amount' => $invoice->total,
            'paid_amount' => $invoice->total,
            'payment_mode' => $paymentMode,
            'payment_date' => now(),
            'type' => 'ap',
        ]);

        return $invoice;
    }

    /** The older invoices table some screens still read (one row per subscription payment). */
    private function recordLegacyInvoice(User $user, ?User $company, float $serviceFee, float $discount, float $total, string $paymentMode): void
    {
        $invoice = new Invoices();
        $invoice->user_id = $user->id;
        $invoice->invoice = $this->uniqueCode(10, fn ($code) => Invoices::where('invoice', $code)->exists());
        $invoice->company_name = $company->organization ?? null;
        $invoice->city = $company->city ?? null;
        $invoice->state = $company->state ?? null;
        $invoice->country = $company->country ?? null;
        $invoice->pincode = $company->pincode ?? null;
        $invoice->phone = $company->phone ?? null;
        $invoice->address = $company->address_line ?? null;
        $invoice->logo = $company->organization_logo ?? null;
        $invoice->to_name = $user->name;
        $invoice->to_company = $user->organization;
        $invoice->to_city = $user->city;
        $invoice->to_state = $user->state;
        $invoice->to_country = $user->country;
        $invoice->to_pincode = $user->pincode;
        $invoice->to_phone = $user->phone;
        $invoice->to_email = $user->email;
        $invoice->service_fee = round($serviceFee, 2);
        $invoice->discount = round(min($discount, $serviceFee), 2);
        $invoice->tax = 0;
        $invoice->payment_mode = $paymentMode;
        $invoice->total = round($total, 2);
        $invoice->save();
    }

    private function sendConfirmation(
        User $user,
        Membership $plan,
        Internal_Invoices $invoice,
        ?User $company,
        float $paid,
        ?string $previousPlanName,
        int $duration,
        string $category
    ): bool {
        if (!$company || !EmailAddress::isValidRecipient($user->email)) {
            Log::warning('Plan update email skipped', ['user_id' => $user->id, 'email' => $user->email]);

            return false;
        }

        try {
            Mail::to($user->email)->send(new PlanSubscriptionMail(
                $user->name,
                $plan->plan_name,
                $plan->validity ?? $duration,
                'Your Subscription Plan Has Been Updated',
                InternalInvoicePdfDataFactory::make($invoice, $user, $company),
                $paid,
                $previousPlanName,
                $this->isFreePlan($plan) ? null : $duration,
                $category
            ));

            return !Mail::failures();
        } catch (\Throwable $e) {
            Log::error('Plan update email failed', ['user_id' => $user->id, 'error' => $e->getMessage()]);

            return false;
        }
    }

    private function logActivity(User $user, string $name, string $detail, string $icon, string $localTime): void
    {
        $activity = new Activities();
        $activity->subscriber_id = $user->id;
        $activity->user_id = $user->id;
        $activity->user_name = $user->name;
        $activity->activity_name = $name;
        $activity->activity_detail = $detail;
        $activity->activity_icon = $icon;
        $activity->local_time = $localTime;
        $activity->save();
    }

    private function uniqueCode(int $length, callable $taken): string
    {
        $chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        do {
            $code = '';
            for ($i = 0; $i < $length; $i++) {
                $code .= $chars[random_int(0, strlen($chars) - 1)];
            }
        } while ($taken($code));

        return $code;
    }
}
