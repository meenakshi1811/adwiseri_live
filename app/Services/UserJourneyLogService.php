<?php

namespace App\Services;

use App\Models\Activities;
use App\Models\Internal_Invoices;
use App\Models\Membership;
use App\Models\Referrals;
use App\Models\User;
use App\Models\UserJourneyLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class UserJourneyLogService
{
    public const DURATION_OPTIONS = [
        'today' => 'Today',
        'last_week' => 'Last Week',
        'last_month' => 'Last Month',
        'last_quarter' => 'Last Quarter',
        'last_year' => 'Last Year',
        'since_inception' => 'Since Inception',
    ];

    public const SUBSCRIBER_LIFECYCLE_EVENTS = [
        'registration',
        'upgrade',
        'renewal',
        'termination',
        'status_change',
    ];

    /** Subscription purchase log — signup, upgrade, renewal only. */
    public const SUBSCRIPTION_PURCHASE_EVENTS = [
        'signup',
        'upgrade',
        'renewal',
    ];

    public const SUBSCRIPTION_EVENT_LABELS = [
        'signup' => 'Signup',
        'upgrade' => 'Upgrade',
        'renewal' => 'Renewal',
    ];

    public function log(
        User $actor,
        string $eventCategory,
        string $eventType,
        string $eventDetail = '',
        ?int $subscriberId = null,
        ?Request $request = null,
        ?array $metadata = null
    ): UserJourneyLog {
        $subscriberId = $subscriberId ?? $this->resolveSubscriberId($actor);

        $log = new UserJourneyLog();
        $log->subscriber_id = $subscriberId;
        $log->user_id = $actor->id;
        $log->user_name = trim($actor->name);
        $log->user_type = $actor->user_type;
        $log->event_category = $eventCategory;
        $log->event_type = $eventType;
        $log->event_detail = $eventDetail;
        $log->local_time = Carbon::now()->format('d-m-Y H:i:s');

        if ($request) {
            $log->page_url = substr($request->fullUrl(), 0, 512);
            $log->http_method = $request->method();
            $log->ip_address = $request->ip();
            $log->user_agent = substr((string) $request->userAgent(), 0, 512);
        }

        if ($metadata !== null) {
            $log->metadata = $metadata;
        }

        $log->save();

        return $log;
    }

    public function logFromRequest(Request $request, User $actor): ?UserJourneyLog
    {
        if ($this->shouldSkipRequest($request)) {
            return null;
        }

        $routeName = $request->route() ? $request->route()->getName() : null;
        $routeLabel = $routeName ?: $request->path();

        if ($request->isMethod('GET')) {
            return $this->log(
                $actor,
                'page_visit',
                'Page Visit',
                'Visited: ' . $routeLabel,
                null,
                $request,
                ['route' => $routeName]
            );
        }

        return $this->log(
            $actor,
            'action',
            ucfirst(strtolower($request->method())) . ' Action',
            'Performed: ' . $routeLabel,
            null,
            $request,
            ['route' => $routeName, 'method' => $request->method()]
        );
    }

    public function logSubscriptionEvent(
        User $actor,
        string $eventCategory,
        string $eventType,
        string $detail,
        ?int $subscriberId = null,
        ?array $metadata = null
    ): UserJourneyLog {
        return $this->log($actor, $eventCategory, $eventType, $detail, $subscriberId, null, $metadata);
    }

    /**
     * Log a subscription purchase event (Signup / Upgrade / Renewal).
     */
    public function logSubscriptionPurchase(
        User $actor,
        string $eventType,
        string $planName,
        int $dosYears,
        ?int $subscriberId = null,
        ?array $extraMetadata = null
    ): UserJourneyLog {
        if (!in_array($eventType, self::SUBSCRIPTION_PURCHASE_EVENTS, true)) {
            $eventType = 'signup';
        }

        $label = self::SUBSCRIPTION_EVENT_LABELS[$eventType] ?? ucfirst($eventType);
        $dosYears = max(1, $dosYears);
        $dosLabel = $dosYears === 1 ? '1 Year' : $dosYears . ' Years';

        $metadata = array_merge([
            'plan' => $planName,
            'dos_years' => $dosYears,
            'dos_label' => $dosLabel,
            'log_type' => 'subscription_purchase',
        ], $extraMetadata ?? []);

        return $this->log(
            $actor,
            $eventType,
            $label,
            $label . ' — Plan: ' . $planName . ', DOS: ' . $dosLabel,
            $subscriberId,
            null,
            $metadata
        );
    }

    /**
     * Classify a subscription purchase as signup, upgrade, or renewal.
     */
    public function classifySubscriptionPurchase(
        bool $isFirstPurchase,
        ?Carbon $previousExpiryDate,
        ?string $previousPlan,
        string $newPlan,
        ?Carbon $purchaseDate = null
    ): string {
        $purchaseDate = $purchaseDate ?? Carbon::now();

        if ($isFirstPurchase || empty($previousPlan)) {
            return 'signup';
        }

        if ($previousExpiryDate && $previousExpiryDate->lt($purchaseDate)) {
            $daysSinceExpiry = $previousExpiryDate->diffInDays($purchaseDate);

            return $daysSinceExpiry > 30 ? 'signup' : 'renewal';
        }

        if ($previousPlan && strcasecmp(trim($previousPlan), trim($newPlan)) !== 0) {
            return 'upgrade';
        }

        return 'renewal';
    }

    public function dosYearsFromValidityDays(?int $validityDays): int
    {
        $days = max(1, (int) $validityDays);

        if ($days >= 365 && ($days % 365) === 0) {
            return (int) ($days / 365);
        }

        return max(1, (int) round($days / 365));
    }

    public function resolveDateRange(string $duration): array
    {
        $now = Carbon::now();

        switch ($duration) {
            case 'today':
                return [$now->copy()->startOfDay(), $now->copy()->endOfDay()];
            case 'last_week':
                return [$now->copy()->subDays(7)->startOfDay(), $now->copy()->endOfDay()];
            case 'last_month':
                return [$now->copy()->subMonth()->startOfDay(), $now->copy()->endOfDay()];
            case 'last_quarter':
                return [$now->copy()->subMonths(3)->startOfDay(), $now->copy()->endOfDay()];
            case 'last_year':
                return [$now->copy()->subYear()->startOfDay(), $now->copy()->endOfDay()];
            case 'since_inception':
            default:
                return [null, null];
        }
    }

    /**
     * Subscription purchase history — Signup, Upgrade, Renewal only.
     *
     * @return array{logs: Collection, total: int}
     */
    public function getSubscriptionHistoryLogs(?int $subscriberId = null, string $duration = 'since_inception'): array
    {
        [$start, $end] = $this->resolveDateRange($duration);

        $merged = $this->mergeSubscriptionHistorySources([
            $this->safeSubscriptionHistorySource(fn () => $this->querySubscriptionPurchaseLogs($subscriberId, $start, $end)),
            $this->safeSubscriptionHistorySource(fn () => $this->querySubscriptionInvoiceHistory($subscriberId, $start, $end)),
            $this->safeSubscriptionHistorySource(fn () => $this->querySubscriptionWalletHistory($subscriberId, $start, $end)),
            $this->safeSubscriptionHistorySource(fn () => $this->querySubscriptionPurchaseLegacyActivities($subscriberId, $start, $end)),
            $this->safeSubscriptionHistorySource(fn () => $this->querySubscriberRegistrationFallback($subscriberId, $start, $end)),
        ]);

        return [
            'logs' => $merged,
            'total' => $merged->count(),
        ];
    }

    private function safeSubscriptionHistorySource(callable $callback): Collection
    {
        try {
            return $callback();
        } catch (\Throwable $exception) {
            Log::warning('Subscription history source failed', [
                'error' => $exception->getMessage(),
            ]);

            return collect();
        }
    }

    private function querySubscriptionPurchaseLogs(?int $subscriberId, ?Carbon $start, ?Carbon $end): Collection
    {
        if (!Schema::hasTable('user_journey_logs')) {
            return collect();
        }

        $allowedCategories = array_merge(
            self::SUBSCRIPTION_PURCHASE_EVENTS,
            ['registration']
        );

        $query = UserJourneyLog::query()
            ->whereIn('event_category', $allowedCategories)
            ->orderByDesc('created_at');

        if ($subscriberId) {
            $query->where(function ($q) use ($subscriberId) {
                $q->where('subscriber_id', $subscriberId)
                    ->orWhere('user_id', $subscriberId);
            });
        }

        if ($start && $end) {
            $query->whereBetween('created_at', [$start, $end]);
        }

        return $query->get()
            ->map(function (UserJourneyLog $log) {
                return $this->formatSubscriptionPurchaseRow($log);
            })
            ->filter(function ($row) {
                return $row !== null;
            })
            ->values();
    }

    private function querySubscriptionPurchaseLegacyActivities(?int $subscriberId, ?Carbon $start, ?Carbon $end): Collection
    {
        if (!Schema::hasTable('activities')) {
            return collect();
        }

        $query = Activities::query()->orderByDesc('created_at');

        $query->where(function ($q) {
            $q->where('activity_name', 'like', '%New Subscriber Added%')
                ->orWhere('activity_name', 'like', '%Subscription Updated%')
                ->orWhere('activity_name', 'like', '%Price Plan Updated%')
                ->orWhere('activity_name', 'like', '%Renew%')
                ->orWhere('activity_detail', 'like', '%updates account%')
                ->orWhere('activity_detail', 'like', '%Subscription Fees%');
        });

        if ($subscriberId) {
            $subscriber = User::find($subscriberId);
            $query->where(function ($q) use ($subscriberId, $subscriber) {
                $q->where('subscriber_id', $subscriberId)
                    ->orWhere('user_id', $subscriberId);

                if ($subscriber && !empty($subscriber->name)) {
                    $q->orWhere('activity_detail', 'like', '%' . $subscriber->name . '%');
                }
            });
        }

        if ($start && $end) {
            $query->whereBetween('created_at', [$start, $end]);
        }

        $rows = $query->get()
            ->map(function (Activities $activity) {
                return $this->formatLegacySubscriptionPurchaseRow($activity);
            })
            ->filter(function ($row) use ($subscriberId) {
                if ($row === null) {
                    return false;
                }

                if ($subscriberId && (int) ($row['subscriber_id'] ?? 0) !== $subscriberId) {
                    return false;
                }

                return true;
            })
            ->values();

        $withCategory = $rows->filter(function ($row) {
            return !empty($row['event_category']);
        });
        $needsClassification = $rows->filter(function ($row) {
            return empty($row['event_category']);
        });

        return $withCategory
            ->concat($this->classifyHistoricalEventCollection($needsClassification))
            ->values();
    }

    private function querySubscriptionInvoiceHistory(?int $subscriberId, ?Carbon $start, ?Carbon $end): Collection
    {
        if (!Schema::hasTable('internal_invoices')) {
            return collect();
        }

        $query = Internal_Invoices::query()
            ->whereNotNull('subscriber_id')
            ->where('subscriber_id', '>', 0)
            ->with('items')
            ->orderBy('created_at', 'asc');

        if ($subscriberId) {
            $query->where('subscriber_id', $subscriberId);
        }

        if ($start && $end) {
            $query->whereBetween('created_at', [$start, $end]);
        }

        $rawEvents = $query->get()
            ->filter(function (Internal_Invoices $invoice) {
                return $invoice->isSubscriptionPackageInvoice();
            })
            ->map(function (Internal_Invoices $invoice) {
                $plan = $this->extractPlanFromInvoiceDetail($invoice->detail);

                if ($plan === '-' && $invoice->relationLoaded('items')) {
                    foreach ($invoice->items as $item) {
                        $plan = $this->extractPlanFromInvoiceDetail($item->detail ?? null);
                        if ($plan !== '-') {
                            break;
                        }
                    }
                }

                if ($plan === '-') {
                    $plan = $this->resolvePlanForSubscriptionEvent(
                        (int) $invoice->subscriber_id,
                        '-',
                        $invoice->created_at
                    );
                }

                $plan = $this->normalizeSubscriptionPlanName($plan);

                return [
                    'id' => 'i-' . $invoice->id,
                    'subscriber_id' => (int) $invoice->subscriber_id,
                    'plan' => $plan,
                    'dos' => $this->dosLabelForPlanName($plan !== '-' ? $plan : null),
                    'datetime' => $invoice->created_at
                        ? Carbon::parse($invoice->created_at)->format('d-m-Y H:i:s')
                        : '-',
                    'created_at' => $invoice->created_at,
                    'source' => 'invoice',
                    'source_priority' => 2,
                    'event_category' => null,
                ];
            });

        return $this->classifyHistoricalEventCollection($rawEvents);
    }

    private function querySubscriptionWalletHistory(?int $subscriberId, ?Carbon $start, ?Carbon $end): Collection
    {
        if (!Schema::hasTable('referrals')) {
            return collect();
        }

        $query = Referrals::query()
            ->where(function ($q) {
                $q->where('type', 'like', 'Plan Upgrade %')
                    ->orWhere('type', 'like', 'Plan Renewal %');
            })
            ->orderBy('created_at', 'asc');

        if ($subscriberId) {
            $query->where('userid', $subscriberId);
        }

        if ($start && $end) {
            $query->whereBetween('created_at', [$start, $end]);
        }

        return $query->get()->map(function (Referrals $referral) {
            $type = (string) $referral->type;

            if ($this->strStartsWith($type, 'Plan Upgrade ')) {
                $category = 'upgrade';
                $plan = trim(substr($type, strlen('Plan Upgrade ')));
            } else {
                $category = 'renewal';
                $plan = trim(substr($type, strlen('Plan Renewal ')));
            }

            return [
                'id' => 'w-' . $referral->id,
                'subscriber_id' => (int) $referral->userid,
                'event' => self::SUBSCRIPTION_EVENT_LABELS[$category],
                'event_category' => $category,
                'plan' => $plan !== '' ? $this->normalizeSubscriptionPlanName($plan) : '-',
                'dos' => $this->dosLabelForPlanName($plan !== '' ? $plan : null),
                'datetime' => $referral->created_at
                    ? Carbon::parse($referral->created_at)->format('d-m-Y H:i:s')
                    : '-',
                'created_at' => $referral->created_at,
                'source' => 'wallet',
                'source_priority' => 3,
            ];
        });
    }

    private function querySubscriberRegistrationFallback(?int $subscriberId, ?Carbon $start, ?Carbon $end): Collection
    {
        $query = User::query()
            ->where('user_type', 'Subscriber')
            ->orderBy('created_at', 'asc');

        if ($subscriberId) {
            $query->where('id', $subscriberId);
        }

        if ($start && $end) {
            $query->whereBetween('created_at', [$start, $end]);
        }

        return $query->get()->map(function (User $user) {
            $plan = trim((string) ($user->membership ?? ''));

            return [
                'id' => 'u-' . $user->id,
                'subscriber_id' => (int) $user->id,
                'event' => self::SUBSCRIPTION_EVENT_LABELS['signup'],
                'event_category' => 'signup',
                'plan' => $plan !== '' ? $this->normalizeSubscriptionPlanName($plan) : '-',
                'dos' => $this->dosLabelForPlanName($plan !== '' ? $plan : null),
                'datetime' => $user->created_at
                    ? Carbon::parse($user->created_at)->format('d-m-Y H:i:s')
                    : '-',
                'created_at' => $user->created_at,
                'source' => 'registration',
                'source_priority' => 5,
            ];
        });
    }

    /**
     * @param  array<int, Collection>  $collections
     */
    private function mergeSubscriptionHistorySources(array $collections): Collection
    {
        $all = collect();

        foreach ($collections as $collection) {
            $all = $all->concat($collection);
        }

        $deduped = [];

        foreach ($all as $row) {
            $priority = (int) ($row['source_priority'] ?? $this->subscriptionSourcePriority($row['source'] ?? 'unknown'));
            $key = $this->subscriptionHistoryDedupeKey($row);

            if (!isset($deduped[$key]) || $priority < (int) ($deduped[$key]['source_priority'] ?? 99)) {
                $deduped[$key] = array_merge($row, ['source_priority' => $priority]);
            }
        }

        return collect(array_values($deduped))
            ->sortByDesc(function ($row) {
                if (empty($row['created_at'])) {
                    return 0;
                }

                return Carbon::parse($row['created_at'])->timestamp;
            })
            ->map(fn ($row) => $this->formatSubscriptionHistoryOutputRow($row))
            ->values();
    }

    private function subscriptionHistoryDedupeKey(array $row): string
    {
        $subscriberId = (int) ($row['subscriber_id'] ?? 0);
        $category = (string) ($row['event_category'] ?? 'unknown');
        $plan = strtolower(trim((string) ($row['plan'] ?? '-')));
        $date = !empty($row['created_at'])
            ? Carbon::parse($row['created_at'])->format('Y-m-d')
            : 'unknown';

        return $subscriberId . '|' . $category . '|' . $plan . '|' . $date;
    }

    private function subscriptionSourcePriority(string $source): int
    {
        switch ($source) {
            case 'journey':
                return 1;
            case 'invoice':
                return 2;
            case 'wallet':
                return 3;
            case 'legacy':
                return 4;
            case 'registration':
                return 5;
            default:
                return 6;
        }
    }

    private function formatSubscriptionHistoryOutputRow(array $row): array
    {
        return [
            'id' => $row['id'],
            'event' => $row['event'],
            'event_category' => $row['event_category'],
            'plan' => $row['plan'],
            'dos' => $row['dos'],
            'datetime' => $row['datetime'],
            'created_at' => $row['created_at'],
        ];
    }

    private function classifyHistoricalEventCollection(Collection $events): Collection
    {
        if ($events->isEmpty()) {
            return $events;
        }

        $result = collect();

        foreach ($events->groupBy('subscriber_id') as $subEvents) {
            $sorted = $subEvents->sortBy('created_at')->values();
            $previousPlan = null;
            $previousExpiry = null;
            $isFirst = true;

            foreach ($sorted as $event) {
                $plan = ($event['plan'] ?? '-') !== '-'
                    ? $this->normalizeSubscriptionPlanName((string) $event['plan'])
                    : ($previousPlan ?? '-');
                $purchaseDate = Carbon::parse($event['created_at']);

                if (!empty($event['event_category'])) {
                    $category = $event['event_category'];
                } else {
                    $category = $this->classifySubscriptionPurchase(
                        $isFirst,
                        $previousExpiry,
                        $previousPlan,
                        $plan,
                        $purchaseDate
                    );
                }

                $result->push(array_merge($event, [
                    'event' => self::SUBSCRIPTION_EVENT_LABELS[$category] ?? ucfirst($category),
                    'event_category' => $category,
                ]));

                if ($plan !== '-') {
                    $membership = Membership::where('plan_name', $plan)->first();
                    $validityDays = $membership ? max(1, (int) ($membership->validity ?? 365)) : 365;
                    $previousExpiry = $purchaseDate->copy()->addDays($validityDays);
                    $previousPlan = $plan;
                }

                $isFirst = false;
            }
        }

        return $result;
    }

    private function resolveActivitySubscriberId(Activities $activity): ?int
    {
        if ($activity->subscriber_id) {
            $subscriber = User::find($activity->subscriber_id);
            if ($subscriber && $subscriber->user_type === 'Subscriber') {
                return (int) $subscriber->id;
            }
        }

        if ($activity->user_id) {
            $subscriber = User::find($activity->user_id);
            if ($subscriber && $subscriber->user_type === 'Subscriber') {
                return (int) $subscriber->id;
            }
        }

        $detail = (string) $activity->activity_detail;
        $patterns = [
            '/New Subscriber\s+(.+?)\s+added by/i',
            '/New Subscriber\s+(.+?)\s+registered at/i',
            '/^(.+?)\s+updates account/i',
            '/for user\s+(.+?)\s+at/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $detail, $matches)) {
                $name = trim($matches[1]);
                $subscriberId = User::where('user_type', 'Subscriber')
                    ->where('name', $name)
                    ->value('id');

                if ($subscriberId) {
                    return (int) $subscriberId;
                }
            }
        }

        return $activity->subscriber_id
            ? (int) $activity->subscriber_id
            : ($activity->user_id ? (int) $activity->user_id : null);
    }

    private function dosLabelForPlanName(?string $planName): string
    {
        if (empty($planName) || $planName === '-') {
            return '-';
        }

        $membership = Membership::where('plan_name', $planName)->first();

        if ($membership && !empty($membership->validity)) {
            $years = $this->dosYearsFromValidityDays((int) $membership->validity);

            return $years === 1 ? '1 Year' : $years . ' Years';
        }

        return '1 Year';
    }

    private function extractPlanFromInvoiceDetail(?string $detail): string
    {
        if (empty($detail)) {
            return '-';
        }

        if (preg_match('/Subscription Fees\s*\(([^)]+)\)/i', $detail, $matches)) {
            return $this->cleanPlanTokenFromDetailSuffix(trim($matches[1]));
        }

        if (preg_match('/Subscription Fees\s*[-–—]\s*(.+)$/i', $detail, $matches)) {
            return $this->cleanPlanTokenFromDetailSuffix(trim($matches[1]));
        }

        if (preg_match('/Plan Upgrade\s+(.+)$/i', $detail, $matches)) {
            return trim($matches[1]);
        }

        if (preg_match('/Plan Renewal\s+(.+)$/i', $detail, $matches)) {
            return trim($matches[1]);
        }

        return $this->extractPlanFromDetail($detail);
    }

    private function formatSubscriptionPurchaseRow(UserJourneyLog $log): ?array
    {
        $category = $log->event_category === 'registration' ? 'signup' : $log->event_category;

        if (!in_array($category, self::SUBSCRIPTION_PURCHASE_EVENTS, true)) {
            return null;
        }

        $metadata = is_array($log->metadata) ? $log->metadata : [];
        $rawPlan = isset($metadata['plan']) ? (string) $metadata['plan'] : $this->extractPlanFromDetail($log->event_detail);
        $plan = $this->resolvePlanForSubscriptionEvent(
            (int) ($log->subscriber_id ?: $log->user_id),
            $rawPlan,
            $log->created_at
        );
        $dosYears = isset($metadata['dos_years']) ? (int) $metadata['dos_years'] : 1;
        $dosLabel = isset($metadata['dos_label'])
            ? $metadata['dos_label']
            : ($dosYears === 1 ? '1 Year' : $dosYears . ' Years');
        $eventTime = $this->resolveSubscriptionEventDateTime($log->created_at, $log->local_time);

        return [
            'id' => 'j-' . $log->id,
            'subscriber_id' => (int) ($log->subscriber_id ?: $log->user_id),
            'event' => self::SUBSCRIPTION_EVENT_LABELS[$category],
            'event_category' => $category,
            'plan' => $plan,
            'dos' => $dosLabel,
            'datetime' => $eventTime['datetime'],
            'created_at' => $eventTime['created_at'] ?? $log->created_at,
            'source' => 'journey',
            'source_priority' => 1,
        ];
    }

    private function formatLegacySubscriptionPurchaseRow(Activities $activity): ?array
    {
        $name = strtolower((string) $activity->activity_name);
        $detail = strtolower((string) $activity->activity_detail);

        if ($this->strContains($name, 'status') || $this->strContains($name, 'invoice')) {
            return null;
        }

        if ($this->strContains($name, 'new subscriber') && $this->strContains($name, 'added')) {
            $category = 'signup';
        } elseif ($this->strContains($name, 'renew')) {
            $category = 'renewal';
        } elseif ($this->strContains($name, 'subscription') && $this->strContains($name, 'updated')) {
            $category = null;
        } elseif ($this->strContains($name, 'price plan') && $this->strContains($name, 'updated')) {
            if (!$this->strContains($detail, 'updates account')) {
                return null;
            }

            $category = null;
        } else {
            return null;
        }

        $plan = $this->extractPlanFromDetail($activity->activity_detail);
        $subscriberId = $this->resolveActivitySubscriberId($activity);

        if (!$subscriberId) {
            return null;
        }

        $eventTime = $this->resolveSubscriptionEventDateTime($activity->created_at, $activity->local_time ?? null);
        $plan = $this->resolvePlanForSubscriptionEvent(
            $subscriberId,
            $plan,
            $eventTime['created_at'] ?? $activity->created_at,
            $activity
        );

        $row = [
            'id' => 'a-' . $activity->id,
            'subscriber_id' => $subscriberId,
            'plan' => $plan,
            'dos' => $this->dosLabelForPlanName($plan !== '-' ? $plan : null),
            'datetime' => $eventTime['datetime'],
            'created_at' => $eventTime['created_at'] ?? $activity->created_at,
            'source' => 'legacy',
            'source_priority' => 4,
        ];

        if ($category !== null) {
            $row['event'] = self::SUBSCRIPTION_EVENT_LABELS[$category];
            $row['event_category'] = $category;
        } else {
            $row['event_category'] = null;
        }

        return $row;
    }

    private function extractPlanFromDetail(?string $detail): string
    {
        if (empty($detail)) {
            return '-';
        }

        $patterns = [
            '/Plan:\s*([^,\n\r]+)/i',
            '/Subscription Fees\s*\(([^)]+)\)/i',
            '/Subscription Fees\s*[-–—]\s*(.+)$/i',
            '/Plan Upgrade\s+(.+)$/i',
            '/Plan Renewal\s+(.+)$/i',
            '/with plan\s+([^,\n\r]+?)(?:\s*,|\s+DOS|\s*$)/i',
            '/to plan\s+([^,\n\r]+?)(?:\s*,|\s+at\s+\d|\s+DOS|\s*$)/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $detail, $matches)) {
                $plan = $this->normalizeSubscriptionPlanName(trim($matches[1]));
                if ($plan !== '-') {
                    return $plan;
                }
            }
        }

        // Avoid false positives such as "... price plan at 17-08-2023 ...".
        if (preg_match('/plan\s+([A-Za-z0-9+\-_]+)/i', $detail, $matches)) {
            $candidate = trim($matches[1]);
            if (!$this->isInvalidExtractedPlanName($candidate)) {
                $plan = $this->normalizeSubscriptionPlanName($candidate);

                return $plan !== '-' ? $plan : $candidate;
            }
        }

        return '-';
    }

    private function cleanPlanTokenFromDetailSuffix(string $token): string
    {
        $token = trim($token);
        if ($token === '') {
            return '-';
        }

        if (preg_match('/^(.+?)\s*[-–—]\s*\d+\s*Years?$/i', $token, $matches)) {
            $token = trim($matches[1]);
        }

        return $this->normalizeSubscriptionPlanName($token);
    }

    private function isInvalidExtractedPlanName(?string $plan): bool
    {
        $plan = strtolower(trim((string) $plan));

        if ($plan === '' || $plan === '-') {
            return true;
        }

        if (in_array($plan, ['at', 'update', 'updated', 'subscription', 'fees', 'account', 'price', 'generated'], true)) {
            return true;
        }

        return (bool) preg_match('/^at\d*$/', $plan);
    }

    private function normalizeSubscriptionPlanName(?string $plan): string
    {
        $plan = trim((string) $plan);

        if ($plan === '' || $this->isInvalidExtractedPlanName($plan)) {
            return '-';
        }

        $exact = Membership::where('plan_name', $plan)->value('plan_name');
        if ($exact) {
            return (string) $exact;
        }

        $lowerPlan = strtolower($plan);
        $matched = Membership::query()
            ->get(['plan_name'])
            ->first(function ($membership) use ($lowerPlan) {
                return strtolower((string) $membership->plan_name) === $lowerPlan;
            });

        if ($matched) {
            return (string) $matched->plan_name;
        }

        return $plan;
    }

    private function resolvePlanForSubscriptionEvent(
        int $subscriberId,
        ?string $extractedPlan,
        $eventTime,
        ?Activities $activity = null
    ): string {
        $plan = $this->normalizeSubscriptionPlanName($extractedPlan);
        if ($plan !== '-') {
            return $plan;
        }

        $eventTime = $eventTime ? Carbon::parse($eventTime) : null;

        if ($eventTime) {
            $fromInvoice = $this->resolvePlanFromNearbyInvoice($subscriberId, $eventTime);
            if ($fromInvoice !== '-') {
                return $fromInvoice;
            }

            $fromReferral = $this->resolvePlanFromNearbyReferral($subscriberId, $eventTime);
            if ($fromReferral !== '-') {
                return $fromReferral;
            }

            $fromJourney = $this->resolvePlanFromNearbyJourneyLog($subscriberId, $eventTime);
            if ($fromJourney !== '-') {
                return $fromJourney;
            }
        }

        if ($activity) {
            $fromDetail = $this->normalizeSubscriptionPlanName(
                $this->extractPlanFromInvoiceDetail($activity->activity_detail)
            );
            if ($fromDetail !== '-') {
                return $fromDetail;
            }
        }

        return '-';
    }

    private function resolvePlanFromNearbyInvoice(int $subscriberId, Carbon $eventTime): string
    {
        if (!Schema::hasTable('internal_invoices')) {
            return '-';
        }

        $invoices = Internal_Invoices::query()
            ->where('subscriber_id', $subscriberId)
            ->whereBetween('created_at', [
                $eventTime->copy()->subDay(),
                $eventTime->copy()->addDay(),
            ])
            ->with('items')
            ->orderBy('created_at')
            ->get()
            ->filter(fn (Internal_Invoices $invoice) => $invoice->isSubscriptionPackageInvoice());

        $bestPlan = '-';
        $bestDiff = PHP_INT_MAX;

        foreach ($invoices as $invoice) {
            $plan = $this->extractPlanFromInvoiceDetail($invoice->detail);
            if ($plan === '-' && $invoice->relationLoaded('items')) {
                foreach ($invoice->items as $item) {
                    $plan = $this->extractPlanFromInvoiceDetail($item->detail ?? null);
                    if ($plan !== '-') {
                        break;
                    }
                }
            }

            $plan = $this->normalizeSubscriptionPlanName($plan);
            if ($plan === '-') {
                continue;
            }

            $diff = abs(Carbon::parse($invoice->created_at)->diffInSeconds($eventTime));
            if ($diff < $bestDiff) {
                $bestDiff = $diff;
                $bestPlan = $plan;
            }
        }

        return $bestPlan;
    }

    private function resolvePlanFromNearbyReferral(int $subscriberId, Carbon $eventTime): string
    {
        if (!Schema::hasTable('referrals')) {
            return '-';
        }

        $referrals = Referrals::query()
            ->where('userid', $subscriberId)
            ->whereBetween('created_at', [
                $eventTime->copy()->subDay(),
                $eventTime->copy()->addDay(),
            ])
            ->where(function ($q) {
                $q->where('type', 'like', 'Plan Upgrade %')
                    ->orWhere('type', 'like', 'Plan Renewal %');
            })
            ->orderBy('created_at')
            ->get();

        $bestPlan = '-';
        $bestDiff = PHP_INT_MAX;

        foreach ($referrals as $referral) {
            $type = (string) $referral->type;
            if ($this->strStartsWith($type, 'Plan Upgrade ')) {
                $plan = trim(substr($type, strlen('Plan Upgrade ')));
            } elseif ($this->strStartsWith($type, 'Plan Renewal ')) {
                $plan = trim(substr($type, strlen('Plan Renewal ')));
            } else {
                continue;
            }

            $plan = $this->normalizeSubscriptionPlanName($plan);
            if ($plan === '-') {
                continue;
            }

            $diff = abs(Carbon::parse($referral->created_at)->diffInSeconds($eventTime));
            if ($diff < $bestDiff) {
                $bestDiff = $diff;
                $bestPlan = $plan;
            }
        }

        return $bestPlan;
    }

    private function resolvePlanFromNearbyJourneyLog(int $subscriberId, Carbon $eventTime): string
    {
        if (!Schema::hasTable('user_journey_logs')) {
            return '-';
        }

        $logs = UserJourneyLog::query()
            ->whereIn('event_category', self::SUBSCRIPTION_PURCHASE_EVENTS)
            ->where(function ($q) use ($subscriberId) {
                $q->where('subscriber_id', $subscriberId)
                    ->orWhere('user_id', $subscriberId);
            })
            ->whereBetween('created_at', [
                $eventTime->copy()->subDay(),
                $eventTime->copy()->addDay(),
            ])
            ->orderBy('created_at')
            ->get();

        $bestPlan = '-';
        $bestDiff = PHP_INT_MAX;

        foreach ($logs as $log) {
            $metadata = is_array($log->metadata) ? $log->metadata : [];
            $plan = isset($metadata['plan'])
                ? $this->normalizeSubscriptionPlanName((string) $metadata['plan'])
                : $this->normalizeSubscriptionPlanName($this->extractPlanFromDetail($log->event_detail));

            if ($plan === '-') {
                continue;
            }

            $diff = abs(Carbon::parse($log->created_at)->diffInSeconds($eventTime));
            if ($diff < $bestDiff) {
                $bestDiff = $diff;
                $bestPlan = $plan;
            }
        }

        return $bestPlan;
    }

    /**
     * Prefer client-recorded local_time when available; fall back to DB created_at.
     *
     * @return array{datetime: string, created_at: Carbon|null}
     */
    private function resolveSubscriptionEventDateTime($createdAt, ?string $localTime): array
    {
        $parsedLocal = $this->parseStoredLocalTime($localTime);
        if ($parsedLocal) {
            return [
                'datetime' => $parsedLocal->format('d-m-Y H:i:s'),
                'created_at' => $parsedLocal,
            ];
        }

        if ($createdAt) {
            $created = Carbon::parse($createdAt);

            return [
                'datetime' => $created->format('d-m-Y H:i:s'),
                'created_at' => $created,
            ];
        }

        return [
            'datetime' => trim((string) $localTime) !== '' ? trim((string) $localTime) : '-',
            'created_at' => null,
        ];
    }

    private function parseStoredLocalTime(?string $localTime): ?Carbon
    {
        $localTime = trim((string) $localTime);
        if ($localTime === '') {
            return null;
        }

        $formats = [
            'd-m-Y H:i:s',
            'd-m-Y H:i',
            'd M, Y H:i:s',
            'd M, Y H:i',
            'Y-m-d H:i:s',
            'Y-m-d H:i',
        ];

        foreach ($formats as $format) {
            try {
                $parsed = Carbon::createFromFormat($format, $localTime);
                if ($parsed !== false) {
                    return $parsed;
                }
            } catch (\Throwable $exception) {
                continue;
            }
        }

        try {
            return Carbon::parse($localTime);
        } catch (\Throwable $exception) {
            return null;
        }
    }

    /**
     * @return array{logs: Collection, chart_labels: array, chart_values: array, total: int}
     */
    public function getSubscriberJourneyLogs(?int $subscriberId, string $duration = 'since_inception'): array
    {
        [$start, $end] = $this->resolveDateRange($duration);

        $journeyLogs = $this->queryJourneyLogs($subscriberId, null, $start, $end);
        $legacyLogs = $this->queryLegacyActivities($subscriberId, null, $start, $end);

        $merged = $journeyLogs
            ->concat($legacyLogs)
            ->sortByDesc(fn ($row) => $row['created_at'])
            ->values();

        return $this->buildResponse($merged);
    }

    /**
     * @return array{logs: Collection, chart_labels: array, chart_values: array, total: int}
     */
    public function getUserActivityLogs(?int $userId, string $duration = 'since_inception'): array
    {
        [$start, $end] = $this->resolveDateRange($duration);

        $journeyLogs = $this->queryJourneyLogs(null, $userId, $start, $end);
        $legacyLogs = $this->queryLegacyActivities(null, $userId, $start, $end);

        $merged = $journeyLogs
            ->concat($legacyLogs)
            ->sortByDesc(fn ($row) => $row['created_at'])
            ->values();

        return $this->buildResponse($merged);
    }

    private function queryJourneyLogs(?int $subscriberId, ?int $userId, ?Carbon $start, ?Carbon $end): Collection
    {
        $query = UserJourneyLog::query()->orderByDesc('created_at');

        if ($subscriberId) {
            $query->where(function ($q) use ($subscriberId) {
                $q->where('subscriber_id', $subscriberId)
                    ->orWhere('user_id', $subscriberId);
            });
        }

        if ($userId) {
            $query->where('user_id', $userId);
        }

        if ($start && $end) {
            $query->whereBetween('created_at', [$start, $end]);
        }

        return $query->get()->map(function (UserJourneyLog $log) {
            return [
                'id' => 'j-' . $log->id,
                'event_category' => $log->event_category,
                'event_type' => $log->event_type,
                'event_detail' => $log->event_detail,
                'user_name' => $log->user_name,
                'page_url' => $log->page_url,
                'http_method' => $log->http_method,
                'ip_address' => $log->ip_address,
                'source' => 'journey',
                'created_at' => $log->created_at,
                'created_at_formatted' => $log->created_at
                    ? $log->created_at->format('d-m-Y H:i:s')
                    : '',
            ];
        });
    }

    private function queryLegacyActivities(?int $subscriberId, ?int $userId, ?Carbon $start, ?Carbon $end): Collection
    {
        $query = Activities::query()->orderByDesc('created_at');

        if ($subscriberId) {
            $query->where(function ($q) use ($subscriberId) {
                $q->where('subscriber_id', $subscriberId)
                    ->orWhere('user_id', $subscriberId);
            });
        }

        if ($userId) {
            $query->where('user_id', $userId);
        }

        if ($start && $end) {
            $query->whereBetween('created_at', [$start, $end]);
        }

        return $query->get()->map(function (Activities $activity) {
            return [
                'id' => 'a-' . $activity->id,
                'event_category' => $this->mapLegacyCategory($activity->activity_name),
                'event_type' => $activity->activity_name,
                'event_detail' => $activity->activity_detail,
                'user_name' => $activity->user_name,
                'page_url' => null,
                'http_method' => null,
                'ip_address' => null,
                'source' => 'legacy',
                'created_at' => $activity->created_at,
                'created_at_formatted' => $activity->created_at
                    ? Carbon::parse($activity->created_at)->format('d-m-Y H:i:s')
                    : '',
            ];
        });
    }

    private function mapLegacyCategory(?string $activityName): string
    {
        $name = strtolower((string) $activityName);

        if ($this->strContains($name, 'subscriber') && $this->strContains($name, 'added')) {
            return 'registration';
        }
        if ($this->strContains($name, 'subscription') || $this->strContains($name, 'plan')) {
            return 'upgrade';
        }
        if ($this->strContains($name, 'renew')) {
            return 'renewal';
        }
        if ($this->strContains($name, 'deactiv') || $this->strContains($name, 'termin') || $this->strContains($name, 'delete')) {
            return 'termination';
        }
        if ($this->strContains($name, 'status')) {
            return 'status_change';
        }

        return 'operation';
    }

    private function strContains(string $haystack, string $needle): bool
    {
        return $needle !== '' && strpos($haystack, $needle) !== false;
    }

    private function strStartsWith(string $haystack, string $needle): bool
    {
        return $needle !== '' && strpos($haystack, $needle) === 0;
    }

    private function buildResponse(Collection $logs): array
    {
        $chartData = $logs->groupBy('event_category')
            ->map(fn ($group) => $group->count())
            ->sortDesc();

        return [
            'logs' => $logs,
            'chart_labels' => $chartData->keys()->values()->all(),
            'chart_values' => $chartData->values()->all(),
            'total' => $logs->count(),
        ];
    }

    private function resolveSubscriberId(User $actor): ?int
    {
        if ($actor->user_type === 'Subscriber') {
            return $actor->id;
        }

        if ($actor->user_type === 'User' && !empty($actor->added_by)) {
            return (int) $actor->added_by;
        }

        return null;
    }

    private function shouldSkipRequest(Request $request): bool
    {
        if (!$request->user()) {
            return true;
        }

        if ($request->ajax() || $request->wantsJson()) {
            return true;
        }

        $path = $request->path();

        $skipPatterns = [
            'journey-log',
            'activity-log-data',
            'subscription-history',
            'discount-offer-history',
            '_debugbar',
            'logout',
        ];

        foreach ($skipPatterns as $pattern) {
            if ($this->strContains($path, $pattern)) {
                return true;
            }
        }

        if (preg_match('/\.(css|js|png|jpg|jpeg|gif|svg|ico|woff|woff2|map)$/i', $path)) {
            return true;
        }

        return false;
    }

    public static function actor(): ?User
    {
        $user = Auth::user();

        return $user instanceof User ? $user : null;
    }
}
