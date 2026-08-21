<?php

namespace App\Services;

use App\Http\Controllers\AssociateController;
use App\Support\ModuleIcons;
use App\Support\PaymentModeChartFilter;
use App\Services\TaxSummaryService;
use App\Models\Activities;
use App\Models\Application_assignments;
use App\Models\Applications;
use App\Models\Client_Docs;
use App\Models\Client_discussions;
use App\Models\Clients;
use App\Models\Internal_Invoices;
use App\Models\Internal_communications;
use App\Models\PaymentARs;
use App\Models\Referrals;
use App\Models\SubscriberDashboardSetting;
use App\Models\Tickets;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Subscriber-configurable dashboard: which stat headers and which charts
 * (module + filter + duration + chart type) appear, and the data behind them.
 */
class DashboardPreferenceService
{
    private TaxSummaryService $taxSummaryService;

    public function __construct(?TaxSummaryService $taxSummaryService = null)
    {
        $this->taxSummaryService = $taxSummaryService ?? app(TaxSummaryService::class);
    }

    public const HEADER_SLOTS = 8;
    public const CHART_SLOTS = 4;
    public const CHART_COUNT_OPTIONS = [4];
    public const DEFAULT_CHART_COUNT = 4;

    /** Max distinct slices/bars per chart before the tail is dropped. */
    private const MAX_CHART_POINTS = 15;

    private const HEADER_OPTIONS = [
        'clients' => ['label' => 'Clients'],
        'applications' => ['label' => 'Applications'],
        'users' => ['label' => 'Users'],
        'invoices' => ['label' => 'Invoices'],
        'payments' => ['label' => 'Payments'],
        'referrals' => ['label' => 'Referrals'],
        'wallet' => ['label' => 'Wallet (USD)'],
        'meeting_notes' => ['label' => 'Meeting Notes'],
        'support_tickets' => ['label' => 'Support Tickets'],
        'documents' => ['label' => 'Documents'],
        'communications' => ['label' => 'Communications'],
        'activities' => ['label' => 'Activity Log'],
    ];

    private const CHART_MODULES = [
        'clients' => [
            'label' => 'Clients',
            'filters' => [
                'by_destination_country' => 'By Destination Country',
                'by_home_country' => 'By Home Country',
                'by_outstanding_payment' => 'By Outstanding Payment',
                'by_nationality' => 'By Nationality',
                'by_age_group' => 'By Age Group',
                'by_year' => 'By Year',
            ],
        ],
        'applications' => [
            'label' => 'Applications',
            'filters' => [
                'by_type' => 'By Application Type',
                'by_status' => 'By Application Status',
                'by_outstanding_payment' => 'By Outstanding Payment',
                'by_visa_country' => 'By Visa Country (Destination)',
                'by_client_home_country' => "By Clients' Home Country",
                'by_year' => 'By Year',
            ],
        ],
        'users' => [
            'label' => 'Users',
            'filters' => [
                'by_applications_assigned' => 'By Applications Assigned',
                'by_role' => 'By Role',
                'by_year' => 'By Year',
            ],
        ],
        'invoices' => [
            'label' => 'Invoices',
            'filters' => [
                'by_status' => 'By Payment Status',
                'by_type' => 'By Type (AR / AP)',
                'by_year' => 'By Year',
            ],
        ],
        'payments' => [
            'label' => 'Payments',
            'filters' => [
                'by_mode' => 'By Payment Mode',
                'by_type' => 'By Type (AR / AP)',
                'by_year' => 'By Year',
            ],
        ],
        'referrals' => [
            'label' => 'Referrals',
            'filters' => [
                'by_type' => 'By Referral Type',
                'by_year' => 'By Year',
            ],
        ],
        'meeting_notes' => [
            'label' => 'Meeting Notes',
            'filters' => [
                'by_type' => 'By Mode of Communication',
                'by_user' => 'By User',
                'by_year' => 'By Year',
            ],
        ],
        'support_tickets' => [
            'label' => 'Support Tickets',
            'filters' => [
                'by_status' => 'By Status',
                'by_support' => 'By Ticket Type',
                'by_year' => 'By Year',
            ],
        ],
        'documents' => [
            'label' => 'Documents',
            'filters' => [
                'by_type' => 'By Document Type',
                'by_folder' => 'By Folder / Section',
                'by_year' => 'By Year',
            ],
        ],
        'communications' => [
            'label' => 'Communications',
            'filters' => [
                'by_user' => 'By User',
                'by_year' => 'By Year',
            ],
        ],
        'activities' => [
            'label' => 'Activity Log',
            'filters' => [
                'by_type' => 'By Activity Type',
                'by_year' => 'By Year',
            ],
        ],
    ];

    /**
     * Keep in sync with public/web_assets/js/adwiseri-chart-types.js, which maps
     * area/scatter/bubble/gauge onto the Chart.js types that actually draw them.
     */
    private const CHART_TYPES = [
        'doughnut' => 'Doughnut',
        'pie' => 'Pie',
        'bar' => 'Bar',
        'line' => 'Line',
        'area' => 'Area',
        'scatter' => 'Scatter',
        'bubble' => 'Bubble',
        'gauge' => 'Gauge',
    ];

    private const DURATIONS = [
        'today' => 'Today',
        'last_week' => 'Last Week',
        'last_month' => 'Last Month',
        'last_quarter' => 'Last Quarter',
        'last_year' => 'Last Year',
        'since_inception' => 'Since Inception (All Data)',
    ];

    /** Mirrors the stat cards the dashboard shipped with before it was configurable. */
    private const DEFAULT_HEADERS = [
        'clients', 'applications', 'users', 'invoices',
        'payments', 'referrals', 'wallet', 'meeting_notes',
    ];

    /** Four default charts — same filters, distinct types in display order. */
    private const DEFAULT_CHARTS = [
        ['module' => 'clients', 'filter' => 'by_destination_country', 'duration' => 'since_inception', 'chart_type' => 'line'],
        ['module' => 'applications', 'filter' => 'by_type', 'duration' => 'since_inception', 'chart_type' => 'bar'],
        ['module' => 'users', 'filter' => 'by_applications_assigned', 'duration' => 'since_inception', 'chart_type' => 'area'],
        ['module' => 'payments', 'filter' => 'by_mode', 'duration' => 'since_inception', 'chart_type' => 'gauge'],
    ];

    public function headerOptions(): array
    {
        $options = [];

        foreach (self::HEADER_OPTIONS as $key => $meta) {
            $options[$key] = [
                'label' => $meta['label'],
                'icon' => ModuleIcons::for($key),
            ];
        }

        return $options;
    }

    public function chartModules(): array
    {
        return self::CHART_MODULES;
    }

    public function chartTypes(): array
    {
        return self::CHART_TYPES;
    }

    public function durations(): array
    {
        return self::DURATIONS;
    }

    public function defaultHeaders(): array
    {
        return self::DEFAULT_HEADERS;
    }

    public function defaultCharts(?int $chartCount = null): array
    {
        $chartCount = $this->normalizeChartCount($chartCount ?? self::DEFAULT_CHART_COUNT);

        return array_slice(self::DEFAULT_CHARTS, 0, $chartCount);
    }

    public function chartCountOptions(): array
    {
        return self::CHART_COUNT_OPTIONS;
    }

    public function normalizeChartCount($chartCount): int
    {
        $chartCount = (int) $chartCount;

        return in_array($chartCount, self::CHART_COUNT_OPTIONS, true)
            ? $chartCount
            : self::DEFAULT_CHART_COUNT;
    }

    public function resolveChartCount(User $subscriber): int
    {
        $setting = $this->getSetting($subscriber);

        if ($setting && Schema::hasColumn('subscriber_dashboard_settings', 'chart_count') && $setting->chart_count !== null) {
            return $this->normalizeChartCount($setting->chart_count);
        }

        return self::DEFAULT_CHART_COUNT;
    }

    /**
     * module|filter => true when the pair has at least one datapoint (since inception).
     * Used by Settings to disable empty chart options so blank charts are not offered.
     *
     * @return array<string, bool>
     */
    public function chartFilterAvailability(User $subscriber): array
    {
        $availability = [];

        foreach (self::CHART_MODULES as $module => $meta) {
            foreach (array_keys($meta['filters']) as $filter) {
                $data = $this->buildChartData($subscriber, [
                    'module' => $module,
                    'filter' => $filter,
                    'duration' => 'since_inception',
                    'chart_type' => 'doughnut',
                ]);

                $availability[$module . '|' . $filter] = $data['labels'] !== [];
            }
        }

        return $availability;
    }

    public function getSetting(User $subscriber): ?SubscriberDashboardSetting
    {
        if (!Schema::hasTable('subscriber_dashboard_settings')) {
            return null;
        }

        return SubscriberDashboardSetting::where('subscriber_id', $subscriber->id)->first();
    }

    public function hasSavedPreferences(User $subscriber): bool
    {
        $setting = $this->getSetting($subscriber);

        if (!$setting) {
            return false;
        }

        return (is_array($setting->headers) && count($setting->headers) > 0)
            || (is_array($setting->charts) && count($setting->charts) > 0)
            || (
                Schema::hasColumn('subscriber_dashboard_settings', 'chart_count')
                && $setting->chart_count !== null
                && (int) $setting->chart_count !== self::DEFAULT_CHART_COUNT
            );
    }

    /**
     * Always returns exactly HEADER_SLOTS entries; '' means "leave this slot empty".
     */
    public function resolveHeaders(User $subscriber): array
    {
        $setting = $this->getSetting($subscriber);
        $saved = ($setting && is_array($setting->headers) && count($setting->headers) > 0)
            ? $setting->headers
            : self::DEFAULT_HEADERS;

        return $this->normalizeHeaders($saved);
    }

    /**
     * Always returns exactly the configured chart-count entries; a null slot means "leave this position empty".
     */
    public function resolveCharts(User $subscriber): array
    {
        $setting = $this->getSetting($subscriber);
        $chartCount = $this->resolveChartCount($subscriber);
        $saved = ($setting && is_array($setting->charts) && count($setting->charts) > 0)
            ? $setting->charts
            : $this->defaultCharts($chartCount);

        return $this->normalizeCharts($saved, $chartCount);
    }

    public function saveSettings(User $subscriber, array $headers, array $charts, $chartCount = null): SubscriberDashboardSetting
    {
        $resolvedCount = $chartCount !== null
            ? $this->normalizeChartCount($chartCount)
            : $this->resolveChartCount($subscriber);

        $payload = [
            'headers' => $this->normalizeHeaders($headers),
            'charts' => $this->normalizeCharts($charts, $resolvedCount),
        ];

        if (Schema::hasColumn('subscriber_dashboard_settings', 'chart_count')) {
            $payload['chart_count'] = $resolvedCount;
        }

        return SubscriberDashboardSetting::updateOrCreate(
            ['subscriber_id' => $subscriber->id],
            $payload
        );
    }

    /**
     * Drops unknown keys and repeats, then pads/truncates to HEADER_SLOTS.
     */
    public function normalizeHeaders(array $headers): array
    {
        $clean = [];

        foreach ($headers as $key) {
            $key = trim((string) $key);

            if ($key === '' || !isset(self::HEADER_OPTIONS[$key]) || in_array($key, $clean, true)) {
                continue;
            }

            $clean[] = $key;

            if (count($clean) >= self::HEADER_SLOTS) {
                break;
            }
        }

        return array_pad($clean, self::HEADER_SLOTS, '');
    }

    /**
     * Drops invalid slots and repeated module+filter pairs, then pads/truncates to the selected chart count.
     */
    public function normalizeCharts(array $charts, $chartCount = null): array
    {
        $slotCount = $this->normalizeChartCount($chartCount ?? self::CHART_SLOTS);
        $clean = [];
        $seen = [];

        foreach ($charts as $slot) {
            if (!is_array($slot)) {
                continue;
            }

            $module = trim((string) ($slot['module'] ?? ''));
            $filter = trim((string) ($slot['filter'] ?? ''));

            if ($module === '' || !isset(self::CHART_MODULES[$module])) {
                continue;
            }

            if ($filter === '' || !isset(self::CHART_MODULES[$module]['filters'][$filter])) {
                continue;
            }

            $pair = $module . '|' . $filter;
            if (in_array($pair, $seen, true)) {
                continue;
            }
            $seen[] = $pair;

            $duration = trim((string) ($slot['duration'] ?? ''));
            $chartType = trim((string) ($slot['chart_type'] ?? ''));

            $clean[] = [
                'module' => $module,
                'filter' => $filter,
                'duration' => isset(self::DURATIONS[$duration]) ? $duration : 'since_inception',
                'chart_type' => isset(self::CHART_TYPES[$chartType]) ? $chartType : 'doughnut',
            ];

            if (count($clean) >= $slotCount) {
                break;
            }
        }

        return array_pad($clean, $slotCount, null);
    }

    /**
     * @return array<int, array{key:string,label:string,icon:string,value:string}>
     */
    public function buildHeaderCards(User $subscriber, User $viewer): array
    {
        $cards = [];

        foreach ($this->resolveHeaders($subscriber) as $key) {
            if ($key === '') {
                continue;
            }

            $cards[] = [
                'key' => $key,
                'label' => self::HEADER_OPTIONS[$key]['label'],
                'icon' => ModuleIcons::for($key),
                'value' => $this->headerValue($key, $subscriber, $viewer),
            ];
        }

        return $cards;
    }

    /**
     * @return array<int, array{id:string,title:string,type:string,labels:array,values:array,empty:bool}>
     */
    public function buildCharts(User $subscriber): array
    {
        $charts = [];

        foreach ($this->resolveCharts($subscriber) as $index => $slot) {
            if (!is_array($slot)) {
                continue;
            }

            $data = $this->buildChartData($subscriber, $slot);

            $charts[] = [
                'id' => 'dashChart' . ($index + 1),
                'title' => $this->chartTitle($slot),
                'type' => $slot['chart_type'],
                'labels' => $data['labels'],
                'values' => $data['values'],
                'empty' => $data['labels'] === [],
            ];
        }

        return $charts;
    }

    public function chartTitle(array $slot): string
    {
        $module = self::CHART_MODULES[$slot['module']]['label'] ?? $slot['module'];
        $filter = self::CHART_MODULES[$slot['module']]['filters'][$slot['filter']] ?? $slot['filter'];
        $duration = self::DURATIONS[$slot['duration']] ?? '';

        $title = $module . ' — ' . $filter;

        if ($slot['duration'] !== 'since_inception' && $duration !== '') {
            $title .= ' (' . $duration . ')';
        }

        return $title;
    }

    private function headerValue(string $key, User $subscriber, User $viewer): string
    {
        switch ($key) {
            case 'clients':
                return (string) Clients::where('subscriber_id', $subscriber->id)->count();

            case 'applications':
                return (string) Applications::where('subscriber_id', $subscriber->id)->count();

            case 'users':
                return (string) User::where('added_by', $subscriber->id)->count();

            case 'invoices':
                $invoices = Internal_Invoices::where('subscriber_id', $subscriber->id)->get();
                $ar = $invoices->filter(fn ($i) => strtolower((string) $i->type) === 'ar')->count();
                $ap = $invoices->filter(fn ($i) => strtolower((string) $i->type) === 'ap')->count();
                $taxCollected = $this->taxSummaryService->totalCollectedTax($subscriber);

                return 'AR: ' . $ar
                    . ' &nbsp; AP: ' . $ap
                    . ' &nbsp; Tax Collected: ' . number_format($taxCollected, 2, '.', '');

            case 'payments':
                $ap = round((float) PaymentARs::whereRaw('LOWER(type) = ?', ['ap'])
                    ->where('subscriber_id', $subscriber->id)
                    ->sum('paid_amount'), 2);
                $ar = round((float) PaymentARs::whereRaw('LOWER(type) = ?', ['ar'])
                    ->where('subscriber_id', $subscriber->id)
                    ->sum(DB::raw('amount - paid_amount')), 2);

                return 'AR: ' . number_format($ar, 2, '.', '')
                    . '<br>AP: ' . number_format($ap, 2, '.', '');

            case 'referrals':
                return (string) $this->referralsQuery($subscriber)
                    ->where('type', 'Referral Commission')
                    ->count();

            case 'wallet':
                return '$' . ($viewer->wallet ?? 0);

            case 'meeting_notes':
                return (string) Client_discussions::where('subscriber_id', $subscriber->id)->count();

            case 'support_tickets':
                return (string) Tickets::where('subscriber_id', $subscriber->id)->count();

            case 'documents':
                return (string) Client_Docs::where('user_id', $subscriber->id)->count();

            case 'communications':
                return (string) Internal_communications::where('subscriber_id', $subscriber->id)->count();

            case 'activities':
                return (string) Activities::where('subscriber_id', $subscriber->id)->count();
        }

        return '0';
    }

    private function buildChartData(User $subscriber, array $slot): array
    {
        $module = $slot['module'];
        $filter = $slot['filter'];

        // Assignments live in their own table rather than on users.
        if ($module === 'users' && $filter === 'by_applications_assigned') {
            return $this->formatChartSeries(
                $module,
                $filter,
                $this->aggregateUsersByApplicationsAssigned($subscriber, $slot['duration'])
            );
        }

        // Distinct clients per destination (visa) country — same idea as Analytics "By Visa Country".
        if ($module === 'clients' && $filter === 'by_destination_country') {
            return $this->formatChartSeries(
                $module,
                $filter,
                $this->aggregateClientsByDestinationCountry($subscriber, $slot['duration'])
            );
        }

        if ($module === 'clients' && $filter === 'by_age_group') {
            return $this->formatChartSeries(
                $module,
                $filter,
                $this->aggregateClientsByAgeGroup($subscriber, $slot['duration'])
            );
        }

        if ($module === 'applications' && $filter === 'by_client_home_country') {
            return $this->formatChartSeries(
                $module,
                $filter,
                $this->aggregateApplicationsByClientHomeCountry($subscriber, $slot['duration'])
            );
        }

        if ($module === 'clients' && $filter === 'by_outstanding_payment') {
            return $this->formatChartSeries(
                $module,
                $filter,
                $this->aggregateClientsByOutstandingPayment($subscriber, $slot['duration'])
            );
        }

        if ($module === 'applications' && $filter === 'by_outstanding_payment') {
            return $this->formatChartSeries(
                $module,
                $filter,
                $this->aggregateApplicationsByOutstandingPayment($subscriber, $slot['duration'])
            );
        }

        $query = $this->baseQuery($module, $subscriber);

        if (!$query) {
            return ['labels' => [], 'values' => []];
        }

        $table = $query->getModel()->getTable();
        $this->applyDuration($query, $table, $slot['duration']);

        if ($filter === 'by_year') {
            return $this->formatChartSeries($module, $filter, $this->aggregateByYear($query, $table));
        }

        $column = $this->filterColumn($module, $filter);

        if ($column === null) {
            return ['labels' => [], 'values' => []];
        }

        // Home country must use clients.country (residence), never visa/destination fields.
        if ($module === 'clients' && $filter === 'by_home_country') {
            $column = $this->resolveClientsHomeCountryColumn($table);
            if ($column === null) {
                return ['labels' => [], 'values' => []];
            }
        }

        if ($module === 'payments' && $filter === 'by_mode') {
            PaymentModeChartFilter::applyToQuery($query, $column, $table);
        }

        return $this->formatChartSeries(
            $module,
            $filter,
            $this->aggregateByColumn($query, $table, $column)
        );
    }

    /**
     * Staff users grouped by how many applications they are assigned to.
     */
    private function aggregateUsersByApplicationsAssigned(User $subscriber, string $duration): array
    {
        if (!Schema::hasTable('application_assignments')) {
            return ['labels' => [], 'values' => []];
        }

        $query = Application_assignments::query();

        if (Schema::hasColumn('application_assignments', 'subscriber_id')) {
            $query->where('subscriber_id', $subscriber->id);
        }

        $query->whereHas('user', function ($userQuery) {
            $userQuery->where('user_type', 'User');
        });

        $this->applyDuration($query, 'application_assignments', $duration);

        $rows = $query
            ->select('user_id', DB::raw('COUNT(*) as aggregate'))
            ->groupBy('user_id')
            ->orderByDesc('aggregate')
            ->limit(self::MAX_CHART_POINTS)
            ->get();

        if ($rows->isEmpty()) {
            return ['labels' => [], 'values' => []];
        }

        $userNames = User::query()
            ->whereIn('id', $rows->pluck('user_id')->filter()->all())
            ->pluck('name', 'id');

        $labels = [];
        $values = [];

        foreach ($rows as $row) {
            $userId = (int) $row->user_id;
            if ($userId <= 0) {
                continue;
            }

            $labels[] = trim((string) ($userNames[$userId] ?? ('User #' . $userId))) . ' (' . $userId . ')';
            $values[] = (int) $row->aggregate;
        }

        return ['labels' => $labels, 'values' => $values];
    }

    private function baseQuery(string $module, User $subscriber)
    {
        switch ($module) {
            case 'clients':
                return Clients::where('subscriber_id', $subscriber->id);

            case 'applications':
                return Applications::where('subscriber_id', $subscriber->id);

            case 'users':
                return User::where('added_by', $subscriber->id);

            case 'invoices':
                return Internal_Invoices::where('subscriber_id', $subscriber->id);

            case 'payments':
                return PaymentARs::where('subscriber_id', $subscriber->id);

            case 'referrals':
                // Chart needs the full ledger (all types). Header count stays page-scoped.
                return $this->referralsChartQuery($subscriber);

            case 'meeting_notes':
                return Client_discussions::where('subscriber_id', $subscriber->id);

            case 'support_tickets':
                return Tickets::where('subscriber_id', $subscriber->id);

            case 'documents':
                return Client_Docs::where('user_id', $subscriber->id);

            case 'communications':
                return Internal_communications::where('subscriber_id', $subscriber->id);

            case 'activities':
                return Activities::where('subscriber_id', $subscriber->id);
        }

        return null;
    }

    /**
     * Scoped the same way the Referrals page scopes it (WebController::referrals),
     * so the dashboard count and that page agree.
     */
    private function referralsQuery(User $subscriber)
    {
        return Referrals::where('userid', '!=', $subscriber->id)
            ->where('referral_code', $subscriber->referral);
    }

    /**
     * All wallet/referral ledger rows for this subscriber, so "By Referral Type"
     * can show Commission, cashback, one-off credits, upgrades, etc.
     */
    private function referralsChartQuery(User $subscriber)
    {
        $referralCode = trim((string) ($subscriber->referral ?? ''));

        return Referrals::query()->where(function ($query) use ($subscriber, $referralCode) {
            $query->where('userid', $subscriber->id);

            if ($referralCode !== '') {
                $query->orWhere('referral_code', $referralCode);
            }
        });
    }

    private function filterColumn(string $module, string $filter): ?string
    {
        $map = [
            'clients' => [
                'by_home_country' => 'country',
                'by_nationality' => 'nationality',
            ],
            'applications' => [
                'by_type' => 'application_name',
                'by_status' => 'application_status',
                'by_visa_country' => 'visa_country',
            ],
            'users' => [
                'by_role' => 'user_type',
            ],
            'invoices' => [
                'by_status' => 'status',
                'by_type' => 'type',
            ],
            'payments' => [
                'by_mode' => 'payment_mode',
                'by_type' => 'type',
            ],
            'referrals' => [
                'by_type' => 'type',
            ],
            'meeting_notes' => [
                'by_type' => 'communication_type',
                'by_user' => 'user_name',
            ],
            'support_tickets' => [
                'by_status' => 'status',
                'by_support' => 'support',
            ],
            'documents' => [
                'by_type' => 'doc_type',
                'by_folder' => 'doc_folder',
            ],
            'communications' => [
                'by_user' => 'user_name',
            ],
            'activities' => [
                'by_type' => 'activity_name',
            ],
        ];

        return $map[$module][$filter] ?? null;
    }

    /**
     * Prefer the real home-country column. Some older schemas used a typo `counntry`.
     */
    private function resolveClientsHomeCountryColumn(string $table): ?string
    {
        if (Schema::hasColumn($table, 'country')) {
            return 'country';
        }

        if (Schema::hasColumn($table, 'counntry')) {
            return 'counntry';
        }

        return null;
    }

    /**
     * Distinct clients grouped by applications.visa_country (destination).
     */
    private function aggregateClientsByDestinationCountry(User $subscriber, string $duration): array
    {
        if (!Schema::hasColumn('applications', 'visa_country') || !Schema::hasColumn('applications', 'client_id')) {
            return ['labels' => [], 'values' => []];
        }

        $query = Applications::where('subscriber_id', $subscriber->id)
            ->whereNotNull('visa_country')
            ->where('visa_country', '!=', '')
            ->whereNotNull('client_id');

        $this->applyDuration($query, 'applications', $duration);

        $rows = $query->select('visa_country', DB::raw('COUNT(DISTINCT client_id) as aggregate'))
            ->groupBy('visa_country')
            ->orderByDesc('aggregate')
            ->limit(self::MAX_CHART_POINTS)
            ->get();

        return [
            'labels' => $rows->pluck('visa_country')->map(fn ($v) => (string) $v)->all(),
            'values' => $rows->pluck('aggregate')->map(fn ($v) => (int) $v)->all(),
        ];
    }

    private function aggregateClientsByAgeGroup(User $subscriber, string $duration): array
    {
        if (!Schema::hasColumn('clients', 'dob')) {
            return ['labels' => [], 'values' => []];
        }

        $query = Clients::where('subscriber_id', $subscriber->id)
            ->whereNotNull('dob')
            ->where('dob', '!=', '')
            ->where('dob', '!=', '0000-00-00');

        $this->applyDuration($query, 'clients', $duration);

        $rows = $query->selectRaw("
                CASE
                    WHEN TIMESTAMPDIFF(YEAR, dob, CURDATE()) < 18 THEN 'Under 18'
                    WHEN TIMESTAMPDIFF(YEAR, dob, CURDATE()) BETWEEN 18 AND 24 THEN '18-24'
                    WHEN TIMESTAMPDIFF(YEAR, dob, CURDATE()) BETWEEN 25 AND 34 THEN '25-34'
                    WHEN TIMESTAMPDIFF(YEAR, dob, CURDATE()) BETWEEN 35 AND 44 THEN '35-44'
                    WHEN TIMESTAMPDIFF(YEAR, dob, CURDATE()) BETWEEN 45 AND 54 THEN '45-55'
                    ELSE '55+'
                END AS age_group,
                COUNT(*) AS aggregate
            ")
            ->groupBy('age_group')
            ->orderByRaw("FIELD(age_group, 'Under 18', '18-24', '25-34', '35-44', '45-55', '55+')")
            ->get();

        $labels = [];
        $values = [];

        foreach ($rows as $row) {
            if ((int) $row->aggregate <= 0) {
                continue;
            }
            $labels[] = (string) $row->age_group;
            $values[] = (int) $row->aggregate;
        }

        return ['labels' => $labels, 'values' => $values];
    }

    private function aggregateApplicationsByClientHomeCountry(User $subscriber, string $duration): array
    {
        $homeColumn = $this->resolveClientsHomeCountryColumn('clients');

        if ($homeColumn === null || !Schema::hasColumn('applications', 'client_id')) {
            return ['labels' => [], 'values' => []];
        }

        $query = Applications::query()
            ->join('clients', 'clients.id', '=', 'applications.client_id')
            ->where('applications.subscriber_id', $subscriber->id)
            ->whereNotNull('clients.' . $homeColumn)
            ->where('clients.' . $homeColumn, '!=', '');

        if ($duration !== 'since_inception' && Schema::hasColumn('applications', 'created_at')) {
            $now = Carbon::now();
            switch ($duration) {
                case 'today':
                    $query->whereBetween('applications.created_at', [$now->copy()->startOfDay(), $now->copy()->endOfDay()]);
                    break;
                case 'last_week':
                    $query->whereBetween('applications.created_at', [$now->copy()->subDays(6)->startOfDay(), $now->copy()->endOfDay()]);
                    break;
                case 'last_month':
                    $query->whereBetween('applications.created_at', [$now->copy()->subDays(29)->startOfDay(), $now->copy()->endOfDay()]);
                    break;
                case 'last_quarter':
                    $query->whereBetween('applications.created_at', [$now->copy()->subMonths(3)->startOfMonth(), $now->copy()->endOfMonth()]);
                    break;
                case 'last_year':
                    $query->whereBetween('applications.created_at', [
                        $now->copy()->subYear()->startOfYear(),
                        $now->copy()->subYear()->endOfYear(),
                    ]);
                    break;
            }
        }

        $rows = $query->select('clients.' . $homeColumn . ' as home_country', DB::raw('COUNT(*) as aggregate'))
            ->groupBy('clients.' . $homeColumn)
            ->orderByDesc('aggregate')
            ->limit(self::MAX_CHART_POINTS)
            ->get();

        return [
            'labels' => $rows->pluck('home_country')->map(fn ($v) => (string) $v)->all(),
            'values' => $rows->pluck('aggregate')->map(fn ($v) => (int) $v)->all(),
        ];
    }

    /**
     * Total AR outstanding per client (all invoices clubbed per client).
     */
    private function aggregateClientsByOutstandingPayment(User $subscriber, string $duration): array
    {
        if (!Schema::hasTable('payment_ar') || !Schema::hasColumn('payment_ar', 'client_id')) {
            return ['labels' => [], 'values' => []];
        }

        $query = PaymentARs::query()
            ->where('payment_ar.subscriber_id', $subscriber->id)
            ->whereRaw('LOWER(payment_ar.type) = ?', ['ar'])
            ->whereNotNull('payment_ar.client_id')
            ->join('clients', 'clients.id', '=', 'payment_ar.client_id');

        $this->applyPaymentArDuration($query, $duration);

        $rows = $query
            ->select(
                'clients.name as client_name',
                DB::raw('SUM(payment_ar.amount - payment_ar.paid_amount) as aggregate')
            )
            ->groupBy('payment_ar.client_id', 'clients.name')
            ->havingRaw('SUM(payment_ar.amount - payment_ar.paid_amount) > 0')
            ->orderByDesc('aggregate')
            ->limit(self::MAX_CHART_POINTS)
            ->get();

        return [
            'labels' => $rows->pluck('client_name')->map(fn ($v) => (string) $v)->all(),
            'values' => $rows->pluck('aggregate')->map(fn ($v) => round((float) $v, 2))->all(),
        ];
    }

    /**
     * Outstanding AR per invoice/service (one bar per receivable line).
     */
    private function aggregateApplicationsByOutstandingPayment(User $subscriber, string $duration): array
    {
        if (!Schema::hasTable('payment_ar') || !Schema::hasColumn('payment_ar', 'client_id')) {
            return ['labels' => [], 'values' => []];
        }

        $query = PaymentARs::query()
            ->where('payment_ar.subscriber_id', $subscriber->id)
            ->whereRaw('LOWER(payment_ar.type) = ?', ['ar'])
            ->whereNotNull('payment_ar.client_id')
            ->join('clients', 'clients.id', '=', 'payment_ar.client_id')
            ->leftJoin('applications', 'applications.id', '=', 'payment_ar.application_id');

        $this->applyPaymentArDuration($query, $duration);

        $rows = $query
            ->select(
                DB::raw("CONCAT(
                    clients.name,
                    ' - ',
                    COALESCE(
                        NULLIF(TRIM(CONCAT(applications.application_name, ' (', applications.application_id, ')')), '()'),
                        NULLIF(TRIM(MAX(payment_ar.service_description)), ''),
                        CONCAT('Invoice ', payment_ar.invoice_no)
                    )
                ) as label"),
                DB::raw('(MAX(payment_ar.amount) - SUM(payment_ar.paid_amount)) as aggregate')
            )
            ->groupBy(
                'payment_ar.client_id',
                'payment_ar.application_id',
                'payment_ar.invoice_no',
                'clients.name',
                'applications.application_name',
                'applications.application_id'
            )
            ->havingRaw('(MAX(payment_ar.amount) - SUM(payment_ar.paid_amount)) > 0')
            ->orderByDesc('aggregate')
            ->limit(self::MAX_CHART_POINTS)
            ->get();

        return [
            'labels' => $rows->pluck('label')->map(fn ($v) => (string) $v)->all(),
            'values' => $rows->pluck('aggregate')->map(fn ($v) => round((float) $v, 2))->all(),
        ];
    }

    /**
     * @param  array{labels: array, values: array}  $series
     * @return array{labels: array, values: array}
     */
    private function formatChartSeries(string $module, string $filter, array $series): array
    {
        $labels = $series['labels'] ?? [];
        $values = $series['values'] ?? [];

        if (($module === 'payments' || $module === 'invoices') && $filter === 'by_type') {
            $labels = array_map(function ($label) {
                $normalized = strtoupper(trim((string) $label));

                return in_array($normalized, ['AR', 'AP'], true) ? $normalized : strtoupper((string) $label);
            }, $labels);
        }

        if ($module === 'clients' && $filter === 'by_age_group') {
            $labels = array_map(function ($label) {
                $raw = trim((string) $label);
                $raw = str_replace('55 +', '55+', $raw);

                if ($raw === '') {
                    return $raw;
                }

                if (stripos($raw, 'year') !== false) {
                    return $raw;
                }

                return $raw . ' Years';
            }, $labels);
        }

        if ($module === 'applications' && $filter === 'by_status') {
            return $this->sortApplicationStatusSeries($labels, $values);
        }

        if ($module === 'payments' && $filter === 'by_mode') {
            return PaymentModeChartFilter::filterSeries($labels, $values);
        }

        return [
            'labels' => $labels,
            'values' => $values,
        ];
    }

    /**
     * Order application-status slices in the same workflow sequence used across the app.
     *
     * @return array{labels: array, values: array}
     */
    private function sortApplicationStatusSeries(array $labels, array $values): array
    {
        $sequence = AssociateController::APPLICATION_STATUS_OPTIONS;
        $indexed = [];

        foreach ($labels as $index => $label) {
            $key = trim((string) $label);
            if ($key === '') {
                continue;
            }

            $indexed[$key] = $values[$index] ?? 0;
        }

        if ($indexed === []) {
            return ['labels' => [], 'values' => []];
        }

        $orderedLabels = [];
        $orderedValues = [];
        $seen = [];

        foreach ($sequence as $status) {
            if (!array_key_exists($status, $indexed)) {
                continue;
            }

            $orderedLabels[] = $status;
            $orderedValues[] = (int) $indexed[$status];
            $seen[$status] = true;
        }

        $remaining = [];
        foreach ($indexed as $status => $count) {
            if (isset($seen[$status])) {
                continue;
            }

            $remaining[$status] = (int) $count;
        }

        arsort($remaining, SORT_NUMERIC);

        foreach ($remaining as $status => $count) {
            $orderedLabels[] = (string) $status;
            $orderedValues[] = $count;
        }

        return [
            'labels' => $orderedLabels,
            'values' => $orderedValues,
        ];
    }

    /**
     * The live schema drifts from the migration history, so a missing column
     * must degrade to an empty chart rather than a fatal SQL error.
     */
    private function aggregateByColumn($query, string $table, string $column): array
    {
        if (!Schema::hasColumn($table, $column)) {
            return ['labels' => [], 'values' => []];
        }

        $rows = $query->select($column, DB::raw('COUNT(*) as aggregate'))
            ->whereNotNull($column)
            ->where($column, '!=', '')
            ->groupBy($column)
            ->orderByDesc('aggregate')
            ->limit(self::MAX_CHART_POINTS)
            ->get();

        return [
            'labels' => $rows->pluck($column)->map(fn ($v) => (string) $v)->all(),
            'values' => $rows->pluck('aggregate')->map(fn ($v) => (int) $v)->all(),
        ];
    }

    private function aggregateByYear($query, string $table): array
    {
        if (!Schema::hasColumn($table, 'created_at')) {
            return ['labels' => [], 'values' => []];
        }

        $rows = $query->select(DB::raw('YEAR(created_at) as bucket'), DB::raw('COUNT(*) as aggregate'))
            ->whereNotNull('created_at')
            ->groupBy(DB::raw('YEAR(created_at)'))
            ->orderBy('bucket')
            ->limit(self::MAX_CHART_POINTS)
            ->get();

        return [
            'labels' => $rows->pluck('bucket')->map(fn ($v) => (string) $v)->all(),
            'values' => $rows->pluck('aggregate')->map(fn ($v) => (int) $v)->all(),
        ];
    }

    private function applyDuration($query, string $table, string $duration): void
    {
        if ($duration === 'since_inception' || !Schema::hasColumn($table, 'created_at')) {
            return;
        }

        $now = Carbon::now();

        switch ($duration) {
            case 'today':
                $query->whereBetween('created_at', [$now->copy()->startOfDay(), $now->copy()->endOfDay()]);
                break;

            case 'last_week':
                $query->whereBetween('created_at', [$now->copy()->subDays(6)->startOfDay(), $now->copy()->endOfDay()]);
                break;

            case 'last_month':
                $query->whereBetween('created_at', [$now->copy()->subDays(29)->startOfDay(), $now->copy()->endOfDay()]);
                break;

            case 'last_quarter':
                $query->whereBetween('created_at', [$now->copy()->subMonths(3)->startOfMonth(), $now->copy()->endOfMonth()]);
                break;

            case 'last_year':
                $query->whereBetween('created_at', [
                    $now->copy()->subYear()->startOfYear(),
                    $now->copy()->subYear()->endOfYear(),
                ]);
                break;
        }
    }

    private function applyPaymentArDuration($query, string $duration): void
    {
        if ($duration === 'since_inception' || !Schema::hasColumn('payment_ar', 'created_at')) {
            return;
        }

        $now = Carbon::now();

        switch ($duration) {
            case 'today':
                $query->whereBetween('payment_ar.created_at', [$now->copy()->startOfDay(), $now->copy()->endOfDay()]);
                break;

            case 'last_week':
                $query->whereBetween('payment_ar.created_at', [$now->copy()->subDays(6)->startOfDay(), $now->copy()->endOfDay()]);
                break;

            case 'last_month':
                $query->whereBetween('payment_ar.created_at', [$now->copy()->subDays(29)->startOfDay(), $now->copy()->endOfDay()]);
                break;

            case 'last_quarter':
                $query->whereBetween('payment_ar.created_at', [$now->copy()->subMonths(3)->startOfMonth(), $now->copy()->endOfMonth()]);
                break;

            case 'last_year':
                $query->whereBetween('payment_ar.created_at', [
                    $now->copy()->subYear()->startOfYear(),
                    $now->copy()->subYear()->endOfYear(),
                ]);
                break;
        }
    }
}
