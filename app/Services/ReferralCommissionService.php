<?php

namespace App\Services;

use App\Models\Referrals;
use App\Models\Used_referrals;
use App\Models\User;

/**
 * The commission a referrer (affiliate or subscriber) earns when someone who signed up with their
 * referral code makes their first paid subscription purchase.
 *
 * Paid sign-ups earn it at registration. Free sign-ups used to earn nothing when they later
 * upgraded to a paid plan, because the commission was only ever worked out at registration, so a
 * referrer lost the commission on every Free-trial conversion. A subscriber earns this once.
 */
class ReferralCommissionService
{
    public const TYPE = 'Referral Commission';

    /** True once the referrer has been paid the first-purchase commission for this subscriber. */
    public function alreadyAwarded(User $subscriber): bool
    {
        return Referrals::query()
            ->where('userid', $subscriber->id)
            ->where('type', self::TYPE)
            ->exists();
    }

    public function referrerFor(User $subscriber): ?User
    {
        $code = trim((string) $subscriber->referral_code);
        if ($code === '') {
            $code = trim((string) Used_referrals::where('subscriber_id', $subscriber->id)->value('referral_code'));
        }
        if ($code === '') {
            return null;
        }

        $referrer = User::where('referral', $code)->first();
        if (!$referrer || (int) $referrer->id === (int) $subscriber->id) {
            return null;
        }

        return $referrer;
    }

    /**
     * Credits the first-purchase commission on what the subscriber paid. Returns the ledger row,
     * or null when nothing is due (no referrer, nothing paid, or already awarded).
     */
    public function awardFirstPurchase(User $subscriber, float $paidAmount): ?Referrals
    {
        $paidAmount = round($paidAmount, 2);
        if ($paidAmount <= 0 || $this->alreadyAwarded($subscriber)) {
            return null;
        }

        $referrer = $this->referrerFor($subscriber);
        if (!$referrer) {
            return null;
        }

        $commission = commission_amount(
            (float) $referrer->affiliateTotalCommission()->sum('amount_added'),
            $paidAmount,
            $referrer
        );
        if ($commission <= 0) {
            return null;
        }

        $previousBalance = (float) $referrer->wallet;
        $referrer->wallet = round($previousBalance + $commission, 2);
        $referrer->save();

        $row = new Referrals();
        $row->referral_code = $referrer->referral;
        $row->userid = $subscriber->id;
        $row->user_name = $subscriber->name;
        $row->total_amount = $paidAmount;
        $row->amount_added = $commission;
        $row->previous_balance = $previousBalance;
        $row->wallet_balance = $referrer->wallet;
        $row->type = self::TYPE;
        $row->save();

        $used = Used_referrals::firstOrNew([
            'subscriber_id' => $subscriber->id,
            'referral_code' => $referrer->referral,
        ]);
        $used->commission_earnt = round((float) ($used->commission_earnt ?? 0) + $commission, 2);
        $used->save();

        app(AffiliateCommissionLedger::class)->sync($referrer->referral);

        return $row;
    }
}
