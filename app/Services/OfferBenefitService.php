<?php

namespace App\Services;

use App\Mail\OfferRewardMail;
use App\Models\Associate;
use App\Models\Clients;
use App\Models\Internal_communications;
use App\Models\Membership;
use App\Models\Offers;
use App\Models\Referrals;
use App\Models\User;
use App\Services\UserJourneyLogService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;

class OfferBenefitService
{
    public const ONE_OFF_CREDIT_TYPES = [
        'one_off_promo',
        'one_off_dr',
        'one_off_bug',
        'one_off_loyalty',
    ];

    public const MANUAL_TYPES = [
        'cashback',
        'one_off_promo',
        'one_off_dr',
        'one_off_bug',
        'one_off_loyalty',
        'double_term',
        'free_upgrade',
    ];

    public const AUTOMATED_TYPES = [
        '3_months_extra',
        '6_months_extra',
        'double_features',
        'double_clients',
        'double_users',
        'double_messages',
        'double_associates',
        'unlimited_associates',
        'analytics_on',
        'free_upgrade',
    ];

    /** Plan keys selectable on automated offers ("Applicable on (Plan)"). */
    public const APPLICABLE_PLAN_OPTIONS = [
        'all' => 'All',
        'Solo' => 'Solo',
        'Adwiseri' => 'Adwiseri',
        'Adwiseri+' => 'Adwiseri+',
        'Enterprises' => 'Enterprises',
    ];

    public const LIMIT_BOOST_TYPES = [
        'double_features',
        'double_clients',
        'double_users',
        'double_messages',
        'double_associates',
    ];

    public const ASSOCIATE_OFFER_TYPES = [
        'double_associates',
        'unlimited_associates',
    ];

    /** Referral row types with monetary wallet impact shown on wallet tables. */
    public const WALLET_TABLE_REFERRAL_TYPES = [
        'cashback',
        'one_off',
        'one_off_promo',
        'one_off_dr',
        'one_off_bug',
        'one_off_loyalty',
        'Referral Commission',
        'Renewal Commission',
        'Wallet Transaction',
    ];

    public static function oneOffCreditTypeOptions(): array
    {
        return [
            'one_off_promo' => 'One-time credit (Promo)',
            'one_off_dr' => 'One-time credit (DR)',
            'one_off_bug' => 'One-time credit (Bug)',
            'one_off_loyalty' => 'One-time credit (Loyalty)',
        ];
    }

    public static function oneOffCreditTypes(): array
    {
        return self::ONE_OFF_CREDIT_TYPES;
    }

    public static function requiresDiscountAmount(string $type): bool
    {
        return $type === 'cashback'
            || $type === 'one_off'
            || in_array($type, self::ONE_OFF_CREDIT_TYPES, true);
    }

    public function isOneOffCreditType(string $type): bool
    {
        return $type === 'one_off' || in_array($type, self::ONE_OFF_CREDIT_TYPES, true);
    }

    public static function walletTableReferralTypes(): array
    {
        return self::WALLET_TABLE_REFERRAL_TYPES;
    }

    public function isWalletTableReferralType(string $type): bool
    {
        return in_array($type, self::walletTableReferralTypes(), true);
    }

    public function isManualType(string $type): bool
    {
        return in_array($type, self::MANUAL_TYPES, true) || $type === 'one_off';
    }

    public function isAutomatedType(string $type): bool
    {
        return in_array($type, self::AUTOMATED_TYPES, true);
    }

    public function isLimitBoostOfferType(string $type): bool
    {
        return in_array($type, self::LIMIT_BOOST_TYPES, true);
    }

    public static function automatedOfferTypeKeys(): array
    {
        return self::AUTOMATED_TYPES;
    }

    public static function applicablePlanOptionKeys(): array
    {
        return array_keys(self::APPLICABLE_PLAN_OPTIONS);
    }

    public static function applicablePlanOptions(): array
    {
        return self::APPLICABLE_PLAN_OPTIONS;
    }

    /**
     * Normalize and validate plan selections from the admin form.
     *
     * @param  array<int, mixed>|null  $plans
     * @return array<int, string>
     */
    public static function normalizeApplicablePlans(?array $plans): array
    {
        if ($plans === null || $plans === []) {
            return ['all'];
        }

        $allowed = self::applicablePlanOptionKeys();
        $normalized = [];

        foreach ($plans as $plan) {
            $plan = trim((string) $plan);
            if ($plan !== '' && in_array($plan, $allowed, true) && !in_array($plan, $normalized, true)) {
                $normalized[] = $plan;
            }
        }

        if ($normalized === [] || in_array('all', $normalized, true)) {
            return ['all'];
        }

        return $normalized;
    }

    /**
     * @param  array<int, string>|null  $plans
     */
    public function campaignAppliesToAllPlans(?array $plans): bool
    {
        $normalized = self::normalizeApplicablePlans($plans);

        return in_array('all', $normalized, true);
    }

    /**
     * Map stored plan keys to membership.plan_name / users.membership values.
     *
     * @return array<int, string>
     */
    public function membershipNamesForPlanKey(string $planKey): array
    {
        return match ($planKey) {
            'Solo' => ['Solo'],
            'Adwiseri' => ['Adwiseri'],
            'Adwiseri+' => ['Adwiseri+', 'Advisory+'],
            'Enterprises' => ['Enterprises', 'Enterprise'],
            default => [$planKey],
        };
    }

    /**
     * @param  array<int, string>|null  $plans
     */
    public function subscriberMatchesApplicablePlans(User $subscriber, ?array $plans): bool
    {
        $normalized = self::normalizeApplicablePlans($plans);

        if ($this->campaignAppliesToAllPlans($normalized)) {
            return true;
        }

        $membership = trim((string) ($subscriber->membership ?? ''));
        if ($membership === '') {
            return false;
        }

        foreach ($normalized as $planKey) {
            foreach ($this->membershipNamesForPlanKey($planKey) as $name) {
                if (strcasecmp($membership, $name) === 0) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Whether two plan selections target overlapping subscription plans.
     *
     * @param  array<int, string>|null  $left
     * @param  array<int, string>|null  $right
     */
    public function applicablePlansOverlap(?array $left, ?array $right): bool
    {
        $leftPlans = self::normalizeApplicablePlans($left);
        $rightPlans = self::normalizeApplicablePlans($right);

        if ($this->campaignAppliesToAllPlans($leftPlans) || $this->campaignAppliesToAllPlans($rightPlans)) {
            return true;
        }

        return count(array_intersect($leftPlans, $rightPlans)) > 0;
    }

    public function isLimitBenefitType(string $type): bool
    {
        return in_array($type, array_merge(['double_term'], self::AUTOMATED_TYPES), true);
    }

    public static function normalizeStorageDate(?string $date): ?string
    {
        if ($date === null || trim($date) === '') {
            return null;
        }

        $date = trim($date);

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return $date;
        }

        if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $date)) {
            return Carbon::createFromFormat('d-m-Y', $date)->format('Y-m-d');
        }

        if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $date)) {
            return Carbon::createFromFormat('d/m/Y', $date)->format('Y-m-d');
        }

        return Carbon::parse($date)->format('Y-m-d');
    }

    public function offerTypeLabel(string $type): string
    {
        return match ($type) {
            'cashback' => 'Cashback',
            'one_off' => 'One-time credit',
            'one_off_promo' => 'One-time credit (Promo)',
            'one_off_dr' => 'One-time credit (DR)',
            'one_off_bug' => 'One-time credit (Bug)',
            'one_off_loyalty' => 'One-time credit (Loyalty)',
            'double_term' => 'Double Subscription Term',
            '3_months_extra' => '3 Months Extra',
            '6_months_extra' => '6 Months Extra',
            'double_features' => 'Double Features',
            'double_clients' => 'Double Clients',
            'double_users' => 'Double Users',
            'double_messages' => 'Double Messages',
            'double_associates' => 'Double Associates',
            'unlimited_associates' => 'Unlimited Associates',
            'analytics_on' => 'Analytics ON',
            'free_upgrade' => 'Free Upgrade',
            default => ucwords(str_replace('_', ' ', $type)),
        };
    }

    public function orderedPaidMembershipPlans(): Collection
    {
        return Membership::query()
            ->where('price_per_year', '>', 0)
            ->whereRaw('LOWER(TRIM(plan_name)) NOT IN (?, ?)', ['free', 'free plan'])
            ->orderBy('price_per_year')
            ->orderBy('plan_name')
            ->get();
    }

    public function resolveNextHigherPlan(User $subscriber): ?Membership
    {
        $current = $this->membershipPlan($subscriber);
        if (!$current || $this->isFreePlan($current)) {
            return null;
        }

        $currentPrice = (float) $current->price_per_year;

        return $this->orderedPaidMembershipPlans()
            ->first(fn (Membership $plan) => (float) $plan->price_per_year > $currentPrice);
    }

    /**
     * @return array{previous_plan: string, new_plan: string}|null
     */
    public function applyFreeUpgrade(User $subscriber): ?array
    {
        $nextPlan = $this->resolveNextHigherPlan($subscriber);
        if (!$nextPlan) {
            return null;
        }

        $previousPlan = trim((string) $subscriber->membership);
        $newPlan = trim((string) $nextPlan->plan_name);

        if ($previousPlan === '' || strcasecmp($previousPlan, $newPlan) === 0) {
            return null;
        }

        $subscriber->membership = $newPlan;

        if (strcasecmp((string) $subscriber->membership_type, 'Free') === 0) {
            $subscriber->membership_type = 'Subscription';
        }

        $staffUpdate = [
            'membership' => $newPlan,
        ];

        if (!empty($subscriber->membership_type)) {
            $staffUpdate['membership_type'] = $subscriber->membership_type;
        }

        User::where('added_by', $subscriber->id)->update($staffUpdate);

        try {
            app(UserJourneyLogService::class)->logSubscriptionPurchase(
                $subscriber,
                'upgrade',
                $newPlan,
                1,
                (int) $subscriber->id,
                ['previous_plan' => $previousPlan, 'free_upgrade' => true]
            );
        } catch (\Throwable $journeyError) {
            Log::warning('Free upgrade journey log failed', [
                'subscriber_id' => $subscriber->id,
                'error' => $journeyError->getMessage(),
            ]);
        }

        return [
            'previous_plan' => $previousPlan,
            'new_plan' => $newPlan,
        ];
    }

    public function resolveSubscriber(User $user): User
    {
        if ($user->user_type === 'Subscriber') {
            return $user;
        }

        if (!empty($user->added_by)) {
            $subscriber = User::find($user->added_by);
            if ($subscriber) {
                return $subscriber;
            }
        }

        return $user;
    }

    /**
     * Subscriber account whose D & O history may be shown to the given actor.
     */
    public function resolveSubscriberIdForHistory(User $user): ?int
    {
        $subscriber = $this->resolveSubscriber($user);

        return $subscriber->user_type === 'Subscriber'
            ? (int) $subscriber->id
            : null;
    }

    public function membershipPlan(User $subscriber): ?Membership
    {
        if (empty($subscriber->membership)) {
            return null;
        }

        return Membership::where('plan_name', $subscriber->membership)->first();
    }

    public function isFreePlan(?Membership $plan): bool
    {
        if (!$plan) {
            return true;
        }

        $planName = strtolower(trim((string) $plan->plan_name));

        return (float) ($plan->price_per_year ?? 0) <= 0
            || in_array($planName, ['free', 'free plan'], true);
    }

    public function isPaidPlanSubscriber(User $subscriber): bool
    {
        if ($subscriber->user_type !== 'Subscriber') {
            return false;
        }

        return !$this->isFreePlan($this->membershipPlan($subscriber));
    }

    /**
     * Subscribers on paid membership plans (excludes Free / Free Plan).
     */
    public function paidSubscribersQuery(): Builder
    {
        return User::query()
            ->where('user_type', 'Subscriber')
            ->whereNotNull('membership')
            ->where('membership', '!=', '')
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('membership')
                    ->whereColumn('membership.plan_name', 'users.membership')
                    ->where('membership.price_per_year', '>', 0)
                    ->whereRaw('LOWER(TRIM(membership.plan_name)) NOT IN (?, ?)', ['free', 'free plan']);
            });
    }

    public function isSubscriptionActive(User $subscriber, ?Carbon $onDate = null): bool
    {
        if (empty($subscriber->membership_expiry_date)) {
            return true;
        }

        $onDate = ($onDate ?? now())->endOfDay();

        return Carbon::parse($subscriber->membership_expiry_date)->endOfDay()->gte($onDate);
    }

    public function isUnlimitedMessages(?string $messaging): bool
    {
        return strcasecmp(trim((string) $messaging), 'Unlimited') === 0;
    }

    public function isOfferCurrentlyActive(Offers $offer, ?Carbon $onDate = null): bool
    {
        $type = (string) $offer->discount_type;
        $onDate = ($onDate ?? now())->startOfDay();

        if ($type === 'analytics_on' && $offer->user_id !== null) {
            return true;
        }

        if ($offer->user_id !== null && $this->isLimitBoostOfferType($type)) {
            return $this->isAssignedBenefitOfferActive($offer, $onDate);
        }

        if ($this->isManualType($type)) {
            return $type === 'double_term';
        }

        if (!$this->isAutomatedType($type)) {
            return false;
        }

        $startDate = $offer->offer_start_date;
        $endDate = $offer->offer_end_date;

        if (empty($startDate) && empty($endDate)) {
            return true;
        }

        if (!empty($startDate)) {
            $start = Carbon::parse(self::normalizeStorageDate((string) $startDate))->startOfDay();
            if ($onDate->lt($start)) {
                return false;
            }
        }

        if (!empty($endDate)) {
            $end = Carbon::parse(self::normalizeStorageDate((string) $endDate))->endOfDay();
            if ($onDate->gt($end)) {
                return false;
            }
        }

        return true;
    }

    private function isAssignedBenefitOfferActive(Offers $offer, Carbon $onDate): bool
    {
        $startDate = $offer->offer_start_date;
        $endDate = $offer->offer_end_date;

        if (!empty($startDate)) {
            $start = Carbon::parse(self::normalizeStorageDate((string) $startDate))->startOfDay();
            if ($onDate->lt($start)) {
                return false;
            }
        }

        if (!empty($endDate)) {
            $end = Carbon::parse(self::normalizeStorageDate((string) $endDate))->endOfDay();

            return $onDate->lte($end);
        }

        $appliedAt = $offer->created_at
            ? Carbon::parse($offer->created_at)->startOfDay()
            : $onDate->copy();

        return $onDate->lte($appliedAt->copy()->addYear()->endOfDay());
    }

    /**
     * @return array{offer_start_date: ?string, offer_end_date: ?string}
     */
    public function manualOfferValidityDates(string $type): array
    {
        if ($this->offerFrequencyRule($type) === 'annual') {
            return [
                'offer_start_date' => now()->format('Y-m-d'),
                'offer_end_date' => now()->addYear()->format('Y-m-d'),
            ];
        }

        return [
            'offer_start_date' => null,
            'offer_end_date' => null,
        ];
    }

    public function subscriberIsEligibleForAdminOfferPicker(User $subscriber, string $type): bool
    {
        if (!$this->isEligibleForOfferBenefits($subscriber)) {
            return false;
        }

        if (!$this->subscriberCanReceiveOffer($subscriber, $type)) {
            return false;
        }

        if ($type === 'analytics_on' && $this->hasAnalyticsAccess($subscriber)) {
            return false;
        }

        if ($type === 'unlimited_associates' && $this->hasUnlimitedAssociatesOffer($subscriber)) {
            return false;
        }

        if ($type === 'double_associates' && $this->getLimitMultiplier($subscriber, 'associates') >= 2.0) {
            return false;
        }

        if ($type === 'free_upgrade' && !$this->resolveNextHigherPlan($subscriber)) {
            return false;
        }

        return true;
    }

    /**
     * @return array<int, array<string, bool>>
     */
    public function subscriberOfferEligibilityMap(iterable $subscribers): array
    {
        $map = [];

        foreach ($subscribers as $subscriber) {
            $map[(int) $subscriber->id] = [];

            foreach (self::allOfferTypeKeys() as $type) {
                $map[(int) $subscriber->id][$type] = $this->subscriberIsEligibleForAdminOfferPicker($subscriber, $type);
            }
        }

        return $map;
    }

    public function getActiveOffers(User $subscriber, ?Carbon $onDate = null): Collection
    {
        if (!$this->isEligibleForOfferBenefits($subscriber)) {
            return collect();
        }

        if (!$this->isSubscriptionActive($subscriber, $onDate)) {
            return collect();
        }

        $onDate = $onDate ?? now();

        $userOffers = Offers::where('user_id', $subscriber->id)
            ->get()
            ->filter(fn (Offers $offer) => $this->isOfferCurrentlyActive($offer, $onDate));

        $appliedTypes = $userOffers->pluck('discount_type')->map(fn ($type) => (string) $type)->all();

        $campaignOffers = $this->eligibleAutomatedCampaignsForSubscriber($subscriber, $onDate)
            ->reject(fn (Offers $campaign) => in_array((string) $campaign->discount_type, $appliedTypes, true))
            ->reject(fn (Offers $campaign) => $this->subscriberHasReceivedCampaign($subscriber, $campaign))
            ->filter(fn (Offers $campaign) => $this->subscriberCanReceiveOffer($subscriber, (string) $campaign->discount_type, $onDate))
            ->each(fn (Offers $campaign) => $this->ensurePassiveCampaignApplicationRecorded($subscriber, $campaign));

        return $userOffers->merge($campaignOffers);
    }

    public function isEligibleForOfferBenefits(User $subscriber): bool
    {
        return $this->isPaidPlanSubscriber($subscriber);
    }

    public function isPaidSubscriptionSubscriber(User $subscriber): bool
    {
        return $this->isEligibleForOfferBenefits($subscriber);
    }

    public function isLoyalSubscriber(User $subscriber): bool
    {
        $loyalSince = !empty($subscriber->membership_start_date)
            ? Carbon::parse($subscriber->membership_start_date)
            : Carbon::parse($subscriber->created_at);

        return $loyalSince->lte(now()->subYears(5));
    }

    public function subscriberMatchesCampaignAudience(User $subscriber, Offers $campaign, ?Carbon $onDate = null): bool
    {
        if (!$this->isPaidSubscriptionSubscriber($subscriber)) {
            return false;
        }

        if (!$this->subscriberMatchesApplicablePlans($subscriber, $campaign->applicable_plans ?? null)) {
            return false;
        }

        $audience = (string) $campaign->subscriber_type;

        return match ($audience) {
            'existing' => true,
            'loyal' => $this->isLoyalSubscriber($subscriber),
            'new' => !empty($campaign->offer_start_date) && !empty($campaign->offer_end_date)
                && Carbon::parse($subscriber->created_at)->between(
                    Carbon::parse(self::normalizeStorageDate((string) $campaign->offer_start_date))->startOfDay(),
                    Carbon::parse(self::normalizeStorageDate((string) $campaign->offer_end_date))->endOfDay()
                ),
            default => false,
        };
    }

    /**
     * @return Collection<int, Offers>
     */
    public function eligibleAutomatedCampaignsForSubscriber(User $subscriber, ?Carbon $onDate = null): Collection
    {
        if (!$this->isPaidSubscriptionSubscriber($subscriber)) {
            return collect();
        }

        $onDate = $onDate ?? now();

        return Offers::query()
            ->whereNull('user_id')
            ->whereIn('subscriber_type', ['existing', 'loyal', 'new'])
            ->whereIn('discount_type', self::AUTOMATED_TYPES)
            ->get()
            ->filter(function (Offers $campaign) use ($subscriber, $onDate) {
                return $this->isOfferCurrentlyActive($campaign, $onDate)
                    && $this->subscriberMatchesCampaignAudience($subscriber, $campaign, $onDate);
            });
    }

    public function getLimitMultiplier(User $subscriber, string $feature, ?Carbon $onDate = null): float
    {
        if (!$this->isPaidPlanSubscriber($subscriber)) {
            return 1.0;
        }

        if (!$this->isSubscriptionActive($subscriber, $onDate)) {
            return 1.0;
        }

        $multiplier = 1.0;

        foreach ($this->getActiveOffers($subscriber, $onDate) as $offer) {
            $type = (string) $offer->discount_type;

            if (in_array($type, ['double_term', 'double_features'], true)) {
                $multiplier = max($multiplier, 2.0);
                continue;
            }

            if ($type === 'double_users' && $feature === 'users') {
                $multiplier = max($multiplier, 2.0);
            }

            if ($type === 'double_clients' && $feature === 'clients') {
                $multiplier = max($multiplier, 2.0);
            }

            if ($type === 'double_messages' && $feature === 'messages') {
                $multiplier = max($multiplier, 2.0);
            }

            if ($type === 'double_associates' && $feature === 'associates') {
                $multiplier = max($multiplier, 2.0);
            }
        }

        return $multiplier;
    }

    public function hasUnlimitedAssociatesOffer(User $subscriber, ?Carbon $onDate = null): bool
    {
        foreach ($this->getActiveOffers($subscriber, $onDate) as $offer) {
            if ((string) $offer->discount_type === 'unlimited_associates') {
                return true;
            }
        }

        return false;
    }

    public function subscriptionDurationYears(User $user): int
    {
        $subscriber = $this->resolveSubscriber($user);

        if (!empty($subscriber->membership_start_date) && !empty($subscriber->membership_expiry_date)) {
            $start = Carbon::parse($subscriber->membership_start_date)->startOfDay();
            $expiry = Carbon::parse($subscriber->membership_expiry_date)->startOfDay();

            if ($expiry->gt($start)) {
                $years = (int) $start->diffInYears($expiry);

                return max(1, $years > 0 ? $years : 1);
            }
        }

        if (!empty($subscriber->membership_expiry_date)) {
            $currentDate = Carbon::now()->startOfDay();
            $expiryDate = Carbon::parse($subscriber->membership_expiry_date)->startOfDay();

            if ($expiryDate->gte($currentDate)) {
                $years = (int) $currentDate->diffInYears($expiryDate);

                return max(1, $years > 0 ? $years : 1);
            }
        }

        $plan = $this->membershipPlan($subscriber);
        if ($plan) {
            $planValidityDays = max(1, (int) ($plan->validity ?? 365));
            $years = (int) floor($planValidityDays / 365);

            if ($years >= 1) {
                return $years;
            }
        }

        return 1;
    }

    public function effectiveUserLimit(User $user): int
    {
        $subscriber = $this->resolveSubscriber($user);
        $plan = $this->membershipPlan($subscriber);

        if (!$plan) {
            return 0;
        }

        $years = $this->subscriptionDurationYears($user);

        return (int) round($plan->no_of_users * $years * $this->getLimitMultiplier($subscriber, 'users'));
    }

    public function effectiveClientLimit(User $user): int|string
    {
        $subscriber = $this->resolveSubscriber($user);
        $plan = $this->membershipPlan($subscriber);

        if (!$plan) {
            return 0;
        }

        if (strcasecmp((string) $plan->client_limit, 'Unlimited') === 0) {
            return 'Unlimited';
        }

        $years = $this->subscriptionDurationYears($user);
        $perYear = (int) $plan->client_limit;
        $multiplier = $this->getLimitMultiplier($subscriber, 'clients');

        return (int) round($perYear * $years * $multiplier);
    }

    public function effectiveMessageLimit(User $user): int|string
    {
        $subscriber = $this->resolveSubscriber($user);
        $plan = $this->membershipPlan($subscriber);

        if (!$plan) {
            return 0;
        }

        if ($this->isUnlimitedMessages($plan->messaging)) {
            return 'Unlimited';
        }

        $years = $this->subscriptionDurationYears($user);
        $limit = (int) preg_replace('/\D/', '', (string) $plan->messaging);

        return (int) round($limit * $years * $this->getLimitMultiplier($subscriber, 'messages'));
    }

    public function baseUserLimit(User $user): int
    {
        $subscriber = $this->resolveSubscriber($user);
        $plan = $this->membershipPlan($subscriber);

        if (!$plan) {
            return 0;
        }

        $years = $this->subscriptionDurationYears($user);

        return (int) round($plan->no_of_users * $years);
    }

    public function baseClientLimit(User $user): int|string
    {
        $subscriber = $this->resolveSubscriber($user);
        $plan = $this->membershipPlan($subscriber);

        if (!$plan) {
            return 0;
        }

        if (strcasecmp((string) $plan->client_limit, 'Unlimited') === 0) {
            return 'Unlimited';
        }

        $years = $this->subscriptionDurationYears($user);
        $perYear = (int) $plan->client_limit;

        return (int) round($perYear * $years);
    }

    public function baseMessageLimit(User $user): int|string
    {
        $subscriber = $this->resolveSubscriber($user);
        $plan = $this->membershipPlan($subscriber);

        if (!$plan) {
            return 0;
        }

        if ($this->isUnlimitedMessages($plan->messaging)) {
            return 'Unlimited';
        }

        $years = $this->subscriptionDurationYears($user);
        $limit = (int) preg_replace('/\D/', '', (string) $plan->messaging);

        return (int) round($limit * $years);
    }

    public function currentMessageCount(User $user): int
    {
        $subscriber = $this->resolveSubscriber($user);
        $query = Internal_communications::where('subscriber_id', $subscriber->id);

        if (!empty($subscriber->membership_start_date)) {
            $query->where(
                'created_at',
                '>=',
                Carbon::parse($subscriber->membership_start_date)->startOfDay()
            );
        }

        return $query->count();
    }

    public function canSendMessage(User $user): bool
    {
        $subscriber = $this->resolveSubscriber($user);
        $messageLimit = $this->effectiveMessageLimit($subscriber);

        if ($messageLimit === 'Unlimited') {
            return true;
        }

        return $this->currentMessageCount($subscriber) < (int) $messageLimit;
    }

    /**
     * @param int|string|null $base
     * @param int|string|null $effective
     * @return array{base: string, effective: string, boosted: bool}
     */
    public function limitDisplayPair(int|string|null $base, int|string|null $effective, bool $boosted): array
    {
        $baseLabel = $this->formatLimitLabel($base);
        $effectiveLabel = $this->formatLimitLabel($effective);
        $showBoost = $boosted && $baseLabel !== $effectiveLabel;

        return [
            'base' => $baseLabel,
            'effective' => $effectiveLabel,
            'boosted' => $showBoost,
        ];
    }

    public function formatLimitLabel(int|string|null $value): string
    {
        if ($value === null || $value === '') {
            return '0';
        }

        if (is_string($value) && strcasecmp(trim($value), 'Unlimited') === 0) {
            return 'Unlimited';
        }

        if (is_numeric($value)) {
            return number_format((float) $value, 0, '.', ',');
        }

        return (string) $value;
    }

    public function currentClientCount(User $user): int
    {
        $subscriber = $this->resolveSubscriber($user);

        return Clients::where('subscriber_id', $subscriber->id)->count();
    }

    public function canAddClient(User $user): bool
    {
        $subscriber = $this->resolveSubscriber($user);
        $clientLimit = $this->effectiveClientLimit($subscriber);

        if ($clientLimit === 'Unlimited') {
            return true;
        }

        return $this->currentClientCount($subscriber) < (int) $clientLimit;
    }

    public function canAddUser(User $user): bool
    {
        $subscriber = $this->resolveSubscriber($user);
        $siteusers = User::where('added_by', $subscriber->id)->count();

        return $siteusers < $this->effectiveUserLimit($subscriber);
    }

    /**
     * Associate allowance = the plan's no_of_associates column (a flat per-plan
     * limit). Unlike users/clients it is NOT multiplied by the subscription
     * duration, so e.g. Enterprise stays at 100 regardless of a multi-year term.
     */
    public function baseAssociateLimit(User $user): int
    {
        $subscriber = $this->resolveSubscriber($user);
        $plan = $this->membershipPlan($subscriber);

        if (!$plan) {
            return 0;
        }

        return (int) ($plan->no_of_associates ?? 0);
    }

    public function effectiveAssociateLimit(User $user): int|string
    {
        $subscriber = $this->resolveSubscriber($user);

        if ($this->hasUnlimitedAssociatesOffer($subscriber)) {
            return 'Unlimited';
        }

        $base = $this->baseAssociateLimit($subscriber);
        $multiplier = $this->getLimitMultiplier($subscriber, 'associates');

        return (int) round($base * $multiplier);
    }

    public function canAddAssociate(User $user): bool
    {
        $subscriber = $this->resolveSubscriber($user);
        $associateLimit = $this->effectiveAssociateLimit($subscriber);

        if ($associateLimit === 'Unlimited') {
            return true;
        }

        $count = Associate::where('added_by', $subscriber->id)->count();

        return $count < (int) $associateLimit;
    }

    /**
     * Limits shown on the Subscription module (includes active offers).
     *
     * @return array{
     *     client_limit: int|string,
     *     client_limit_display: array{base: string, effective: string, boosted: bool},
     *     user_limit: int,
     *     user_limit_display: array{base: string, effective: string, boosted: bool},
     *     associate_limit: int|string,
     *     associate_limit_display: array{base: string, effective: string, boosted: bool},
     *     message_limit: int|string,
     *     message_limit_label: string,
     *     message_limit_display: array{base: string, effective: string, boosted: bool},
     *     analytics: string,
     *     analytics_display: array{base: string, effective: string, boosted: bool},
     *     active_offer_labels: array<int, string>,
     *     subscription_active: bool
     * }
     */
    public function effectiveLimitsForDisplay(User $user): array
    {
        $subscriber = $this->resolveSubscriber($user);
        $plan = $this->membershipPlan($subscriber);
        $subscriptionActive = $this->isSubscriptionActive($subscriber);
        $activeOffers = $this->getActiveOffers($subscriber);
        $activeOfferLabels = $activeOffers
            ->map(fn (Offers $offer) => $this->offerTypeLabel((string) $offer->discount_type))
            ->unique()
            ->values()
            ->all();

        if (!$plan) {
            return [
                'client_limit' => 0,
                'client_limit_display' => $this->limitDisplayPair(0, 0, false),
                'user_limit' => 0,
                'user_limit_display' => $this->limitDisplayPair(0, 0, false),
                'associate_limit' => 0,
                'associate_limit_display' => $this->limitDisplayPair(0, 0, false),
                'message_limit' => 0,
                'message_limit_label' => '0',
                'message_limit_display' => $this->limitDisplayPair(0, 0, false),
                'analytics' => 'No',
                'analytics_display' => $this->limitDisplayPair('No', 'No', false),
                'active_offer_labels' => [],
                'subscription_active' => $subscriptionActive,
            ];
        }

        $baseUserLimit = $this->baseUserLimit($subscriber);
        $baseClientLimit = $this->baseClientLimit($subscriber);
        $baseMessageLimit = $this->baseMessageLimit($subscriber);
        $baseAnalytics = strcasecmp((string) $plan->analytics, 'Yes') === 0 ? 'Yes' : 'No';

        if (!$subscriptionActive) {
            return [
                'client_limit' => $baseClientLimit,
                'client_limit_display' => $this->limitDisplayPair($baseClientLimit, $baseClientLimit, false),
                'user_limit' => $baseUserLimit,
                'user_limit_display' => $this->limitDisplayPair($baseUserLimit, $baseUserLimit, false),
                'associate_limit' => $this->baseAssociateLimit($subscriber),
                'associate_limit_display' => $this->limitDisplayPair(
                    $this->baseAssociateLimit($subscriber),
                    $this->baseAssociateLimit($subscriber),
                    false
                ),
                'message_limit' => $baseMessageLimit,
                'message_limit_label' => $this->formatLimitLabel($baseMessageLimit),
                'message_limit_display' => $this->limitDisplayPair($baseMessageLimit, $baseMessageLimit, false),
                'analytics' => $baseAnalytics,
                'analytics_display' => $this->limitDisplayPair($baseAnalytics, $baseAnalytics, false),
                'active_offer_labels' => [],
                'subscription_active' => false,
            ];
        }

        $clientMultiplier = $this->getLimitMultiplier($subscriber, 'clients');
        $userMultiplier = $this->getLimitMultiplier($subscriber, 'users');
        $messageMultiplier = $this->getLimitMultiplier($subscriber, 'messages');
        $associateMultiplier = $this->getLimitMultiplier($subscriber, 'associates');
        $effectiveClientLimit = $this->effectiveClientLimit($subscriber);
        $effectiveUserLimit = $this->effectiveUserLimit($subscriber);
        $effectiveMessageLimit = $this->effectiveMessageLimit($subscriber);
        $baseAssociateLimit = $this->baseAssociateLimit($subscriber);
        $effectiveAssociateLimit = $this->effectiveAssociateLimit($subscriber);
        $analyticsEnabled = $this->hasAnalyticsAccess($subscriber);
        $effectiveAnalytics = $analyticsEnabled ? 'Yes' : 'No';

        return [
            'client_limit' => $effectiveClientLimit,
            'client_limit_display' => $this->limitDisplayPair(
                $baseClientLimit,
                $effectiveClientLimit,
                $clientMultiplier > 1.0
            ),
            'user_limit' => $effectiveUserLimit,
            'user_limit_display' => $this->limitDisplayPair(
                $baseUserLimit,
                $effectiveUserLimit,
                $userMultiplier > 1.0
            ),
            'associate_limit' => $effectiveAssociateLimit,
            'associate_limit_display' => $this->limitDisplayPair(
                $baseAssociateLimit,
                $effectiveAssociateLimit,
                $associateMultiplier > 1.0 || $effectiveAssociateLimit === 'Unlimited'
            ),
            'message_limit' => $effectiveMessageLimit,
            'message_limit_label' => $this->formatLimitLabel($effectiveMessageLimit),
            'message_limit_display' => $this->limitDisplayPair(
                $baseMessageLimit,
                $effectiveMessageLimit,
                $messageMultiplier > 1.0 && !$this->isUnlimitedMessages($plan->messaging)
            ),
            'analytics' => $effectiveAnalytics,
            'analytics_display' => $this->limitDisplayPair(
                $baseAnalytics,
                $effectiveAnalytics,
                $analyticsEnabled && $baseAnalytics !== 'Yes'
            ),
            'active_offer_labels' => $activeOfferLabels,
            'subscription_active' => true,
        ];
    }

    public function hasAnalyticsAccess(User $user): bool
    {
        if ($user->user_type === 'admin') {
            return true;
        }

        $subscriber = $this->resolveSubscriber($user);

        // Match sidebar eligibility: these plan names always expose Analytics.
        if (in_array((string) $subscriber->membership, ['Adwiseri', 'Adwiseri+', 'Enterprise'], true)) {
            return true;
        }

        $plan = $this->membershipPlan($subscriber);

        if ($plan && strcasecmp((string) $plan->analytics, 'Yes') === 0) {
            return true;
        }

        if (!$this->isSubscriptionActive($subscriber)) {
            return false;
        }

        return $this->getActiveOffers($subscriber)
            ->contains(fn (Offers $offer) => $offer->discount_type === 'analytics_on');
    }

    public function applyImmediateEffects(User $subscriber, string $type): void
    {
        $expiry = $this->resolveSubscriptionExpiry($subscriber);

        if (!$expiry) {
            return;
        }

        switch ($type) {
            case 'double_term':
                $subscriber->membership_expiry_date = $expiry->copy()->addYear()->toDateString();
                break;
            case '3_months_extra':
                $subscriber->membership_expiry_date = $expiry->copy()->addMonths(3)->toDateString();
                break;
            case '6_months_extra':
                $subscriber->membership_expiry_date = $expiry->copy()->addMonths(6)->toDateString();
                break;
        }
    }

    private function resolveSubscriptionExpiry(User $subscriber): ?Carbon
    {
        if (!empty($subscriber->membership_expiry_date)) {
            return Carbon::parse($subscriber->membership_expiry_date)->startOfDay();
        }

        if (empty($subscriber->membership_start_date)) {
            return null;
        }

        $plan = $this->membershipPlan($subscriber);
        $planValidityDays = max(1, (int) ($plan->validity ?? 365));

        return Carbon::parse($subscriber->membership_start_date)->startOfDay()->addDays($planValidityDays);
    }

    public function offerRewardDescription(string $type, User $subscriber, array $context = []): string
    {
        return $this->buildOfferEmailDetails($type, $subscriber, $context)['offer_description'];
    }

    /**
     * @return array{
     *     offer_label: string,
     *     offer_description: string,
     *     credit_label: ?string,
     *     credit_value: ?string,
     *     credit_is_html: bool
     * }
     */
    public function buildOfferEmailDetails(string $type, User $subscriber, array $context = []): array
    {
        $creditAmount = (float) ($context['credit_amount'] ?? 0);
        $discountValue = (float) ($context['discount_value'] ?? 0);

        $details = [
            'offer_label' => $this->offerTypeLabel($type),
            'offer_description' => $this->offerEmailSummary($type, $context),
            'credit_label' => null,
            'credit_value' => null,
            'credit_is_html' => false,
        ];

        if ($this->isOneOffCreditType($type)) {
            $details['credit_label'] = 'Credit (Wallet)';
            $amount = $creditAmount > 0 ? $creditAmount : $discountValue;
            $details['credit_value'] = 'USD ' . number_format($amount, 2);

            return $details;
        }

        switch ($type) {
            case 'double_users':
                $details['credit_label'] = 'Credit (Users)';
                $details['credit_value'] = (string) $this->effectiveUserLimit($subscriber);
                break;
            case 'double_clients':
                $details['credit_label'] = 'Credit (Clients)';
                $details['credit_value'] = (string) $this->effectiveClientLimit($subscriber);
                break;
            case 'double_messages':
                $details['credit_label'] = 'Credit (Messages)';
                $details['credit_value'] = $this->formatLimitLabel($this->effectiveMessageLimit($subscriber));
                break;
            case 'double_associates':
                $details['credit_label'] = 'Credit (Associates)';
                $details['credit_value'] = $this->formatLimitLabel($this->effectiveAssociateLimit($subscriber));
                break;
            case 'unlimited_associates':
                $details['credit_label'] = 'Credit (Associates)';
                $details['credit_value'] = 'Unlimited';
                break;
            case 'double_features':
                $details['credit_label'] = 'Credit (All Features)';
                $users = $this->effectiveUserLimit($subscriber);
                $clients = $this->effectiveClientLimit($subscriber);
                $messages = $this->formatLimitLabel($this->effectiveMessageLimit($subscriber));
                $associates = $this->formatLimitLabel($this->effectiveAssociateLimit($subscriber));
                $clientLine = $clients === 'Unlimited'
                    ? 'Unlimited Clients'
                    : number_format((int) $clients) . ' Clients';
                $details['credit_value'] = "{$users} User License<br>{$clientLine}<br>{$messages} Messages<br>{$associates} Associates";
                $details['credit_is_html'] = true;
                break;
            case 'cashback':
                $details['credit_label'] = 'Credit (Wallet)';
                $details['credit_value'] = 'USD ' . number_format($creditAmount, 2);
                break;
            case 'double_term':
                $details['credit_label'] = 'Credit (Subscription Term)';
                $details['credit_value'] = '1 additional year';
                break;
            case '3_months_extra':
                $details['credit_label'] = 'Credit (Subscription Term)';
                $details['credit_value'] = '3 months';
                break;
            case '6_months_extra':
                $details['credit_label'] = 'Credit (Subscription Term)';
                $details['credit_value'] = '6 months';
                break;
            case 'analytics_on':
                $details['credit_label'] = 'Credit (Analytics)';
                $details['credit_value'] = 'Enabled';
                break;
            case 'free_upgrade':
                $previousPlan = trim((string) ($context['previous_plan'] ?? ''));
                $newPlan = trim((string) ($context['new_plan'] ?? ''));
                $details['credit_label'] = 'New Plan';
                $details['credit_value'] = $newPlan !== '' ? $newPlan : 'Next tier plan';
                if ($previousPlan !== '' && $newPlan !== '') {
                    $details['offer_description'] = sprintf(
                        'Your subscription has been upgraded from %s to %s at no charge',
                        $previousPlan,
                        $newPlan
                    );
                }
                break;
        }

        return $details;
    }

    public function subscriberNameForEmail(User $subscriber): string
    {
        return trim((string) ($subscriber->name ?? ''));
    }

    public function canSendOfferRewardEmail(User $subscriber): bool
    {
        return trim((string) ($subscriber->email ?? '')) !== '';
    }

    public function offerNotificationName(User $subscriber): string
    {
        $name = $this->subscriberNameForEmail($subscriber);

        return strlen($name) >= 3 ? $name : 'Subscriber';
    }

    /**
     * @return array<string, mixed>
     */
    public function buildOfferRewardMailPayload(User $subscriber, string $type, array $context = []): array
    {
        return array_merge(
            $this->buildOfferEmailDetails($type, $subscriber, $context),
            [
                'name' => $this->offerNotificationName($subscriber),
                'email' => trim((string) ($subscriber->email ?? '')),
            ]
        );
    }

    /**
     * @param  array<string, mixed>  $mailPayload
     */
    public function sendOfferRewardConfirmationEmail(array $mailPayload, string $offerType = ''): bool
    {
        $email = trim((string) ($mailPayload['email'] ?? ''));

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $mailPayload['name'] = trim((string) ($mailPayload['name'] ?? ''));
        if ($mailPayload['name'] === '' || strlen($mailPayload['name']) < 3) {
            $mailPayload['name'] = 'Subscriber';
        }

        try {
            Mail::to($email)->send(new OfferRewardMail($mailPayload));

            return true;
        } catch (\Throwable $mailException) {
            Log::warning('Offer reward email could not be delivered', [
                'email' => $email,
                'offer_type' => $offerType,
                'error' => $mailException->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $mailPayloads
     * @return array<int, string>
     */
    public function sendOfferRewardConfirmationEmails(array $mailPayloads, string $offerType = ''): array
    {
        $failures = [];

        foreach ($mailPayloads as $mailPayload) {
            $email = trim((string) ($mailPayload['email'] ?? ''));

            if ($email === '') {
                continue;
            }

            if (!$this->sendOfferRewardConfirmationEmail($mailPayload, $offerType)) {
                $failures[] = $email;
            }
        }

        return $failures;
    }

    private function offerEmailSummary(string $type, array $context = []): string
    {
        $discountValue = (float) ($context['discount_value'] ?? 0);

        if ($this->isOneOffCreditType($type)) {
            return $this->offerTypeLabel($type) . ' has been added to your wallet';
        }

        return match ($type) {
            'cashback' => sprintf(
                'Cashback of %s%% has been added to your wallet',
                rtrim(rtrim(number_format($discountValue, 2, '.', ''), '0'), '.')
            ),
            'double_term' => 'Your subscription term will be extended by one year',
            '3_months_extra' => 'Your subscription term will be extended by 3 months',
            '6_months_extra' => 'Your subscription term will be extended by 6 months',
            'double_features' => 'All feature limits will be doubled',
            'double_users' => 'User License limits will be doubled',
            'double_clients' => 'Client limits will be doubled',
            'double_messages' => 'Message limits will be doubled',
            'double_associates' => 'Associate limits will be doubled',
            'unlimited_associates' => 'Unlimited associates will be enabled on your account',
            'analytics_on' => 'Analytics access will be enabled on your account',
            'free_upgrade' => isset($context['previous_plan'], $context['new_plan'])
                ? sprintf(
                    'Your subscription has been upgraded from %s to %s at no charge',
                    $context['previous_plan'],
                    $context['new_plan']
                )
                : 'Your subscription has been upgraded to the next plan tier at no charge',
            default => $this->offerTypeLabel($type) . ' has been applied to your account',
        };
    }

    /**
     * Admin-facing description for Applied D & O history (not subscriber email copy).
     */
    private function offerHistorySummary(string $type, array $context = []): string
    {
        $discountValue = (float) ($context['discount_value'] ?? 0);

        if ($this->isOneOffCreditType($type)) {
            return $this->offerTypeLabel($type) . ' has been added to the wallet';
        }

        return match ($type) {
            'cashback' => sprintf(
                'Cashback of %s%% has been added to the wallet',
                rtrim(rtrim(number_format($discountValue, 2, '.', ''), '0'), '.')
            ),
            'double_term' => 'Subscription term will be extended by one year',
            '3_months_extra' => 'Subscription will be extended for 3 months',
            '6_months_extra' => 'Subscription will be extended for 6 months',
            'double_features' => 'All feature limits will be doubled',
            'double_users' => 'User License limits will be doubled',
            'double_clients' => 'Client limits will be doubled',
            'double_messages' => 'Message limits will be doubled',
            'double_associates' => 'Associate limits will be doubled',
            'unlimited_associates' => 'Unlimited associates will be enabled',
            'analytics_on' => 'Analytics access will be enabled',
            'free_upgrade' => isset($context['previous_plan'], $context['new_plan'])
                ? sprintf(
                    'Plan upgraded from %s to %s at no charge',
                    $context['previous_plan'],
                    $context['new_plan']
                )
                : 'Subscription plan upgraded to the next tier at no charge',
            default => $this->offerTypeLabel($type) . ' has been applied',
        };
    }

    public static function allOfferTypeKeys(): array
    {
        return array_values(array_unique(array_merge(self::MANUAL_TYPES, self::AUTOMATED_TYPES, ['one_off'])));
    }

    public function offerModeLabel(string $type): string
    {
        return $this->isManualType($type) ? 'Manual' : 'Automated';
    }

    /**
     * Manual / Automated label for Applied D & O history (based on how it was applied).
     */
    public function offerModeLabelForRecord(?Offers $offer, string $fallbackType = ''): string
    {
        if ($offer) {
            if ($offer->user_id === null) {
                return 'Automated';
            }

            $type = (string) $offer->discount_type;

            if ($this->isManualType($type)) {
                return 'Manual';
            }

            if ($this->isAutomatedType($type) && $offer->offer_start_date && $offer->offer_end_date) {
                return 'Automated';
            }

            return 'Manual';
        }

        return $this->isManualType($fallbackType) ? 'Manual' : 'Automated';
    }

    public function offerHistoryShowsDateRange(Offers $offer): bool
    {
        if ($offer->user_id === null) {
            return (bool) ($offer->offer_start_date || $offer->offer_end_date);
        }

        return $this->isAutomatedType((string) $offer->discount_type)
            && $offer->offer_start_date
            && $offer->offer_end_date;
    }

    public function formatOfferValidityRange(Offers $offer): string
    {
        $start = $offer->offer_start_date
            ? Carbon::parse($offer->offer_start_date)->format('d-m-Y')
            : null;
        $end = $offer->offer_end_date
            ? Carbon::parse($offer->offer_end_date)->format('d-m-Y')
            : null;

        if ($start && $end) {
            return $start . ' to ' . $end;
        }

        if ($start) {
            return 'from ' . $start;
        }

        if ($end) {
            return 'until ' . $end;
        }

        return '';
    }

    public function isUncappedOfferType(string $type): bool
    {
        return $this->isOneOffCreditType($type) || $type === '3_months_extra';
    }

    public function isRepeatableOfferType(string $type): bool
    {
        return $this->isUncappedOfferType($type);
    }

    public function offerFrequencyRule(string $type): string
    {
        if ($this->isUncappedOfferType($type)) {
            return 'uncapped';
        }

        if ($type === 'double_term') {
            return 'subscription_term';
        }

        if ($type === 'analytics_on') {
            return 'subscriber_lifetime';
        }

        if ($type === 'free_upgrade') {
            return 'subscriber_lifetime';
        }

        return 'annual';
    }

    public function manualOfferIneligibilityMessage(string $type, int $selectedCount): string
    {
        if ($type === 'free_upgrade') {
            return $selectedCount === 1
                ? 'This subscriber is already on the highest available plan and cannot receive a free upgrade.'
                : 'None of the selected subscribers can receive a free upgrade because they are already on the highest available plan or have already received this offer.';
        }

        if ($selectedCount === 1) {
            return match ($this->offerFrequencyRule($type)) {
                'subscription_term' => 'This subscriber cannot receive this offer again within the same subscription period.',
                'subscriber_lifetime' => 'This subscriber has already received this offer.',
                'annual' => 'This subscriber cannot receive this offer again within the same year.',
                default => 'This subscriber cannot receive this offer at this time.',
            };
        }

        return match ($this->offerFrequencyRule($type)) {
            'subscription_term' => 'None of the selected subscribers can receive this offer within the same subscription period.',
            'subscriber_lifetime' => 'None of the selected subscribers can receive this offer because it was already granted previously.',
            'annual' => 'None of the selected subscribers can receive this offer within the same year.',
            default => 'None of the selected subscribers can receive this offer at this time.',
        };
    }

    public function manualOfferSkippedSummaryMessage(string $type, int $skippedCount): string
    {
        return match ($this->offerFrequencyRule($type)) {
            'subscription_term' => $skippedCount . ' subscriber(s) were skipped because they already received this offer within the same subscription period.',
            'subscriber_lifetime' => $skippedCount . ' subscriber(s) were skipped because they have already received this offer.',
            'annual' => $skippedCount . ' subscriber(s) were skipped because they already received this offer within the same year.',
            default => $skippedCount . ' subscriber(s) were skipped because they are not eligible for this offer.',
        };
    }

    /**
     * @param  array<int, string>  $skippedSubscribers
     */
    public function formatSkippedSubscribersMessage(array $skippedSubscribers, int $selectedCount): string
    {
        if ($selectedCount <= 1 || empty($skippedSubscribers)) {
            return '';
        }

        $preview = array_slice($skippedSubscribers, 0, 10);
        $message = "\n\nSkipped: " . implode(', ', $preview);

        if (count($skippedSubscribers) > 10) {
            $message .= ' and ' . (count($skippedSubscribers) - 10) . ' more.';
        }

        return $message;
    }

    public function subscriberCanReceiveOffer(User $subscriber, string $type, ?Carbon $onDate = null): bool
    {
        if (!$this->isEligibleForOfferBenefits($subscriber)) {
            return false;
        }

        return match ($this->offerFrequencyRule($type)) {
            'uncapped' => true,
            'subscription_term' => !$this->subscriberHasOfferInCurrentSubscription($subscriber, $type),
            'subscriber_lifetime' => !$this->subscriberHasEverReceivedOffer($subscriber, $type),
            'annual' => !$this->subscriberHasOfferInLastYear($subscriber, $type, $onDate ?? now()),
            default => true,
        };
    }

    public function subscriberHasOfferType(int $subscriberId, string $type): bool
    {
        $subscriber = User::find($subscriberId);

        if (!$subscriber) {
            return false;
        }

        return !$this->subscriberCanReceiveOffer($subscriber, $type);
    }

    /**
     * @return Collection<int, User>
     */
    public function filterEligibleSubscribersForOffer(iterable $subscribers, string $type, ?Carbon $onDate = null): Collection
    {
        return collect($subscribers)
            ->filter(fn (User $subscriber) => $this->subscriberCanReceiveOffer($subscriber, $type, $onDate))
            ->values();
    }

    /**
     * @return array<int, string>
     */
    public function findIneligibleSubscribersForAdminOfferPicker(iterable $subscribers, string $type, ?Carbon $onDate = null): array
    {
        $ineligible = [];

        foreach ($subscribers as $subscriber) {
            if (!$this->subscriberIsEligibleForAdminOfferPicker($subscriber, $type)) {
                $ineligible[] = $subscriber->name;
            }
        }

        return $ineligible;
    }

    /**
     * @return array<int, string>
     */
    public function findIneligibleSubscribersForOffer(iterable $subscribers, string $type, ?Carbon $onDate = null): array
    {
        $ineligible = [];

        foreach ($subscribers as $subscriber) {
            if (!$this->subscriberCanReceiveOffer($subscriber, $type, $onDate)) {
                $ineligible[] = $subscriber->name;
            }
        }

        return $ineligible;
    }

    /**
     * @return array<int, string>
     */
    public function findDuplicateSubscribersForOffer(iterable $subscribers, string $type): array
    {
        return $this->findIneligibleSubscribersForOffer($subscribers, $type);
    }

    public function subscriberHasEverReceivedOffer(User $subscriber, string $type): bool
    {
        return $this->subscriberOfferApplicationsQuery((int) $subscriber->id, $type)->exists();
    }

    public function subscriberHasOfferInCurrentSubscription(User $subscriber, string $type): bool
    {
        return $this->subscriberOfferApplicationsQuery((int) $subscriber->id, $type)
            ->where('created_at', '>=', $this->currentSubscriptionPeriodStart($subscriber))
            ->exists();
    }

    public function subscriberHasOfferInLastYear(User $subscriber, string $type, ?Carbon $onDate = null): bool
    {
        $lastAppliedAt = $this->subscriberLastOfferApplicationDate($subscriber, $type);

        if (!$lastAppliedAt) {
            return false;
        }

        $onDate = ($onDate ?? now())->startOfDay();

        return $lastAppliedAt->copy()->addYear()->gt($onDate);
    }

    public function subscriberHasReceivedCampaign(User $subscriber, Offers $campaign): bool
    {
        if ($campaign->user_id !== null) {
            return false;
        }

        $query = Offers::query()
            ->where('user_id', $subscriber->id)
            ->where('discount_type', (string) $campaign->discount_type)
            ->whereExists(function ($subQuery) {
                $subQuery->selectRaw('1')
                    ->from('referrals')
                    ->whereColumn('referrals.offer_id', 'offers.id')
                    ->whereNotNull('referrals.offer_id');
            });

        if ($campaign->offer_start_date) {
            $query->whereDate('offer_start_date', Carbon::parse($campaign->offer_start_date)->toDateString());
        } else {
            $query->whereNull('offer_start_date');
        }

        if ($campaign->offer_end_date) {
            $query->whereDate('offer_end_date', Carbon::parse($campaign->offer_end_date)->toDateString());
        } else {
            $query->whereNull('offer_end_date');
        }

        return $query->exists();
    }

    public function ensurePassiveCampaignApplicationRecorded(User $subscriber, Offers $campaign): void
    {
        if ($campaign->user_id !== null || $this->subscriberHasReceivedCampaign($subscriber, $campaign)) {
            return;
        }

        $type = (string) $campaign->discount_type;

        if (!$this->subscriberCanReceiveOffer($subscriber, $type)) {
            return;
        }

        if ($type === 'free_upgrade' && !$this->subscriberMatchesApplicablePlans($subscriber, $campaign->applicable_plans ?? null)) {
            return;
        }

        $upgradeContext = null;
        if ($type === 'free_upgrade') {
            $upgradeContext = $this->applyFreeUpgrade($subscriber);
            if (!$upgradeContext) {
                return;
            }
        } else {
            $this->applyImmediateEffects($subscriber, $type);
        }

        $offerPayload = $this->withApplicablePlansPayload([
            'user_id' => $subscriber->id,
            'discount_type' => $type,
            'discount_value' => $campaign->discount_value,
            'subscriber_type' => (string) $campaign->subscriber_type,
            'offer_start_date' => $campaign->offer_start_date,
            'offer_end_date' => $campaign->offer_end_date,
            'applied_by' => $campaign->applied_by,
            'applied_by_name' => $campaign->applied_by_name,
        ], $campaign->applicable_plans ?? null);

        if ($upgradeContext) {
            $offerPayload['upgrade_from_plan'] = $upgradeContext['previous_plan'];
            $offerPayload['upgrade_to_plan'] = $upgradeContext['new_plan'];
        }

        $offer = Offers::create($offerPayload);

        Referrals::create([
            'referral_code' => $subscriber->referral ?? '',
            'userid' => $subscriber->id,
            'user_name' => $subscriber->name,
            'total_amount' => $subscriber->wallet,
            'type' => $type,
            'amount_added' => 0,
            'offer_id' => $offer->id,
            'previous_balance' => $subscriber->wallet,
            'wallet_balance' => $subscriber->wallet,
            'applied_by' => $campaign->applied_by,
            'applied_by_name' => $campaign->applied_by_name,
        ]);

        $subscriber->save();

        if ($type === 'free_upgrade' && $upgradeContext && $this->canSendOfferRewardEmail($subscriber)) {
            $this->sendOfferRewardConfirmationEmail(
                $this->buildOfferRewardMailPayload($subscriber, $type, [
                    'discount_value' => $campaign->discount_value,
                    'credit_amount' => 0,
                    'previous_plan' => $upgradeContext['previous_plan'],
                    'new_plan' => $upgradeContext['new_plan'],
                ]),
                $type
            );
        }
    }

    private function currentSubscriptionPeriodStart(User $subscriber): Carbon
    {
        if (!empty($subscriber->membership_start_date)) {
            return Carbon::parse($subscriber->membership_start_date)->startOfDay();
        }

        return Carbon::parse($subscriber->created_at)->startOfDay();
    }

    private function subscriberLastOfferApplicationDate(User $subscriber, string $type): ?Carbon
    {
        $referral = Referrals::query()
            ->where('userid', $subscriber->id)
            ->where('type', $type)
            ->whereNotNull('offer_id')
            ->whereHas('offer', fn ($query) => $query->where('discount_type', $type))
            ->orderByDesc('created_at')
            ->first();

        return $referral ? Carbon::parse($referral->created_at)->startOfDay() : null;
    }

    private function subscriberOfferApplicationsQuery(int $subscriberId, string $type)
    {
        return Referrals::query()
            ->where('userid', $subscriberId)
            ->where('type', $type)
            ->whereNotNull('offer_id')
            ->whereHas('offer', fn ($query) => $query->where('discount_type', $type));
    }

    public function isAutomatedCampaign(?Offers $offer): bool
    {
        if (!$offer || $offer->user_id !== null) {
            return false;
        }

        return in_array((string) $offer->subscriber_type, ['existing', 'loyal', 'new'], true)
            && $this->isAutomatedType((string) $offer->discount_type);
    }

    public function isAutomatedNewSubscriberCampaign(?Offers $offer): bool
    {
        return $this->isAutomatedCampaign($offer) && (string) $offer->subscriber_type === 'new';
    }

    public function hasDuplicateAutomatedCampaign(
        string $type,
        string $subscriberType,
        ?string $startDate,
        ?string $endDate,
        ?array $applicablePlans = null
    ): bool {
        $start = Carbon::parse(self::normalizeStorageDate($startDate))->startOfDay();
        $end = Carbon::parse(self::normalizeStorageDate($endDate))->endOfDay();
        $plans = self::normalizeApplicablePlans($applicablePlans);

        return Offers::query()
            ->whereNull('user_id')
            ->where('subscriber_type', $subscriberType)
            ->where('discount_type', $type)
            ->whereNotNull('offer_start_date')
            ->whereNotNull('offer_end_date')
            ->where(function ($query) use ($start, $end) {
                $query->whereDate('offer_start_date', '<=', $end)
                    ->whereDate('offer_end_date', '>=', $start);
            })
            ->get()
            ->contains(function (Offers $campaign) use ($plans) {
                return $this->applicablePlansOverlap($plans, $campaign->applicable_plans ?? null);
            });
    }

    public function hasDuplicateAutomatedNewCampaign(string $type, ?string $startDate, ?string $endDate, ?array $applicablePlans = null): bool
    {
        return $this->hasDuplicateAutomatedCampaign($type, 'new', $startDate, $endDate, $applicablePlans);
    }

    /**
     * @return Collection<int, Offers>
     */
    public function eligibleAutomatedNewSubscriberCampaigns(User $subscriber): Collection
    {
        return $this->eligibleAutomatedCampaignsForSubscriber($subscriber, Carbon::parse($subscriber->created_at))
            ->filter(fn (Offers $campaign) => (string) $campaign->subscriber_type === 'new');
    }

    public function applyEligibleNewSubscriberOffers(User $subscriber, ?User $appliedBy = null): int
    {
        if (!$this->isEligibleForOfferBenefits($subscriber)) {
            return 0;
        }

        $appliedCount = 0;

        foreach ($this->eligibleAutomatedNewSubscriberCampaigns($subscriber) as $campaign) {
            $type = (string) $campaign->discount_type;

            if (!$this->subscriberCanReceiveOffer($subscriber, $type)) {
                continue;
            }

            if ($this->subscriberHasReceivedCampaign($subscriber, $campaign)) {
                continue;
            }

            if ($type === 'free_upgrade' && !$this->subscriberMatchesApplicablePlans($subscriber, $campaign->applicable_plans ?? null)) {
                continue;
            }

            $upgradeContext = null;
            if ($type === 'free_upgrade') {
                $upgradeContext = $this->applyFreeUpgrade($subscriber);
                if (!$upgradeContext) {
                    continue;
                }
            } else {
                $this->applyImmediateEffects($subscriber, $type);
            }

            $offerPayload = $this->withApplicablePlansPayload([
                'user_id' => $subscriber->id,
                'discount_type' => $type,
                'discount_value' => $campaign->discount_value,
                'subscriber_type' => 'new',
                'offer_start_date' => $campaign->offer_start_date,
                'offer_end_date' => $campaign->offer_end_date,
                'applied_by' => $appliedBy?->id ?? $campaign->applied_by,
                'applied_by_name' => $appliedBy?->name ?? $campaign->applied_by_name,
            ], $campaign->applicable_plans ?? null);

            if ($upgradeContext) {
                $offerPayload['upgrade_from_plan'] = $upgradeContext['previous_plan'];
                $offerPayload['upgrade_to_plan'] = $upgradeContext['new_plan'];
            }

            $offer = Offers::create($offerPayload);

            Referrals::create([
                'referral_code' => $subscriber->referral,
                'userid' => $subscriber->id,
                'user_name' => $subscriber->name,
                'total_amount' => $subscriber->wallet,
                'type' => $type,
                'amount_added' => 0,
                'offer_id' => $offer->id,
                'previous_balance' => $subscriber->wallet,
                'wallet_balance' => $subscriber->wallet,
                'applied_by' => $appliedBy?->id ?? $campaign->applied_by,
                'applied_by_name' => $appliedBy?->name ?? $campaign->applied_by_name,
            ]);

            $subscriber->save();
            $appliedCount++;

            $emailContext = [
                'discount_value' => $campaign->discount_value,
                'credit_amount' => 0,
            ];

            if ($upgradeContext) {
                $emailContext['previous_plan'] = $upgradeContext['previous_plan'];
                $emailContext['new_plan'] = $upgradeContext['new_plan'];
            }

            $this->sendOfferRewardConfirmationEmail(
                $this->buildOfferRewardMailPayload($subscriber, $type, $emailContext),
                $type
            );
        }

        return $appliedCount;
    }

    public function offerDescriptionForRecord(Offers $offer, ?User $subscriber = null): string
    {
        $type = (string) $offer->discount_type;

        if ($type === 'free_upgrade') {
            $fromPlan = trim((string) ($offer->upgrade_from_plan ?? ''));
            $toPlan = trim((string) ($offer->upgrade_to_plan ?? ''));

            $description = ($fromPlan !== '' && $toPlan !== '')
                ? sprintf('Plan upgraded from %s to %s at no charge', $fromPlan, $toPlan)
                : 'Subscription plan upgraded to the next tier at no charge';

            if ($this->offerHistoryShowsDateRange($offer)) {
                $rangeText = $this->formatOfferValidityRange($offer);
                if ($rangeText !== '') {
                    $description .= "\n\nValid for offer period: " . $rangeText . '.';
                }
            }

            if (!empty($offer->applicable_plans)) {
                $planScopeText = $this->formatApplicablePlansLabel($offer->applicable_plans);
                if ($planScopeText !== '') {
                    $description .= "\n\nApplicable on (Plan): " . $planScopeText . '.';
                }
            }

            return $description;
        }

        $subscriber = $subscriber ?? User::find($offer->user_id);

        if (!$subscriber) {
            $description = $this->offerTypeLabel((string) $offer->discount_type);
        } else {
            $description = $this->offerHistorySummary((string) $offer->discount_type, [
                'discount_value' => $offer->discount_value,
                'credit_amount' => $offer->discount_value,
            ]);
        }

        if ($this->offerHistoryShowsDateRange($offer)) {
            $rangeText = $this->formatOfferValidityRange($offer);
            if ($rangeText !== '') {
                $description .= "\n\nValid for offer period: " . $rangeText . '.';
            }
        }

        if (!empty($offer->applicable_plans)) {
            $planScopeText = $this->formatApplicablePlansLabel($offer->applicable_plans);
            if ($planScopeText !== '') {
                $description .= "\n\nApplicable on (Plan): " . $planScopeText . '.';
            }
        }

        return $description;
    }

    /**
     * @param  array<int, string>|null  $plans
     */
    public function formatApplicablePlansLabel(?array $plans): string
    {
        $normalized = self::normalizeApplicablePlans($plans);

        if ($this->campaignAppliesToAllPlans($normalized)) {
            return 'All';
        }

        $labels = [];
        foreach ($normalized as $planKey) {
            $labels[] = self::APPLICABLE_PLAN_OPTIONS[$planKey] ?? $planKey;
        }

        return implode(', ', $labels);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    protected function withApplicablePlansPayload(array $payload, ?array $plans): array
    {
        if (Schema::hasColumn('offers', 'applicable_plans')) {
            $payload['applicable_plans'] = self::normalizeApplicablePlans($plans);
        }

        return $payload;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, Referrals>
     */
    public function successfulOfferApplications()
    {
        return Referrals::query()
            ->whereIn('type', self::allOfferTypeKeys())
            ->whereNotNull('offer_id')
            ->whereHas('offer')
            ->with(['offer', 'user'])
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Applied discounts & offers history for the Subscription module (since inception).
     *
     * When $subscriberId is set, only rows for that subscriber are returned.
     *
     * @return array{logs: Collection, total: int}
     */
    public function getDiscountOfferHistoryLogs(?int $subscriberId = null, bool $requireSubscriberScope = false): array
    {
        if ($requireSubscriberScope && (!$subscriberId || $subscriberId <= 0)) {
            return [
                'logs' => collect(),
                'total' => 0,
            ];
        }

        $query = Referrals::query()
            ->whereIn('type', self::allOfferTypeKeys())
            ->whereNotNull('offer_id')
            ->whereHas('offer')
            ->with(['offer', 'user'])
            ->orderByDesc('created_at');

        if ($subscriberId) {
            $query->where('userid', $subscriberId)
                ->whereHas('offer', function (Builder $offerQuery) use ($subscriberId) {
                    $offerQuery->where('user_id', $subscriberId);
                });
        }

        $logs = $query->get()
            ->map(function (Referrals $application) {
                $offer = $application->offer;
                $offerType = (string) ($application->type ?? $offer?->discount_type ?? '');
                $description = $offer
                    ? $this->offerDescriptionForRecord($offer, $application->user)
                    : $this->offerTypeLabel($offerType);

                $createdAt = $application->created_at ? Carbon::parse($application->created_at) : null;

                return [
                    'id' => $application->id,
                    'discount_offer' => $description,
                    'datetime' => $createdAt ? $createdAt->format('d-m-Y H:i') : '—',
                    'created_at' => $createdAt ? $createdAt->toIso8601String() : null,
                ];
            })
            ->values();

        return [
            'logs' => $logs,
            'total' => $logs->count(),
        ];
    }

    /**
     * Subscription dates/validity for the Subscription module (reflects term extensions).
     *
     * @return array{
     *     start: string,
     *     end: string,
     *     end_display: array{base: string, effective: string, boosted: bool},
     *     validity_display: array{base: string, effective: string, boosted: bool},
     *     header_expires_on: string
     * }
     */
    public function subscriptionTermForDisplay(User $subscriber, ?Membership $plan = null): array
    {
        $plan = $plan ?? $this->membershipPlan($subscriber);
        $planValidityDays = max(1, (int) ($plan->validity ?? 365));

        $start = !empty($subscriber->membership_start_date)
            ? Carbon::parse($subscriber->membership_start_date)->startOfDay()
            : null;

        $actualExpiry = !empty($subscriber->membership_expiry_date)
            ? Carbon::parse($subscriber->membership_expiry_date)->startOfDay()
            : null;

        $baseExpiry = $start ? $start->copy()->addDays($planValidityDays) : null;
        $baseEndLabel = $baseExpiry ? $baseExpiry->format('d-m-Y') : '-';
        $effectiveEndLabel = $actualExpiry ? $actualExpiry->format('d-m-Y') : $baseEndLabel;

        $baseValidityLabel = $this->validityLabelFromDays($planValidityDays);
        $effectiveValidityDays = ($start && $actualExpiry)
            ? max(1, $start->diffInDays($actualExpiry))
            : $planValidityDays;
        $effectiveValidityLabel = $this->validityLabelFromDays($effectiveValidityDays);

        $termExtended = $baseExpiry && $actualExpiry && $actualExpiry->gt($baseExpiry);

        return [
            'start' => $start ? $start->format('d-m-Y') : '-',
            'end' => $effectiveEndLabel,
            'header_expires_on' => $effectiveEndLabel,
            'end_display' => $this->limitDisplayPair($baseEndLabel, $effectiveEndLabel, (bool) $termExtended),
            'validity_display' => $this->limitDisplayPair(
                $baseValidityLabel,
                $effectiveValidityLabel,
                $termExtended || $baseValidityLabel !== $effectiveValidityLabel
            ),
        ];
    }

    private function validityLabelFromDays(int $days): string
    {
        $years = (int) floor($days / 365);

        if ($years >= 1 && ($days % 365) === 0) {
            return $years . ' ' . ($years === 1 ? 'Year' : 'Years');
        }

        return $days . ' Days';
    }

    private function formatClientLimitAmount(User $subscriber, float $multiplier): string
    {
        $plan = $this->membershipPlan($subscriber);

        if (!$plan || strcasecmp((string) $plan->client_limit, 'Unlimited') === 0) {
            return 'Unlimited';
        }

        $years = $this->subscriptionDurationYears($subscriber);
        $perYear = (int) $plan->client_limit;
        $total = (int) round($perYear * $years * $multiplier);

        return number_format($total);
    }
}
