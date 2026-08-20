<?php

namespace App\Services;

use App\Models\User;

class RoleModuleAccessService
{
    /**
     * Admin Staff (is_support=1) — not the main Admin super-user.
     */
    public const ADMIN_STAFF_MODULES = [
        'Subscribers',
        'Activity Logs',
        'Demo Requests',
    ];

    /**
     * Affiliate portal modules.
     */
    public const AFFILIATE_MODULES = [
        'Subscribers',
        'Referrals',
        'Wallet',
        'Commissions',
    ];

    /**
     * Route names Admin Staff may access (plus always-allowed account routes).
     */
    public const ADMIN_STAFF_ROUTE_NAMES = [
        // Subscribers
        'subscribers',
        'subscribersReport',
        'subscribers_export',
        'update_subscriber',
        'admin_subscriber_journey_log_data',
        'admin_subscription_history_data',
        'admin_discount_offer_history_data',

        // Activity Logs
        'activity_log',
        'activityReport',
        'admin_user_activity_log_data',

        // Demo Requests
        'demo_requests',
        'demo_status',
        'demoReport',
        'demoRequestReport',
    ];

    /**
     * Routes every authenticated admin (including staff) may always hit.
     */
    public const ADMIN_ALWAYS_ALLOWED_ROUTES = [
        'admin_profile',
        'update_admin_profile',
        'admin_notifications',
        'logout',
        'admin',
        'login',
    ];

    public function isAdminStaff(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        return strtolower((string) $user->user_type) === 'admin'
            && (int) ($user->is_support ?? 0) === 1;
    }

    public function isFullAdmin(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        return strtolower((string) $user->user_type) === 'admin'
            && (int) ($user->is_support ?? 0) !== 1;
    }

    public function adminStaffModules(): array
    {
        return self::ADMIN_STAFF_MODULES;
    }

    public function affiliateModules(): array
    {
        return self::AFFILIATE_MODULES;
    }

    public function adminStaffCanAccessRoute(?string $routeName): bool
    {
        if ($routeName === null || $routeName === '') {
            return false;
        }

        if (in_array($routeName, self::ADMIN_ALWAYS_ALLOWED_ROUTES, true)) {
            return true;
        }

        return in_array($routeName, self::ADMIN_STAFF_ROUTE_NAMES, true);
    }

    public function adminStaffHomeRoute(): string
    {
        return 'subscribers';
    }

    public function affiliateHomeRoute(): string
    {
        return 'subscribers_affiliate';
    }
}
