<?php

namespace App\Services;

use App\Models\Membership;
use App\Models\Referrals;
use App\Models\User;

class WalletLedgerService
{
    public function isPlanUpgrade(?string $previousPlanName, ?Membership $newPlan): bool
    {
        if (!$previousPlanName || !$newPlan) {
            return false;
        }

        if (strcasecmp($previousPlanName, (string) $newPlan->plan_name) === 0) {
            return false;
        }

        $oldPlan = Membership::where('plan_name', $previousPlanName)->first();

        if (!$oldPlan) {
            return false;
        }

        return (float) $newPlan->price_per_year > (float) $oldPlan->price_per_year;
    }

    public function subscriptionDebitDescription(?string $previousPlanName, Membership $newPlan): string
    {
        if ($this->isPlanUpgrade($previousPlanName, $newPlan)) {
            return 'Plan Upgrade ' . $newPlan->plan_name;
        }

        return 'Plan Renewal ' . $newPlan->plan_name;
    }

    public function recordSubscriptionDebit(
        User $user,
        float $debitAmount,
        float $previousBalance,
        float $newBalance,
        string $description
    ): void {
        if ($debitAmount <= 0) {
            return;
        }

        Referrals::create([
            'referral_code' => $user->referral ?? '',
            'userid' => $user->id,
            'user_name' => $user->name,
            'type' => $description,
            'total_amount' => round($debitAmount, 2),
            'debit_amount' => round($debitAmount, 2),
            'previous_balance' => round($previousBalance, 2),
            'wallet_balance' => round($newBalance, 2),
        ]);
    }

    public function walletReferralDescription(Referrals $referral): string
    {
        $type = (string) $referral->type;

        if (str_starts_with($type, 'Plan Upgrade ') || str_starts_with($type, 'Plan Renewal ')) {
            return $type;
        }

        $offerBenefit = app(OfferBenefitService::class);
        $displayText = $offerBenefit->offerTypeLabel($type);

        if (
            in_array($type, ['Referral Commission', RenewalCommissionService::TYPE], true)
            && $referral->userid
        ) {
            return $displayText . ' (' . $referral->userid . ')';
        }

        return $displayText;
    }

    public function subscriberWalletEntriesQuery(int $subscriberId, ?string $referralCode)
    {
        return Referrals::query()
            ->where(function ($query) use ($subscriberId, $referralCode) {
                if (!empty($referralCode)) {
                    $query->where('referral_code', '=', $referralCode);
                }

                $query->orWhere('userid', '=', $subscriberId);
            })
            ->walletTableVisible()
            ->orderByDesc('created_at');
    }
}
