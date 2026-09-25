<?php

namespace App\Http\Middleware;

use App\Services\RoleModuleAccessService;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Closure;
use Illuminate\Support\Facades\Auth;

class AdminAuthenticate extends Middleware
{
    public function handle($request, Closure $next, ...$guards)
    {
        // 🔒 First, call parent to ensure authentication check works
        $this->authenticate($request, $guards);

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
