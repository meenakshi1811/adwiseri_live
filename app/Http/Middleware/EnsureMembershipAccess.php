<?php

namespace App\Http\Middleware;

use App\Services\SubscriptionTermPricing;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureMembershipAccess
{
    /** @var array<int, string> */
    private const LAPSED_ALLOWED_ROUTE_NAMES = [
        'membership',
        'membership_renewal',
        'user_membership',
        'make_payment',
        'upgrade_plan',
        'pay_securely',
        'stripe.post',
        'logout',
        'check_login',
        '/',
        'home',
    ];

    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();

        if (!SubscriptionTermPricing::isMembershipAccessBlocked($user)) {
            return $next($request);
        }

        $routeName = $request->route()?->getName();

        if ($routeName && in_array($routeName, self::LAPSED_ALLOWED_ROUTE_NAMES, true)) {
            return $next($request);
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'message' => 'Your subscription has lapsed. Please renew or choose a new plan to continue.',
            ], 403);
        }

        return redirect()
            ->route('membership')
            ->with('membership_expiry', 'Your subscription has lapsed. Please choose a plan to continue.');
    }
}
