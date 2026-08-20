<?php

namespace App\Http\Controllers;



use Illuminate\Http\Request;

use App\Mail\Invoicemail;
use App\Mail\PlanSubscriptionMail;
use App\Models\User;
use App\Models\Clients;
use App\Models\Currency;
use App\Models\Offers;
use App\Models\Countries;
use App\Models\States;
use App\Models\Contactus;
use App\Models\Membership;
use App\Models\Features;
use App\Models\About_Advisori;
use App\Models\Messages;
use App\Models\Activities;
use App\Models\Invoices;
use App\Models\Subscriber_Categories;
use App\Models\Subscriber_Sub_Categories;
use App\Models\Client_jobs;
use App\Models\Client_Docs;
use App\Models\Applications;
use App\Models\Application_assignments;
use App\Models\Internal_Invoices;
use App\Models\Client_discussions;
use App\Models\Tickets;
use App\Models\PaymentARs;
use App\Support\PaymentModeChartFilter;
use App\Models\Invoice_settings;
use App\Models\DemoRequests;
use App\Models\UserRoles;
use App\Models\Referrals;
use App\Models\Used_referrals;
use App\Models\AffiliateCommissionEarnt;
use App\Models\Internal_communications;
use App\Models\Affiliates;
use App\Models\Dependants;
use App\Models\Feedbacks;
use App\Services\OfferBenefitService;
use App\Http\Controllers\Concerns\ScopesConsultancyReports;
use App\Http\Controllers\AssociateController;
use Carbon\CarbonInterface;

use DataTables;
use DateTime;
use DateTimeZone;
use Carbon\Carbon;
use DB;


class SubscriberFilterController extends Controller
{
    use ScopesConsultancyReports;

    /**
     * Age buckets shared by Clients / Users By Age Group analytics charts.
     * Returns [labelsSqlFragment, caseSqlExpression] for TIMESTAMPDIFF age.
     */
    private function ageGroupBucketSql(string $dobColumn = 'dob'): array
    {
        $ageExpr = "TIMESTAMPDIFF(YEAR, {$dobColumn}, CURDATE())";

        $labelsSql = '(SELECT "Under 18" AS age_group UNION ALL
            SELECT "18-24" UNION ALL
            SELECT "25-34" UNION ALL
            SELECT "35-44" UNION ALL
            SELECT "45-55" UNION ALL
            SELECT "55 +" ) AS age_groups';

        /* 45-55 = ages 45..54; 55+ = ages 55 and above (no double-count at 55). */
        $caseSql = "
            CASE
                WHEN {$ageExpr} < 18 THEN 'Under 18'
                WHEN {$ageExpr} BETWEEN 18 AND 24 THEN '18-24'
                WHEN {$ageExpr} BETWEEN 25 AND 34 THEN '25-34'
                WHEN {$ageExpr} BETWEEN 35 AND 44 THEN '35-44'
                WHEN {$ageExpr} BETWEEN 45 AND 54 THEN '45-55'
                ELSE '55 +'
            END
        ";

        return [$labelsSql, $caseSql];
    }

    private function applyAgeGroupConsultancyScope($query, User $user, string $column)
    {
        if ($this->isConsultancyMember($user)) {
            $subscriberId = $this->consultancySubscriberId($user);
            if ($subscriberId) {
                return $query->where($column, $subscriberId);
            }
        }

        if ($user->user_type === 'admin' && !empty(request()->subid)) {
            return $query->where($column, (int) request()->subid);
        }

        return $query;
    }

    private function parseReportDate(?string $value, bool $isEndDate = false): Carbon
    {
        $value = trim((string) $value);

        if ($value === '') {
            // No date supplied: treat as an open range (all-time) instead of "today".
            // Charts such as "By Age Group" / "By Gender" are run without an explicit
            // date range, so defaulting the start bound to "today" made them return no rows.
            return $isEndDate ? Carbon::now()->endOfDay() : Carbon::create(1970, 1, 1)->startOfDay();
        }

        $normalizedValue = str_replace(['/', '.'], '-', $value);

        $supportedFormats = [
            'd-m-Y',
            'Y-m-d',
            'm-d-Y',
            'Y-n-j',
            'j-n-Y',
            'n-j-Y',
        ];

        foreach ($supportedFormats as $format) {
            try {
                $date = Carbon::createFromFormat($format, $normalizedValue);
                if ($date !== false) {
                    return $isEndDate ? $date->endOfDay() : $date->startOfDay();
                }
            } catch (\Throwable $e) {
                // Try next format.
            }
        }

        if (preg_match('/^\d{1,2}$/', $normalizedValue)) {
            $month = (int) $normalizedValue;
            if ($month >= 1 && $month <= 12) {
                $date = Carbon::create(Carbon::now()->year, $month, 1);
                return $isEndDate ? $date->endOfMonth()->endOfDay() : $date->startOfMonth()->startOfDay();
            }
        }

        $date = Carbon::parse($value);
        return $isEndDate ? $date->endOfDay() : $date->startOfDay();
    }

    private function formatClientApplicationChartLabel(?string $clientName, ?string $applicationName, $applicationId = null): string
    {
        $clientName = trim((string) $clientName);
        $applicationName = trim((string) $applicationName);

        if ($applicationName === '' && $applicationId !== null && $applicationId !== '') {
            $applicationName = 'Application ' . $applicationId;
        }

        $parts = preg_split('/\s+/', $clientName, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $shortName = $parts[0] ?? '';

        if (count($parts) > 1) {
            $shortName .= ' ' . strtoupper(mb_substr($parts[count($parts) - 1], 0, 1)) . '.';
        }

        if ($applicationName !== '') {
            return $shortName !== '' ? $shortName . ' - ' . $applicationName : $applicationName;
        }

        return $shortName !== '' ? $shortName : 'Unknown';
    }

    private function buildTimelineDurationData($query, $query1): \Illuminate\Support\Collection
    {
        $currentDate = now();
        $lastWeekStart = now()->subWeek()->startOfWeek();
        $lastWeekEnd = now()->subWeek()->endOfWeek();
        $lastMonthStart = now()->subMonth()->startOfMonth();
        $lastMonthEnd = now()->subMonth()->endOfMonth();
        $lastQuarterStart = now()->subQuarter()->startOfQuarter();
        $lastQuarterEnd = now()->subQuarter()->endOfQuarter();
        $lastYearStart = now()->subYear()->startOfYear();
        $lastYearEnd = now()->subYear()->endOfYear();

        $inspectionStartDate = (clone $query1)->orderBy('created_at', 'asc')->first();

        $todayApplications = (clone $query)->whereDate('created_at', $currentDate)
            ->selectRaw("'Today' as type, COUNT(*) as count")
            ->get();

        $lastWeekApplications = (clone $query)->whereBetween('created_at', [$lastWeekStart, $lastWeekEnd])
            ->selectRaw("'Last Week' as type, COUNT(*) as count")
            ->get();

        $lastMonthApplications = (clone $query)->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])
            ->selectRaw("'Last Month' as type, COUNT(*) as count")
            ->get();

        $lastQuarterApplications = (clone $query)->whereBetween('created_at', [$lastQuarterStart, $lastQuarterEnd])
            ->selectRaw("'Last Quarter' as type, COUNT(*) as count")
            ->get();

        $yearlyApplications = (clone $query)->whereBetween('created_at', [$lastYearStart, $lastYearEnd])
            ->selectRaw("'Last Year' as type, COUNT(*) as count")
            ->get();

        $sinceInspectionQuery = clone $query1;
        if ($inspectionStartDate) {
            $sinceInspectionQuery = $sinceInspectionQuery->whereDate('created_at', '>=', $inspectionStartDate->created_at);
        }

        $sinceInspectionData = $sinceInspectionQuery
            ->selectRaw("'Since Inception' as type, COUNT(*) as count")
            ->get();

        return collect()
            ->merge($todayApplications)
            ->merge($lastWeekApplications)
            ->merge($lastMonthApplications)
            ->merge($lastQuarterApplications)
            ->merge($yearlyApplications)
            ->merge($sinceInspectionData)
            ->map(function ($item) {
                return [
                    'type' => $item['type'],
                    'count' => (int) $item['count'],
                ];
            });
    }


    public function subscribersReport()
    {
        // This is for tabs
        $user = auth()->user();
        $startDate = $this->parseReportDate(request()->input('startDate'));
        $endDate = $this->parseReportDate(request()->input('endDate'), true);
        if (request()->type == "country") {

            if ($user->user_type == 'admin') {
                $subscribersByCountry = User::where('user_type', 'Subscriber')->select('country as country_name', DB::raw('COUNT(users.id) as No_of_Subscribers'))
                    ->groupBy('country')->get();
            } else {

                $subscribersByCountry = User::where('user_type', 'User')->where('added_by', $user->id)->select('country as country_name', DB::raw('COUNT(users.id) as No_of_Subscribers'))
                    ->groupBy('country')->get();
            }
            return  DataTables::of($subscribersByCountry)
                ->addIndexColumn()
                ->make(true);

            return response()->json(['data' => $subscribersByCountry]);
        } elseif (request()->type == "bySubscriberCountryChart") {

            $country = (request()->input('country') == 'All') ? Countries::pluck('country_name') : Countries::where('id', [request()->input('country')])->pluck('country_name');

            if (!empty(request()->subid)) {

                $subscribersByCountry = User::where('added_by', request()->subid)->where('user_type', 'Subscriber')
                    ->whereIn('country', $country) // Use `whereIn` to handle both cases
                    ->whereBetween('created_a t', [$startDate, $endDate])
                    ->select('country', DB::raw('COUNT(users.id) as No_of_Subscribers'))
                    ->groupBy('country')
                    ->get();
            } else {
                $subscribersByCountry = User::where('user_type', 'Subscriber')
                    ->whereIn('country', $country) // Use `whereIn` to handle both cases
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->select('country', DB::raw('COUNT(users.id) as No_of_Subscribers'))
                    ->groupBy('country')
                    ->get();
            }
            return  DataTables::of($subscribersByCountry)
                ->addIndexColumn()
                ->make(true);
        } elseif (request()->type == "bySubscriberCategoryChart") {
            if (!empty(request()->subid)) {
                $byCategory = User::whereNotNull('category')->where('id', request()->subid)->whereBetween('created_at', [$startDate, $endDate])->select('category', DB::raw('COUNT(users.id) as userCount'))->orderBy('userCount', 'desc')->groupBy('category')->get();
            } else {
                $byCategory = User::whereNotNull('category')  // Ensure category is not null
                    ->where('user_type', 'Subscriber')  // Filter by user type
                    // ->whereBetween('created_at', [$startDate, $endDate])  // Filter by the date range
                    ->select('category', DB::raw('COUNT(users.id) as userCount'))  // Count the number of users per category
                    ->groupBy('category')  // Group by category
                    ->havingRaw('COUNT(users.id) > 0')  // Only include categories with non-zero users
                    ->orderBy('userCount', 'desc')  // Order by user count in descending order
                    ->get();
            }

            return  DataTables::of($byCategory)
                ->addIndexColumn()
                ->make(true);
        } elseif (request()->type == "bySubscriberSubcategoryChart") {

            if (!empty(request()->subid)) {
                $bySubcategory = User::where('id', request()->subid)->whereBetween('created_at', [$startDate, $endDate])->select('sub_category', DB::raw('COUNT(users.id) as userCount'))->orderBy('userCount', 'desc')->groupBy('sub_category')->get();
            } else {
                $bySubcategory = User::where('user_type', 'Subscriber')->whereBetween('created_at', [$startDate, $endDate])->select('sub_category', DB::raw('COUNT(users.id) as userCount'))->orderBy('userCount', 'desc')->groupBy('sub_category')->get();
            }
            return  DataTables::of($bySubcategory)
                ->addIndexColumn()
                ->make(true);
        } elseif (request()->type == "bySubscriberplanTypeChart") {

            if (!empty(request()->subid)) {
                $sub = User::where('id', request()->subid)->whereIn('membership', ['FREE', 'SOLO', 'Adwiseri', 'Adwiseri+', 'Enterprise'])->whereBetween('created_at', [$startDate, $endDate])->where('user_type', 'Subscriber')->select('membership as membership_type', DB::raw('COUNT(id) as userCount'))->groupBy('membership')->get();
            } else {
                if ($user->user_type == 'admin') {
                    $sub = User::whereBetween('created_at', [$startDate, $endDate])->whereIn('membership', ['FREE', 'SOLO', 'Adwiseri', 'Adwiseri+', 'Enterprise'])->where('user_type', 'Subscriber')->select('membership as membership_type', DB::raw('COUNT(id) as userCount'))->groupBy('membership')->get();
                } else {
                    $sub = User::where('added_by', $user->id)->whereBetween('created_at', [$startDate, $endDate])->whereIn('membership', ['FREE', 'SOLO', 'Adwiseri', 'Adwiseri+', 'Enterprise'])->where('user_type', 'Subscriber')->select('membership as membership_type', DB::raw('COUNT(id) as userCount'))->groupBy('membership')->get();
                }

                return  DataTables::of($sub)
                    ->addIndexColumn()
                    ->make(true);;
            }
        } elseif (request()->type == "bysubscriptionDurationChart") {

            if (!empty(request()->subid)) {

                $subscriberCounts = User::where('id', request()->subid)->whereBetween('created_at', [$startDate, $endDate])
                    ->where('user_type', 'Subscriber')
                    ->select(
                        DB::raw('
                        CASE
                            WHEN TIMESTAMPDIFF(YEAR, membership_start_date, membership_expiry_date) = 0 THEN "Under 1 Year"
                            WHEN TIMESTAMPDIFF(YEAR, membership_start_date, membership_expiry_date) = 1 THEN "1 Year"
                            WHEN TIMESTAMPDIFF(YEAR, membership_start_date, membership_expiry_date) = 2 THEN "2 Years"
                            WHEN TIMESTAMPDIFF(YEAR, membership_start_date, membership_expiry_date) = 3 THEN "3 Years"
                            WHEN TIMESTAMPDIFF(YEAR, membership_start_date, membership_expiry_date) >= 5 THEN "5 Years"

                        END AS duration,
                        COUNT(*) AS total_subscribers
                    ')
                    )
                    ->groupBy('duration')
                    ->get();
            } else {
                $subscriberCounts = User::whereBetween('created_at', [$startDate, $endDate])
                    ->where('user_type', 'Subscriber')
                    ->whereNotNull('membership_start_date')
                    ->whereNotNull('membership_expiry_date')
                    ->select(
                        DB::raw('
                        CASE
                            WHEN TIMESTAMPDIFF(YEAR, membership_start_date, membership_expiry_date) = 0 THEN "Under 1 Year"
                            WHEN TIMESTAMPDIFF(YEAR, membership_start_date, membership_expiry_date) = 1 THEN "1 Year"
                            WHEN TIMESTAMPDIFF(YEAR, membership_start_date, membership_expiry_date) = 2 THEN "2 Years"
                            WHEN TIMESTAMPDIFF(YEAR, membership_start_date, membership_expiry_date) = 3 THEN "3 Years"
                            WHEN TIMESTAMPDIFF(YEAR, membership_start_date, membership_expiry_date) >= 5 THEN "5 Years"
                        ELSE NULL
                        END AS duration,
                        COUNT(*) AS total_subscribers
                    ')
                    )
                    ->groupBy('duration')
                    ->havingRaw('duration IS NOT NULL')
                    ->orderByRaw('FIELD(duration, "Under 1 Year", "1 Year", "2 Years", "3 Years", "5 Years")')
                    ->get();
            }
            return  DataTables::of($subscriberCounts)
                ->addIndexColumn()
                ->make(true);
        } elseif (request()->type == "bySubscriberNoOfClientsChart") {

            if (!empty(request()->subid)) {
                // Filter by specific subscriber ID
                $sub = User::where('id', request()->subid)
                    ->whereIn('membership', ['FREE', 'SOLO', 'Adwiseri', 'Adwiseri+', 'Enterprise'])
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->where('user_type', 'Subscriber')
                    ->withCount('clients') // Count the clients related to each subscriber
                    ->select(
                        'membership as membership_type',
                        DB::raw('COUNT(id) as userCount') // Count subscribers
                    )
                    ->groupBy('membership')
                    ->get()
                    ->map(function ($subscriber) {
                        $subscriber->totalClients = $subscriber->clients_count;
                        return $subscriber;
                    });
            } else {

                $sub = User::whereBetween('users.created_at', [$startDate, $endDate]) // Filter users by date range
                    ->where('users.user_type', 'Subscriber') // Filter by user type
                    ->withCount('clients') // Automatically count related clients and add clients_count to result

                    ->orderBy('clients_count', 'desc') // Order by the auto-counted clients_count in descending order
                    ->havingRaw('clients_count > 0')
                    ->get();
            }
            return  DataTables::of($sub)
                ->addIndexColumn()
                ->editColumn('name', function ($row) {
                    return $row->name . '(' . $row->id . ')';
                })
                ->make(true);
        } elseif (request()->type == "bySubscriberNoOfUserChart") {

            if (!empty(request()->subid)) {
                // Filter by specific subscriber ID
                $sub = User::where('id', request()->subid)
                    ->whereIn('membership', ['FREE', 'SOLO', 'Adwiseri', 'Adwiseri+', 'Enterprise'])
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->where('user_type', 'Subscriber')
                    ->withCount('clients') // Count the clients related to each subscriber
                    ->select(
                        'membership as membership_type',
                        DB::raw('COUNT(id) as userCount') // Count subscribers
                    )
                    ->groupBy('membership')
                    ->get()
                    ->map(function ($subscriber) {
                        $subscriber->totalClients = $subscriber->clients_count;
                        return $subscriber;
                    });
            } else {


                $sub = User::whereBetween('users.created_at', [$startDate, $endDate]) // Filter users by date range
                    ->where('users.user_type', 'Subscriber') // Filter by user type
                    ->withCount('subscriber') // Automatically count related clients and add clients_count to result

                    ->orderBy('subscriber_count', 'desc') // Order by the auto-counted clients_count in descending order
                    ->havingRaw('subscriber_count > 0')
                    ->get();
            }
            return  DataTables::of($sub)
                ->addIndexColumn()
                ->editColumn('name', function ($row) {
                    return $row->name . '(' . $row->id . ')';
                })
                ->make(true);
        } elseif (request()->type == "bySubscriberReferrals") {

            if (!empty(request()->subid)) {

                $referrals  = User::where('id', request()->subid)->where('user_type', 'Subscriber')
                    ->leftJoin('users as referrals', 'users.id', '=', 'referrals.referrer_code')
                    ->select('users.name as name', DB::raw('COUNT(referrals.id) as total_referrals'))
                    ->groupBy('users.id', 'users.name')
                    ->get();
            } else {
                // $referrals = User::where('users.user_type', 'Subscriber') // Filter by user type
                //     ->whereIn('users.membership', ['FREE', 'SOLO', 'Adwiseri', 'Adwiseri+', 'Enterprise']) // Filter by membership type
                //     ->join('users as referrals', 'users.referral', '=', 'referrals.referral_code') // Join the referrals table
                //     ->select(
                //         'users.membership as membership_type', // Group by membership type
                //         DB::raw('COUNT(referrals.id) as referrals_count'), // Count the total number of referrals
                //         DB::raw('SUM(CASE WHEN referrals.id IS NOT NULL THEN 1 ELSE 0 END) as total_referrals') // Sum all the referrals
                //     )
                //     ->groupBy('users.membership') // Group by membership type
                //     ->havingRaw('SUM(CASE WHEN referrals.id IS NOT NULL THEN 1 ELSE 0 END) > 0') // Only include groups with referrals
                //     ->get();
                $referrals = User::where('users.user_type', 'Subscriber') // Filter by user type
                    ->join('users as referrals', 'users.referral', '=', 'referrals.referral_code') // Join the referrals table
                    ->select(
                        'users.id',
                        'users.name as user_name', // Get the subscriber's name
                        DB::raw('COUNT(referrals.id) as total_referrals') // Count the total number of referrals for each subscriber
                    )
                    ->groupBy('users.id', 'users.name') // Group by user ID and name
                    ->havingRaw('COUNT(referrals.id) > 0') // Only include users with referrals
                    ->orderBy('total_referrals', 'desc')
                    ->get();
            }
            return  DataTables::of($referrals)
                ->addIndexColumn()
                ->editColumn('user_name', function ($row) {
                    return $row->user_name . '(' . $row->id . ')';
                })
                ->make(true);
        } elseif (request()->type == "bySubscriberWalletAmountChart") {

            if (!empty(request()->subid)) {
                $referrals = Referrals::where('id', request()->subid)->whereBetween('created_at', [$startDate, $endDate])->where('debit_amount', '=', null)->orderBy('created_at', 'desc')->get();
                return response()->json(['data' => $referrals]);
            } else {

                $wallets = User::where('users.user_type', 'Subscriber') // Filter by user type
                    ->where('users.wallet', '>', 0) // Only include users with a wallet balance greater than 0
                    ->select(
                        'users.id',
                        'users.name as user_name', // Get the user's name
                        DB::raw('ROUND(users.wallet, 2) as wallet_balance') // Get the wallet balance rounded to 2 decimal places
                    )
                    ->orderByDesc('wallet_balance') // Order by wallet balance in descending order
                    ->get();
            }
            return  DataTables::of($wallets)
                ->addIndexColumn()
                ->editColumn('user_name', function ($row) {
                    return $row->user_name . '(' . $row->id . ')';
                })
                ->make(true);
        } elseif (request()->type == "bySubscriberNoOfApplicationChart") {


            if (!empty(request()->subid)) {
                $referrals = Applications::with('subscriber')->where('users.id', request()->subid)->whereBetween('created_at', [$startDate, $endDate])->select('subscriber_id', DB::raw('COUNT(application_id) as application_id'))->groupBy('subscriber_id')->get();

                return response()->json(['data' => $referrals]);
            } else {

                $subApplication = User::where('user_type', 'Subscriber') // Filter by user type
                    ->leftJoin('applications', 'users.id', '=', 'applications.subscriber_id') // Join with applications table
                    ->select(
                        'users.id',
                        'users.name as user_name', // Get the user's name
                        DB::raw('COUNT(applications.id) as applications_count') // Count the total number of applications
                    )
                    ->groupBy('users.id', 'users.name') // Group by user ID and name to count applications per user
                    ->havingRaw('COUNT(applications.id) > 0') // Only include users with at least one application
                    ->orderByDesc('applications_count')
                    ->get();
            }
            return  DataTables::of($subApplication)
                ->addIndexColumn()
                ->editColumn('user_name', function ($row) {
                    return $row->user_name . '(' . $row->id . ')';
                })
                ->make(true);
        } elseif (request()->type == "bySubscribeDocumentStore") {

            if (!empty(request()->subid)) {
                $client_docs = Client_Docs::with('user')->where('users.id', request()->subid)->whereBetween('client_docs.created_at', [$startDate, $endDate])->select('user_id', DB::raw('COUNT(doc_name) as clients'))->groupBy('user_id')->get();
            } else {


                $client_docs = User::where('user_type', 'Subscriber') // Filter by user type
                    ->leftJoin('client_docs', 'users.id', '=', 'client_docs.user_id') // Join with client_docs table
                    ->select(
                        'users.id',
                        'users.name as user_name', // Group by membership type
                        DB::raw('COUNT(client_docs.id) as docs_count') // Count the number of documents
                    )
                    ->groupBy('users.id', 'users.name')
                    ->havingRaw('COUNT(client_docs.id) > 0')
                    // Only include memberships with at least one document
                    ->orderByDesc('docs_count')
                    ->get();

                // $referrals = Applications::with('user')->whereBetween('created_at', [$startDate, $endDate])->select('subscriber_id', DB::raw('COUNT(application_id) as application_id'))->groupBy('subscriber_id')->get();
                return  DataTables::of($client_docs)
                    ->addIndexColumn()
                    ->editColumn('user_name', function ($row) {
                        return $row->user_name . '(' . $row->id . ')';
                    })
                    ->make(true);
            }
            return response()->json(['data' => $client_docs]);
        } elseif (request()->type == "byClientHomeCountry") {
            $query = new Clients();
            if (($user->membership == 'Adwiseri' || $user->membership == 'Adwiseri+' || $user->membership == 'Enterprise') && $user->user_type == 'Subscriber') {
                // Apply condition for 'Subscriber' user type and membership types
                $query = $query->where('subscriber_id', request()->subid);
            }

            $clients = $query->whereBetween('created_at', [$startDate, $endDate])
                ->whereNotNull('country')
                ->where('country', '!=', '')
                ->select(
                    'country',
                    DB::raw('COUNT(*) AS No_of_clients')
                )
                ->groupBy('country')
                ->orderBy('No_of_clients', 'desc')
                ->get();

            return response()->json(['data' => $clients]);
        } elseif (request()->type == "byVisaCountryClient") {

            $query = new Applications();
            if (($user->membership == 'Adwiseri' || $user->membership == 'Adwiseri+' || $user->membership == 'Enterprise') && $user->user_type == 'Subscriber') {
                // Apply condition for 'Subscriber' user type and membership types
                $query = $query->where('subscriber_id', request()->subid);
            }
            $clientByVisaCountry = $query->whereBetween('created_at', [$startDate, $endDate])
                ->whereNotNull('visa_country')  // Ensures visa_country is not null
                ->groupBy('visa_country')  // Group by visa_country
                ->select('visa_country', DB::raw('COUNT(DISTINCT client_id) as total_clients')) // Count distinct clients per visa_country
                ->get();
            return response()->json(['data' => $clientByVisaCountry]);
        } elseif (request()->type === 'clientVisaChartFilterAvailability') {
            $subscriberId = (int) request()->subid;
            $availability = app(\App\Services\AnalyticsClientChartService::class)
                ->visaDetailFilterAvailability($subscriberId);

            return response()->json(['data' => $availability]);
        } elseif (in_array(request()->type, [
            'byClientUniversity',
            'byClientCourse',
            'byClientIntake',
            'byClientEmployer',
            'byClientJobRole',
        ], true)) {
            $subscriberId = (int) request()->subid;
            $chartService = app(\App\Services\AnalyticsClientChartService::class);

            $fieldMap = [
                'byClientUniversity' => ['column' => 'institution', 'scope' => 'study'],
                'byClientCourse' => ['column' => 'course_name', 'scope' => 'study'],
                'byClientIntake' => ['column' => 'intake', 'scope' => 'study'],
                'byClientEmployer' => ['column' => 'employer_name', 'scope' => 'work'],
                'byClientJobRole' => ['column' => 'employment_role', 'scope' => 'work'],
            ];

            $config = $fieldMap[request()->type];
            $rows = $chartService->aggregateClientsByVisaDetailField(
                $subscriberId,
                $config['column'],
                $config['scope'],
                $startDate,
                $endDate
            );

            return response()->json(['data' => $rows]);
        } elseif (request()->type == "byAgeGroupClient") {
            [$labelsSql, $caseSql] = $this->ageGroupBucketSql('dob');

            $query = Clients::query();
            $query = $this->applyAgeGroupConsultancyScope($query, $user, 'subscriber_id');

            $clientsAgeGroup = DB::table(DB::raw($labelsSql))
                ->leftJoinSub(
                    $query->whereBetween('created_at', [$startDate, $endDate])
                        ->whereNotNull('dob')
                        ->where('dob', '!=', '')
                        ->where('dob', '!=', '0000-00-00')
                        ->whereRaw('dob REGEXP ?', ['^[0-9]{4}-[0-9]{2}-[0-9]{2}'])
                        ->selectRaw("{$caseSql} AS age_group, COUNT(*) AS count")
                        ->groupBy('age_group'),
                    'clients',
                    'age_groups.age_group',
                    '=',
                    'clients.age_group'
                )
                ->select('age_groups.age_group', DB::raw('COALESCE(clients.count, 0) AS count'))
                ->orderByRaw("
                        FIELD(age_groups.age_group, 'Under 18', '18-24', '25-34', '35-44', '45-55', '55 +')
                    ")
                ->get();

                // ✅ Check if all counts are 0, and return empty if so
                if ($clientsAgeGroup->sum('count') === 0) {
                    return response()->json(['data' => []]); // frontend will show "No data available"
                }
            return response()->json(['data' => $clientsAgeGroup]);
        } elseif (request()->type == "byClientApplicationType") {

            $query =  new Clients();
            if (($user->membership == 'Adwiseri' || $user->membership == 'Adwiseri+' || $user->membership == 'Enterprise') && $user->user_type == 'Subscriber') {
                // Apply condition for 'Subscriber' user type and membership types
                $query = $query->where('clients.subscriber_id', request()->subid);
            }
            $clientApplicationType = $query->leftJoin('applications', 'applications.client_id', '=', 'clients.id') // ✅ Correct Join
                ->whereBetween('clients.created_at', [$startDate, $endDate])
                ->select(
                    'clients.id',
                    'clients.name as client_name',
                    DB::raw('COUNT(DISTINCT applications.id) as applications_count') // ✅ Count unique applications
                )
                ->groupBy('clients.id', 'clients.name')
                ->havingRaw('COUNT(DISTINCT applications.id) > 0') // Only include clients with more than 0 applications
                ->get();
            return response()->json(['data' => $clientApplicationType]);
            // if (!empty(request()->subid)) {
            //     $data = Clients::leftJoin('applications', 'clients.id', '=', 'applications.client_id')
            //     ->whereBetween('clients.created_at', [$startDate, $endDate])
            //     ->where('clients.subscriber_id', request()->subid)
            //     ->select(
            //         'clients.id',
            //         'clients.name',
            //         DB::raw('COUNT(DISTINCT applications.id) as applications_count') // Use DISTINCT to count unique applications
            //     )
            //     ->groupBy('clients.id', 'clients.name')
            //     ->havingRaw('COUNT(DISTINCT applications.id) > 0') // Only include clients with more than 0 applications
            //     ->get();
            // } else {
            //     $data = Clients::whereBetween('clients.created_at', [$startDate, $endDate])
            //     ->leftJoin('applications', 'clients.id', '=', 'applications.client_id')
            //     ->select(
            //         'clients.id',
            //         'clients.name',
            //         DB::raw('COUNT(applications.id) as applications_count')
            //     )
            //     ->groupBy('clients.id', 'clients.name')
            //     ->havingRaw('COUNT(applications.id) > 1') // Only clients with more than 1 application
            //     ->get();
            // }
            // return  DataTables::of($data)
            // ->addIndexColumn()
            // ->make(true);
        } elseif (request()->type == "byTotalNoOfApplications") {

            if (!empty(request()->subid)) {
                // Filtered by specific subscriber ID
                $applications = DB::table('clients')
                    ->join('applications', 'clients.id', '=', 'applications.client_id')
                    ->join('users', 'clients.subscriber_id', '=', 'users.id')
                    ->join('payments', 'users.id', '=', 'payments.user_id')
                    ->where('clients.subscriber_id', request()->subid)
                    ->whereBetween('clients.created_at', [$startDate, $endDate])
                    ->select(
                        'applications.id as application_id',
                        'users.id as user_id',
                        'clients.name as client_name',
                        DB::raw('COUNT(applications.id) as no_of_applications'),
                        DB::raw('GROUP_CONCAT(applications.application_name SEPARATOR ", ") as application_names'),
                        DB::raw('COUNT(payments.payment_mode) as payment_mode_count')
                    )
                    ->groupBy('clients.id', 'applications.id', 'users.id')
                    ->havingRaw('COUNT(applications.id) > 1') // Applications count > 1
                    ->limit(10)
                    ->get();
            } else {
                // Without subscriber ID filter
                $applications = DB::table('clients')
                    ->join('applications', 'clients.id', '=', 'applications.client_id')
                    ->join('users', 'clients.subscriber_id', '=', 'users.id')
                    ->join('payments', 'users.id', '=', 'payments.user_id')
                    ->whereBetween('clients.created_at', [$startDate, $endDate])
                    ->select(
                        'applications.id as application_id',
                        'users.id as user_id',
                        'clients.name as client_name',
                        DB::raw('COUNT(applications.id) as no_of_applications'),
                        DB::raw('GROUP_CONCAT(applications.application_name SEPARATOR ", ") as application_names'),
                        DB::raw('COUNT(payments.payment_mode) as payment_mode_count')
                    )
                    ->groupBy('clients.id', 'applications.id', 'users.id')
                    ->havingRaw('COUNT(applications.id) > 1') // Applications count > 1
                    ->limit(10)
                    ->get();
            }
            return  DataTables::of($applications)
                ->addIndexColumn()
                ->make(true);
        } elseif (request()->type == "byPaymentModeClientChart") {
            $query = new PaymentARs();
            if (($user->membership == 'Adwiseri' || $user->membership == 'Adwiseri+' || $user->membership == 'Enterprise') && $user->user_type == 'Subscriber') {
                $query = $query->where('payment_ar.subscriber_id', request()->subid);
            }

            $clientsPaymentMode = $query
                ->whereBetween('payment_ar.created_at', [$startDate, $endDate])
                ->whereNotNull('payment_ar.client_id')
                ->join('clients', 'clients.id', '=', 'payment_ar.client_id')
                ->leftJoin('applications', 'applications.id', '=', 'payment_ar.application_id');

            PaymentModeChartFilter::applyToQuery($clientsPaymentMode, 'payment_mode', 'payment_ar');

            $clientsPaymentMode = $clientsPaymentMode
                ->select(
                    'payment_ar.payment_mode', // ✅ Ensure this is included
                    DB::raw('COUNT(payment_ar.id) as no_of_applications') // ✅ Count applications per mode
                )
                ->groupBy('payment_ar.payment_mode')
                ->orderBy('no_of_applications', 'desc')
                ->get();

            return response()->json(['data' => $clientsPaymentMode]);
        } elseif (request()->type == "byClientNumberofDocumentsStoredChart") {
            $query = Client_Docs::join('clients', 'client_docs.client_id', '=', 'clients.id') // Join clients table
                ->join('applications', 'client_docs.application_id', '=', 'applications.application_id') // Join applications table
                ->join('users', 'client_docs.user_id', '=', 'users.id'); // Join users table

            // Apply the subscriber filter for Adwiseri or Enterprise memberships
            if (($user->membership == 'Adwiseri' || $user->membership == 'Adwiseri+' || $user->membership == 'Enterprise') && $user->user_type == 'Subscriber') {
                $query = $query->where('clients.subscriber_id', request()->subid); // Apply filter on clients table, not client_docs
            }

            // Select necessary columns and counts
            $clinetDocuments = $query->select(
                'users.name as sub_name', // Subscriber name
                'users.id as sub_id', // Subscriber ID
                'clients.name as client_name', // Client name
                'clients.id as client_id', // Client ID
                'applications.application_name', // Application name
                'applications.application_id as application_id', // Application ID
                DB::raw('COUNT(client_docs.id) as no_of_docs'), // Count the number of client documents
                DB::raw('COUNT(DISTINCT applications.id) as no_of_applications') // Count the number of unique applications
            )
                ->whereBetween('client_docs.created_at', [$startDate, $endDate]) // Filter by date range
                ->whereNotNull('client_docs.application_id') // Ensure application_id is not null
                ->groupBy(
                    'users.name',
                    'users.id',
                    'clients.id',
                    'clients.name',
                    'applications.application_name',
                    'applications.application_id'
                ) // Group by subscriber, client, and application
                ->get()
                ->map(function ($row) {
                    $row->chart_label = $this->formatClientApplicationChartLabel(
                        $row->client_name,
                        $row->application_name,
                        $row->application_id
                    );

                    return $row;
                });

            return response()->json(['data' => $clinetDocuments]);
        } elseif (request()->type == "byClientNoOfClientsTimeline") {
            $query = new Clients();
            $query1 = clone $query;

            if (($user->membership == 'Adwiseri' || $user->membership == 'Adwiseri+' || $user->membership == 'Enterprise')
                && $user->user_type == 'Subscriber') {
                $query = $query->where('subscriber_id', $user->id);
                $query1 = $query1->where('subscriber_id', $user->id);
            }

            $formattedData = $this->buildTimelineDurationData($query, $query1);

            return response()->json([
                'status' => 'success',
                'data' => $formattedData
            ]);
                
            
        } elseif (request()->type == "byClientNoOfClientsYear") {
            $query =  new Clients();
            if (($user->membership == 'Adwiseri' || $user->membership == 'Adwiseri+' || $user->membership == 'Enterprise') && $user->user_type == 'Subscriber') {
                // Apply condition for 'Subscriber' user type and membership types
                $query =   $query->where('subscriber_id', request()->subid);
            }
            $clients = $query->select(
                DB::raw('YEAR(created_at) AS year'), // Extract year from referral creation date
                DB::raw('COUNT(*) AS count') // Count referrals per year
            )
                ->groupBy(DB::raw('YEAR(created_at)')) // Group by year
                ->orderBy('year', 'asc') // Order from oldest to newest
                ->get();

            return response()->json(['data' => $clients]);
        } elseif (request()->type == "byClientOutstandingPaymentsAmountChart") {
            $query =  new PaymentARs();
            if (($user->membership == 'Adwiseri' || $user->membership == 'Adwiseri+' || $user->membership == 'Enterprise') && $user->user_type == 'Subscriber') {
                // Apply condition for 'Subscriber' user type and membership types
                $query =   $query->where('payment_ar.subscriber_id', request()->subid);
            }
            $clientsOutstandingAmount = $query->whereBetween('payment_ar.created_at', [$startDate, $endDate])
                ->whereNotNull('payment_ar.client_id') // Ensure there is a client_id
                ->join('clients', 'clients.id', '=', 'payment_ar.client_id') // ✅ Properly join clients table
                ->select(
                    'payment_ar.client_id',
                    'clients.name as client_name', // ✅ Properly select client name
                    DB::raw('MAX(payment_ar.created_at) as created_at'),
                    // ✅ Club ALL outstanding across the client's applications/invoices into a single per-client total
                    DB::raw('SUM(payment_ar.amount - payment_ar.paid_amount) as amount_to_pay')
                )
                ->groupBy('payment_ar.client_id', 'clients.name') // ✅ One entry per client (amounts clubbed together)
                ->havingRaw('SUM(payment_ar.amount - payment_ar.paid_amount) > 0')
                ->orderByDesc('amount_to_pay') // ✅ Largest outstanding client first
                ->get();
            return response()->json(['data' => $clientsOutstandingAmount]);
        } elseif (request()->type == "byClientDependant") {
            $query = Clients::query();
            if (($user->membership == 'Adwiseri' || $user->membership == 'Adwiseri+' || $user->membership == 'Enterprise') && $user->user_type == 'Subscriber') {
                // Apply condition for 'Subscriber' user type and membership types
                $query =   $query->where('dependants.subscriber_id', request()->subid);
            }
            $byClientDependant = $query
                ->leftJoin('dependants', 'clients.id', '=', 'dependants.client_id') // Assuming `dependents` table has `client_id`
                ->whereBetween('clients.created_at', [$startDate, $endDate])
                ->select('clients.name as client_name', DB::raw('COUNT(dependants.id) as num_dependents'))
                ->groupBy('clients.id', 'clients.name')
                ->orderBy('num_dependents', 'desc') // Sorting by most dependents first
                ->get();
            return response()->json(['data' => $byClientDependant]);
        } elseif (request()->type == "byApplicationVisaCountryChart") {

            $query = new Applications();
            if (($user->membership == 'Adwiseri' || $user->membership == 'Adwiseri+' || $user->membership == 'Enterprise') && $user->user_type == 'Subscriber') {
                // Apply condition for 'Subscriber' user type and membership types
                $query = $query->where('subscriber_id', request()->subid);
            }
            $applicationByVisaCountry = $query->whereBetween('created_at', [$startDate, $endDate])
                ->whereNotNull('visa_country')  // Ensures visa_country is not null
                ->groupBy('visa_country')  // Group by visa_country
                ->select('visa_country', DB::raw('COUNT(DISTINCT client_id) as total_clients')) // Count distinct clients per visa_country
                ->get();
            return response()->json(['data' => $applicationByVisaCountry]);
        } elseif (request()->type == "byApplicationCountryChart") {
            $query = new Applications();
            if (($user->membership == 'Adwiseri' || $user->membership == 'Adwiseri+' || $user->membership == 'Enterprise') && $user->user_type == 'Subscriber') {
                $query = $query->where('subscriber_id', request()->subid);
            }
            $applicationByHomeCountry = $query->whereBetween('created_at', [$startDate, $endDate])
                ->whereNotNull('application_country')  // Ensures visa_country is not null
                ->groupBy('application_country')  // Group by visa_country
                ->select('application_country', DB::raw('COUNT(DISTINCT client_id) as total_clients')) // Count distinct clients per visa_country
                ->get();
            return response()->json(['data' => $applicationByHomeCountry]);
        } elseif (request()->type == "byApplicationType") {

            $query = new Applications();
            if (($user->membership == 'Adwiseri' || $user->membership == 'Adwiseri+' || $user->membership == 'Enterprise') && $user->user_type == 'Subscriber') {
                $query = $query->where('subscriber_id', request()->subid);
            }
            // $byApplicationType = $query->whereBetween('created_at', [$startDate, $endDate])
            //     ->whereNotNull('visa_country')  // Ensures visa_country is not null
            //     ->groupBy('application_name')  // Group by visa_country
            //     ->selectRaw(
            //         'application_name,
                    
            //          COUNT(client_id) AS number_of_clients') // Count distinct clients per visa_country
            //     ->get();

            $byApplicationType = $query->whereBetween('created_at', [$startDate, $endDate])
                ->groupBy('application_name')
                ->select('application_name', DB::raw('COUNT(*) AS number_of_clients'))
                ->orderByDesc('number_of_clients')
                ->get();

            return response()->json(['data' => $byApplicationType]);
        } elseif (request()->type == "byApplicationStatus") {

            $query = Applications::query();
            if (($user->membership == 'Adwiseri' || $user->membership == 'Adwiseri+' || $user->membership == 'Enterprise') && $user->user_type == 'Subscriber') {
                $query = $query->where('subscriber_id', request()->subid);
            }

            $countsByStatus = $query->whereBetween('created_at', [$startDate, $endDate])
                ->selectRaw("COALESCE(NULLIF(application_status, ''), 'Not Set') as application_status, COUNT(*) as status_count")
                ->groupBy('application_status')
                ->pluck('status_count', 'application_status');

            $statusFlow = AssociateController::APPLICATION_STATUS_OPTIONS;
            $byApplicationStatus = collect();

            foreach ($statusFlow as $status) {
                $byApplicationStatus->push([
                    'application_status' => $status,
                    'status_count' => (int) ($countsByStatus[$status] ?? 0),
                ]);
            }

            foreach ($countsByStatus as $status => $count) {
                if (!in_array($status, $statusFlow, true) && $status !== 'Not Set') {
                    $byApplicationStatus->push([
                        'application_status' => $status,
                        'status_count' => (int) $count,
                    ]);
                }
            }

            if ($countsByStatus->has('Not Set')) {
                $byApplicationStatus->push([
                    'application_status' => 'Not Set',
                    'status_count' => (int) $countsByStatus['Not Set'],
                ]);
            }

            return response()->json(['data' => $byApplicationStatus->values()]);
        } elseif (request()->type == "byApplicationCountsByDependantsChart") {

            $dependantBuckets = [
                0 => '0 Dependant',
                1 => '1 Dependant',
                2 => '2 Dependants',
                3 => '3 Dependants',
                4 => '4 Dependants',
                5 => '5+ Dependants',
            ];
            $applicationCounts = [];
            foreach ($dependantBuckets as $bucket => $label) {
                $applicationCounts[$bucket] = [
                    'dependant_bucket' => $label,
                    'dependant_count' => $bucket,
                    'application_count' => 0,
                ];
            }

            $dependantCounts = Dependants::select('client_id', DB::raw('COUNT(*) as dependant_count'))
                ->groupBy('client_id');

            $query = Applications::query()
                ->leftJoinSub($dependantCounts, 'dependant_counts', function ($join) {
                    $join->on('applications.client_id', '=', 'dependant_counts.client_id');
                })
                ->whereBetween('applications.created_at', [$startDate, $endDate]);

            if (($user->membership == 'Adwiseri' || $user->membership == 'Adwiseri+' || $user->membership == 'Enterprise') && $user->user_type == 'Subscriber') {
                $query = $query->where('applications.subscriber_id', request()->subid);
            }

            $query->selectRaw(
                "CASE
                    WHEN COALESCE(dependant_counts.dependant_count, 0) >= 5 THEN 5
                    ELSE COALESCE(dependant_counts.dependant_count, 0)
                END as dependant_bucket_key,
                COUNT(DISTINCT applications.id) as application_count"
            )
                ->groupBy('dependant_bucket_key')
                ->get()
                ->each(function ($row) use (&$applicationCounts) {
                    $bucket = (int) $row->dependant_bucket_key;
                    $applicationCounts[$bucket]['application_count'] = (int) $row->application_count;
                });

            return response()->json(['data' => array_values($applicationCounts)]);
        } elseif (request()->type == "byNoofApplicantsPerApplicationChart") {

            $query = new Applications();

            if (($user->membership == 'Adwiseri' || $user->membership == 'Adwiseri+' || $user->membership == 'Enterprise') && $user->user_type == 'Subscriber') {
                $query = $query->where('applications.subscriber_id', request()->subid);
            }

            $byApplicationDependant = $query
                ->leftJoin('clients', 'applications.client_id', '=', 'clients.id') // Join clients
                ->leftJoin('dependants', 'clients.id', '=', 'dependants.client_id') // Join dependents
                ->whereBetween('applications.created_at', [$startDate, $endDate])
                ->whereNotNull('applications.visa_country') // Ensure visa_country is not null
                ->groupBy('applications.application_name') // Group by application_name
                ->selectRaw("
                    applications.application_name, 
                    COUNT(DISTINCT clients.id) AS total_clients, 
                    COUNT(DISTINCT dependants.id) AS total_dependents,
                    SUM(CASE WHEN dependants.id IS NULL THEN 1 ELSE 0 END) AS single_clients,
                    SUM(CASE WHEN dependants.id IS NOT NULL THEN 1 ELSE 0 END) AS joint_clients
                ") // Count total clients, dependents, single, and joint clients
                ->get();

            return response()->json(['data' => $byApplicationDependant]);
        } elseif (request()->type == 'byApplicationPaymentModeChart') {
            // Applications by payment mode must come from payment_ar (client payments ledger).
            // The old query joined applications × invoices.user_id, which cartesian-producted
            // every application with every subscription invoice and only surfaced Card/Cash.
            $query = PaymentARs::query()
                ->whereBetween('payment_ar.created_at', [$startDate, $endDate])
                ->whereNotNull('payment_ar.payment_mode')
                ->whereRaw("TRIM(payment_ar.payment_mode) <> ''");

            if (!empty(request()->subid)) {
                $query->where('payment_ar.subscriber_id', request()->subid);
            } elseif (
                ($user->membership == 'Adwiseri' || $user->membership == 'Adwiseri+' || $user->membership == 'Enterprise')
                && $user->user_type == 'Subscriber'
            ) {
                $query->where('payment_ar.subscriber_id', $user->id);
            }

            PaymentModeChartFilter::applyToQuery($query, 'payment_mode', 'payment_ar');

            $applications = $query
                ->select(
                    'payment_ar.payment_mode',
                    DB::raw(
                        "COUNT(DISTINCT CONCAT(
                            CASE WHEN payment_ar.application_id IS NOT NULL THEN 'app-' ELSE 'pay-' END,
                            COALESCE(payment_ar.application_id, payment_ar.id)
                        )) as no_of_applications"
                    )
                )
                ->groupBy('payment_ar.payment_mode')
                ->havingRaw('no_of_applications > 0')
                ->orderByDesc('no_of_applications')
                ->get();

            return response()->json(['data' => $applications]);
        } elseif (request()->type == "byOutstandingAplicationPaymentsAmountChart") {
            $query = new PaymentARs();
            if (($user->membership == 'Adwiseri' || $user->membership == 'Adwiseri+' || $user->membership == 'Enterprise') && $user->user_type == 'Subscriber') {
                $query = $query->where('payment_ar.subscriber_id', request()->subid);
            }

            $applicationOutstandingAmount = $query->whereBetween('payment_ar.created_at', [$startDate, $endDate])
                ->where('payment_ar.type', 'ar')
                ->join('clients', 'clients.id', '=', 'payment_ar.client_id')
                ->leftJoin('applications', 'applications.id', '=', 'payment_ar.application_id')
                ->select(
                    'payment_ar.client_id',
                    'payment_ar.application_id',
                    'payment_ar.invoice_no',
                    'clients.name as client_name',
                    DB::raw("CONCAT(
                                clients.name,
                                ' - ',
                                COALESCE(
                                    NULLIF(TRIM(CONCAT(applications.application_name, ' (', applications.application_id, ')')), '()'),
                                    NULLIF(TRIM(MAX(payment_ar.service_description)), ''),
                                    CONCAT('Invoice ', payment_ar.invoice_no)
                                )
                            ) as application_name"),
                    DB::raw('MAX(payment_ar.created_at) as created_at'),
                    DB::raw('(MAX(payment_ar.amount) - SUM(payment_ar.paid_amount)) as amount_to_pay')
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
                ->orderByDesc('amount_to_pay')
                ->get();

            return response()->json(['data' => $applicationOutstandingAmount]);
        } elseif (request()->type == "byNumberOfApplicationDocumentStoreChart") {
            $query = Client_Docs::join('clients', 'client_docs.client_id', '=', 'clients.id') // Join clients table
                ->join('applications', 'client_docs.application_id', '=', 'applications.application_id') // Join applications table
                ->join('users', 'client_docs.user_id', '=', 'users.id'); // Join users table
            if (($user->membership == 'Adwiseri' || $user->membership == 'Adwiseri+' || $user->membership == 'Enterprise') && $user->user_type == 'Subscriber') {
                // Apply condition for 'Subscriber' user type and membership types
                $query = $query->where('client_docs.user_id', $user->id);
            }
            // // Filter by specific subscriber ID
            // $byNumberOfApplicationDocumentStoreChart = $query->select(
            //     'users.name as sub_name', // Subscriber name
            //     'users.id as sub_id', // Subscriber ID
            //     'clients.name as client_name', // Client name
            //     'clients.id as client_id', // Client ID
            //     'applications.application_name', // Application name
            //     'applications.application_id as application_id', // Application ID
            //     DB::raw('COUNT(client_docs.id) as docs_count'), // Count the number of client documents
            //     DB::raw('COUNT(DISTINCT applications.id) as no_of_applications') // Count the number of unique applications
            // )

            //     ->whereBetween('client_docs.created_at', [$startDate, $endDate]) // Filter by date range
            //     ->whereNotNull('client_docs.application_id') // Ensure application_id is not null

            //     ->groupBy(
            //         'users.name',
            //         'users.id',
            //         'clients.id',
            //         'clients.name',
            //         'applications.application_name',
            //         'applications.id'
            //     ) // Group by subscriber, client, and application
            //     //     // ->havingRaw('no_of_applications = 1') // Ensure the client has documents for only one application
            //     //     // ->havingRaw('no_of_docs > 0') // Ensure the client uploaded multiple documents
            //     ->havingRaw('docs_count > 0')
            //     ->orderByDesc('docs_count') // Order by the number of documents uploaded
            //     ->get();
            $byNumberOfApplicationDocumentStoreChart = $query->select(
                'users.name as sub_name', // Subscriber name
                'users.id as sub_id', // Subscriber ID
                'clients.name as client_name', // Client name
                'clients.id as client_id', // Client ID
                DB::raw("CONCAT(applications.application_name, ' (', applications.application_id, ')') as application_name"), // Format: Test (1)
                DB::raw('COUNT(client_docs.id) as docs_count'), // Count the number of client documents
                DB::raw('COUNT(DISTINCT applications.application_id) as no_of_applications') // Count the number of unique applications
            )
            ->whereBetween('client_docs.created_at', [$startDate, $endDate]) // Filter by date range
            ->whereNotNull('client_docs.application_id') // Ensure application_id is not null
            ->groupBy(
                'users.name',
                'users.id',
                'clients.id',
                'clients.name',
                'applications.application_id',
                'applications.application_name'
            ) // Group by subscriber, client, and application
            ->havingRaw('docs_count > 0') // Ensure the client uploaded at least one document
            ->orderByDesc('docs_count') // Order by the number of documents uploaded
            ->get();
        
            return response()->json(['data' => $byNumberOfApplicationDocumentStoreChart]);
        } elseif (request()->type == "byApplicationYear") {
            $query =  new Applications();
            if (($user->membership == 'Adwiseri' || $user->membership == 'Adwiseri+' || $user->membership == 'Enterprise') && $user->user_type == 'Subscriber') {
                // Apply condition for 'Subscriber' user type and membership types
                $query =   $query->where('subscriber_id', request()->subid);
            }
            $clients = $query->select(
                DB::raw('YEAR(created_at) AS year'), // Extract year from referral creation date
                DB::raw('COUNT(*) AS count') // Count referrals per year
            )
                ->groupBy(DB::raw('YEAR(created_at)')) // Group by year
                ->orderBy('year', 'asc') // Order from oldest to newest
                ->get();

            return response()->json(['data' => $clients]);
        } elseif (request()->type == "byApplicationTimeline") {
            $currentYear = date('Y');
            $currentDate = now();
            $lastWeekStart = now()->subWeek()->startOfWeek();
            $lastWeekEnd = now()->subWeek()->endOfWeek();
            $lastMonthStart = now()->subMonth()->startOfMonth();
            $lastMonthEnd = now()->subMonth()->endOfMonth();
            $lastQuarterStart = now()->subQuarter()->startOfQuarter();
            $lastQuarterEnd = now()->subQuarter()->endOfQuarter();
            $lastYearStart = now()->subYear()->startOfYear();
            $lastYearEnd = now()->subYear()->endOfYear();
            // Get Inspection Start Date (modify this based on where the date is stored)
            
            // Base Query
            $query = new Applications();
            $query1 = clone $query;
            
            if (($user->membership == 'Adwiseri' || $user->membership == 'Adwiseri+' || $user->membership == 'Enterprise') 
                && $user->user_type == 'Subscriber') {
                $query = $query->where('subscriber_id', request()->subid)->whereYear('created_at', '=', $currentYear);
                $query1 = $query1->where('subscriber_id', request()->subid);
                $inspectionStartDate = $query1->where('subscriber_id', request()->subid)->orderBy('created_at','asc')->first();
            }else{
                $inspectionStartDate = $query1->orderBy('created_at','asc')->first();
            }
            
            // 🔹 Today's Applications
            $todayApplications = clone $query;
            $todayApplications = $todayApplications->whereDate('created_at', $currentDate)
                ->selectRaw("'Today' as type, COUNT(*) as count")
                ->get();
            
            // 🔹 Last Week's Applications
            $lastWeekApplications = clone $query;
            $lastWeekApplications = $lastWeekApplications->whereBetween('created_at', [$lastWeekStart, $lastWeekEnd])
                ->selectRaw("'Last Week' as type, COUNT(*) as count")
                ->get();
            
            // 🔹 Last Month's Applications
            $lastMonthApplications = clone $query;
            $lastMonthApplications = $lastMonthApplications->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])
                ->selectRaw("'Last Month' as type, COUNT(*) as count")
                ->get();
            
            // 🔹 Last Quarter's Applications
            $lastQuarterApplications = clone $query;
            $lastQuarterApplications = $lastQuarterApplications->whereBetween('created_at', [$lastQuarterStart, $lastQuarterEnd])
                ->selectRaw("'Last Quarter' as type, COUNT(*) as count")
                ->get();
            
            $yarlyApplications = clone $query;
            $yarlyApplications = $yarlyApplications->whereBetween('created_at', [$lastYearStart, $lastYearEnd])
                ->selectRaw("'Last Year' as type, COUNT(*) as count")
                ->get();
 
            
            // 🔹 Since Inception Data (Replacing "Past Year Data")
            $sinceInspectionData = clone $query1;
            $sinceInspectionData = $sinceInspectionData
                ->whereDate('created_at', '>=', $inspectionStartDate->created_at) 
                ->selectRaw("'Since Inception' as type, COUNT(*) as count")
                ->get();
            
            // 🔹 Weekly Applications
            $weeklyApplications = clone $query;
            $weeklyApplications = $weeklyApplications->selectRaw("
                WEEK(created_at) as week_num, 
                YEAR(created_at) as year_num, 
                COUNT(*) as count
            ")
                ->groupBy('year_num', 'week_num')
                ->orderBy('year_num', 'asc')
                ->orderBy('week_num', 'asc')
                ->get();
            
            // 🔹 Quarterly Applications
            $quarterlyApplications = clone $query;
            $quarterlyApplications = $quarterlyApplications->selectRaw("
                YEAR(created_at) as year, 
                QUARTER(created_at) as quarter, 
                COUNT(*) as count
            ")
                ->groupBy('year', 'quarter')
                ->orderBy('year', 'asc')
                ->orderBy('quarter', 'asc')
                ->get();
            
            // 🔹 Monthly Applications
            $monthlyApplications = clone $query;
            $monthlyApplications = $monthlyApplications->selectRaw("
                YEAR(created_at) as year, 
                MONTH(created_at) as month, 
                COUNT(*) as count
            ")
                ->groupBy('year', 'month')
                ->orderBy('year', 'asc')
                ->orderBy('month', 'asc')
                ->get();
                // dd($monthlyApplications);
            // 🔹 Merge All Data
            $formattedData = collect()
                ->merge($todayApplications)
                ->merge($lastWeekApplications)
                ->merge($lastMonthApplications)
                ->merge($lastQuarterApplications)
                // ->merge($weeklyApplications)
                ->merge($yarlyApplications)
                // ->merge($monthlyApplications)
                ->merge($sinceInspectionData); // ✅ Replacing past year with "Since Inception"
            
            // 🔹 Format Data for Output
            $formattedData = $formattedData->map(function ($item) {
                return [
                    'type' => $item['type'],
                    'count' => $item['count'],
                ];
            });
            // dd($formattedData);
            return response()->json([
                'status' => 'success',
                'data' => $formattedData
            ]);
            
        } elseif (request()->type == "byDocumentNoofApplicationsChart") {
            $query =  new Client_Docs();
            if (($user->membership == 'Adwiseri' || $user->membership == 'Adwiseri+' || $user->membership == 'Enterprise') && $user->user_type == 'Subscriber') {
                // Apply condition for 'Subscriber' user type and membership types
                $query =   $query->where('client_docs.user_id', request()->subid);
            }

            // $byDocumentNoofApplications = $query->join('applications', 'client_docs.application_id', '=', 'applications.application_id') // Join applications table
            //     ->whereBetween('client_docs.created_at', [$startDate, $endDate]) // Filter by date range
            //     ->whereNotNull('client_docs.application_id') // Ensure application_id exists
            //     ->select(
            //         'applications.application_name', // Application Name
            //         DB::raw('COUNT(DISTINCT client_docs.id) as no_of_docs') // Count total number of documents
            //     )
            //     ->groupBy('applications.application_name') // Group by application name
            //     ->orderBy('no_of_docs', 'desc') // Order by highest number of docs
            //     ->limit(50) // Limit results (optional)
            //     ->get();
            $byDocumentNoofApplications = $query->join('applications', 'client_docs.application_id', '=', 'applications.application_id') // Join applications table
                ->whereBetween('client_docs.created_at', [$startDate, $endDate]) // Filter by date range
                ->whereNotNull('client_docs.application_id') // Ensure application_id exists
                ->select(
                    DB::raw("CONCAT(applications.application_name, ' (', applications.application_id, ')') as application_name"), // Format: Test (1)
                    DB::raw('COUNT(DISTINCT client_docs.id) as no_of_docs') // Count total number of documents
                )
                ->groupBy('applications.application_id', 'applications.application_name') // Group by application_id & application_name
                ->orderBy('no_of_docs', 'desc') // Order by highest number of docs
                ->limit(50) // Limit results (optional)
                ->get();

                    
            return response()->json(['data' => $byDocumentNoofApplications]);
        } elseif (request()->type == "byNoofApplicantsChart") {
            $startDate = $this->parseReportDate(request()->start);
            $endDate = $this->parseReportDate(request()->end, true);
            if (!empty(request()->subid)) {
                // Filter by specific subscriber ID
                $data = Applications::whereBetween('created_at', [$startDate, $endDate]) // Explicitly specify the table for created_at
                    ->where('subscriber_id', request()->subid)
                    ->count();
            } else {
                // Without subscriber ID filter


                $data = Applications::whereBetween('created_at', [$startDate, $endDate]) // Explicitly specify the table for created_at
                    ->count();
            }

            return response()->json(['data' =>  $data]);
        } elseif (request()->type == "byDocumentYear") {
          
            $query =   new Client_Docs();

            if (($user->membership == 'Adwiseri' || $user->membership == 'Adwiseri+' || $user->membership == 'Enterprise') && $user->user_type == 'Subscriber') {
                $query =  $query = $query->where('user_id', $user->id);
            }

            $byUserTimeline = $query
                ->select(
                    DB::raw('YEAR(created_at) AS year'), // ✅ Specify users.created_at to avoid ambiguity
                    DB::raw('COUNT(*) AS count') // ✅ Count based on users.id
                )
                ->groupBy(DB::raw('YEAR(created_at)')) // ✅ Group by extracted year
                ->orderBy('year', 'asc') // ✅ Sort by oldest first
                ->get();

            return response()->json(['data' => $byUserTimeline]);
        } elseif (request()->type == "byDocumentTimeline(Duration)") {
            $query = new Client_Docs();
            $query1 = clone $query;

            if (($user->membership == 'Adwiseri' || $user->membership == 'Adwiseri+' || $user->membership == 'Enterprise')
                && $user->user_type == 'Subscriber') {
                $query = $query->where('user_id', $user->id);
                $query1 = $query1->where('user_id', $user->id);
            }

            $formattedData = $this->buildTimelineDurationData($query, $query1);

            return response()->json([
                'status' => 'success',
                'data' => $formattedData
            ]);
            
        
        
        } elseif (request()->type == "ByClientsTopDocs") {

            $query = new Client_Docs();

            if (($user->membership == 'Adwiseri' || $user->membership == 'Adwiseri+' || $user->membership == 'Enterprise')
                && $user->user_type == 'Subscriber'
            ) {
                $query = $query->where('client_docs.user_id', request()->subid);
            }

            $ByClientsTopDocs = $query->whereNotNull('client_id')->join('clients', 'client_docs.client_id', '=', 'clients.id') // Join clients table
                ->whereBetween('client_docs.created_at', [$startDate, $endDate]) // Filter by date range
                ->select(
                    'clients.name as client_name', // Client Name
                    DB::raw('COUNT(DISTINCT client_docs.id) as no_of_docs') // Count total number of documents
                )
                ->groupBy('clients.id', 'clients.name') // Group by client
                ->orderBy('no_of_docs', 'desc') // Order by highest number of docs
                ->limit(50) // Limit results (optional)
                ->get();

            return response()->json(['data' => $ByClientsTopDocs]);
        } elseif (request()->type == "byFileSizeDocsChart") {
            $query = Client_Docs::join('clients', 'client_docs.client_id', '=', 'clients.id')

                ->join('applications', 'client_docs.application_id', '=', 'applications.application_id') // Joining applications table
                ->select(
                    'client_docs.doc_file',
                    'client_docs.client_id',
                    'clients.name as client_name',
                    'applications.application_name', // Select the application name
                    'applications.application_id as application_id', // Select the application ID
                    'client_docs.doc_name',
                    'client_docs.id'
                )
                ->whereBetween('client_docs.created_at', [$startDate, $endDate]);

            if (in_array($user->membership, ['Adwiseri', 'Adwiseri+', 'Enterprise']) && $user->user_type == 'Subscriber') {
                $query->where('client_docs.user_id', $user->id);
            }

            $documents = $query->get();
            $filesWithSize = [];
            foreach ($documents as $doc) {
                // Construct the file path
                $filePath = public_path('web_assets/users/client' . $doc->client_id . '/docs/' . $doc->doc_file);

                // Check if the file exists
                if (file_exists($filePath)) {
                    // Get file size in bytes
                    $fileSize = filesize($filePath);

                    // Format the file size
                    $formattedSize = $fileSize < 1024
                        ? $fileSize . ' B'
                        : ($fileSize < 1048576
                            ? round($fileSize / 1024, 2) . ' KB'
                            : ($fileSize < 1073741824
                                ? round($fileSize / 1048576, 2) . ' MB'
                                : round($fileSize / 1073741824, 2) . ' GB'));

                    // Add the document and its size to the array
                    $filesWithSize[] = [
                        'client_name' => $doc->client_name . '(' . $doc->client_id . ')',
                        'application_name' => $doc->application_name . '(' . $doc->application_id . ')', // Application name
                        'application_id' => $doc->application_id, // Application ID
                        'docs_name' => $doc->doc_name . ' (' . $doc->id . ')',
                        'doc_file' => \App\Support\DocumentFileName::forTable($doc->doc_file, $doc->doc_name),
                        'file_size' => $fileSize, // Size in bytes
                        'formatted_size' => $formattedSize, // Human-readable size
                    ];
                }
            }
            // Sort files by size (highest to lowest)
            usort($filesWithSize, function ($a, $b) {
                return $b['file_size'] <=> $a['file_size'];
            });
            $topFiles = array_slice($filesWithSize, 0, 50);

            return response()->json(['data' => $topFiles]);
        } elseif (request()->type == "byFileTypeDocsChart") {
            // Count by extension from DB records (not disk), so missing/moved files still appear in Analytics.
            $query = Client_Docs::query()
                ->select(
                    DB::raw("LOWER(SUBSTRING_INDEX(client_docs.doc_file, '.', -1)) as file_type"),
                    DB::raw('COUNT(*) as count')
                )
                ->where('client_docs.user_id', $user->id)
                ->whereNotNull('client_docs.doc_file')
                ->where('client_docs.doc_file', '!=', '')
                ->where('client_docs.doc_file', 'like', '%.%')
                ->whereBetween('client_docs.created_at', [$startDate, $endDate])
                ->groupBy('file_type')
                ->orderByDesc('count');

            $fileTypeCount = $query->get()
                ->filter(function ($row) {
                    $type = trim((string) ($row->file_type ?? ''));
                    return $type !== '';
                })
                ->map(function ($row) {
                    return [
                        'file_type' => strtolower(trim((string) $row->file_type)),
                        'count' => (int) $row->count,
                    ];
                })
                ->values()
                ->all();

            return response()->json(['data' => $fileTypeCount]);
        } elseif (request()->type == "byUserRoleChart") {

            $query =  new User();
            if (($user->membership == 'Adwiseri' || $user->membership == 'Adwiseri+' || $user->membership == 'Enterprise') && $user->user_type == 'Subscriber') {
                // Apply condition for 'Subscriber' user type and membership types
                $query =   $query->where('added_by', request()->subid);
            }

            // Filter by 'added_by' if 'subid' is provided
            $roles = $query->whereBetween('created_at', [$startDate, $endDate])
                ->select('designation', DB::raw('count(*) as users'))
                ->groupBy('designation')
                ->get();

            return response()->json(['data' => $roles]);
        } elseif (request()->type == "byUserAgeGroupChart") {
            [$labelsSql, $caseSql] = $this->ageGroupBucketSql('dob');

            $query = User::query()->where('user_type', 'User');
            $query = $this->applyAgeGroupConsultancyScope($query, $user, 'added_by');

            $byUserAgeGroup = DB::table(DB::raw($labelsSql))
                ->leftJoinSub(
                    $query->whereBetween('created_at', [$startDate, $endDate])
                        ->whereNotNull('dob')
                        ->where('dob', '!=', '')
                        ->where('dob', '!=', '0000-00-00')
                        ->whereRaw('dob REGEXP ?', ['^[0-9]{4}-[0-9]{2}-[0-9]{2}'])
                        ->selectRaw("{$caseSql} AS age_group, COUNT(*) AS count")
                        ->groupBy('age_group'),
                    'age_counts',
                    'age_groups.age_group',
                    '=',
                    'age_counts.age_group'
                )
                ->select('age_groups.age_group', DB::raw('COALESCE(age_counts.count, 0) AS count'))
                ->orderByRaw("
                        FIELD(age_groups.age_group, 'Under 18', '18-24', '25-34', '35-44', '45-55', '55 +')
                    ")
                ->get();
                // ✅ Check if all counts are 0, and return empty if so
                if ($byUserAgeGroup->sum('count') === 0) {
                    return response()->json(['data' => []]); // frontend will show "No data available"
                }
            return response()->json(['data' => $byUserAgeGroup]);
        } elseif (request()->type == "byUserGenderChart") {
            $query =  new User();
            if (($user->membership == 'Adwiseri' || $user->membership == 'Adwiseri+' || $user->membership == 'Enterprise') && $user->user_type == 'Subscriber') {
                // Apply condition for 'Subscriber' user type and membership types
                $query =   $query->where('added_by', request()->subid);
            }
            $byUserGender = $query->whereBetween('created_at', [$startDate, $endDate])->select(
                DB::raw('COUNT(*) AS count') // Count referrals per year
            )
                ->groupBy('gender') // Group by year
                ->orderBy('count', 'desc') // Order by count, highest to lowest
                ->get();
            return response()->json(['data' => $byUserGender]);
        } elseif (request()->type == "byUserApplicationProcessedChart") {

            $query =  new Application_assignments();
            if (($user->membership == 'Adwiseri' || $user->membership == 'Adwiseri+' || $user->membership == 'Enterprise') && $user->user_type == 'Subscriber') {
                // Apply condition for 'Subscriber' user type and membership types
                $query =   $query->where('subscriber_id', request()->subid);
            }
            $byUserTotalApplicationAssigned = $query->whereBetween('application_assignments.created_at', [$startDate, $endDate])
                ->join('users', 'users.id', '=', 'application_assignments.user_id')
                ->select(
                    'users.id as user_id',
                    'users.name as user_name',
                    DB::raw('COUNT(application_assignments.id) AS total_assignments')
                )
                ->groupBy('users.id', 'users.name') // Group by user_id and user_name
                ->orderBy('total_assignments', 'desc') // Order by assignment count (highest first)
                ->get();

            return response()->json(['data' => $byUserTotalApplicationAssigned]);
        } elseif (request()->type == "byUserMeetingNotesChart") {

            $query = new Client_discussions();
            if (($user->membership == 'Adwiseri' || $user->membership == 'Adwiseri+' || $user->membership == 'Enterprise') && $user->user_type == 'Subscriber') {
                $query =   $query->where('subscriber_id', request()->subid);
            }
            $application = $query->whereBetween('created_at', [$startDate, $endDate])->select('user_name', DB::raw('count(discussion) as discussion'))->groupBy('user_name')->get();
            return response()->json(['data' => $application]);
        } elseif (request()->type == "byUserModeofCommunicationChart") {

            $query = new Client_discussions();
            if (($user->membership == 'Adwiseri' || $user->membership == 'Adwiseri+' || $user->membership == 'Enterprise') && $user->user_type == 'Subscriber') {
                $query =   $query->where('subscriber_id', request()->subid);
            }
            $byUserModeofCommunication =  $query->whereBetween('created_at', [$startDate, $endDate])
                ->select(
                    'communication_type',
                    DB::raw("COUNT(DISTINCT user_id) AS total_users") // Count distinct users for each communication type
                )
                ->groupBy('communication_type') // Group by communication type
                ->get();

            return response()->json(['data' => $byUserModeofCommunication]);

            // -------------------------- work from here by Adil iqbal ---------------------

        } elseif (request()->type == "byUserNoofMessagesChart") {


            $query = new Internal_communications();
            if (($user->membership == 'Adwiseri' || $user->membership == 'Adwiseri+' || $user->membership == 'Enterprise') && $user->user_type == 'Subscriber') {
                $query =   $query->where('subscriber_id', request()->subid);
            }
            $byUserNoofMessages = $query->whereBetween('internal_communications.created_at', [$startDate, $endDate])
                ->join('users', 'internal_communications.send_by', '=', 'users.id')
                ->select('users.name', DB::raw('COUNT(*) as total_messages'))
                ->groupBy('users.name')->get();
            return response()->json(['data' => $byUserNoofMessages]);
        } elseif (request()->type == "byUserYear") {
            $query = new User();
            if (($user->membership == 'Adwiseri' || $user->membership == 'Adwiseri+' || $user->membership == 'Enterprise') && $user->user_type == 'Subscriber') {
                $query =   $query->where('added_by', request()->subid);
            }
            $byUserTimeline =   $query->select(
                DB::raw('YEAR(created_at) AS year'), // Extract year from referral creation date
                DB::raw('COUNT(*) AS count') // Count referrals per year
            )
                ->groupBy(DB::raw('YEAR(created_at)')) // Group by year
                ->orderBy('year', 'asc') // Order from oldest to newest
                ->get();


            return response()->json(['data' =>  $byUserTimeline]);
        }  elseif (request()->type == "byUserTimeline(Duration)") {
            $currentYear = date('Y');
            $currentDate = now();
            $lastWeekStart = now()->subWeek()->startOfWeek();
            $lastWeekEnd = now()->subWeek()->endOfWeek();
            $lastMonthStart = now()->subMonth()->startOfMonth();
            $lastMonthEnd = now()->subMonth()->endOfMonth();
            $lastQuarterStart = now()->subQuarter()->startOfQuarter();
            $lastQuarterEnd = now()->subQuarter()->endOfQuarter();
            $lastYearStart = now()->subYear()->startOfYear();
            $lastYearEnd = now()->subYear()->endOfYear();

            $query = User::query()->where('user_type', 'User');
            $query1 = User::query()->where('user_type', 'User');
            if (($user->membership == 'Adwiseri' || $user->membership == 'Adwiseri+' || $user->membership == 'Enterprise') 
            && $user->user_type == 'Subscriber') {
            $query = $query->where('added_by', $user->id)->whereYear('created_at', '=', $currentYear);
            // Scope the "Since Inception" base to this subscriber's staff too, otherwise it counts all users in the system.
            $query1 = $query1->where('added_by', $user->id);
            $inspectionStartDate = (clone $query1)->orderBy('created_at','asc')->first();
            }else{
                $inspectionStartDate = (clone $query1)->orderBy('created_at','asc')->first();
            }
        
        // 🔹 Today's Applications
        $todayApplications = clone $query;
        $todayApplications = $todayApplications->whereDate('created_at', $currentDate)
            ->selectRaw("'Today' as type, COUNT(*) as count")
            ->get();
        
        // 🔹 Last Week's Applications
        $lastWeekApplications = clone $query;
        $lastWeekApplications = $lastWeekApplications->whereBetween('created_at', [$lastWeekStart, $lastWeekEnd])
            ->selectRaw("'Last Week' as type, COUNT(*) as count")
            ->get();
        
        // 🔹 Last Month's Applications
        $lastMonthApplications = clone $query;
        $lastMonthApplications = $lastMonthApplications->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])
            ->selectRaw("'Last Month' as type, COUNT(*) as count")
            ->get();
        
        // 🔹 Last Quarter's Applications
        $lastQuarterApplications = clone $query;
        $lastQuarterApplications = $lastQuarterApplications->whereBetween('created_at', [$lastQuarterStart, $lastQuarterEnd])
            ->selectRaw("'Last Quarter' as type, COUNT(*) as count")
            ->get();

        $yearlyApplications = clone $query;
        $yearlyApplications = $yearlyApplications->whereBetween('created_at', [$lastYearStart, $lastYearEnd])
            ->selectRaw("'Last Year' as type, COUNT(*) as count")
            ->get();
        
        // 🔹 Since Inception Data (Replacing "Past Year Data")
        $sinceInspectionData = clone $query1;
        $sinceInspectionData = $sinceInspectionData
            ->whereDate('created_at', '>=', $inspectionStartDate->created_at) 
            ->selectRaw("'Since Inception' as type, COUNT(*) as count")
            ->get();
        
        // 🔹 Weekly Applications
        $weeklyApplications = clone $query;
        $weeklyApplications = $weeklyApplications->selectRaw("
            WEEK(created_at) as week_num, 
            YEAR(created_at) as year_num, 
            COUNT(*) as count
        ")
            ->groupBy('year_num', 'week_num')
            ->orderBy('year_num', 'asc')
            ->orderBy('week_num', 'asc')
            ->get();
        
        // 🔹 Quarterly Applications
        $quarterlyApplications = clone $query;
        $quarterlyApplications = $quarterlyApplications->selectRaw("
            YEAR(created_at) as year, 
            QUARTER(created_at) as quarter, 
            COUNT(*) as count
        ")
            ->groupBy('year', 'quarter')
            ->orderBy('year', 'asc')
            ->orderBy('quarter', 'asc')
            ->get();
        
        // 🔹 Monthly Applications
        $monthlyApplications = clone $query;
        $monthlyApplications = $monthlyApplications->selectRaw("
            YEAR(created_at) as year, 
            MONTH(created_at) as month, 
            COUNT(*) as count
        ")
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();
        
        // 🔹 Merge All Data
        $formattedData = collect()
            ->merge($todayApplications)
            ->merge($lastWeekApplications)
            ->merge($lastMonthApplications)
            ->merge($lastQuarterApplications)
            ->merge($yearlyApplications)
            // ->merge($quarterlyApplications)
            // ->merge($monthlyApplications)
            ->merge($sinceInspectionData); // ✅ Replacing past year with "Since Inception"
        
        // 🔹 Format Data for Output
        $formattedData = $formattedData->map(function ($item) {
            return [
                'type' => $item['type'],
                'count' => $item['count'],
            ];
        });
        
        return response()->json([
            'status' => 'success',
            'data' => $formattedData
        ]);
            


            return response()->json(['data' =>  $byUserTimeline]);
        } elseif (request()->type == "byInvoiceAmountChart") {

            $query = Internal_Invoices::whereBetween('created_at', [$startDate, $endDate]);
            if (in_array($user->membership, ['Adwiseri', 'Adwiseri+', 'Enterprise']) && $user->user_type == 'Subscriber') {
                $query = $query->where('subscriber_id', $user->id)->where('type', 'ar');
            }
            $byInvoiceAmount = $query
                ->selectRaw("
                CASE 
                    WHEN amount BETWEEN 1 AND 99 THEN '1-99'
                    WHEN amount BETWEEN 100 AND 249 THEN '100-249'
                    WHEN amount BETWEEN 250 AND 499 THEN '250-499'
                    WHEN amount BETWEEN 500 AND 999 THEN '500-999'
                    WHEN amount BETWEEN 1000 AND 2499 THEN '1000-2499'
                    WHEN amount BETWEEN 2500 AND 4999 THEN '2500-4999'
                    WHEN amount BETWEEN 5000 AND 9999 THEN '5000-9999'
                    WHEN amount >= 10000 THEN '10,000+'
                END AS amount_range
            ")

                ->selectRaw('COUNT(*) as number_of_invoices')
                ->groupBy('amount_range')
                ->orderByRaw("FIELD(amount_range, '1-99', '100-249', '250-499', '500-999', '1000-2499', '2500-4999', '5000-9999', '10,000+') ASC")
                ->get();



            return response()->json(['data' => $byInvoiceAmount]);
        } elseif (request()->type == "byInvoiceTypeChart") {
            $query = Internal_Invoices::whereBetween('created_at', [$startDate, $endDate]);
            if (in_array($user->membership, ['Adwiseri', 'Adwiseri+', 'Enterprise']) && $user->user_type == 'Subscriber') {
                $query = $query->where('subscriber_id', $user->id)->where('type', 'ar');
            }
            $byInvoiceType = $query->whereBetween('created_at', [$startDate, $endDate])
                ->select('status') // Include the `status` field
                ->selectRaw('COUNT(*) as number_of_invoices') // Count invoices per status
                ->selectRaw('SUM(total) as total_amount_sum') // Sum of `total` per status
                ->groupBy('status') // Group by `status`
                ->get();


            return response()->json(['data' => $byInvoiceType]);
        } elseif (request()->type == "byInvoiceServiceOfferedChart") {
            $query = Internal_Invoices::whereBetween('created_at', [$startDate, $endDate]);
            if (in_array($user->membership, ['Adwiseri', 'Adwiseri+', 'Enterprise']) && $user->user_type == 'Subscriber') {
                $query = $query->where('subscriber_id', $user->id)->where('type', 'ar');
            }
            $byInvoiceServiceOffered = $query->whereBetween('created_at', [$startDate, $endDate])
                ->select('detail') // Include the `status` field
                ->selectRaw('COUNT(*) as number_of_invoices') // Count invoices per status
                ->selectRaw('SUM(total) as total_amount_sum') // Sum of `total` per status
                ->groupBy('detail') // Group by `status`
                ->get();



            return response()->json(['data' => $byInvoiceServiceOffered]);
        } elseif (request()->type == "byInvoiceYear") {
            $query = new Internal_Invoices ();
            if (in_array($user->membership, ['Adwiseri', 'Adwiseri+', 'Enterprise']) && $user->user_type == 'Subscriber') {
                $query = $query->where('subscriber_id', $user->id)->where('type', 'ar');
            }
            $byInvoiceServiceOffered  = $query
         // ✅ Ensure correct filtering
            ->select(
                DB::raw('YEAR(created_at) AS year'), // ✅ Specify users.created_at to avoid ambiguity
                DB::raw('COUNT(*) AS count') // ✅ Count based on users.id
            )
            ->groupBy(DB::raw('YEAR(created_at)')) // ✅ Group by extracted year
            ->orderBy('year', 'asc') // ✅ Sort by oldest first
            ->get();



            return response()->json(['data' => $byInvoiceServiceOffered]);
        }elseif (request()->type == "byInvoiceTimeline") {
            $currentYear = date('Y');
            $currentDate = now();
            $lastWeekStart = now()->subWeek()->startOfWeek();
            $lastWeekEnd = now()->subWeek()->endOfWeek();
            $lastMonthStart = now()->subMonth()->startOfMonth();
            $lastMonthEnd = now()->subMonth()->endOfMonth();
            $lastQuarterStart = now()->subQuarter()->startOfQuarter();
            $lastQuarterEnd = now()->subQuarter()->endOfQuarter();
            $lastYearStart = now()->subYear()->startOfYear();
            $lastYearEnd = now()->subYear()->endOfYear();
            
            // Keep every AR timeline bucket on the same invoice type and subscriber scope.
            $query = Internal_Invoices::query()->where('type', 'ar');
            if ($user->user_type == 'Subscriber') {
                $query->where('subscriber_id', $user->id);
            }
            $query1 = clone $query;
            $inspectionStartDate = (clone $query1)->orderBy('created_at', 'asc')->first();
            // 🔹 Today's Applications
            $todayApplications = clone $query;
            $todayApplications = $todayApplications->whereDate('created_at', $currentDate)
                ->selectRaw("'Today' as type, COUNT(*) as count")
                ->get();
            
            // 🔹 Last Week's Applications
            $lastWeekApplications = clone $query;
            $lastWeekApplications = $lastWeekApplications->whereBetween('created_at', [$lastWeekStart, $lastWeekEnd])
                ->selectRaw("'Last Week' as type, COUNT(*) as count")
                ->get();
            
            // 🔹 Last Month's Applications
            $lastMonthApplications = clone $query;
            $lastMonthApplications = $lastMonthApplications->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])
                ->selectRaw("'Last Month' as type, COUNT(*) as count")
                ->get();
            
            // 🔹 Last Quarter's Applications
            $lastQuarterApplications = clone $query;
            $lastQuarterApplications = $lastQuarterApplications->whereBetween('created_at', [$lastQuarterStart, $lastQuarterEnd])
                ->selectRaw("'Last Quarter' as type, COUNT(*) as count")
                ->get();

            $yearlyApplications = clone $query;
            $yearlyApplications = $yearlyApplications->whereBetween('created_at', [$lastYearStart, $lastYearEnd])
                ->selectRaw("'Last Year' as type, COUNT(*) as count")
                ->get();
            
            // 🔹 Since Inception Data (Replacing "Past Year Data")
            $sinceInspectionData = (clone $query1)
                ->when($inspectionStartDate, function ($invoiceQuery) use ($inspectionStartDate) {
                    $invoiceQuery->whereDate('created_at', '>=', $inspectionStartDate->created_at);
                })
                ->selectRaw("'Since Inception' as type, COUNT(*) as count")
                ->get();
            
            // 🔹 Weekly Applications
            $weeklyApplications = clone $query;
            $weeklyApplications = $weeklyApplications->selectRaw("
                WEEK(created_at) as week_num, 
                YEAR(created_at) as year_num, 
                COUNT(*) as count
            ")
                ->groupBy('year_num', 'week_num')
                ->orderBy('year_num', 'asc')
                ->orderBy('week_num', 'asc')
                ->get();
            
            // 🔹 Quarterly Applications
            $quarterlyApplications = clone $query;
            $quarterlyApplications = $quarterlyApplications->selectRaw("
                YEAR(created_at) as year, 
                QUARTER(created_at) as quarter, 
                COUNT(*) as count
            ")
                ->groupBy('year', 'quarter')
                ->orderBy('year', 'asc')
                ->orderBy('quarter', 'asc')
                ->get();
            
            // 🔹 Monthly Applications
            $monthlyApplications = clone $query;
            $monthlyApplications = $monthlyApplications->selectRaw("
                YEAR(created_at) as year, 
                MONTH(created_at) as month, 
                COUNT(*) as count
            ")
                ->groupBy('year', 'month')
                ->orderBy('year', 'asc')
                ->orderBy('month', 'asc')
                ->get();
            
            // 🔹 Merge All Data
            $formattedData = collect()
                ->merge($todayApplications)
                ->merge($lastWeekApplications)
                ->merge($lastMonthApplications)
                ->merge($lastQuarterApplications)
                ->merge($yearlyApplications)
                // ->merge($quarterlyApplications)
                // ->merge($monthlyApplications)
                ->merge($sinceInspectionData); // ✅ Replacing past year with "Since Inception"
            
            // 🔹 Format Data for Output
            $formattedData = $formattedData->map(function ($item) {
                return [
                    'type' => $item['type'],
                    'count' => $item['count'],
                ];
            });
            
            return response()->json([
                'status' => 'success',
                'data' => $formattedData
            ]);
        }
        elseif (request()->type == "byInvoiceAPAmountChart") {

            $query = Internal_Invoices::whereBetween('created_at', [$startDate, $endDate]);
            if (in_array($user->membership, ['Adwiseri', 'Adwiseri+', 'Enterprise']) && $user->user_type == 'Subscriber') {
                $query = $query->where('subscriber_id', $user->id)->where('type', 'ap');
            }
            $byInvoiceAmount = $query
                ->selectRaw("
                CASE 
                    WHEN amount BETWEEN 1 AND 99 THEN '1-99'
                    WHEN amount BETWEEN 100 AND 249 THEN '100-249'
                    WHEN amount BETWEEN 250 AND 499 THEN '250-499'
                    WHEN amount BETWEEN 500 AND 999 THEN '500-999'
                    WHEN amount BETWEEN 1000 AND 2499 THEN '1000-2499'
                    WHEN amount BETWEEN 2500 AND 4999 THEN '2500-4999'
                    WHEN amount BETWEEN 5000 AND 9999 THEN '5000-9999'
                    WHEN amount >= 10000 THEN '10,000+'
                END AS amount_range
            ")

                ->selectRaw('COUNT(*) as number_of_invoices')
                ->groupBy('amount_range')
                ->orderByRaw("FIELD(amount_range, '1-99', '100-249', '250-499', '500-999', '1000-2499', '2500-4999', '5000-9999', '10,000+') ASC")
                ->get();



            return response()->json(['data' => $byInvoiceAmount]);
        } elseif (request()->type == "byInvoiceAPTypeChart") {
            $query = Internal_Invoices::whereBetween('created_at', [$startDate, $endDate]);
            if (in_array($user->membership, ['Adwiseri', 'Adwiseri+', 'Enterprise']) && $user->user_type == 'Subscriber') {
                $query = $query->where('subscriber_id', $user->id)->where('type', 'ap');
            }
            $byInvoiceType = $query->whereBetween('created_at', [$startDate, $endDate])
                ->select('status') // Include the `status` field
                ->selectRaw('COUNT(*) as number_of_invoices') // Count invoices per status
                ->selectRaw('SUM(total) as total_amount_sum') // Sum of `total` per status
                ->groupBy('status') // Group by `status`
                ->get();


            return response()->json(['data' => $byInvoiceType]);
        } elseif (request()->type == "byInvoiceAPServiceOfferedChart") {
            // AP invoices record Product/Service Taken (from vendors), grouped by detail.
            $query = Internal_Invoices::query()
                ->where('type', 'ap')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->whereNotNull('detail')
                ->where('detail', '!=', '');

            if ($user->user_type == 'Subscriber') {
                $query->where('subscriber_id', $user->id);
            }

            $byInvoiceServicesTaken = $query
                ->select('detail')
                ->selectRaw('COUNT(*) as number_of_invoices')
                ->selectRaw('SUM(total) as total_amount_sum')
                ->groupBy('detail')
                ->orderByDesc('number_of_invoices')
                ->get();

            return response()->json(['data' => $byInvoiceServicesTaken]);
        } elseif (request()->type == "byInvoiceAPYear") {
            $query = new Internal_Invoices ();
            if (in_array($user->membership, ['Adwiseri', 'Adwiseri+', 'Enterprise']) && $user->user_type == 'Subscriber') {
                $query = $query->where('subscriber_id', $user->id)->where('type', 'ap');
            }
            $byInvoiceServiceOffered  = $query
         // ✅ Ensure correct filtering
            ->select(
                DB::raw('YEAR(created_at) AS year'), // ✅ Specify users.created_at to avoid ambiguity
                DB::raw('COUNT(*) AS count') // ✅ Count based on users.id
            )
            ->groupBy(DB::raw('YEAR(created_at)')) // ✅ Group by extracted year
            ->orderBy('year', 'asc') // ✅ Sort by oldest first
            ->get();



            return response()->json(['data' => $byInvoiceServiceOffered]);
        }elseif (request()->type == "byInvoiceAPTimeline") {
            $currentYear = date('Y');
            $currentDate = now();
            $lastWeekStart = now()->subWeek()->startOfWeek();
            $lastWeekEnd = now()->subWeek()->endOfWeek();
            $lastMonthStart = now()->subMonth()->startOfMonth();
            $lastMonthEnd = now()->subMonth()->endOfMonth();
            $lastQuarterStart = now()->subQuarter()->startOfQuarter();
            $lastQuarterEnd = now()->subQuarter()->endOfQuarter();
            $lastYearStart = now()->subYear()->startOfYear();
            $lastYearEnd = now()->subYear()->endOfYear();
            
            // Keep every AP timeline bucket on the same invoice type and subscriber scope.
            $query = Internal_Invoices::query()->where('type', 'ap');
            if ($user->user_type == 'Subscriber') {
                $query->where('subscriber_id', $user->id);
            }
            $query1 = clone $query;
            $inspectionStartDate = (clone $query1)->orderBy('created_at', 'asc')->first();
            // 🔹 Today's Applications
            $todayApplications = clone $query;
            $todayApplications = $todayApplications->whereDate('created_at', $currentDate)
                ->selectRaw("'Today' as type, COUNT(*) as count")
                ->get();
            
            // 🔹 Last Week's Applications
            $lastWeekApplications = clone $query;
            $lastWeekApplications = $lastWeekApplications->whereBetween('created_at', [$lastWeekStart, $lastWeekEnd])
                ->selectRaw("'Last Week' as type, COUNT(*) as count")
                ->get();
            
            // 🔹 Last Month's Applications
            $lastMonthApplications = clone $query;
            $lastMonthApplications = $lastMonthApplications->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])
                ->selectRaw("'Last Month' as type, COUNT(*) as count")
                ->get();
            
            // 🔹 Last Quarter's Applications
            $lastQuarterApplications = clone $query;
            $lastQuarterApplications = $lastQuarterApplications->whereBetween('created_at', [$lastQuarterStart, $lastQuarterEnd])
                ->selectRaw("'Last Quarter' as type, COUNT(*) as count")
                ->get();

            $yearlyApplications = clone $query;
            $yearlyApplications = $yearlyApplications->whereBetween('created_at', [$lastYearStart, $lastYearEnd])
                ->selectRaw("'Last Year' as type, COUNT(*) as count")
                ->get();
            
            // 🔹 Since Inception Data (Replacing "Past Year Data")
            $sinceInspectionData = (clone $query1)
                ->when($inspectionStartDate, function ($invoiceQuery) use ($inspectionStartDate) {
                    $invoiceQuery->whereDate('created_at', '>=', $inspectionStartDate->created_at);
                })
                ->selectRaw("'Since Inception' as type, COUNT(*) as count")
                ->get();
            
            // 🔹 Weekly Applications
            $weeklyApplications = clone $query;
            $weeklyApplications = $weeklyApplications->selectRaw("
                WEEK(created_at) as week_num, 
                YEAR(created_at) as year_num, 
                COUNT(*) as count
            ")
                ->groupBy('year_num', 'week_num')
                ->orderBy('year_num', 'asc')
                ->orderBy('week_num', 'asc')
                ->get();
            
            // 🔹 Quarterly Applications
            $quarterlyApplications = clone $query;
            $quarterlyApplications = $quarterlyApplications->selectRaw("
                YEAR(created_at) as year, 
                QUARTER(created_at) as quarter, 
                COUNT(*) as count
            ")
                ->groupBy('year', 'quarter')
                ->orderBy('year', 'asc')
                ->orderBy('quarter', 'asc')
                ->get();
            
            // 🔹 Monthly Applications
            $monthlyApplications = clone $query;
            $monthlyApplications = $monthlyApplications->selectRaw("
                YEAR(created_at) as year, 
                MONTH(created_at) as month, 
                COUNT(*) as count
            ")
                ->groupBy('year', 'month')
                ->orderBy('year', 'asc')
                ->orderBy('month', 'asc')
                ->get();
            
            // 🔹 Merge All Data
            $formattedData = collect()
                ->merge($todayApplications)
                ->merge($lastWeekApplications)
                ->merge($lastMonthApplications)
                ->merge($lastQuarterApplications)
                ->merge($yearlyApplications)
                // ->merge($quarterlyApplications)
                // ->merge($monthlyApplications)
                ->merge($sinceInspectionData); // ✅ Replacing past year with "Since Inception"
            
            // 🔹 Format Data for Output
            $formattedData = $formattedData->map(function ($item) {
                return [
                    'type' => $item['type'],
                    'count' => $item['count'],
                ];
            });
            
            return response()->json([
                'status' => 'success',
                'data' => $formattedData
            ]);
        }

         elseif (request()->type == "byPaymentARChart") {
            $query = new PaymentARs();
            if (($user->membership == 'Adwiseri' || $user->membership == 'Adwiseri+' || $user->membership == 'Enterprise') && $user->user_type == 'Subscriber') {
                $query =   $query->where('subscriber_id', request()->subid);
            }
            $byPaymentAR = $query->where('type', 'ar')->select(
                DB::raw('
                            CASE
                                WHEN amount BETWEEN 1 AND 99 THEN "1-99"
                                WHEN amount BETWEEN 100 AND 249 THEN "100-249"
                                WHEN amount BETWEEN 250 AND 499 THEN "250-499"
                                WHEN amount BETWEEN 500 AND 999 THEN "500-999"
                                WHEN amount BETWEEN 1000 AND 2499 THEN "1000-2499"
                                WHEN amount BETWEEN 2500 AND 4999 THEN "2500-4999"
                                WHEN amount BETWEEN 5000 AND 9999 THEN "5000-9999"
                                WHEN amount >= 10000 THEN "10,000+"
                            END AS amount_range')
            )
                ->whereBetween('created_at', [$startDate, $endDate])
                ->selectRaw('COUNT(*) as number_of_invoices')
                ->groupBy('amount_range')
                ->orderBy('amount_range', 'asc')
                ->get();

            return response()->json(['data' => $byPaymentAR]);
        } elseif (request()->type == "byPaymentAPChart") {
            $query = new PaymentARs();
            if (($user->membership == 'Adwiseri' || $user->membership == 'Adwiseri+' || $user->membership == 'Enterprise') && $user->user_type == 'Subscriber') {
                $query =   $query->where('subscriber_id', request()->subid);
            }
            $byPaymentAR = $query->where('type', 'ap')->select(
                DB::raw('
                            CASE
                                WHEN amount BETWEEN 1 AND 99 THEN "1-99"
                                WHEN amount BETWEEN 100 AND 249 THEN "100-249"
                                WHEN amount BETWEEN 250 AND 499 THEN "250-499"
                                WHEN amount BETWEEN 500 AND 999 THEN "500-999"
                                WHEN amount BETWEEN 1000 AND 2499 THEN "1000-2499"
                                WHEN amount BETWEEN 2500 AND 4999 THEN "2500-4999"
                                WHEN amount BETWEEN 5000 AND 9999 THEN "5000-9999"
                                WHEN amount >= 10000 THEN "10,000+"
                            END AS amount_range')
            )
                ->whereBetween('created_at', [$startDate, $endDate])
                ->selectRaw('COUNT(*) as number_of_invoices')
                ->groupBy('amount_range')
                ->orderBy('amount_range', 'asc')
                ->get();

            return response()->json(['data' => $byPaymentAR]);
        } elseif (request()->type == "byPaymentModeChart") {
            $query = new PaymentARs();
            if (($user->membership == 'Adwiseri' || $user->membership == 'Adwiseri+' || $user->membership == 'Enterprise') && $user->user_type == 'Subscriber') {
                $query =   $query->where('subscriber_id', request()->subid);
            }
            $byPaymentMode = $query->whereBetween('created_at', [$startDate, $endDate])
                ->where('type', 'ar');

            PaymentModeChartFilter::applyToQuery($byPaymentMode, 'payment_mode');

            $byPaymentMode = $byPaymentMode
                ->selectRaw('payment_mode, COUNT(*) as number_of_invoices')
                ->groupBy('payment_mode') // Group by payment mode
                ->get();
            return response()->json(['data' => $byPaymentMode]);
        } elseif (request()->type == "byPaymentYearChart") {
            $query = new PaymentARs();
            if (($user->membership == 'Adwiseri' || $user->membership == 'Adwiseri+' || $user->membership == 'Enterprise') && $user->user_type == 'Subscriber') {
                $query =   $query->where('subscriber_id', request()->subid);
            }
            $byPaymentMode  =$query
           // ✅ Ensure correct filtering
            ->select(
                DB::raw('YEAR(created_at) AS year'), // ✅ Specify users.created_at to avoid ambiguity
                DB::raw('COUNT(*) AS count') // ✅ Count based on users.id
            )
            ->groupBy(DB::raw('YEAR(created_at)')) // ✅ Group by extracted year
            ->orderBy('year', 'asc') // ✅ Sort by oldest first
            ->where('type', 'ar')
            ->get();
            return response()->json(['data' => $byPaymentMode]);
        }  elseif (request()->type == "byPaymentTimelineChart") {
            $currentYear = date('Y');
            $currentDate = now();
            $lastWeekStart = now()->subWeek()->startOfWeek();
            $lastWeekEnd = now()->subWeek()->endOfWeek();
            $lastMonthStart = now()->subMonth()->startOfMonth();
            $lastMonthEnd = now()->subMonth()->endOfMonth();
            $lastQuarterStart = now()->subQuarter()->startOfQuarter();
            $lastQuarterEnd = now()->subQuarter()->endOfQuarter();
            $lastYearStart = now()->subYear()->startOfYear();
            $lastYearEnd = now()->subYear()->endOfYear();

            // Keep every AR payment timeline bucket on the same type + subscriber scope.
            $query = PaymentARs::query()->where('type', 'ar');
            if ($user->user_type == 'Subscriber') {
                $query->where('subscriber_id', $user->id);
            }
            $query1 = clone $query;
            $inspectionStartDate = (clone $query1)->orderBy('created_at', 'asc')->first();

            $todayApplications = clone $query;
            $todayApplications = $todayApplications->whereDate('created_at', $currentDate)
                ->selectRaw("'Today' as type, COUNT(*) as count")
                ->get();

            $lastWeekApplications = clone $query;
            $lastWeekApplications = $lastWeekApplications->whereBetween('created_at', [$lastWeekStart, $lastWeekEnd])
                ->selectRaw("'Last Week' as type, COUNT(*) as count")
                ->get();

            $lastMonthApplications = clone $query;
            $lastMonthApplications = $lastMonthApplications->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])
                ->selectRaw("'Last Month' as type, COUNT(*) as count")
                ->get();

            $lastQuarterApplications = clone $query;
            $lastQuarterApplications = $lastQuarterApplications->whereBetween('created_at', [$lastQuarterStart, $lastQuarterEnd])
                ->selectRaw("'Last Quarter' as type, COUNT(*) as count")
                ->get();

            $yearlyApplications = clone $query;
            $yearlyApplications = $yearlyApplications->whereBetween('created_at', [$lastYearStart, $lastYearEnd])
                ->selectRaw("'Last Year' as type, COUNT(*) as count")
                ->get();

            $sinceInspectionData = (clone $query1)
                ->when($inspectionStartDate, function ($paymentQuery) use ($inspectionStartDate) {
                    $paymentQuery->whereDate('created_at', '>=', $inspectionStartDate->created_at);
                })
                ->selectRaw("'Since Inception' as type, COUNT(*) as count")
                ->get();

            $formattedData = collect()
                ->merge($todayApplications)
                ->merge($lastWeekApplications)
                ->merge($lastMonthApplications)
                ->merge($lastQuarterApplications)
                ->merge($yearlyApplications)
                ->merge($sinceInspectionData);

            $formattedData = $formattedData->map(function ($item) {
                return [
                    'type' => $item['type'] ?? null,
                    'count' => $item['count'],
                ];
            })->filter(function ($item) {
                return !empty($item['type']);
            })->values();

            return response()->json([
                'status' => 'success',
                'data' => $formattedData
            ]);
        } elseif (request()->type == "byPaymentVisaCountryChart") {
            $startDate = $this->parseReportDate(request()->start);
            $endDate = $this->parseReportDate(request()->end, true);
            $country = (request()->input('country') == 'All') ? Countries::pluck('country_name') : Countries::where('id', [request()->input('country')])->pluck('country_name');
            if (!empty(request()->subid)) {
                $data = PaymentARs::whereBetween('payment_ar.created_at', [$startDate, $endDate])
                    ->where('payment_ar.subscriber_id', request()->subid)
                    ->whereNotNull('payment_ar.application_id')
                    ->join('applications', 'applications.id', '=', 'payment_ar.application_id') // Join with applications
                    ->selectRaw('applications.application_country as country') // Use the county column from applications
                    ->selectRaw('COUNT(payment_ar.id) as number_of_payment') // Count the number of invoices
                    ->selectRaw('SUM(payment_ar.amount) as total_invoice_sum') // Calculate the sum of invoice totals
                    ->groupBy('applications.application_country') // Group by applications.county
                    ->where('type', 'ar')
                    ->get();
            } else {
                $data = PaymentARs::whereBetween('payment_ar.created_at', [$startDate, $endDate])
                    ->whereNotNull('payment_ar.application_id')
                    ->join('applications', 'applications.id', '=', 'payment_ar.application_id') // Join with applications
                    ->selectRaw('applications.application_country as country') // Use the county column from applications
                    ->selectRaw('COUNT(payment_ar.id) as number_of_payment') // Count the number of invoices
                    ->selectRaw('SUM(payment_ar.amount) as total_invoice_sum') // Calculate the sum of invoice totals
                    ->groupBy('applications.application_country') // Group by applications.county
                    ->where('type', 'ar')
                    ->get();
            }

            return response()->json(['data' => $data]);
        } elseif (request()->type == "byPaymentAmountChart") {
            $query = new PaymentARs();
            if (($user->membership == 'Adwiseri' || $user->membership == 'Adwiseri+' || $user->membership == 'Enterprise') && $user->user_type == 'Subscriber') {
                $query =   $query->where('subscriber_id', request()->subid)->where('type', 'ar');
            }
            $byPaymentMode = $query->where('type', 'ap')->select(
                DB::raw('
                        CASE
                            WHEN amount BETWEEN 1 AND 99 THEN "1-99"
                            WHEN amount BETWEEN 100 AND 249 THEN "100-249"
                            WHEN amount BETWEEN 250 AND 499 THEN "250-499"
                            WHEN amount BETWEEN 500 AND 999 THEN "500-999"
                            WHEN amount BETWEEN 1000 AND 2499 THEN "1000-2499"
                            WHEN amount BETWEEN 2500 AND 4999 THEN "2500-4999"
                            WHEN amount BETWEEN 5000 AND 9999 THEN "5000-9999"
                            WHEN amount >= 10000 THEN "10,000+"
                        END AS amount_range')
            )
                ->whereBetween('created_at', [$startDate, $endDate])
                ->selectRaw('COUNT(*) as number_of_invoices')
                ->groupBy('amount_range')
                ->where('type', 'ar')
                ->get();
            return response()->json(['data' =>  $byPaymentMode]);
        } elseif (request()->type == "byPaymentOutstandingAmout") {

            $query = PaymentARs::where('type', 'ar');
            if (($user->membership == 'Adwiseri' || $user->membership == 'Adwiseri+' || $user->membership == 'Enterprise') && $user->user_type == 'Subscriber') {
                $query =   $query->where('subscriber_id', request()->subid);
            }
            $paymentOutstanding = PaymentARs::selectRaw("
        CASE 
            WHEN total_outstanding BETWEEN 1 AND 99 THEN '1-99'
            WHEN total_outstanding BETWEEN 100 AND 249 THEN '100-249'
            WHEN total_outstanding BETWEEN 250 AND 499 THEN '250-499'
            WHEN total_outstanding BETWEEN 500 AND 999 THEN '500-999'
            WHEN total_outstanding BETWEEN 1000 AND 2499 THEN '1000-2499'
            WHEN total_outstanding BETWEEN 2500 AND 4999 THEN '2500-4999'
            WHEN total_outstanding BETWEEN 5000 AND 9999 THEN '5000-9999'
            WHEN total_outstanding >= 10000 THEN '10,000+'
        END AS amount_range,
        COUNT(*) as total_invoices
    ")
                ->fromSub(
                    PaymentARs::selectRaw('SUM(amount - paid_amount) as total_outstanding')
                        ->whereRaw('amount - paid_amount > 0') // Ensures only unpaid invoices are considered
                        ->groupBy('client_id', 'application_id', 'service_description'),
                    'outstanding'
                )
                ->groupBy('amount_range')
                ->orderByRaw("FIELD(amount_range, '1-99', '100-249', '250-499', '500-999', '1000-2499', '2500-4999', '5000-9999', '10,000+') ASC")
                
                ->get();

            return response()->json(['data' => $paymentOutstanding]);
            return response()->json(['data' =>  $paymentOutstanding]);
        } elseif (request()->type == "byPaymentApplicationTypeChart") {
            $startDate = $this->parseReportDate(request()->start);
            $endDate = $this->parseReportDate(request()->end, true);
            $country = (request()->input('country') == 'All') ? Countries::pluck('country_name') : Countries::where('id', [request()->input('country')])->pluck('country_name');
            if (!empty(request()->subid)) {
                $data = PaymentARs::whereBetween('payment_ar.created_at', [$startDate, $endDate])
                    ->where('payment_ar.subscriber_id', request()->subid)
                    ->whereNotNull('payment_ar.application_id')
                    ->join('applications', 'applications.id', '=', 'payment_ar.application_id') // Join with applications
                    ->selectRaw('applications.application_name as application_type') // Use the county column from applications
                    ->selectRaw('COUNT(payment_ar.id) as number_of_application') // Count the number of invoices
                    ->selectRaw('SUM(payment_ar.amount) as total_invoice_sum') // Calculate the sum of invoice totals
                    ->groupBy('applications.application_name') // Group by applications.county
                    ->where('type', 'ar')
                    ->get();
            } else {
                $data = PaymentARs::whereBetween('payment_ar.created_at', [$startDate, $endDate])
                    ->whereNotNull('payment_ar.application_id')
                    ->join('applications', 'applications.id', '=', 'payment_ar.application_id') // Join with applications
                    ->selectRaw('applications.application_name as application_type') // Select application name for grouping
                    ->selectRaw('COUNT(payment_ar.application_id) as number_of_application') // Count occurrences of application_id
                    ->selectRaw('SUM(payment_ar.amount) as total_invoice_sum') // Calculate the sum of invoice amounts
                    ->groupBy('applications.application_name') // Group by application name
                    ->where('type', 'ar')
                    ->get();
            }

            return response()->json(['data' => $data]);
        } elseif (request()->type == "byPaymentModeChartAP") {
            $query = new PaymentARs();
            if (($user->membership == 'Adwiseri' || $user->membership == 'Adwiseri+' || $user->membership == 'Enterprise') && $user->user_type == 'Subscriber') {
                $query =   $query->where('subscriber_id', request()->subid);
            }
            $byPaymentMode = $query->whereBetween('created_at', [$startDate, $endDate])
                ->where('type', 'ap');

            PaymentModeChartFilter::applyToQuery($byPaymentMode, 'payment_mode');

            $byPaymentMode = $byPaymentMode
                ->selectRaw('payment_mode, COUNT(*) as number_of_invoices')
                ->groupBy('payment_mode') // Group by payment mode
                ->get();
            return response()->json(['data' => $byPaymentMode]);
        } elseif (request()->type == "byPaymentYearChartAP") {
            $query = new PaymentARs();
            if (($user->membership == 'Adwiseri' || $user->membership == 'Adwiseri+' || $user->membership == 'Enterprise') && $user->user_type == 'Subscriber') {
                $query =   $query->where('subscriber_id', request()->subid);
            }
            $byPaymentMode  =$query
           // ✅ Ensure correct filtering
            ->select(
                DB::raw('YEAR(created_at) AS year'), // ✅ Specify users.created_at to avoid ambiguity
                DB::raw('COUNT(*) AS count') // ✅ Count based on users.id
            )
            ->groupBy(DB::raw('YEAR(created_at)')) // ✅ Group by extracted year
            ->orderBy('year', 'asc') // ✅ Sort by oldest first
            ->where('type', 'ap')
            ->get();
            return response()->json(['data' => $byPaymentMode]);
        }  elseif (request()->type == "byPaymentTimelineChartAP") {
            $currentYear = date('Y');
            $currentDate = now();
            $lastWeekStart = now()->subWeek()->startOfWeek();
            $lastWeekEnd = now()->subWeek()->endOfWeek();
            $lastMonthStart = now()->subMonth()->startOfMonth();
            $lastMonthEnd = now()->subMonth()->endOfMonth();
            $lastQuarterStart = now()->subQuarter()->startOfQuarter();
            $lastQuarterEnd = now()->subQuarter()->endOfQuarter();
            $lastYearStart = now()->subYear()->startOfYear();
            $lastYearEnd = now()->subYear()->endOfYear();

            // Keep every AP payment timeline bucket on the same type + subscriber scope.
            $query = PaymentARs::query()->where('type', 'ap');
            if ($user->user_type == 'Subscriber') {
                $query->where('subscriber_id', $user->id);
            }
            $query1 = clone $query;
            $inspectionStartDate = (clone $query1)->orderBy('created_at', 'asc')->first();

            $todayApplications = clone $query;
            $todayApplications = $todayApplications->whereDate('created_at', $currentDate)
                ->selectRaw("'Today' as type, COUNT(*) as count")
                ->get();

            $lastWeekApplications = clone $query;
            $lastWeekApplications = $lastWeekApplications->whereBetween('created_at', [$lastWeekStart, $lastWeekEnd])
                ->selectRaw("'Last Week' as type, COUNT(*) as count")
                ->get();

            $lastMonthApplications = clone $query;
            $lastMonthApplications = $lastMonthApplications->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])
                ->selectRaw("'Last Month' as type, COUNT(*) as count")
                ->get();

            $lastQuarterApplications = clone $query;
            $lastQuarterApplications = $lastQuarterApplications->whereBetween('created_at', [$lastQuarterStart, $lastQuarterEnd])
                ->selectRaw("'Last Quarter' as type, COUNT(*) as count")
                ->get();

            $yearlyApplications = clone $query;
            $yearlyApplications = $yearlyApplications->whereBetween('created_at', [$lastYearStart, $lastYearEnd])
                ->selectRaw("'Last Year' as type, COUNT(*) as count")
                ->get();

            $sinceInspectionData = (clone $query1)
                ->when($inspectionStartDate, function ($paymentQuery) use ($inspectionStartDate) {
                    $paymentQuery->whereDate('created_at', '>=', $inspectionStartDate->created_at);
                })
                ->selectRaw("'Since Inception' as type, COUNT(*) as count")
                ->get();

            $formattedData = collect()
                ->merge($todayApplications)
                ->merge($lastWeekApplications)
                ->merge($lastMonthApplications)
                ->merge($lastQuarterApplications)
                ->merge($yearlyApplications)
                ->merge($sinceInspectionData);

            $formattedData = $formattedData->map(function ($item) {
                return [
                    'type' => $item['type'] ?? null,
                    'count' => $item['count'],
                ];
            })->filter(function ($item) {
                return !empty($item['type']);
            })->values();

            return response()->json([
                'status' => 'success',
                'data' => $formattedData
            ]);
        } elseif (request()->type == "byPaymentVisaCountryChartAP") {
            $startDate = $this->parseReportDate(request()->start);
            $endDate = $this->parseReportDate(request()->end, true);
            $country = (request()->input('country') == 'All') ? Countries::pluck('country_name') : Countries::where('id', [request()->input('country')])->pluck('country_name');
            if (!empty(request()->subid)) {
                $data = PaymentARs::whereBetween('payment_ar.created_at', [$startDate, $endDate])
                    ->where('payment_ar.subscriber_id', request()->subid)
                    ->whereNotNull('payment_ar.application_id')
                    ->join('applications', 'applications.id', '=', 'payment_ar.application_id') // Join with applications
                    ->selectRaw('applications.application_country as country') // Use the county column from applications
                    ->selectRaw('COUNT(payment_ar.id) as number_of_payment') // Count the number of invoices
                    ->selectRaw('SUM(payment_ar.amount) as total_invoice_sum') // Calculate the sum of invoice totals
                    ->groupBy('applications.application_country') // Group by applications.county
                    ->where('type', 'ap')
                    ->get();
            } else {
                $data = PaymentARs::whereBetween('payment_ar.created_at', [$startDate, $endDate])
                    ->whereNotNull('payment_ar.application_id')
                    ->join('applications', 'applications.id', '=', 'payment_ar.application_id') // Join with applications
                    ->selectRaw('applications.application_country as country') // Use the county column from applications
                    ->selectRaw('COUNT(payment_ar.id) as number_of_payment') // Count the number of invoices
                    ->selectRaw('SUM(payment_ar.amount) as total_invoice_sum') // Calculate the sum of invoice totals
                    ->groupBy('applications.application_country') // Group by applications.county
                    ->where('type', 'ap')
                    ->get();
            }

            return response()->json(['data' => $data]);
        } elseif (request()->type == "byPaymentAmountChartAP") {
            $query = new PaymentARs();
            if (($user->membership == 'Adwiseri' || $user->membership == 'Adwiseri+' || $user->membership == 'Enterprise') && $user->user_type == 'Subscriber') {
                $query =   $query->where('subscriber_id', request()->subid)->where('type', 'ap');
            }
            $byPaymentMode = $query->where('type', 'ap')->select(
                DB::raw('
                        CASE
                            WHEN amount BETWEEN 1 AND 99 THEN "1-99"
                            WHEN amount BETWEEN 100 AND 249 THEN "100-249"
                            WHEN amount BETWEEN 250 AND 499 THEN "250-499"
                            WHEN amount BETWEEN 500 AND 999 THEN "500-999"
                            WHEN amount BETWEEN 1000 AND 2499 THEN "1000-2499"
                            WHEN amount BETWEEN 2500 AND 4999 THEN "2500-4999"
                            WHEN amount BETWEEN 5000 AND 9999 THEN "5000-9999"
                            WHEN amount >= 10000 THEN "10,000+"
                        END AS amount_range')
            )
                ->whereBetween('created_at', [$startDate, $endDate])
                ->selectRaw('COUNT(*) as number_of_invoices')
                ->where('type', 'ap')
                ->groupBy('amount_range')

                ->get();
            return response()->json(['data' =>  $byPaymentMode]);
        } elseif (request()->type == "byPaymentOutstandingAmoutAP") {

            $query = new PaymentARs();
            if (($user->membership == 'Adwiseri' || $user->membership == 'Adwiseri+' || $user->membership == 'Enterprise') && $user->user_type == 'Subscriber') {
                $query =   $query->where('subscriber_id', request()->subid)->where('type', 'ap');
            }
            $paymentOutstanding = PaymentARs::selectRaw("
        CASE 
            WHEN total_outstanding BETWEEN 1 AND 99 THEN '1-99'
            WHEN total_outstanding BETWEEN 100 AND 249 THEN '100-249'
            WHEN total_outstanding BETWEEN 250 AND 499 THEN '250-499'
            WHEN total_outstanding BETWEEN 500 AND 999 THEN '500-999'
            WHEN total_outstanding BETWEEN 1000 AND 2499 THEN '1000-2499'
            WHEN total_outstanding BETWEEN 2500 AND 4999 THEN '2500-4999'
            WHEN total_outstanding BETWEEN 5000 AND 9999 THEN '5000-9999'
            WHEN total_outstanding >= 10000 THEN '10,000+'
        END AS amount_range,
        COUNT(*) as total_invoices
    ")
                ->fromSub(
                    PaymentARs::selectRaw('SUM(amount - paid_amount) as total_outstanding')
                        ->whereRaw('amount - paid_amount > 0') // Ensures only unpaid invoices are considered
                        ->groupBy('client_id', 'application_id', 'service_description'),
                    'outstanding'
                )
                ->groupBy('amount_range')
                ->orderByRaw("FIELD(amount_range, '1-99', '100-249', '250-499', '500-999', '1000-2499', '2500-4999', '5000-9999', '10,000+') ASC")
                ->where('type', 'ap')
                ->get();

            return response()->json(['data' => $paymentOutstanding]);
            return response()->json(['data' =>  $paymentOutstanding]);
        } elseif (request()->type == "byPaymentApplicationTypeChartAP") {
            $startDate = $this->parseReportDate(request()->start);
            $endDate = $this->parseReportDate(request()->end, true);
            $country = (request()->input('country') == 'All') ? Countries::pluck('country_name') : Countries::where('id', [request()->input('country')])->pluck('country_name');
            if (!empty(request()->subid)) {
                $data = PaymentARs::whereBetween('payment_ar.created_at', [$startDate, $endDate])
                    ->where('payment_ar.subscriber_id', request()->subid)
                    ->whereNotNull('payment_ar.application_id')
                    ->join('applications', 'applications.id', '=', 'payment_ar.application_id') // Join with applications
                    ->selectRaw('applications.application_name as application_type') // Use the county column from applications
                    ->selectRaw('COUNT(payment_ar.id) as number_of_application') // Count the number of invoices
                    ->selectRaw('SUM(payment_ar.amount) as total_invoice_sum') // Calculate the sum of invoice totals
                    ->groupBy('applications.application_name') // Group by applications.county
                    ->where('type', 'ap')
                    ->get();
            } else {
                $data = PaymentARs::whereBetween('payment_ar.created_at', [$startDate, $endDate])
                    ->whereNotNull('payment_ar.application_id')
                    ->join('applications', 'applications.id', '=', 'payment_ar.application_id') // Join with applications
                    ->selectRaw('applications.application_name as application_type') // Select application name for grouping
                    ->selectRaw('COUNT(payment_ar.application_id) as number_of_application') // Count occurrences of application_id
                    ->selectRaw('SUM(payment_ar.amount) as total_invoice_sum') // Calculate the sum of invoice amounts
                    ->groupBy('applications.application_name') // Group by application name
                    ->where('type', 'ap')
                    ->get();
            }

            return response()->json(['data' => $data]);
        } elseif (request()->type == "byNoMessagesSentByUser") {
            $query = new Internal_communications();
            if (($user->membership == 'Adwiseri' || $user->membership == 'Adwiseri+' || $user->membership == 'Enterprise') && $user->user_type == 'Subscriber') {
                $query =   $query->where('subscriber_id', request()->subid);
            }
            $byCommunicationNoOfMessage = $query->whereBetween('internal_communications.created_at', [$startDate, $endDate])
                ->join('users', 'internal_communications.send_by', '=', 'users.id')
                ->select('users.name', DB::raw('COUNT(*) as total_messages'))
                ->groupBy('users.name')->get();
            return response()->json(['data' => $byCommunicationNoOfMessage]);
        } elseif (request()->type == "byUserNoCommunicationMeetingNotes") {
            $query = new Client_discussions();
            if (($user->membership == 'Adwiseri' || $user->membership == 'Adwiseri+' || $user->membership == 'Enterprise') && $user->user_type == 'Subscriber') {
                $query =   $query->where('subscriber_id', request()->subid);
            }
            $byNoCommunicationMeetingNotes =
                $query->select('user_name', DB::raw('count(discussion) as discussion'))->groupBy('user_name')->get();
            return response()->json(['data' => $byNoCommunicationMeetingNotes]);
        } elseif (request()->type == "byCommunicationTypeChart") {

            if (!empty(request()->subid)) {

                $data = Internal_communications::whereBetween('internal_communications.created_at', [$startDate, $endDate])
                    ->where('subscriber_id', request()->subid)
                    ->join('users', 'internal_communications.send_by', '=', 'users.id')
                    ->select(
                        DB::raw('CASE
                            WHEN JSON_LENGTH(internal_communications.send_to) = 1 THEN "One-to-One"
                            ELSE "One-to-Many"
                         END as communication_type'),
                        DB::raw('COUNT(*) as total_messages')
                    )
                    ->groupBy('communication_type')
                    ->get();
            } else {
                $data = Internal_communications::whereBetween('internal_communications.created_at', [$startDate, $endDate])
                    ->join('users', 'internal_communications.send_by', '=', 'users.id')
                    ->select(
                        DB::raw('CASE
                                WHEN JSON_LENGTH(internal_communications.send_to) = 1 THEN "One-to-One"
                                ELSE "One-to-Many"
                             END as communication_type'),
                        DB::raw('COUNT(*) as total_messages')
                    )
                    ->groupBy('communication_type')
                    ->get();
            }
            return response()->json(['data' => $data]);
        } elseif (request()->type == "byCommunicationMeetingNoteTypeChart") {

            $query = new Client_discussions();
            if (($user->membership == 'Adwiseri' || $user->membership == 'Adwiseri+' || $user->membership == 'Enterprise') && $user->user_type == 'Subscriber') {
                $query =   $query->where('subscriber_id', request()->subid);
            }
            $byCommunicationMeetingNoteType = $query->whereBetween('created_at', [$startDate, $endDate])
                ->select(
                    'communication_type', // Select the communication type
                    DB::raw('COUNT(*) as total_count') // Count total for each communication type
                )
                ->groupBy('communication_type') // Group by communication type
                ->get();
            return response()->json(['data' =>  $byCommunicationMeetingNoteType]);
        } elseif (request()->type == "byCommunicationMessagesByYear") {
          
            $query =   new Client_discussions();
            $query1 = clone $query;

            if (($user->membership == 'Adwiseri' || $user->membership == 'Adwiseri+' || $user->membership == 'Enterprise') && $user->user_type == 'Subscriber') {
                $query =  $query = $query->where('subscriber_id', $user->id);
            }

            $byUserTimeline = $query
                // ✅ Ensure correct filtering
                ->select(
                    DB::raw('YEAR(created_at) AS year'), // ✅ Specify users.created_at to avoid ambiguity
                    DB::raw('COUNT(*) AS count') // ✅ Count based on users.id
                )
                ->groupBy(DB::raw('YEAR(created_at)')) // ✅ Group by extracted year
                ->orderBy('year', 'asc') // ✅ Sort by oldest first
                ->get();

            return response()->json(['data' => $byUserTimeline]);
        } elseif (request()->type == "byCommunicationMessagesByTimeline(Duration)") {
            $query = new Client_discussions();
            $query1 = clone $query;

            if (($user->membership == 'Adwiseri' || $user->membership == 'Adwiseri+' || $user->membership == 'Enterprise')
                && $user->user_type == 'Subscriber') {
                $query = $query->where('subscriber_id', $user->id);
                $query1 = $query1->where('subscriber_id', $user->id);
            }

            $formattedData = $this->buildTimelineDurationData($query, $query1);

            return response()->json([
                'status' => 'success',
                'data' => $formattedData
            ]);

            
        } elseif (request()->type == "byCommunicationMeetingNotesByYear") {
          
            $query =   new Internal_communications();
            $query1 = clone $query;

            if (($user->membership == 'Adwiseri' || $user->membership == 'Adwiseri+' || $user->membership == 'Enterprise') && $user->user_type == 'Subscriber') {
                $query =  $query = $query->where('subscriber_id', $user->id);
            }

            $byUserTimeline = $query
                // ✅ Ensure correct filtering
                ->select(
                    DB::raw('YEAR(created_at) AS year'), // ✅ Specify users.created_at to avoid ambiguity
                    DB::raw('COUNT(*) AS count') // ✅ Count based on users.id
                )
                ->groupBy(DB::raw('YEAR(created_at)')) // ✅ Group by extracted year
                ->orderBy('year', 'asc') // ✅ Sort by oldest first
                ->get();

            return response()->json(['data' => $byUserTimeline]);
        } elseif (request()->type == "byNoOfMeetingNotesByYear") {
          
            $query = new Client_discussions();
            if (($user->membership == 'Adwiseri' || $user->membership == 'Adwiseri+' || $user->membership == 'Enterprise') && $user->user_type == 'Subscriber') {
                $query =   $query->where('subscriber_id', request()->subid);
            }
            $byCommunicationMeetingNoteType = $query
                 ->select(
                    DB::raw('YEAR(communication_date) AS year'), // ✅ Specify users.communication_date to avoid ambiguity
                    DB::raw('COUNT(*) AS count') // ✅ Count based on users.id
                )
                ->groupBy(DB::raw('YEAR(communication_date)')) // ✅ Group by extracted year
                ->orderBy('year', 'asc') // ✅ Sort by oldest first
                ->get();
            return response()->json(['data' =>  $byCommunicationMeetingNoteType]);
        } elseif (request()->type == "byCommunicationMeetingNotesByTimeline(Duration)") {
            $query = new Internal_communications();
            $query1 = clone $query;

            if (($user->membership == 'Adwiseri' || $user->membership == 'Adwiseri+' || $user->membership == 'Enterprise')
                && $user->user_type == 'Subscriber') {
                $query = $query->where('subscriber_id', $user->id);
                $query1 = $query1->where('subscriber_id', $user->id);
            }

            $formattedData = $this->buildTimelineDurationData($query, $query1);

            return response()->json([
                'status' => 'success',
                'data' => $formattedData
            ]);
        } 
        
        elseif (request()->type == "byReferralSubscribersCategory") {

            $query = Referrals::join('users', 'referrals.userid', '=', 'users.id')
                // Filter by user creation date
                ->where('users.user_type', 'Subscriber') // Ensure user_type is 'Subscriber'
                ->whereNotNull('users.referral_code') // Ensure referral_code exists
                ->whereNull('debit_amount') // Ensure debit_amount is null
                ->where('type', 'Referral Commission');
            // Order by referral creation date
            //    / Select all columns from referrals

            if (($user->membership == 'Adwiseri' || $user->membership == 'Adwiseri+' || $user->membership == 'Enterprise') && $user->user_type == 'Subscriber') {
                $query = $query->where('users.referral_code', $user->referral);
            }
            $byNoOfSubscribersReferred = $query
                ->whereBetween('users.created_at', [$startDate, $endDate])
                ->select(
                    DB::raw('MAX(users.created_at) as latest_created_at'), // ✅ Get the latest created_at
                    'users.category',
                    DB::raw('COUNT(users.id) as count')
                )
                ->groupBy('users.category')
                ->orderBy('latest_created_at', 'desc') // ✅ Sort by the latest user created
                ->get();

            return response()->json(['data' => $byNoOfSubscribersReferred]);
        } elseif (request()->type == "byReferralSubscriberSubCategory") {
            $query = Referrals::join('users', 'referrals.userid', '=', 'users.id')
                // Filter by user creation date
                ->where('users.user_type', 'Subscriber') // Ensure user_type is 'Subscriber'
                ->whereNotNull('users.referral_code') // Ensure referral_code exists
                ->whereNull('debit_amount') // Ensure debit_amount is null
                ->where('type', 'Referral Commission');
            // Order by referral creation date
            //    / Select all columns from referrals

            if (($user->membership == 'Adwiseri' || $user->membership == 'Adwiseri+' || $user->membership == 'Enterprise') && $user->user_type == 'Subscriber') {
                $query = $query->where('users.referral_code', $user->referral);
            }
            $byReferralSubscriberSubCategory = $query
                ->whereBetween('users.created_at', [$startDate, $endDate])
                ->select(
                    DB::raw('MAX(users.created_at) as latest_created_at'), // ✅ Get the latest created_at
                    'users.sub_category',
                    DB::raw('COUNT(users.id) as count')
                )
                ->groupBy('users.sub_category')
                ->orderBy('latest_created_at', 'desc') // ✅ Sort by the latest user created
                ->get();

            return response()->json(['data' => $byReferralSubscriberSubCategory]);
        } elseif (request()->type == "byReferralSubscribedPlan") {
            $query = Referrals::join('users', 'referrals.userid', '=', 'users.id')
                // Filter by user creation date
                ->where('users.user_type', 'Subscriber') // Ensure user_type is 'Subscriber'
                ->whereNotNull('users.referral_code') // Ensure referral_code exists
                ->whereNull('debit_amount') // Ensure debit_amount is null
                ->where('type', 'Referral Commission');
            // Order by referral creation date
            //    / Select all columns from referrals

            if (($user->membership == 'Adwiseri' || $user->membership == 'Adwiseri+' || $user->membership == 'Enterprise') && $user->user_type == 'Subscriber') {
                $query = $query->where('users.referral_code', $user->referral);
            }
            $byReferralSubscribedPlan = $query
                ->whereBetween('users.created_at', [$startDate, $endDate])
                ->select(
                    DB::raw('MAX(users.created_at) as latest_created_at'), // ✅ Get the latest created_at
                    'users.membership',
                    DB::raw('COUNT(users.id) as count')
                )
                ->groupBy('users.membership')
                ->orderBy('latest_created_at', 'desc') // ✅ Sort by the latest user created
                ->get();
            return response()->json(['data' => $byReferralSubscribedPlan]);
        } elseif (request()->type == "byReferralYear") {

            $query = Referrals::join('users', 'referrals.userid', '=', 'users.id')
                // Filter by user creation date
                ->where('users.user_type', 'Subscriber') // Ensure user_type is 'Subscriber'
                ->whereNotNull('users.referral_code') // Ensure referral_code exists
                ->whereNull('debit_amount') // Ensure debit_amount is null
                ->where('type', 'Referral Commission');
            // Order by referral creation date
            //    / Select all columns from referrals

            if (($user->membership == 'Adwiseri' || $user->membership == 'Adwiseri+' || $user->membership == 'Enterprise') && $user->user_type == 'Subscriber') {
                $query = $query->where('users.referral_code', $user->referral);
            }

            $byUserTimeline = $query
                ->whereBetween('users.created_at', [$startDate, $endDate]) // ✅ Ensure correct filtering
                ->select(
                    DB::raw('YEAR(users.created_at) AS year'), // ✅ Specify users.created_at to avoid ambiguity
                    DB::raw('COUNT(users.id) AS count') // ✅ Count based on users.id
                )
                ->groupBy(DB::raw('YEAR(users.created_at)')) // ✅ Group by extracted year
                ->orderBy('year', 'asc') // ✅ Sort by oldest first
                ->get();

            return response()->json(['data' => $byUserTimeline]);
        } elseif (request()->type == "byReferralTimelineDuration") {
            $currentYear = date('Y');
            $currentDate = now();
            $lastWeekStart = now()->subWeek()->startOfWeek();
            $lastWeekEnd = now()->subWeek()->endOfWeek();
            $lastMonthStart = now()->subMonth()->startOfMonth();
            $lastMonthEnd = now()->subMonth()->endOfMonth();
            $lastQuarterStart = now()->subQuarter()->startOfQuarter();
            $lastQuarterEnd = now()->subQuarter()->endOfQuarter();
            $lastYearStart = now()->subYear()->startOfYear();
            $lastYearEnd = now()->subYear()->endOfYear();
            // Get Inspection Start Date (modify this based on where the date is stored)
            
            // Base Query
            $query =   Referrals::join('users', 'referrals.userid', '=', 'users.id')
            // Filter by user creation date
            ->where('users.user_type', 'Subscriber') // Ensure user_type is 'Subscriber'
            ->whereNotNull('users.referral_code') // Ensure referral_code exists
            ->whereNull('debit_amount') // Ensure debit_amount is null
            ->where('type', 'Referral Commission');
            $query1 = clone $query;
            
            if (($user->membership == 'Adwiseri' || $user->membership == 'Adwiseri+' || $user->membership == 'Enterprise') 
                && $user->user_type == 'Subscriber') {
                $query = $query->where('users.referral_code', $user->referral)->whereYear('users.created_at', '=', $currentYear);
                // $query1 = $query1->where('users.referral_code', $user->referral);;
                $inspectionStartDate = $query1->where('users.referral_code', $user->referral)->orderBy('users.created_at','asc')->first();
            }else{
                $inspectionStartDate = $query1->orderBy('users.created_at','asc')->first();
            }
            
            // 🔹 Today's Applications
            $todayApplications = clone $query;
            $todayApplications = $todayApplications->whereDate('users.created_at', $currentDate)
                ->selectRaw("'Today' as type, COUNT(*) as count")
                ->get();
            
            // 🔹 Last Week's Applications
            $lastWeekApplications = clone $query;
            $lastWeekApplications = $lastWeekApplications->whereBetween('users.created_at', [$lastWeekStart, $lastWeekEnd])
                ->selectRaw("'Last Week' as type, COUNT(*) as count")
                ->get();
            
            // 🔹 Last Month's Applications
            $lastMonthApplications = clone $query;
            $lastMonthApplications = $lastMonthApplications->whereBetween('users.created_at', [$lastMonthStart, $lastMonthEnd])
                ->selectRaw("'Last Month' as type, COUNT(*) as count")
                ->get();
            
            // 🔹 Last Quarter's Applications
            $lastQuarterApplications = clone $query;
            $lastQuarterApplications = $lastQuarterApplications->whereBetween('users.created_at', [$lastQuarterStart, $lastQuarterEnd])
                ->selectRaw("'Last Quarter' as type, COUNT(*) as count")
                ->get();
            
            $yearlyApplications = clone $query;
            $yearlyApplications = $yearlyApplications->whereBetween('users.created_at', [$lastYearStart, $lastYearEnd])
                ->selectRaw("'Last Year' as type, COUNT(*) as count")
                ->get();
                
            // 🔹 Since Inception Data (Replacing "Past Year Data")
            $sinceInspectionData = clone $query1;
            $sinceInspectionData = $sinceInspectionData
                ->whereDate('users.created_at', '>=', $inspectionStartDate->created_at) 
                ->selectRaw("'Since Inception' as type, COUNT(*) as count")
                ->get();
            
            // 🔹 Weekly Applications
            $weeklyApplications = clone $query;
            $weeklyApplications = $weeklyApplications->selectRaw("
                WEEK(users.created_at) as week_num, 
                YEAR(users.created_at) as year_num, 
                COUNT(*) as count
            ")
                ->groupBy('year_num', 'week_num')
                ->orderBy('year_num', 'asc')
                ->orderBy('week_num', 'asc')
                ->get();
            
            // 🔹 Quarterly Applications
            $quarterlyApplications = clone $query;
            $quarterlyApplications = $quarterlyApplications->selectRaw("
                YEAR(users.created_at) as year, 
                QUARTER(users.created_at) as quarter, 
                COUNT(*) as count
            ")
                ->groupBy('year', 'quarter')
                ->orderBy('year', 'asc')
                ->orderBy('quarter', 'asc')
                ->get();
            
            // 🔹 Monthly Applications
            $monthlyApplications = clone $query;
            $monthlyApplications = $monthlyApplications->selectRaw("
                YEAR(users.created_at) as year, 
                MONTH(users.created_at) as month, 
                COUNT(*) as count
            ")
                ->groupBy('year', 'month')
                ->orderBy('year', 'asc')
                ->orderBy('month', 'asc')
                ->get();
            
            // 🔹 Merge All Data
            $formattedData = collect()
                ->merge($todayApplications)
                ->merge($lastWeekApplications)
                ->merge($lastMonthApplications)
                ->merge($lastQuarterApplications)
                ->merge($yearlyApplications)
                // ->merge($quarterlyApplications)
                // ->merge($monthlyApplications)
                ->merge($sinceInspectionData); // ✅ Replacing past year with "Since Inception"
            
            // 🔹 Format Data for Output
            $formattedData = $formattedData->map(function ($item) {
                return [
                    'type' => $item['type'],
                    'count' => $item['count'],
                ];
            });
            
            return response()->json([
                'status' => 'success',
                'data' => $formattedData
            ]);
        } elseif (request()->type == "byWalletTransactionType") {
            $query = Referrals::query()
                ->walletTableVisible()
                ->whereNotNull('type')
                ->where('type', '!=', '')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->select(
                    'type',
                    DB::raw('COUNT(*) as transaction_count')
                );

            if ($user->user_type == 'Subscriber') {
                $query->where('userid', $user->id);
            }

            $transactions = $query->groupBy('type')->orderByDesc('transaction_count')->get();

            $formattedTransactions = $transactions->map(function ($row) {
                return [
                    'transaction_type' => app(OfferBenefitService::class)->offerTypeLabel((string) $row->type),
                    'transaction_count' => (int) $row->transaction_count,
                ];
            })->filter(function ($row) {
                return !empty($row['transaction_type']) && $row['transaction_count'] > 0;
            })->values();

            return response()->json([
                'status' => 'success',
                'data' => $formattedTransactions
            ]);
        } elseif (request()->type == "byWalletYear") {
            $query = Referrals::query()->walletTableVisible();
            if ($user->user_type == 'Subscriber') {
                $query->where('userid', $user->id);
            }

            $byWalletYear = $query
                ->select(
                    DB::raw('YEAR(created_at) AS year'),
                    DB::raw('COUNT(id) AS count')
                )
                ->groupBy(DB::raw('YEAR(created_at)'))
                ->orderBy('year', 'asc')
                ->get();

            return response()->json(['data' => $byWalletYear]);
        } elseif (request()->type == "byWalletTimeline(Duration)") {
            $currentDate = now();
            $lastWeekStart = now()->subWeek()->startOfWeek();
            $lastWeekEnd = now()->subWeek()->endOfWeek();
            $lastMonthStart = now()->subMonth()->startOfMonth();
            $lastMonthEnd = now()->subMonth()->endOfMonth();
            $lastQuarterStart = now()->subQuarter()->startOfQuarter();
            $lastQuarterEnd = now()->subQuarter()->endOfQuarter();
            $lastYearStart = now()->subYear()->startOfYear();
            $lastYearEnd = now()->subYear()->endOfYear();

            // Count only wallet-ledger referral rows for this subscriber.
            $query = Referrals::query()->walletTableVisible();
            if ($user->user_type == 'Subscriber') {
                $query->where('userid', $user->id);
            }
            $query1 = clone $query;
            $inspectionStartDate = (clone $query1)->orderBy('created_at', 'asc')->first();

            $todayApplications = (clone $query)->whereDate('created_at', $currentDate)
                ->selectRaw("'Today' as type, COUNT(*) as count")
                ->get();

            $lastWeekApplications = (clone $query)->whereBetween('created_at', [$lastWeekStart, $lastWeekEnd])
                ->selectRaw("'Last Week' as type, COUNT(*) as count")
                ->get();

            $lastMonthApplications = (clone $query)->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])
                ->selectRaw("'Last Month' as type, COUNT(*) as count")
                ->get();

            $lastQuarterApplications = (clone $query)->whereBetween('created_at', [$lastQuarterStart, $lastQuarterEnd])
                ->selectRaw("'Last Quarter' as type, COUNT(*) as count")
                ->get();

            $yearlyApplications = (clone $query)->whereBetween('created_at', [$lastYearStart, $lastYearEnd])
                ->selectRaw("'Last Year' as type, COUNT(*) as count")
                ->get();

            $sinceInspectionData = (clone $query1)
                ->when($inspectionStartDate, function ($walletQuery) use ($inspectionStartDate) {
                    $walletQuery->whereDate('created_at', '>=', $inspectionStartDate->created_at);
                })
                ->selectRaw("'Since Inception' as type, COUNT(*) as count")
                ->get();

            $formattedData = collect()
                ->merge($todayApplications)
                ->merge($lastWeekApplications)
                ->merge($lastMonthApplications)
                ->merge($lastQuarterApplications)
                ->merge($yearlyApplications)
                ->merge($sinceInspectionData);

            $formattedData = $formattedData->map(function ($item) {
                return [
                    'type' => $item['type'] ?? null,
                    'count' => $item['count'],
                ];
            })->filter(function ($item) {
                return !empty($item['type']);
            })->values();

            return response()->json([
                'status' => 'success',
                'data' => $formattedData
            ]);
        } elseif (request()->type == "byTicketType") {
            $query = new Tickets();
            if (($user->membership == 'Adwiseri' || $user->membership == 'Adwiseri+' || $user->membership == 'Enterprise') && $user->user_type == 'Subscriber') {
                $query =  $query->where('subscriber_id', request()->subid);
            }
            $cd = $query->whereIn('support', ['Billing', 'Sales', 'Support'])
                ->select('support')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->selectRaw('COUNT(*) as number_of_tickets')
                ->groupBy('support')
                ->get();


            $data = $cd->map(function ($item) {
                return [
                    'support' => $item->support,
                    'number_of_tickets' => $item->number_of_tickets,
                ];
            });

            return response()->json(['data' => $data]);
        } elseif (request()->type == "ByUser" || request()->type == "byTicketUser") {
            // Support Tickets → By User: tickets raised, grouped by the user who created them.
            $query = Tickets::query()
                ->whereNotNull('user_id')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->select(
                    'user_id',
                    DB::raw('COUNT(*) as number_of_tickets')
                )
                ->groupBy('user_id')
                ->orderByDesc('number_of_tickets');

            if ($user->user_type == 'Subscriber') {
                $query->where('subscriber_id', $user->id);
            } elseif (!empty(request()->subid)) {
                $query->where('subscriber_id', request()->subid);
            }

            $data = $query->get()
                ->map(function ($row) {
                    $ticketUser = User::find($row->user_id);
                    $name = $ticketUser ? trim((string) $ticketUser->name) : '';

                    return [
                        'user_id' => $row->user_id,
                        'username' => $name !== '' ? $name : 'Unknown',
                        'number_of_tickets' => (int) $row->number_of_tickets,
                    ];
                })
                ->filter(function ($row) {
                    return $row['number_of_tickets'] > 0;
                })
                ->values();

            return response()->json(['data' => $data]);
        } elseif (request()->type == "byTicketYear") {
            $query = new Tickets();
            if (($user->membership == 'Adwiseri' || $user->membership == 'Adwiseri+' || $user->membership == 'Enterprise') && $user->user_type == 'Subscriber') {
                $query = $query->where('subscriber_id', $user->id);
            }

            $byUserTimeline = $query
                 // ✅ Ensure correct filtering
                ->select(
                    DB::raw('YEAR(created_at) AS year'), // ✅ Specify users.created_at to avoid ambiguity
                    DB::raw('COUNT(id) AS count') // ✅ Count based on users.id
                )
                ->groupBy(DB::raw('YEAR(created_at)')) // ✅ Group by extracted year
                ->orderBy('year', 'asc') // ✅ Sort by oldest first
                ->get();

            return response()->json(['data' => $byUserTimeline]);
        } elseif (request()->type == "byTicketTimeline(Duration)") {
            $currentYear = date('Y');
            $currentDate = now();
            $lastWeekStart = now()->subWeek()->startOfWeek();
            $lastWeekEnd = now()->subWeek()->endOfWeek();
            $lastMonthStart = now()->subMonth()->startOfMonth();
            $lastMonthEnd = now()->subMonth()->endOfMonth();
            $lastQuarterStart = now()->subQuarter()->startOfQuarter();
            $lastQuarterEnd = now()->subQuarter()->endOfQuarter();
            
            // Get Inspection Start Date (modify this based on where the date is stored)
            
            // Base Query
            $query = new Tickets();
            $query1 = clone $query;
            
            if (($user->membership == 'Adwiseri' || $user->membership == 'Adwiseri+' || $user->membership == 'Enterprise') 
                && $user->user_type == 'Subscriber') {
                $query = $query->where('subscriber_id', request()->subid)->whereYear('created_at', '=', $currentYear);
                $query1 = $query1->where('subscriber_id', request()->subid);
                $inspectionStartDate = $query1->where('subscriber_id', request()->subid)->orderBy('created_at','asc')->first();
            }else{
                $inspectionStartDate = $query1->orderBy('created_at','asc')->first();
            }
            
            // 🔹 Today's Applications
            $todayApplications = clone $query;
            $todayApplications = $todayApplications->whereDate('created_at', $currentDate)
                ->selectRaw("'Today' as type, COUNT(*) as count")
                ->get();
            
            // 🔹 Last Week's Applications
            $lastWeekApplications = clone $query;
            $lastWeekApplications = $lastWeekApplications->whereBetween('created_at', [$lastWeekStart, $lastWeekEnd])
                ->selectRaw("'Last Week' as type, COUNT(*) as count")
                ->get();
            
            // 🔹 Last Month's Applications
            $lastMonthApplications = clone $query;
            $lastMonthApplications = $lastMonthApplications->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])
                ->selectRaw("'Last Month' as type, COUNT(*) as count")
                ->get();
            
            // 🔹 Last Quarter's Applications
            $lastQuarterApplications = clone $query;
            $lastQuarterApplications = $lastQuarterApplications->whereBetween('created_at', [$lastQuarterStart, $lastQuarterEnd])
                ->selectRaw("'Last Quarter' as type, COUNT(*) as count")
                ->get();
            
            // 🔹 Since Inception Data (Replacing "Past Year Data")
            $sinceInspectionData = clone $query1;
            $sinceInspectionData = $sinceInspectionData
                ->whereDate('created_at', '>=', $inspectionStartDate->created_at) 
                ->selectRaw("'Since Inception' as type, COUNT(*) as count")
                ->get();
            
            // 🔹 Weekly Applications
            $weeklyApplications = clone $query;
            $weeklyApplications = $weeklyApplications->selectRaw("
                WEEK(created_at) as week_num, 
                YEAR(created_at) as year_num, 
                COUNT(*) as count
            ")
                ->groupBy('year_num', 'week_num')
                ->orderBy('year_num', 'asc')
                ->orderBy('week_num', 'asc')
                ->get();
            
            // 🔹 Quarterly Applications
            $quarterlyApplications = clone $query;
            $quarterlyApplications = $quarterlyApplications->selectRaw("
                YEAR(created_at) as year, 
                QUARTER(created_at) as quarter, 
                COUNT(*) as count
            ")
                ->groupBy('year', 'quarter')
                ->orderBy('year', 'asc')
                ->orderBy('quarter', 'asc')
                ->get();
            
            // 🔹 Monthly Applications
            $monthlyApplications = clone $query;
            $monthlyApplications = $monthlyApplications->selectRaw("
                YEAR(created_at) as year, 
                MONTH(created_at) as month, 
                COUNT(*) as count
            ")
                ->groupBy('year', 'month')
                ->orderBy('year', 'asc')
                ->orderBy('month', 'asc')
                ->get();
            
            // 🔹 Merge All Data (named timeline buckets only — weekly/monthly/quarterly rows have no `type` label)
            $formattedData = collect()
                ->merge($todayApplications)
                ->merge($lastWeekApplications)
                ->merge($lastMonthApplications)
                ->merge($lastQuarterApplications)
                // ->merge($weeklyApplications)
                // ->merge($quarterlyApplications)
                // ->merge($monthlyApplications)
                ->merge($sinceInspectionData); // ✅ Replacing past year with "Since Inception"
            
            // 🔹 Format Data for Output
            $formattedData = $formattedData->map(function ($item) {
                return [
                    'type' => $item['type'] ?? null,
                    'count' => $item['count'],
                ];
            })->filter(function ($item) {
                return !empty($item['type']);
            })->values();
            
            return response()->json([
                'status' => 'success',
                'data' => $formattedData
            ]);
            
        } elseif (request()->type == "bySupportStaffChart") {
            $startDate = $this->parseReportDate(request()->start);
            $endDate = $this->parseReportDate(request()->end, true);

            if (!empty(request()->subid)) {
                $cd = Tickets::select(
                    'user_id',
                    DB::raw('COUNT(id) AS no_of_tickets_solved'),
                    DB::raw('AVG(TIMESTAMPDIFF(SECOND, `created_at`, `updated_at`)) / 3600 AS avg_time_taken_hours')
                )->where('subscriber_id', request()->subid)
                    ->groupBy('user_id')
                    ->get();
            } else {
                $cd = Tickets::select(
                    'user_id',
                    DB::raw('COUNT(id) AS no_of_tickets_solved'),
                    DB::raw('AVG(TIMESTAMPDIFF(SECOND, `created_at`, `updated_at`)) / 3600 AS avg_time_taken_hours')
                )
                    ->groupBy('user_id')
                    ->get();
            }


            $data = $cd->map(function ($row) {
                $user = User::find($row->user_id);
                return [
                    'user_id' => $row->user_id,
                    'no_of_tickets_solved' => $row->no_of_tickets_solved,
                    'avg_time_taken_hours' => $row->avg_time_taken_hours,
                    'username' => !empty($user) ? $user->name : '',
                ];
            });
            return response()->json(['data' => $data]);
        } elseif (request()->type == "bySupportStaffNameChart") {
            $startDate = $this->parseReportDate(request()->start);
            $endDate = $this->parseReportDate(request()->end, true);

            $searchUsername = request()->username; // Get the search username from the request

            if (!empty(request()->subid)) {
                $cd = Tickets::select(
                    'user_id',
                    DB::raw('COUNT(id) AS no_of_tickets_solved'),
                    DB::raw('AVG(TIMESTAMPDIFF(SECOND, `created_at`, `updated_at`)) / 3600 AS avg_time_taken_hours')
                )->where('subscriber_id', request()->subid)
                    ->groupBy('user_id')
                    ->get();
            } else {
                $data = Tickets::select(
                    'tickets.support', // Group by support type
                    DB::raw('COUNT(*) AS total_tickets'),
                    DB::raw('SUM(CASE WHEN `tickets`.`status` = "open" THEN 1 ELSE 0 END) AS open_tickets'),
                    DB::raw('SUM(CASE WHEN `tickets`.`status` = "closed" THEN 1 ELSE 0 END) AS closed_tickets')
                )
                    ->join('users', 'tickets.user_id', '=', 'users.id')
                    ->whereBetween('tickets.created_at', [$startDate, $endDate]) // Filter by date range
                    ->whereIn('tickets.support', ['Billing', 'Sales', 'Support']) // Filter by support type
                    ->when(!empty($searchUsername), function ($query) use ($searchUsername) {
                        // Filter by username if provided
                        $query->where('users.name', 'like', '%' . $searchUsername . '%');
                    })
                    ->groupBy('tickets.support') // Group by support type
                    ->get();
            }

            return response()->json(['data' => $data]);
        } elseif (request()->type == "byDemoRequestStatusChart") {
            $startDate = $this->parseReportDate(request()->start);
            $endDate = $this->parseReportDate(request()->end, true);

            $searchUsername = request()->username; // Get the search username from the request

            if (!empty(request()->subid)) {
                $cd = Tickets::select(
                    'user_id',
                    DB::raw('COUNT(id) AS no_of_tickets_solved'),
                    DB::raw('AVG(TIMESTAMPDIFF(SECOND, `created_at`, `updated_at`)) / 3600 AS avg_time_taken_hours')
                )->where('subscriber_id', request()->subid)
                    ->groupBy('user_id')
                    ->get();
            } else {
                $data = DB::table('demo_requests')
                    ->select('status', DB::raw('COUNT(*) as status_count'))
                    ->groupBy('status')
                    ->get();
            }

            return response()->json(['data' => $data]);
        } elseif (request()->type == "byCounrtyDemoRequestChart") {
            $startDate = $this->parseReportDate(request()->start);
            $endDate = $this->parseReportDate(request()->end, true);
            $country = (request()->input('country') == 'All') ? Countries::pluck('country_name') : Countries::where('id', [request()->input('country')])->pluck('country_name');

            if (!empty(request()->subid)) {
                $cd = Tickets::select(
                    'user_id',
                    DB::raw('COUNT(id) AS no_of_tickets_solved'),
                    DB::raw('AVG(TIMESTAMPDIFF(SECOND, `created_at`, `updated_at`)) / 3600 AS avg_time_taken_hours')
                )->where('subscriber_id', request()->subid)
                    ->groupBy('user_id')
                    ->get();
            } else {
                $data =   DB::table('demo_requests')->whereBetween('created_at', [$startDate, $endDate])
                    ->select('country', DB::raw('COUNT(id) as demo_request_count')) // Select specific columns
                    ->whereIn('country', $country) // Filter by specific countries
                    ->groupBy('country') // Group by country
                    ->get();
            }

            return response()->json(['data' => $data]);
        } elseif (request()->type == "bytimelineDemoRequestChart") {
            $startDate = $this->parseReportDate(request()->start);
            $endDate = $this->parseReportDate(request()->end, true);
            $country = (request()->input('country') == 'All') ? Countries::pluck('country_name') : Countries::where('id', [request()->input('country')])->pluck('country_name');

            if (!empty(request()->subid)) {
                $cd = Tickets::select(
                    'user_id',
                    DB::raw('COUNT(id) AS no_of_tickets_solved'),
                    DB::raw('AVG(TIMESTAMPDIFF(SECOND, `created_at`, `updated_at`)) / 3600 AS avg_time_taken_hours')
                )->where('subscriber_id', request()->subid)
                    ->groupBy('user_id')
                    ->get();
            } else {
                $cd = Tickets::select(
                    'user_id',
                    DB::raw('COUNT(id) AS no_of_tickets_solved'),
                    DB::raw('AVG(TIMESTAMPDIFF(SECOND, `created_at`, `updated_at`)) / 3600 AS avg_time_taken_hours')
                )
                    ->groupBy('user_id')
                    ->get();
            }

            return response()->json(['data' => $data]);
        } elseif (request()->type == "bytimeTakenDemoRequestChart") {
            $startDate = $this->parseReportDate(request()->start);
            $endDate = $this->parseReportDate(request()->end, true);

            if (!empty(request()->subid)) {
                $cd = Tickets::select(
                    'user_id',
                    DB::raw('COUNT(id) AS no_of_tickets_solved'),
                    DB::raw('AVG(TIMESTAMPDIFF(SECOND, `created_at`, `updated_at`)) / 3600 AS avg_time_taken_hours')
                )->where('subscriber_id', request()->subid)
                    ->groupBy('user_id')
                    ->get();
            } else {
                $data = DB::table('demo_requests')
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->where('status', 'true') // Ensure status is appropriately filtered
                    ->select(
                        'country',
                        DB::raw("
                                CASE
                                    WHEN TIMESTAMPDIFF(DAY, `created_at`, `updated_at`) <= 1 THEN '1 Day'
                                    WHEN TIMESTAMPDIFF(DAY, `created_at`, `updated_at`) = 2 THEN '2 Days'
                                    WHEN TIMESTAMPDIFF(DAY, `created_at`, `updated_at`) = 3 THEN '3 Days'
                                    WHEN TIMESTAMPDIFF(DAY, `created_at`, `updated_at`) <= 7 THEN '1 Week'
                                    WHEN TIMESTAMPDIFF(DAY, `created_at`, `updated_at`) <= 14 THEN '2 Weeks'
                                    WHEN TIMESTAMPDIFF(DAY, `created_at`, `updated_at`) > 14 THEN '2 Weeks+'
                                    ELSE 'Longer'
                                END AS time_interval,
                                COUNT(*) AS total_tickets
                            ")
                    )
                    ->groupBy('country', 'time_interval') // Group by country and time_interval
                    ->get();
            }

            return response()->json(['data' => $data]);
        }
        elseif (request()->type == "byActivityLogsType") {
            $query = new Activities();
            if (($user->membership == 'Adwiseri' || $user->membership == 'Adwiseri+' || $user->membership == 'Enterprise') && $user->user_type == 'Subscriber') {
                $query = $query->where('subscriber_id', $user->id);
            }
            $activities = $query->select('activity_name', DB::raw('count(*) as count'))
                    ->groupBy('activity_name')
                    ->get();

            $data = $activities->map(function ($activity) {
                return [
                    'activity_name' => $activity->activity_name,
                    'count' => $activity->count,
                ];
            });


            return response()->json(['data' => $data]);
        } elseif (request()->type == "byActivityLogsUser(Top10)") {
            $query = Activities::join('users', 'activities.user_id', '=', 'users.id') // ✅ Join users table
                    ->select(
                        'activities.user_id',
                        'users.name as user_name', // ✅ Fetch user name
                        DB::raw('COUNT(activities.id) as activity_count') // ✅ Count activities per user
                    )
                    ->whereIn('users.user_type', ['Subscriber', 'User'])
                    ->groupBy('activities.user_id', 'users.name') // ✅ Group by user ID and name
                    ->orderBy('activity_count', 'desc') // ✅ Sort by highest activity count
                    ->limit(10); // ✅ Get top 10 users

                // Apply filter for specific subscribers
                if (($user->membership == 'Adwiseri' || $user->membership == 'Adwiseri+' || $user->membership == 'Enterprise') 
                    && $user->user_type == 'Subscriber') {
                    $query->where('activities.subscriber_id', $user->id);
                }

                $topActiveUsers = $query->get();

                return response()->json([
                    'status' => 'success',
                    'data' => $topActiveUsers
                ]);
        } elseif (request()->type == "byActivityLogsYear") {
            $query = new Activities();
            if (($user->membership == 'Adwiseri' || $user->membership == 'Adwiseri+' || $user->membership == 'Enterprise') && $user->user_type == 'Subscriber') {
                $query = $query->where('subscriber_id', $user->id);
            }

            $byUserTimeline = $query
                // ✅ Ensure correct filtering
                ->select(
                    DB::raw('YEAR(created_at) AS year'), // ✅ Specify users.created_at to avoid ambiguity
                    DB::raw('COUNT(id) AS count') // ✅ Count based on users.id
                )
                ->groupBy(DB::raw('YEAR(created_at)')) // ✅ Group by extracted year
                ->orderBy('year', 'asc') // ✅ Sort by oldest first
                ->get();

            return response()->json(['data' => $byUserTimeline]);
        } elseif (request()->type == "byActivityLogsTimeline(Duration)") {
            $currentYear = date('Y');
            $currentDate = now();
            $lastWeekStart = now()->subWeek()->startOfWeek();
            $lastWeekEnd = now()->subWeek()->endOfWeek();
            $lastMonthStart = now()->subMonth()->startOfMonth();
            $lastMonthEnd = now()->subMonth()->endOfMonth();
            $lastQuarterStart = now()->subQuarter()->startOfQuarter();
            $lastQuarterEnd = now()->subQuarter()->endOfQuarter();
            $lastYearStart = now()->subYear()->startOfYear();
            $lastYearEnd = now()->subYear()->endOfYear();
            // Get Inspection Start Date (modify this based on where the date is stored)
            
            // Base Query
            $query = new Activities();
            $query1 = clone $query;
            
            if (($user->membership == 'Adwiseri' || $user->membership == 'Adwiseri+' || $user->membership == 'Enterprise') 
                && $user->user_type == 'Subscriber') {
                $query = $query->where('subscriber_id', request()->subid)->whereYear('created_at', '=', $currentYear);
                $query1 = $query1->where('subscriber_id', request()->subid);
                $inspectionStartDate = $query1->where('subscriber_id', request()->subid)->orderBy('created_at','asc')->first();
            }else{
                $inspectionStartDate = $query1->orderBy('created_at','asc')->first();
            }
            
            // 🔹 Today's Applications
            $todayApplications = clone $query;
            $todayApplications = $todayApplications->whereDate('created_at', $currentDate)
                ->selectRaw("'Today' as type, COUNT(*) as count")
                ->get();
            
            // 🔹 Last Week's Applications
            $lastWeekApplications = clone $query;
            $lastWeekApplications = $lastWeekApplications->whereBetween('created_at', [$lastWeekStart, $lastWeekEnd])
                ->selectRaw("'Last Week' as type, COUNT(*) as count")
                ->get();
            
            // 🔹 Last Month's Applications
            $lastMonthApplications = clone $query;
            $lastMonthApplications = $lastMonthApplications->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])
                ->selectRaw("'Last Month' as type, COUNT(*) as count")
                ->get();
            
            // 🔹 Last Quarter's Applications
            $lastQuarterApplications = clone $query;
            $lastQuarterApplications = $lastQuarterApplications->whereBetween('created_at', [$lastQuarterStart, $lastQuarterEnd])
                ->selectRaw("'Last Quarter' as type, COUNT(*) as count")
                ->get();

            $yearlyApplications = clone $query;
            $yearlyApplications = $yearlyApplications->whereBetween('created_at', [$lastYearStart, $lastYearEnd])
                ->selectRaw("'Last Year' as type, COUNT(*) as count")
                ->get();
            
            // 🔹 Since Inception Data (Replacing "Past Year Data")
            $sinceInspectionData = clone $query1;
            $sinceInspectionData = $sinceInspectionData
                ->whereDate('created_at', '>=', $inspectionStartDate->created_at) 
                ->selectRaw("'Since Inception' as type, COUNT(*) as count")
                ->get();
            
            // 🔹 Weekly Applications
            $weeklyApplications = clone $query;
            $weeklyApplications = $weeklyApplications->selectRaw("
                WEEK(created_at) as week_num, 
                YEAR(created_at) as year_num, 
                COUNT(*) as count
            ")
                ->groupBy('year_num', 'week_num')
                ->orderBy('year_num', 'asc')
                ->orderBy('week_num', 'asc')
                ->get();
            
            // 🔹 Quarterly Applications
            $quarterlyApplications = clone $query;
            $quarterlyApplications = $quarterlyApplications->selectRaw("
                YEAR(created_at) as year, 
                QUARTER(created_at) as quarter, 
                COUNT(*) as count
            ")
                ->groupBy('year', 'quarter')
                ->orderBy('year', 'asc')
                ->orderBy('quarter', 'asc')
                ->get();
            
            // 🔹 Monthly Applications
            $monthlyApplications = clone $query;
            $monthlyApplications = $monthlyApplications->selectRaw("
                YEAR(created_at) as year, 
                MONTH(created_at) as month, 
                COUNT(*) as count
            ")
                ->groupBy('year', 'month')
                ->orderBy('year', 'asc')
                ->orderBy('month', 'asc')
                ->get();
            
            // 🔹 Merge All Data
            $formattedData = collect()
                ->merge($todayApplications)
                ->merge($lastWeekApplications)
                ->merge($lastMonthApplications)
                ->merge($lastQuarterApplications)
                ->merge($yearlyApplications)
                // ->merge($quarterlyApplications)
                // ->merge($monthlyApplications)
                ->merge($sinceInspectionData); // ✅ Replacing past year with "Since Inception"
            
            // 🔹 Format Data for Output
            $formattedData = $formattedData->map(function ($item) {
                return [
                    'type' => $item['type'],
                    'count' => $item['count'],
                ];
            });
            
            return response()->json([
                'status' => 'success',
                'data' => $formattedData
            ]);
        } 
        
        elseif (request()->type == "byTop10SubscribersChart") {
            $startDate = $this->parseReportDate(request()->start);
            $endDate = $this->parseReportDate(request()->end, true);


            if (!empty(request()->subid)) {
                $result  = Activities::whereNotNull('subscriber_id')
                    ->where('subscriber_id', request()->subid)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->select('subscriber_id', DB::raw('COUNT(*) as total_activities'))
                    ->groupBy('subscriber_id')
                    ->limit(10)
                    ->get()
                    ->map(function ($activity) {
                        return [
                            'subscriber' => !empty($activity->user) ? $activity->user->name : '',
                            'total_activities' => $activity->total_activities
                        ];
                    });
            } else {
                $result  = Activities::whereNotNull('subscriber_id')
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->select('subscriber_id', DB::raw('COUNT(*) as total_activities'))
                    ->groupBy('subscriber_id')
                    ->limit(10)
                    ->get()
                    ->map(function ($activity) {
                        return [
                            'subscriber' => !empty($activity->user) ? $activity->user->name : '',
                            'total_activities' => $activity->total_activities
                        ];
                    });
            }

            return response()->json(['data' => $result]);
        } elseif (request()->type == "byPaymentModePaymentAmountChart") {
            $query = new PaymentARs();
            if (($user->membership == 'Adwiseri' || $user->membership == 'Adwiseri+' || $user->membership == 'Enterprise') && $user->user_type == 'Subscriber') {
                $query =   $query->where('subscriber_id', request()->subid);
            }

            if (!empty(request()->subid)) {

                $applications = PaymentARs::select(
                    DB::raw('
                            CASE
                                WHEN amount BETWEEN 1 AND 99 THEN "1-99"
                                WHEN amount BETWEEN 100 AND 249 THEN "100-249"
                                WHEN amount BETWEEN 250 AND 499 THEN "250-499"
                                WHEN amount BETWEEN 500 AND 999 THEN "500-999"
                                WHEN amount BETWEEN 1000 AND 2499 THEN "1000-2499"
                                WHEN amount BETWEEN 2500 AND 4999 THEN "2500-4999"
                                WHEN amount BETWEEN 5000 AND 9999 THEN "5000-9999"
                                WHEN amount >= 10000 THEN "10,000+"
                            END AS amount_range')
                )
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->where('subscriber_id', request()->subid)
                    ->selectRaw('COUNT(*) as number_of_invoices')
                    ->groupBy('amount_range')
                    ->having('amount_range', '=',  $priceRange)
                    ->get();
            } else {

                $applications = PaymentARs::select(
                    DB::raw('
                        CASE
                            WHEN amount BETWEEN 1 AND 99 THEN "1-99"
                            WHEN amount BETWEEN 100 AND 249 THEN "100-249"
                            WHEN amount BETWEEN 250 AND 499 THEN "250-499"
                            WHEN amount BETWEEN 500 AND 999 THEN "500-999"
                            WHEN amount BETWEEN 1000 AND 2499 THEN "1000-2499"
                            WHEN amount BETWEEN 2500 AND 4999 THEN "2500-4999"
                            WHEN amount BETWEEN 5000 AND 9999 THEN "5000-9999"
                            WHEN amount >= 10000 THEN "10,000+"
                        END AS amount_range')
                )
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->selectRaw('COUNT(*) as number_of_invoices')
                    ->groupBy('amount_range')
                    ->having('amount_range', '=',  $priceRange)
                    ->get();
            }
            return response()->json(['data' => $applications]);
        } elseif (request()->type == "byNoOfTransactionDatesChart") {

            $startDate = $this->parseReportDate(request()->start);
            $endDate = $this->parseReportDate(request()->end, true);
            if (!empty(request()->subid)) {
            } else {

                $data = DB::table('referrals')
                    ->join('users', 'referrals.userid', '=', 'users.id') // Join with the users table based on userid
                    ->whereBetween('referrals.created_at', [$startDate, $endDate]) // Filter by transaction date range
                    ->select(
                        'users.name as user_name', // Select user name
                        DB::raw('
                            CASE
                            WHEN previous_balance < wallet_balance THEN "Credit"
                            WHEN previous_balance > wallet_balance THEN "Debit"
                            ELSE "No Change"
                        END as operation_type'),  // Determine whether it's a Credit or Debit
                        DB::raw('
                        ROUND(SUM(CASE
                            WHEN previous_balance < wallet_balance THEN wallet_balance - previous_balance
                            WHEN previous_balance > wallet_balance THEN previous_balance - wallet_balance
                            ELSE 0
                        END), 2) as total_balance_change'), // Sum the balance changes
                        DB::raw('COUNT(referrals.id) as total_transactions'), // Count the number of transactions per user
                        DB::raw('DATE(referrals.created_at) as transaction_date') // Extract the transaction date
                    )
                    ->groupBy('transaction_date', 'users.name', DB::raw('
                    CASE
                        WHEN previous_balance < wallet_balance THEN "Credit"
                        WHEN previous_balance > wallet_balance THEN "Debit"
                        ELSE "No Change"
                    END')) // Group by transaction date, user name, and operation type
                    ->orderBy('transaction_date', 'asc') // Order by transaction date
                    ->get();
            }
            return response()->json(['data' => $data]);
        } elseif (request()->type == "byAffiliatesNoofSubscribersReferredsChart") {
            $startDate = $this->parseReportDate(request()->start);
            $endDate = $this->parseReportDate(request()->end, true);

            if (!empty(request()->subid)) {
                $data = DB::table('users as referrer')
                    ->join('users as referred', 'referrer.referral', '=', 'referred.referral_code')
                    ->select(
                        'referrer.name as name', // Include referrer name
                        DB::raw('COUNT(referred.id) as count') // Count referred subscribers
                    )
                    ->whereBetween('reffered.created_at', [$startDate, $endDate])
                    ->where('referrer.id', request()->subid)
                    ->where('referred.user_type', 'subscriber')
                    ->groupBy('referrer.name') // Group by referrer name to include it in the result
                    ->get();
            } else {
                $data = DB::table('referrals')
                    ->leftJoin('users', 'referrals.referral_code', '=', 'users.referral')
                    ->select(
                        'users.name as name',
                        DB::raw('COUNT(users.id) as count')
                    )
                    ->whereBetween('referrals.created_at', [$startDate, $endDate])

                    ->where('users.user_type', 'Affiliate') // Filter by user_type
                    ->groupBy('users.name')
                    ->get();
            }
            return response()->json(['data' => $data]);
        } elseif (request()->type == "byAmountOfCommissionsEarntChart") {
            $startDate = $this->parseReportDate(request()->start);
            $endDate = $this->parseReportDate(request()->end, true);

            if (!empty(request()->subid)) {

                $dataQuery = DB::table('referrals')
                    ->whereBetween('referrals.created_at', [$startDate, $endDate])
                    ->leftJoin('users', 'referrals.referral_code', '=', 'users.referral') // Join referrals with users
                    ->select(
                        'users.name as affiliate_name', // Affiliate's name
                        DB::raw('COUNT(referrals.id) as referral_count'), // Count of referrals
                        DB::raw('ROUND(SUM(referrals.wallet_balance), 2) as total_wallet_balance') // Sum of wallet_balance
                    )
                    ->where('users.user_type', 'Affiliate') // Filter by user_type
                    ->groupBy('users.name'); // Group by affiliate name

                // If a subid is provided, filter by that user
                if (!empty(request()->subid)) {
                    $dataQuery->where('users.id', request()->subid); // Filter by user ID
                }

                $data = $dataQuery->get();
            } else {
                $data = DB::table('referrals')
                    ->whereBetween('referrals.created_at', [$startDate, $endDate])
                    ->leftJoin('users', 'referrals.referral_code', '=', 'users.referral') // Join referrals with users
                    ->select(
                        'users.name as affiliate_name', // Affiliate's name
                        DB::raw('COUNT(referrals.id) as referral_count'), // Count of referrals
                        DB::raw('ROUND(SUM(referrals.wallet_balance), 2) as total_wallet_balance') // Sum of wallet_balance
                    )
                    ->where('users.user_type', 'Affiliate') // Filter by user_type
                    ->groupBy('users.name') // Group by affiliate name
                    ->get();
            }

            return response()->json(['data' => $data]);
        } elseif (request()->type == "byAffiliateCountryChart") {
            $startDate = $this->parseReportDate(request()->start);
            $endDate = $this->parseReportDate(request()->end, true);

            $country = (request()->input('country') == 'All') ? Countries::pluck('country_name') : Countries::where('id', [request()->input('country')])->pluck('country_name');

            if (!empty(request()->subid)) {

                $referrals = User::find(request()->subid);
                $data = User::where('referral', $referrals->referral_code)
                    ->where('user_type', 'Subscriber')
                    ->whereIn('country', $country)
                    ->groupBy('country') // Group by country
                    ->select('country', DB::raw('COUNT(*) as No_of_Affiliate')) // Select country and count referrals
                    ->get();
            } else {
                $data = User::where('user_type', 'Affiliate')
                    ->whereIn('country', $country) // Use `whereIn` to handle both cases
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->select('country', DB::raw('COUNT(users.id) as No_of_Affiliate'))
                    ->groupBy('country')
                    ->get();
            }

            return response()->json(['data' => $data]);
        } elseif (request()->type == "byAffiliateSubscribedPlanChart") {


            $country = (request()->input('country') == 'All') ? Countries::pluck('country_name') : Countries::where('id', [request()->input('country')])->pluck('country_name');

            if (!empty(request()->subid)) {

                $data = User::where('user_type', 'Affiliate')
                    ->whereIn('country', $country) // Use `whereIn` to handle both cases
                    ->whereBetween('users.created_at', [$startDate, $endDate])
                    ->select(
                        'membership',
                        DB::raw('COUNT(id) as subscriber_count'),
                        // DB::raw('MAX(referrals.referral_code) as referral_code'), // Use MAX or any other aggregate function
                        // DB::raw('MAX(referrals.other_column) as other_column') // Apply aggregate function on other columns
                    )
                    ->groupBy('membership')
                    ->get();
                $referrals = User::find(request()->subid);
                $data = User::join('referrals', 'users.referral', '=', 'referrals.referral_code') // Join referrals table
                    ->where('users.user_type', 'Subscriber') // Ensure user is a Subscriber
                    ->whereNotNull('users.referral_code') // Ensure the user has a referral code
                    ->select(
                        'users.membership',
                        DB::raw('COUNT(users.id) as subscriber_count'),
                        DB::raw('MAX(referrals.referral_code) as referral_code'), // Use MAX or any other aggregate function
                        // DB::raw('MAX(referrals.other_column) as other_column') // Apply aggregate function on other columns
                    )
                    ->groupBy('users.membership') // Group by membership
                    ->get();
            } else {

                $data = User::join('referrals', 'users.referral', '=', 'referrals.referral_code') // Join referrals table
                    ->where('users.user_type', 'Subscriber') // Ensure user is a Subscriber
                    ->whereNotNull('users.referral_code') // Ensure the user has a referral code
                    ->select(
                        'users.membership',
                        DB::raw('COUNT(users.id) as subscriber_count'),
                        DB::raw('MAX(referrals.referral_code) as referral_code'), // Use MAX or any other aggregate function
                        // DB::raw('MAX(referrals.other_column) as other_column') // Apply aggregate function on other columns
                    )
                    ->groupBy('users.membership') // Group by membership
                    ->get();
            }

            return response()->json(['data' => $data]);
        } elseif (request()->type == "byAffiliateCurrentWalletCreditsChart") {
            $startDate = $this->parseReportDate(request()->start);
            $endDate = $this->parseReportDate(request()->end, true);

            if (!empty(request()->subid)) {
                $referralsPerWalletBalance = DB::table('referrals')
                    ->leftJoin('users', 'referrals.referral_code', '=', 'users.referral_code')
                    ->select(
                        'referrals.wallet_balance',
                        DB::raw('COUNT(referrals.referral_code) as count')
                    )
                    ->where('subscriber_id', request()->subid)
                    ->groupBy('referrals.wallet_balance')
                    ->get();
            } else {
                $data = DB::table('referrals')
                    ->leftJoin('users', 'referrals.referral_code', '=', 'users.referral') // Join referrals with users
                    ->select(
                        'users.name', // Select user name
                        DB::raw('SUM(referrals.wallet_balance) as total_wallet_balance'), // Sum of user wallet balances
                        DB::raw('COUNT(referrals.referral_code) as count') // Count the number of referrals
                    )
                    ->where('users.user_type', 'Affiliate') // Filter by user_type Affiliate
                    ->groupBy('users.name') // Group by user name
                    ->get();
            }

            return response()->json(['data' => $data]);
        }
    }
}
