<?php

namespace App\Services;

use App\Models\AffiliateCommissionEarnt;
use App\Models\Referrals;
use Illuminate\Database\Eloquent\Builder;

/**
 * An affiliate's commission figures, worked out from the referral ledger rows themselves:
 * earned = commission rows (purchase, renewal and sign-up bonus), paid = admin payouts,
 * pending = earned - paid (never below zero).
 *
 * The affiliate_commission_earnts summary row used to be the source, but it was updated
 * inconsistently (a sign-up bonus raised "earned" but not "pending"; a purchase commission only
 * updated an existing row; a first payout seeded it from the wallet balance), so pages showed a
 * negative pending amount and totals that did not match the commission list. That row is now
 * only kept in step with the ledger (see sync()).
 */
class AffiliateCommissionLedger
{
    public const SIGNUP_BONUS_TYPE = 'Sign-up Bonus';
    public const PAYOUT_TYPE = 'Payout';

    /** Ledger row types that are commission the affiliate earned. */
    public static function earningTypes(): array
    {
        return ['Referral Commission', RenewalCommissionService::TYPE, self::SIGNUP_BONUS_TYPE];
    }

    /**
     * Commission rows for a referral code. Older sign-up bonus rows were saved without a type,
     * so untyped rows that credited money count as sign-up bonuses.
     */
    public function earningsQuery(?string $referralCode): Builder
    {
        return Referrals::query()
            ->where('referral_code', (string) $referralCode)
            ->where(function (Builder $query) {
                $query->whereIn('type', self::earningTypes())
                    ->orWhere(function (Builder $untyped) {
                        $untyped->where(function (Builder $empty) {
                            $empty->whereNull('type')->orWhere('type', '');
                        })->where('amount_added', '>', 0);
                    });
            });
    }

    /** Display name of a commission row's kind. */
    public static function earningLabel(?string $type): string
    {
        $type = trim((string) $type);

        return $type === '' ? self::SIGNUP_BONUS_TYPE : $type;
    }

    /**
     * @return array{earned: float, paid: float, pending: float}
     */
    public function totals(?string $referralCode): array
    {
        if (trim((string) $referralCode) === '') {
            return ['earned' => 0.0, 'paid' => 0.0, 'pending' => 0.0];
        }

        $earned = round((float) $this->earningsQuery($referralCode)->sum('amount_added'), 2);
        $paid = round((float) Referrals::query()
            ->where('referral_code', (string) $referralCode)
            ->where('type', self::PAYOUT_TYPE)
            ->sum('debit_amount'), 2);

        return [
            'earned' => $earned,
            'paid' => $paid,
            'pending' => round(max(0.0, $earned - $paid), 2),
        ];
    }

    /** Brings the affiliate_commission_earnts summary row in line with the ledger. */
    public function sync(?string $referralCode, bool $paidNow = false): ?AffiliateCommissionEarnt
    {
        if (trim((string) $referralCode) === '') {
            return null;
        }

        $totals = $this->totals($referralCode);
        $summary = AffiliateCommissionEarnt::firstOrNew(['referral_code' => (string) $referralCode]);
        $summary->total_earned = $totals['earned'];
        $summary->paid_amount = $totals['paid'];
        $summary->pending_amount = $totals['pending'];
        if ($paidNow) {
            $summary->last_paid_at = now();
        }
        $summary->save();

        return $summary;
    }
}
