<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\OfferBenefitService;
use App\Services\RoleModuleAccessService;
use App\Services\UserJourneyLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JourneyLogController extends Controller
{
    protected UserJourneyLogService $journeyLogService;

    protected OfferBenefitService $offerBenefitService;

    public function __construct(
        UserJourneyLogService $journeyLogService,
        OfferBenefitService $offerBenefitService
    ) {
        $this->journeyLogService = $journeyLogService;
        $this->offerBenefitService = $offerBenefitService;
    }

    public function subscriberJourneyLogData(Request $request)
    {
        $subscriberId = $request->input('subscriber_id');
        $duration = $request->input('duration', 'since_inception');

        if (!array_key_exists($duration, UserJourneyLogService::DURATION_OPTIONS)) {
            $duration = 'since_inception';
        }

        $data = $this->journeyLogService->getSubscriberJourneyLogs(
            $subscriberId ? (int) $subscriberId : null,
            $duration
        );

        if ($request->ajax()) {
            return response()->json([
                'draw' => (int) $request->input('draw', 1),
                'recordsTotal' => $data['total'],
                'recordsFiltered' => $data['total'],
                'data' => $data['logs']->values(),
                'chart' => [
                    'labels' => $data['chart_labels'],
                    'values' => $data['chart_values'],
                ],
                'total' => $data['total'],
            ]);
        }

        return response()->json($data);
    }

    public function userActivityLogData(Request $request)
    {
        $userId = $request->input('user_id');
        $duration = $request->input('duration', 'since_inception');

        if (!array_key_exists($duration, UserJourneyLogService::DURATION_OPTIONS)) {
            $duration = 'since_inception';
        }

        $data = $this->journeyLogService->getUserActivityLogs(
            $userId ? (int) $userId : null,
            $duration
        );

        if ($request->ajax()) {
            return response()->json([
                'draw' => (int) $request->input('draw', 1),
                'recordsTotal' => $data['total'],
                'recordsFiltered' => $data['total'],
                'data' => $data['logs']->values(),
                'chart' => [
                    'labels' => $data['chart_labels'],
                    'values' => $data['chart_values'],
                ],
                'total' => $data['total'],
            ]);
        }

        return response()->json($data);
    }

    public function subscriberJourneyLogPage()
    {
        $user = Auth::user();
        $page = 'subscriber';
        $subscribers = User::where('user_type', 'Subscriber')
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return view('admin.subscriber_journey_log', compact('user', 'page', 'subscribers'));
    }

    public function userActivityLogPage()
    {
        $user = Auth::user();
        $page = 'users';
        $staffUsers = User::where('user_type', 'User')
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'added_by']);

        return view('admin.user_activity_log', compact('user', 'page', 'staffUsers'));
    }

    public function subscriptionHistoryData(Request $request)
    {
        try {
            $subscriberId = $request->input('subscriber_id');

            $data = $this->journeyLogService->getSubscriptionHistoryLogs(
                $subscriberId ? (int) $subscriberId : null,
                'since_inception'
            );

            return response()->json([
                'draw' => (int) $request->input('draw', 1),
                'recordsTotal' => $data['total'],
                'recordsFiltered' => $data['total'],
                'data' => $data['logs']->values(),
                'total' => $data['total'],
            ]);
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'Unable to load subscription history.',
                'data' => [],
                'total' => 0,
            ], 500);
        }
    }

    public function subscriberSubscriptionHistoryData(Request $request)
    {
        try {
            $user = Auth::user();
            $subscriberId = $user && $user->user_type === 'Subscriber'
                ? $user->id
                : (int) ($user->added_by ?? 0);

            if (!$subscriberId) {
                return response()->json(['data' => [], 'total' => 0]);
            }

            $data = $this->journeyLogService->getSubscriptionHistoryLogs($subscriberId, 'since_inception');

            return response()->json([
                'draw' => (int) $request->input('draw', 1),
                'recordsTotal' => $data['total'],
                'recordsFiltered' => $data['total'],
                'data' => $data['logs']->values(),
                'total' => $data['total'],
            ]);
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'Unable to load subscription history.',
                'data' => [],
                'total' => 0,
            ], 500);
        }
    }

    public function discountOfferHistoryData(Request $request)
    {
        try {
            $user = Auth::user();
            $requestedSubscriberId = $request->input('subscriber_id');
            $subscriberId = $this->resolveDiscountOfferHistorySubscriberId(
                $user,
                $requestedSubscriberId ? (int) $requestedSubscriberId : null,
                false
            );

            if ($subscriberId === false) {
                return response()->json([
                    'message' => 'You do not have access to discounts and offers history.',
                    'data' => [],
                    'total' => 0,
                ], 403);
            }

            $data = $this->offerBenefitService->getDiscountOfferHistoryLogs(
                $subscriberId,
                $subscriberId !== null
            );

            return response()->json([
                'draw' => (int) $request->input('draw', 1),
                'recordsTotal' => $data['total'],
                'recordsFiltered' => $data['total'],
                'data' => $data['logs']->values(),
                'total' => $data['total'],
            ]);
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'Unable to load discounts and offers history.',
                'data' => [],
                'total' => 0,
            ], 500);
        }
    }

    public function subscriberDiscountOfferHistoryData(Request $request)
    {
        try {
            $user = Auth::user();
            $subscriberId = $this->resolveDiscountOfferHistorySubscriberId($user, null, true);

            if ($subscriberId === false || !$subscriberId) {
                return response()->json(['data' => [], 'total' => 0]);
            }

            $data = $this->offerBenefitService->getDiscountOfferHistoryLogs($subscriberId, true);

            return response()->json([
                'draw' => (int) $request->input('draw', 1),
                'recordsTotal' => $data['total'],
                'recordsFiltered' => $data['total'],
                'data' => $data['logs']->values(),
                'total' => $data['total'],
            ]);
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'Unable to load discounts and offers history.',
                'data' => [],
                'total' => 0,
            ], 500);
        }
    }

    /**
     * Resolve which subscriber's D & O history may be loaded.
     *
     * @return int|null|false  int = scoped subscriber, null = all subscribers (admin), false = denied
     */
    private function resolveDiscountOfferHistorySubscriberId(?User $user, ?int $requestedSubscriberId, bool $subscriberOnly)
    {
        if (!$user) {
            return false;
        }

        $isAdmin = strtolower((string) $user->user_type) === 'admin';
        $moduleAccess = app(RoleModuleAccessService::class);

        if ($isAdmin && !$subscriberOnly) {
            if ($moduleAccess->isAdminStaff($user) && !$moduleAccess->adminStaffCanAccessRoute('admin_discount_offer_history_data')) {
                return false;
            }

            return $requestedSubscriberId ?: null;
        }

        $subscriberId = $this->offerBenefitService->resolveSubscriberIdForHistory($user);

        return $subscriberId ?: false;
    }
}
