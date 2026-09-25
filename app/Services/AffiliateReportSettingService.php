<?php

namespace App\Services;

use App\Models\Affiliates;
use App\Models\ReportSetting;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AffiliateReportSettingService
{
    public const MODULE_SUBSCRIBERS = 'affiliate_subscribers';

    public const MODULE_COMMISSIONS = 'affiliate_commissions';

    public const MODULE_WALLET = 'affiliate_wallet';

    public const DEFAULT_MODULES = [
        self::MODULE_SUBSCRIBERS,
        self::MODULE_COMMISSIONS,
        self::MODULE_WALLET,
    ];

    /**
     * @return array<string, string>
     */
    public function moduleLabels(): array
    {
        return [
            self::MODULE_SUBSCRIBERS => 'Subscribers Referred',
            self::MODULE_COMMISSIONS => 'Commissions',
            self::MODULE_WALLET => 'Wallet',
        ];
    }

    public function isAffiliateUser(?User $user): bool
    {
        return $user && strtolower((string) $user->user_type) === 'affiliate';
    }

    /**
     * Linked users row for an authenticated affiliates-guard account.
     */
    public function resolveLinkedUser(?Affiliates $affiliateAccount): ?User
    {
        if (!$affiliateAccount) {
            return null;
        }

        $email = trim((string) $affiliateAccount->email);
        if ($email === '') {
            return null;
        }

        return User::where('email', $email)
            ->where('user_type', 'Affiliate')
            ->where('status', 'true')
            ->first();
    }

    public function resolveUserForReportSettings(): ?User
    {
        $user = Auth::user();
        if ($this->isAffiliateUser($user)) {
            return $user;
        }

        return $this->resolveLinkedUser(Auth::guard('affiliates')->user());
    }

    public function ensureReportSetting(User $affiliateUser): ReportSetting
    {
        $email = trim((string) $affiliateUser->email);
        $existing = ReportSetting::where('user_id', $affiliateUser->id)->first();

        if ($existing) {
            $modules = array_values(array_intersect(
                (array) ($existing->modules ?? []),
                self::DEFAULT_MODULES
            ));

            if ($modules === []) {
                $existing->modules = self::DEFAULT_MODULES;
            }

            if (empty($existing->frequency)) {
                $existing->frequency = 'monthly';
            }

            if (empty($existing->delivery_mode)) {
                $existing->delivery_mode = 'attachment';
            }

            if (trim((string) $existing->emails) === '' && $email !== '') {
                $existing->emails = $email;
            }

            if ($existing->isDirty()) {
                $existing->save();
            }

            return $existing;
        }

        return ReportSetting::create([
            'user_id' => $affiliateUser->id,
            'modules' => self::DEFAULT_MODULES,
            'frequency' => 'monthly',
            'delivery_mode' => 'attachment',
            'emails' => $email,
        ]);
    }

    /**
     * @return \Illuminate\Support\Collection<int, User>
     */
    public function activeAffiliateUsers()
    {
        return User::query()
            ->where('user_type', 'Affiliate')
            ->where('status', 'true')
            ->orderBy('id')
            ->get();
    }

    public function backfillMissingSettings(): int
    {
        $created = 0;

        foreach ($this->activeAffiliateUsers() as $affiliateUser) {
            if (!ReportSetting::where('user_id', $affiliateUser->id)->exists()) {
                $this->ensureReportSetting($affiliateUser);
                $created++;
            }
        }

        return $created;
    }
}
