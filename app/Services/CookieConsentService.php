<?php

namespace App\Services;

use App\Models\CookieConsentLog;
use App\Models\User;
use Illuminate\Http\Request;

class CookieConsentService
{
    public const CONSENT_COOKIE = 'adwiseri_cookie_consent';

    public function logAcceptance(Request $request, ?User $user = null): CookieConsentLog
    {
        $user = $user ?: $request->user();

        return CookieConsentLog::create([
            'user_id' => $user?->id,
            'subscriber_id' => $user ? $this->resolveSubscriberId($user) : null,
            'consent_action' => 'accepted',
            'page_url' => substr((string) $request->headers->get('referer', $request->fullUrl()), 0, 512),
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 512),
            'accepted_at' => now(),
        ]);
    }

    public function userHasAccepted(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        return CookieConsentLog::query()
            ->where('consent_action', 'accepted')
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id);

                $subscriberId = $this->resolveSubscriberId($user);
                if ($subscriberId) {
                    $query->orWhere('subscriber_id', $subscriberId);
                }
            })
            ->exists();
    }

    public function linkAnonymousConsentToUser(Request $request, User $user): void
    {
        if ($this->userHasAccepted($user)) {
            return;
        }

        if ($request->cookie(self::CONSENT_COOKIE) !== 'accepted') {
            return;
        }

        $this->logAcceptance($request, $user);
    }

    private function resolveSubscriberId(User $user): ?int
    {
        if ($user->user_type === 'Subscriber') {
            return $user->id;
        }

        if ($user->user_type === 'User' && !empty($user->added_by)) {
            return (int) $user->added_by;
        }

        return null;
    }
}
