<?php

namespace App\Support;

use App\Models\Activities;
use App\Models\Applications;
use App\Models\Associate;
use App\Models\Client_discussions;
use App\Models\Client_Docs;
use App\Models\Clients;
use App\Models\DemoRequests;
use App\Models\Internal_communications;
use App\Models\Internal_Invoices;
use App\Models\PaymentARs;
use App\Models\Referrals;
use App\Models\Tickets;
use App\Models\User;
use App\Models\Affiliates;
use App\Models\Used_referrals;

class ModuleAvailability
{
    public static function subscriberId(User $user): ?int
    {
        if (in_array($user->user_type, ['Subscriber', 'admin'], true)) {
            return (int) $user->id;
        }

        if ($user->user_type === 'User' && $user->added_by) {
            return (int) $user->added_by;
        }

        return null;
    }

    /**
     * Whether the current user can operate on at least one client record.
     */
    public static function hasClients(User $user): bool
    {
        return NoClientGuard::hasClients($user);
    }

    /**
     * Whether the consultancy has at least one application (subscriber scope).
     */
    public static function hasApplications(User $user): bool
    {
        $subscriberId = self::subscriberId($user);
        if (!$subscriberId) {
            return false;
        }

        return Applications::where('subscriber_id', $subscriberId)->exists();
    }

    /**
     * Whether the consultancy has at least one staff user.
     */
    public static function hasStaffUsers(User $user): bool
    {
        $subscriberId = self::subscriberId($user);
        if (!$subscriberId) {
            return false;
        }

        return User::where('added_by', $subscriberId)
            ->where('user_type', 'User')
            ->exists();
    }

    /**
     * Report tab availability — each key is enabled when at least one
     * underlying record exists for the relevant scope.
     *
     * @return array<string, bool>
     */
    public static function reportModules(User $user): array
    {
        if ($user->user_type === 'admin') {
            return self::adminReportModules();
        }

        $subscriberId = self::subscriberId($user);
        if (!$subscriberId) {
            return self::emptyReportModules();
        }

        return self::subscriberReportModules($subscriberId);
    }

    /**
     * @return array<string, bool>
     */
    public static function subscriberReportModules(int $subscriberId): array
    {
        $subscriber = User::find($subscriberId);
        $referralCode = trim((string) ($subscriber->referral ?? ''));

        return [
            'clients' => Clients::where('subscriber_id', $subscriberId)->exists(),
            'applications' => Applications::where('subscriber_id', $subscriberId)->exists(),
            'users' => User::where('added_by', $subscriberId)->where('user_type', 'User')->exists(),
            'documents' => Client_Docs::where('user_id', $subscriberId)
                ->whereNotNull('application_id')
                ->exists(),
            'communications' => Internal_communications::where('subscriber_id', $subscriberId)->exists()
                || Client_discussions::where('subscriber_id', $subscriberId)->exists(),
            'associates' => Associate::where('added_by', $subscriberId)->exists(),
            'invoices_ar' => Internal_Invoices::where('subscriber_id', $subscriberId)
                ->where(function ($query) {
                    $query->whereNull('type')->orWhere('type', '!=', 'ap');
                })->exists(),
            'invoices_ap' => Internal_Invoices::where('subscriber_id', $subscriberId)
                ->where('type', 'ap')
                ->exists(),
            'payments_ar' => PaymentARs::where('subscriber_id', $subscriberId)
                ->where(function ($query) {
                    $query->where('type', 'ar')->orWhereNull('type');
                })->exists(),
            'payments_ap' => PaymentARs::where('subscriber_id', $subscriberId)
                ->where('type', 'ap')
                ->exists(),
            'referrals' => ($referralCode !== '' && Referrals::where('referral_code', $referralCode)->exists())
                || Used_referrals::where('subscriber_id', $subscriberId)->exists(),
            'wallet' => Referrals::where('userid', $subscriberId)->exists(),
            'support_tickets' => Tickets::where('subscriber_id', $subscriberId)->exists(),
            'activity_log' => Activities::where('subscriber_id', $subscriberId)->exists(),
        ];
    }

    /**
     * @return array<string, bool>
     */
    public static function adminReportModules(): array
    {
        return [
            'subscribers' => User::where('user_type', 'Subscriber')->exists(),
            'clients' => Clients::exists(),
            'applications' => Applications::exists(),
            'users' => User::where('user_type', 'User')->exists(),
            'documents' => Client_Docs::whereNotNull('application_id')->exists(),
            'communications' => Internal_communications::exists()
                || Client_discussions::exists(),
            'associates' => Associate::exists(),
            'invoices_ar' => Internal_Invoices::where(function ($query) {
                $query->whereNull('type')->orWhere('type', '!=', 'ap');
            })->exists(),
            'invoices_ap' => Internal_Invoices::where('type', 'ap')->exists(),
            'payments_ar' => PaymentARs::where(function ($query) {
                $query->where('type', 'ar')->orWhereNull('type');
            })->exists(),
            'payments_ap' => PaymentARs::where('type', 'ap')->exists(),
            'referrals' => Referrals::exists() || Used_referrals::exists(),
            'wallet' => Referrals::exists(),
            'support_tickets' => Tickets::exists(),
            'activity_log' => Activities::exists(),
            'affiliates' => Affiliates::exists(),
            'demo_request' => DemoRequests::exists(),
        ];
    }

    /**
     * @return array<string, bool>
     */
    private static function emptyReportModules(): array
    {
        $keys = [
            'clients', 'applications', 'users', 'documents', 'communications', 'associates',
            'invoices_ar', 'invoices_ap', 'payments_ar', 'payments_ap', 'referrals',
            'wallet', 'support_tickets', 'activity_log',
        ];

        return array_fill_keys($keys, false);
    }
}
