<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use DateTime;

class SubscriptionTermPricing
{
    /** @var array<int, float> Years => effective per-year multiplier after term discount */
    public const TERM_MULTIPLIERS = [
        1 => 1.0,
        2 => 0.9,
        3 => 0.8,
        4 => 0.7,
        5 => 0.5,
    ];

    /** @return array<int, int> */
    public static function allowedDurations(): array
    {
        $durations = array_keys(self::TERM_MULTIPLIERS);
        sort($durations);

        return $durations;
    }

    public static function isAllowedDuration(int $duration): bool
    {
        return array_key_exists($duration, self::TERM_MULTIPLIERS);
    }

    public static function normalizeDuration(?int $duration): int
    {
        $duration = (int) ($duration ?? 1);

        return self::isAllowedDuration($duration) ? $duration : 1;
    }

    public static function calculate(float $pricePerYear, int $duration): float
    {
        $duration = self::normalizeDuration($duration);
        $multiplier = self::TERM_MULTIPLIERS[$duration];

        return round($pricePerYear * $duration * $multiplier);
    }

    public static function label(int $duration): string
    {
        $duration = self::normalizeDuration($duration);

        return $duration === 1 ? '1 Year' : $duration . ' Years';
    }

    /**
     * Standard AP invoice / payment line for subscription purchases.
     */
    public static function subscriptionFeeDetail(string $planName, ?int $durationYears = null): string
    {
        $planName = trim($planName);
        $base = 'Subscription Fees (' . ($planName !== '' ? $planName : 'Plan') . ')';

        if ($durationYears === null) {
            return $base;
        }

        return $base . ' - ' . self::label($durationYears);
    }

    public static function discountHint(int $duration): ?string
    {
        return match (self::normalizeDuration($duration)) {
            2 => '10% savings',
            3 => '20% savings',
            4 => '30% savings',
            5 => '50% savings',
            default => null,
        };
    }

    /**
     * @return array<int, float>
     */
    public static function amountsByDuration(float $pricePerYear): array
    {
        $amounts = [];

        foreach (self::allowedDurations() as $years) {
            $amounts[$years] = self::calculate($pricePerYear, $years);
        }

        return $amounts;
    }

    /** @return array<int, array{value:int,label:string,hint:?string}> */
    public static function durationOptions(): array
    {
        $options = [];
        foreach (self::allowedDurations() as $years) {
            $options[$years] = [
                'value' => $years,
                'label' => self::label($years),
                'hint' => self::discountHint($years),
            ];
        }

        return $options;
    }

    /**
     * Resolve membership start/expiry for a purchase.
     *
     * Renewals keep the original start date and extend expiry from the later of
     * now or the previous expiry, so purchased years (and plan limits) accumulate
     * and show correctly in the Subscription Module.
     *
     * Signup / upgrade reset the term from today.
     *
     * @return array{start: DateTime, expiry: DateTime}
     */
    public static function resolveMembershipDates(
        User $user,
        int $durationYears,
        string $purchaseCategory,
        ?Carbon $previousExpiry = null,
        ?Carbon $purchaseDate = null
    ): array {
        $durationYears = self::normalizeDuration($durationYears);
        $purchaseDate = ($purchaseDate ?? Carbon::now())->copy();
        $now = DateTime::createFromFormat('Y-m-d H:i:s', $purchaseDate->format('Y-m-d H:i:s'))
            ?: new DateTime($purchaseDate->toDateTimeString());

        if ($purchaseCategory === 'renewal' && !empty($user->membership_start_date)) {
            $start = $user->membership_start_date instanceof DateTime
                ? clone $user->membership_start_date
                : new DateTime((string) $user->membership_start_date);

            $anchor = $purchaseDate->copy();
            if ($previousExpiry && $previousExpiry->gt($anchor)) {
                $anchor = $previousExpiry->copy();
            }

            $expiry = DateTime::createFromFormat('Y-m-d H:i:s', $anchor->format('Y-m-d H:i:s'))
                ?: new DateTime($anchor->toDateTimeString());
            $expiry->modify('+' . $durationYears . ' years');

            return [
                'start' => $start,
                'expiry' => $expiry,
            ];
        }

        $expiry = clone $now;
        $expiry->modify('+' . $durationYears . ' years');

        return [
            'start' => $now,
            'expiry' => $expiry,
        ];
    }

    /**
     * Apply membership start/expiry on the user for a classified purchase.
     */
    public static function applyMembershipDates(
        User $user,
        int $durationYears,
        string $purchaseCategory,
        ?Carbon $previousExpiry = null,
        ?Carbon $purchaseDate = null
    ): array {
        $dates = self::resolveMembershipDates(
            $user,
            $durationYears,
            $purchaseCategory,
            $previousExpiry,
            $purchaseDate
        );

        $user->membership_start_date = $dates['start'];
        $user->membership_expiry_date = $dates['expiry'];

        return $dates;
    }

    /**
     * Compare two plans for upgrade/downgrade ordering.
     *
     * @return int -1 when $target is lower, 0 when equal, 1 when $target is higher
     */
    public static function comparePlans(object $currentPlan, object $targetPlan): int
    {
        $currentOrder = (int) ($currentPlan->plan_order ?? 0);
        $targetOrder = (int) ($targetPlan->plan_order ?? 0);

        if ($currentOrder > 0 && $targetOrder > 0 && $currentOrder !== $targetOrder) {
            return $targetOrder <=> $currentOrder;
        }

        $currentPrice = (float) ($currentPlan->price_per_year ?? 0);
        $targetPrice = (float) ($targetPlan->price_per_year ?? 0);

        return $targetPrice <=> $currentPrice;
    }

    public static function isUpgradePlan(object $currentPlan, object $targetPlan): bool
    {
        return self::comparePlans($currentPlan, $targetPlan) > 0;
    }

    public static function isDowngradePlan(object $currentPlan, object $targetPlan): bool
    {
        return self::comparePlans($currentPlan, $targetPlan) < 0;
    }

    /**
     * Renewal window: from 60 days before expiry through 30 days after.
     */
    public static function isRenewalWindowOpen(User $subscriber, ?object $currentPlan = null): bool
    {
        if (empty($subscriber->membership_expiry_date)) {
            return false;
        }

        $planName = strtoupper(trim((string) ($currentPlan->plan_name ?? $subscriber->membership ?? '')));
        if ($planName === '' || $planName === 'FREE') {
            return false;
        }

        $expiryDate = Carbon::parse($subscriber->membership_expiry_date);
        $daysBeforeExpiry = Carbon::now()->diffInDays($expiryDate, false);

        return $daysBeforeExpiry <= 60 && $daysBeforeExpiry >= -30;
    }

    /**
     * Subscription is lapsed when expiry was more than 30 days ago.
     * After this point the subscriber is treated as a new subscriber for renewals.
     */
    public static function isSubscriptionLapsed(User $subscriber): bool
    {
        if (empty($subscriber->membership_expiry_date)) {
            return false;
        }

        $expiryDate = Carbon::parse($subscriber->membership_expiry_date);
        $daysBeforeExpiry = Carbon::now()->diffInDays($expiryDate, false);

        return $daysBeforeExpiry < -30;
    }

    /**
     * Resolve the billing subscriber for a logged-in user (staff inherit subscriber membership).
     */
    public static function billingSubscriber(User $user): ?User
    {
        if ($user->user_type === 'admin') {
            return null;
        }

        if ($user->user_type === 'Subscriber') {
            return $user;
        }

        if (!empty($user->added_by)) {
            $subscriber = User::find($user->added_by);

            if ($subscriber && $subscriber->user_type === 'Subscriber') {
                return $subscriber;
            }
        }

        return $user;
    }

    /**
     * Block app access only after the 30-day post-expiry grace window (lapsed).
     */
    public static function isMembershipAccessBlocked(User $user): bool
    {
        if ($user->user_type === 'admin') {
            return false;
        }

        $subscriber = self::billingSubscriber($user);

        if (!$subscriber) {
            return false;
        }

        return self::isSubscriptionLapsed($subscriber);
    }

    /**
     * Loyalty wallet credits are forfeited on lapse or when treated as a new signup.
     */
    public static function shouldForfeitWalletOnPurchase(User $subscriber, string $purchaseCategory): bool
    {
        return $purchaseCategory === 'signup' || self::isSubscriptionLapsed($subscriber);
    }

    /**
     * Wallet balance that may be applied toward a subscription payment.
     */
    public static function walletCreditForSubscriptionPayment(User $subscriber, string $purchaseCategory): float
    {
        if (self::shouldForfeitWalletOnPurchase($subscriber, $purchaseCategory)) {
            return 0.0;
        }

        return (float) $subscriber->wallet;
    }
}
