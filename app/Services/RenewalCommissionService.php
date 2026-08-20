<?php

namespace App\Services;

use App\Models\AffiliateCommissionEarnt;
use App\Models\Referrals;
use App\Models\Used_referrals;
use App\Models\User;
use Carbon\Carbon;

class RenewalCommissionService
{
    public const TYPE = 'Renewal Commission';

    public const RATE = 0.05;

    public function renewalCommissionAmount(float $paymentAmount): float
    {
        if ($paymentAmount <= 0) {
            return 0.0;
        }

        return round($paymentAmount * self::RATE, 2);
    }

    /**
     * Continuing clients renew on or before their membership expiry date.
     */
    public function isContinuingClient(?Carbon $previousExpiry, ?Carbon $purchaseDate = null): bool
    {
        if (!$previousExpiry) {
            return false;
        }

        $purchaseDate = ($purchaseDate ?? Carbon::now())->copy()->startOfDay();

        return $previousExpiry->copy()->endOfDay()->gte($purchaseDate);
    }

    public function qualifiesForRenewalCommission(
        string $purchaseCategory,
        ?Carbon $previousExpiry,
        ?Carbon $purchaseDate = null
    ): bool {
        return $purchaseCategory === 'renewal'
            && $this->isContinuingClient($previousExpiry, $purchaseDate);
    }

    public function processRenewalCommission(
        User $subscriber,
        float $paymentAmount,
        string $purchaseCategory,
        ?Carbon $previousExpiry,
        ?Carbon $purchaseDate = null
    ): ?Referrals {
        if (!$this->qualifiesForRenewalCommission($purchaseCategory, $previousExpiry, $purchaseDate)) {
            return null;
        }

        $usedReferral = Used_referrals::where('subscriber_id', $subscriber->id)->first();
        if (!$usedReferral || empty($usedReferral->referral_code)) {
            return null;
        }

        $affiliate = User::where('referral', $usedReferral->referral_code)->first();
        if (!$affiliate || $affiliate->user_type !== 'Affiliate') {
            return null;
        }

        $commissionAmount = $this->renewalCommissionAmount($paymentAmount);
        if ($commissionAmount <= 0) {
            return null;
        }

        $ace = AffiliateCommissionEarnt::firstOrNew(['referral_code' => $affiliate->referral]);
        $ace->total_earned = (float) ($ace->total_earned ?? 0) + $commissionAmount;
        $ace->pending_amount = (float) ($ace->pending_amount ?? 0) + $commissionAmount;
        $ace->last_paid_at = now()->format('Y-m-d H:i:s');
        $ace->save();

        $previousBalance = (float) $affiliate->wallet;
        $affiliate->wallet = $previousBalance + $commissionAmount;
        $affiliate->save();

        return Referrals::create([
            'referral_code' => $affiliate->referral,
            'userid' => $subscriber->id,
            'user_name' => $subscriber->name,
            'total_amount' => round($paymentAmount, 2),
            'amount_added' => $commissionAmount,
            'previous_balance' => $previousBalance,
            'wallet_balance' => $affiliate->wallet,
            'type' => self::TYPE,
        ]);
    }
}
