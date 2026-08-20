<?php

namespace App\Http\Middleware;

use App\Services\RoleModuleAccessService;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Closure;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class AdminAuthenticate extends Middleware
{
    public function handle($request, Closure $next, ...$guards)
    {
        // 🔒 First, call parent to ensure authentication check works
        $this->authenticate($request, $guards);

        // ⏰ Expiry logic
        $expiryDate = Carbon::create(2025, 8, 1); // base + 15 days
        $today = Carbon::now();

        $blockedRoutes = [
            'new_user',
            'view_user',
        ];

        if ($today->greaterThanOrEqualTo($expiryDate)) {
            foreach ($blockedRoutes as $route) {
                if ($request->is($route)) {
                    abort(403, ' Page Not Found.');
                }
            }
        }

        $user = Auth::user();
        $moduleAccess = app(RoleModuleAccessService::class);
        if ($moduleAccess->isAdminStaff($user)) {
            $routeName = optional($request->route())->getName();
            if (!$moduleAccess->adminStaffCanAccessRoute($routeName)) {
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'message' => 'You do not have access to this module.',
                    ], 403);
                }

                return redirect()
                    ->route($moduleAccess->adminStaffHomeRoute())
                    ->withErrors(['message' => 'You do not have access to that module.']);
            }
        }

        return $next($request);
    }

    protected function redirectTo($request)
    {
        if (! $request->expectsJson()) {
            return route('login');
        }
    }
}
