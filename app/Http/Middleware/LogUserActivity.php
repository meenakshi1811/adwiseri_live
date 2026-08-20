<?php

namespace App\Http\Middleware;

use App\Services\UserJourneyLogService;
use Closure;
use Illuminate\Http\Request;

class LogUserActivity
{
    protected UserJourneyLogService $journeyLogService;

    public function __construct(UserJourneyLogService $journeyLogService)
    {
        $this->journeyLogService = $journeyLogService;
    }

    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $actor = UserJourneyLogService::actor();
        if ($actor) {
            try {
                $this->journeyLogService->logFromRequest($request, $actor);
            } catch (\Throwable $e) {
                // Never block the request if logging fails.
            }
        }

        return $response;
    }
}
