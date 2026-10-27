<?php

namespace App\Http\Controllers;

use App\Services\EmailBroadcastLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmailBroadcastLogController extends Controller
{
    public function __construct(
        private readonly EmailBroadcastLogService $emailBroadcastLogService
    ) {
    }

    public function subscriberLogData(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json(['data' => [], 'total' => 0]);
            }

            $logs = $this->emailBroadcastLogService->getSubscriberLogs($this->subscriberIdFor($user));

            return response()->json([
                'draw' => (int) $request->input('draw', 1),
                'recordsTotal' => $logs->count(),
                'recordsFiltered' => $logs->count(),
                'data' => $logs->values(),
                'total' => $logs->count(),
            ]);
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'Unable to load email broadcast log.',
                'data' => [],
                'total' => 0,
            ], 500);
        }
    }

    public function subscriberRecipients(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Broadcast not found.'], 404);
        }

        return $this->recipientsResponse($request, $this->subscriberIdFor($user));
    }

    public function adminRecipients(Request $request)
    {
        return $this->recipientsResponse($request, null);
    }

    public function adminLogData(Request $request)
    {
        try {
            $subscriberId = $request->input('subscriber_id');
            $logs = $this->emailBroadcastLogService->getAdminLogs(
                $subscriberId ? (int) $subscriberId : null
            );

            return response()->json([
                'draw' => (int) $request->input('draw', 1),
                'recordsTotal' => $logs->count(),
                'recordsFiltered' => $logs->count(),
                'data' => $logs->values(),
                'total' => $logs->count(),
            ]);
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'Unable to load email broadcast log.',
                'data' => [],
                'total' => 0,
            ], 500);
        }
    }

    private function recipientsResponse(Request $request, ?int $subscriberId)
    {
        $broadcastId = trim((string) $request->input('broadcast_id', ''));

        if ($broadcastId === '') {
            return response()->json(['message' => 'Broadcast not found.'], 404);
        }

        try {
            $result = $this->emailBroadcastLogService->getBroadcastRecipients($broadcastId, $subscriberId);
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json(['message' => 'Unable to load the recipients.'], 500);
        }

        if (!$result) {
            return response()->json(['message' => 'Broadcast not found.'], 404);
        }

        return response()->json($result);
    }

    private function subscriberIdFor($user): int
    {
        return $user->user_type === 'Subscriber'
            ? (int) $user->id
            : (int) ($user->added_by ?? 0);
    }
}
