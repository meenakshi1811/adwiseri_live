<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

// if (!function_exists('formatDOBDate')) {
//     function formatDOBDate($date, $user) {
//         Log::debug('formatDOBDate called');

//         $countryCode = ($user->country === 'United States') ? 'US' : $user->country;
//         Log::debug('Country Code: ' . $countryCode);

//         $dateFormat = match (strtoupper($countryCode)) {
//             'US' => 'd-m-Y',
//             default => 'd-m-Y',
//         };

//         Log::debug('Date Format: ' . $dateFormat);

//         return $date ? Carbon::parse($date)->format($dateFormat) : null;
//     }
// }
if (!function_exists('commission_amount')) {
    function commission_amount($commission, $amount, $find_referral)
    {
        if ($find_referral->user_type === 'Subscriber') {
            // Always apply 15% commission for Subscribers
            $total = $amount * 0.15;
        } else {
            // Apply tiered commission for other user types
            if ($commission <= 10000) {
                $total = $amount * 0.15; // 15% commission
            } elseif ($commission > 10000 && $commission <= 25000) {
                $total = $amount * 0.18; // 18% commission
            } else {
                $total = $amount * 0.20; // 20% commission
            }
        }
        return round($total, 2);
    }
}

if (!function_exists('renewal_commission_amount')) {
    function renewal_commission_amount(float $amount): float
    {
        return app(\App\Services\RenewalCommissionService::class)->renewalCommissionAmount($amount);
    }
}

if (!function_exists('activity_fa_icon')) {
    /**
     * Soft Indigo Font Awesome icon class for an activity (unique + meaningful).
     */
    function activity_fa_icon($activity): string
    {
        $name = is_object($activity) ? (string) ($activity->activity_name ?? '') : (string) $activity;

        $map = [
            'Message Sent' => 'fas fa-paper-plane',
            'New Message' => 'fas fa-envelope',
            'Email Broadcast' => 'fas fa-bullhorn',
            'New User Added' => 'fas fa-user-plus',
            'New Admin User Added' => 'fas fa-user-shield',
            'User Data Update' => 'fas fa-user-edit',
            'User Status Updated' => 'fas fa-user-check',
            'New Client Added' => 'fas fa-users',
            'Client Data Update' => 'fas fa-user-edit',
            'New Subscriber Added' => 'fas fa-building',
            'Subscriber Data Update' => 'fas fa-building',
            'Subscriber Status Updated' => 'fas fa-toggle-on',
            'New Application Added' => 'fas fa-folder-plus',
            'Application Updated' => 'fas fa-folder-open',
            'New Dependant Added' => 'fas fa-user-friends',
            'New Enquiry Added' => 'fas fa-clipboard-list',
            'Invoice Generated' => 'fas fa-file-invoice-dollar',
            'Invoice Updated' => 'fas fa-file-invoice',
            'Payment Received' => 'fas fa-hand-holding-usd',
            'Payment Made' => 'fas fa-credit-card',
            'Document Uploaded' => 'fas fa-file-upload',
            'Documents Uploaded' => 'fas fa-file-alt',
            'New Job Added' => 'fas fa-briefcase',
            'Job Updated' => 'fas fa-briefcase',
            'Support Ticket Created' => 'fas fa-headset',
            'Support Ticket Updated' => 'fas fa-ticket-alt',
            'Meeting Note Added' => 'fas fa-phone-alt',
            'Profile Updated' => 'fas fa-id-badge',
            'Admin Profile Updated' => 'fas fa-id-badge',
            'Organization Logo Updated' => 'fas fa-image',
            'Subscription Renewed' => 'fas fa-sync-alt',
            'Plan Subscribed' => 'fas fa-crown',
            'Referral Added' => 'fas fa-handshake',
            'Wallet Credited' => 'fas fa-wallet',
            'Wallet Debited' => 'fas fa-wallet',
        ];

        if (isset($map[$name])) {
            return $map[$name];
        }

        // Fuzzy fallbacks for similar activity labels
        if (stripos($name, 'Message') !== false) {
            return 'fas fa-paper-plane';
        }
        if (stripos($name, 'Broadcast') !== false || stripos($name, 'Email') !== false) {
            return 'fas fa-bullhorn';
        }
        if (stripos($name, 'Invoice') !== false) {
            return 'fas fa-file-invoice-dollar';
        }
        if (stripos($name, 'Payment') !== false) {
            return 'fas fa-credit-card';
        }
        if (stripos($name, 'User') !== false) {
            return 'fas fa-user-plus';
        }
        if (stripos($name, 'Client') !== false) {
            return 'fas fa-users';
        }
        if (stripos($name, 'Application') !== false) {
            return 'fas fa-folder-open';
        }
        if (stripos($name, 'Document') !== false) {
            return 'fas fa-file-alt';
        }
        if (stripos($name, 'Ticket') !== false || stripos($name, 'Support') !== false) {
            return 'fas fa-headset';
        }

        return 'fas fa-bell';
    }
}

if (!function_exists('activity_icon_for')) {
    /**
     * @deprecated Prefer activity_fa_icon() for themed FA icons.
     */
    function activity_icon_for($activity): string
    {
        return activity_fa_icon($activity);
    }
}

if (!function_exists('activity_user_initials')) {
    /**
     * Short initials from a full name (e.g. Ethan Hunt → EH).
     */
    function activity_user_initials(?string $name): string
    {
        $name = trim(preg_replace('/\s+/', ' ', (string) $name) ?? '');
        if ($name === '') {
            return '?';
        }

        // Drop parenthetical suffixes like "ADMIN (adwiseri.com)"
        $name = trim(preg_replace('/\s*\([^)]*\)\s*/', ' ', $name) ?? '');
        $parts = preg_split('/\s+/', $name, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        if (count($parts) === 0) {
            return '?';
        }

        if (count($parts) === 1) {
            return strtoupper(mb_substr($parts[0], 0, min(2, mb_strlen($parts[0]))));
        }

        $initials = '';
        foreach ($parts as $part) {
            $initials .= strtoupper(mb_substr($part, 0, 1));
        }

        return $initials;
    }
}

if (!function_exists('activity_actor_label')) {
    /**
     * Actor short label for the Activities panel.
     * Subscriber → SUB; staff/user → EH(22)
     */
    function activity_actor_label($activity): string
    {
        if (!is_object($activity)) {
            return '';
        }

        $user = $activity->relationLoaded('user')
            ? $activity->user
            : null;

        if (!$user && !empty($activity->user_id)) {
            $user = $activity->user;
        }

        $userType = is_object($user) ? (string) ($user->user_type ?? '') : '';
        if ($userType === 'Subscriber') {
            return 'SUB';
        }

        $name = is_object($user)
            ? (string) ($user->name ?? '')
            : (string) ($activity->user_name ?? '');
        $userId = is_object($user)
            ? ($user->id ?? null)
            : ($activity->user_id ?? null);

        $initials = activity_user_initials($name);
        if ($userId !== null && $userId !== '') {
            return $initials . '(' . $userId . ')';
        }

        return $initials;
    }
}

if (!function_exists('activity_panel_label')) {
    /**
     * Activities panel line: "Message Sent - EH(22)" or "Email Broadcast - SUB"
     */
    function activity_panel_label($activity): string
    {
        $name = is_object($activity) ? trim((string) ($activity->activity_name ?? '')) : '';
        $actor = activity_actor_label($activity);

        if ($name === '') {
            return $actor;
        }

        return $actor !== '' ? ($name . ' - ' . $actor) : $name;
    }
}

if (!function_exists('short_document_file_name')) {
    function short_document_file_name(?string $fileName, ?string $docName = null, int $maxLength = 30): string
    {
        return \App\Support\DocumentFileName::shorten($fileName, $docName, $maxLength);
    }
}

if (!function_exists('membership_access_blocked')) {
    /**
     * True when the subscriber is past the 30-day post-expiry grace window.
     */
    function membership_access_blocked(?\App\Models\User $user): bool
    {
        if (!$user) {
            return false;
        }

        return \App\Services\SubscriptionTermPricing::isMembershipAccessBlocked($user);
    }
}

if (!function_exists('membership_access_blocked_for_subscriber')) {
    function membership_access_blocked_for_subscriber(?\App\Models\User $subscriber): bool
    {
        if (!$subscriber) {
            return false;
        }

        return \App\Services\SubscriptionTermPricing::isSubscriptionLapsed($subscriber);
    }
}
