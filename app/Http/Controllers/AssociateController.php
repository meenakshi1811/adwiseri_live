<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

use App\Models\User;
use App\Models\Clients;
use App\Models\Countries;
use App\Models\Currency;
use App\Models\Activities;
use App\Models\Applications;
use App\Models\Associate;
use App\Models\AssociateBusiness;
use App\Models\AssociateInvoice;
use App\Models\AssociatePayment;
use App\Models\Invoice_settings;
use App\Services\InvoiceItemService;
use App\Services\OfferBenefitService;
use App\Services\TableFilterCountService;
use App\Support\BrandedMail;
use App\Mail\Invoicemail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class AssociateController extends Controller
{
    /** Services an associate can be billed for (multi-select checkbox list). */
    public const SERVICE_OPTIONS = ['Student Admission', 'Job Recruitment', 'Visa Processing', 'Finance', 'Other'];

    /** Application status values (mirrors the Applications module status dropdown). */
    public const APPLICATION_STATUS_OPTIONS = [
        'Client Registered',
        'Client Counselled',
        'Preparation',
        'Appointment Booked',
        'Applied',
        'Decision',
        'Appeal Lodged',
        'Appeal Decision',
        'AR / JR Lodged',
        'AR / JR Decision',
        'Withdrawn',
        'Cancelled',
    ];

    /** Invoice status values (stored value => label). Mirrors internal_invoices. */
    public const STATUS_OPTIONS = [
        'UnPaid' => 'Unpaid',
        'Paid' => 'Paid',
        'PartiallyPaid' => 'Partially Paid',
        'Cancelled' => 'Cancelled',
    ];

    /**
     * Resolve the owning subscriber for the logged-in user (site-users inherit
     * their subscriber via added_by).
     */
    private function subscriber(): User
    {
        $user = Auth::user();

        if ($user->user_type === 'Subscriber' || $user->user_type === 'admin') {
            return $user;
        }

        return User::find($user->added_by) ?? $user;
    }

    /**
     * Guard: an associate row must belong to the current subscriber.
     */
    private function ownedAssociates(User $subscriber)
    {
        return Associate::where('added_by', $subscriber->id);
    }

    /**
     * Reject a submitted date when it is after today (picker max + server check).
     * Accepts dd-mm-yyyy or Y-m-d; empty/null values are allowed.
     */
    private function rejectFutureDate($rawDate, string $field, string $label)
    {
        if ($rawDate === null || trim((string) $rawDate) === '') {
            return null;
        }

        $normalized = OfferBenefitService::normalizeStorageDate($rawDate);
        if (!$normalized) {
            return null;
        }

        try {
            $date = Carbon::createFromFormat('Y-m-d', $normalized)->startOfDay();
        } catch (\Exception $e) {
            return null;
        }

        if ($date->gt(Carbon::today())) {
            return back()->withInput()->withErrors([
                $field => $label . ' cannot be a future date.',
            ]);
        }

        return null;
    }

    /**
     * Applications belonging to the current subscriber (for linking to associates).
     */
    private function subscriberApplications(User $subscriber)
    {
        return Applications::where('subscriber_id', $subscriber->id)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Validate the submitted services checkbox list against SERVICE_OPTIONS and
     * return them joined as a comma-separated string (empty string if none).
     */
    private function normalizeServices($services): string
    {
        if (!is_array($services)) {
            return '';
        }

        $valid = array_values(array_intersect($services, self::SERVICE_OPTIONS));

        return implode(', ', $valid);
    }

    /**
     * Associate invoice description shown in PDF/email Description column.
     */
    private function formatAssociateInvoiceDetail(AssociateInvoice $invoice): string
    {
        $lines = [];

        $clientName = trim((string) ($invoice->client_name ?? ''));
        if ($clientName !== '') {
            $lines[] = 'Client: ' . $clientName;
        }

        $applicationName = trim((string) ($invoice->application_name ?? ''));
        if ($applicationName !== '') {
            $lines[] = 'Application: ' . $applicationName;
        }

        $services = trim((string) ($invoice->services ?: $invoice->service_provided ?: ''));
        $lines[] = 'Service(s): ' . ($services !== '' ? $services : 'Professional Services');

        return implode("\n", $lines);
    }

    // ------------------------------------------------------------------
    // Landing
    // ------------------------------------------------------------------

    public function index()
    {
        return redirect()->route('associates');
    }

    // ------------------------------------------------------------------
    // Tab 1 — Associates
    // ------------------------------------------------------------------

    public function associates()
    {
        $user = Auth::user();
        $subscriber = $this->subscriber();
        $page = "associates";

        $associates = Associate::where('added_by', $subscriber->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $offerBenefitService = app(OfferBenefitService::class);
        $associateLimit = $offerBenefitService->formatLimitLabel(
            $offerBenefitService->effectiveAssociateLimit($subscriber)
        );

        $associateLocationFilters = TableFilterCountService::countBy(
            $associates,
            fn ($associate) => TableFilterCountService::associateLocationLabel($associate)
        );

        return view('web.associates.associates', compact('user', 'associates', 'page', 'associateLimit', 'associateLocationFilters'));
    }

    public function add_associate()
    {
        $user = Auth::user();
        $subscriber = $this->subscriber();
        $page = "associates";

        $offerBenefitService = app(OfferBenefitService::class);
        if (!$offerBenefitService->canAddAssociate($subscriber)) {
            return redirect()->route('associates')
                ->with('associate_limit', 'Upgrade membership to add more associates.');
        }

        $countries = Countries::get();
        $services = self::SERVICE_OPTIONS;

        return view('web.associates.add_associate', compact('user', 'countries', 'page', 'services'));
    }

    public function store_associate(Request $request)
    {
        $subscriber = $this->subscriber();
        $offerBenefitService = app(OfferBenefitService::class);

        // Server-side limit enforcement (the GET form is also gated).
        if (!$offerBenefitService->canAddAssociate($subscriber)) {
            return redirect()->route('associates')
                ->with('associate_limit', 'Upgrade membership to add more associates.');
        }

        $this->validate($request, [
            'name' => 'required|string|min:3|max:255',
            'phone' => 'required|string|max:50',
            'email' => 'required|email|max:255|unique:associates,email',
            'organization' => 'nullable|string|max:255',
            'country' => 'required',
            'state' => 'required',
            'city' => 'required|string|min:2|max:255',
            'pincode' => 'required|regex:/^[A-Za-z0-9\s\-]{3,10}$/',
            'home_country' => 'nullable|string|max:100',
            'visa_country' => 'nullable|string|max:100',
            'application_type' => 'nullable|string|max:100',
        ]);

        $country = Countries::find($request->country);
        $countryName = $country ? $country->country_name : $request->country;

        $currencyLabel = "USD($)";
        if ($country) {
            $currency = Currency::where('currency_code', '=', $country->currency)->first();
            if ($currency) {
                $currencyLabel = $currency->currency_code . "(" . $currency->currency_symbol . ")";
            }
        }

        $associate = new Associate();
        $associate->added_by = $subscriber->id;
        $associate->name = $request->name;
        $associate->phone = $request->phone;
        $associate->email = $request->email;
        $associate->organization = $request->organization;
        $associate->country = $countryName;
        $associate->state = $request->state;
        $associate->city = $request->city;
        $associate->pincode = strtoupper($request->pincode);
        $associate->home_country = $request->home_country;
        $associate->visa_country = $request->visa_country;
        $associate->application_type = $request->application_type;
        $associate->currency = $currencyLabel;
        $associate->status = "true";
        $associate->save();

        $associate->associate_code = 'ASC-' . str_pad($associate->id, 5, '0', STR_PAD_LEFT);
        $associate->save();

        $this->logActivity($subscriber, "New Associate Added",
            "New associate " . $request->name . " added.", $request->local_time);

        return redirect()->route('associates')->with('associate_added', "Associate added successfully.");
    }

    public function edit_associate($id)
    {
        $user = Auth::user();
        $subscriber = $this->subscriber();
        $page = "associates";

        $associate = $this->ownedAssociates($subscriber)->findOrFail($id);
        $countries = Countries::get();
        $services = self::SERVICE_OPTIONS;

        return view('web.associates.edit_associate', compact('user', 'associate', 'countries', 'page', 'services'));
    }

    public function update_associate(Request $request)
    {
        $subscriber = $this->subscriber();
        $associate = $this->ownedAssociates($subscriber)->findOrFail($request->id);

        $this->validate($request, [
            'name' => 'required|string|min:3|max:255',
            'phone' => 'required|string|max:50',
            'email' => 'required|email|max:255|unique:associates,email,' . $associate->id,
            'organization' => 'nullable|string|max:255',
            'country' => 'required',
            'state' => 'required',
            'city' => 'required|string|min:2|max:255',
            'pincode' => 'required|regex:/^[A-Za-z0-9\s\-]{3,10}$/',
            'home_country' => 'nullable|string|max:100',
            'visa_country' => 'nullable|string|max:100',
            'application_type' => 'nullable|string|max:100',
        ]);

        $country = Countries::find($request->country);
        $countryName = $country ? $country->country_name : $request->country;

        $associate->name = $request->name;
        $associate->phone = $request->phone;
        $associate->email = $request->email;
        $associate->organization = $request->organization;
        $associate->country = $countryName;
        $associate->state = $request->state;
        $associate->city = $request->city;
        $associate->pincode = strtoupper($request->pincode);
        $associate->home_country = $request->home_country;
        $associate->visa_country = $request->visa_country;
        $associate->application_type = $request->application_type;
        $associate->save();

        return redirect()->route('associates')->with('associate_updated', "Associate updated successfully.");
    }

    public function delete_associate($id)
    {
        $subscriber = $this->subscriber();
        $associate = $this->ownedAssociates($subscriber)->findOrFail($id);
        $associate->delete();

        return redirect()->route('associates')->with('associate_deleted', "Associate deleted successfully.");
    }

    // ------------------------------------------------------------------
    // Tab 2 — Business (Referrals)
    // ------------------------------------------------------------------

    public function business()
    {
        $user = Auth::user();
        $subscriber = $this->subscriber();
        $page = "associates";

        $businesses = AssociateBusiness::where('subscriber_id', $subscriber->id)
            ->with('associate')
            ->orderBy('created_at', 'desc')
            ->get();

        $associateReferralFilters = TableFilterCountService::countBy(
            $businesses,
            function ($business) {
                $associateName = trim((string) optional($business->associate)->name);

                return $associateName !== '' ? $associateName : 'Unassigned';
            }
        );

        return view('web.associates.business', compact('user', 'businesses', 'page', 'associateReferralFilters'));
    }

    public function add_business()
    {
        $user = Auth::user();
        $subscriber = $this->subscriber();
        $page = "associates";

        $associates = $this->ownedAssociates($subscriber)->orderBy('name')->get();
        $clients = Clients::where('subscriber_id', $subscriber->id)->orderBy('name')->get();
        $applications = $this->subscriberApplications($subscriber);
        $services = self::SERVICE_OPTIONS;
        $assignedApplicationIds = $this->assignedApplicationIds($subscriber);

        // Only offer applications that belong to a client on this form.
        $clientIds = $clients->pluck('id')->map(fn ($id) => (int) $id)->all();
        $applications = $applications->filter(function ($application) use ($clientIds) {
            return $application->client_id && in_array((int) $application->client_id, $clientIds, true);
        })->values();

        return view('web.associates.add_business', compact('user', 'associates', 'clients', 'applications', 'services', 'assignedApplicationIds', 'page'));
    }

    public function store_business(Request $request)
    {
        $subscriber = $this->subscriber();

        $this->validate($request, [
            'associate_id' => 'required|exists:associates,id',
            'client_id' => 'required|exists:clients,id',
            'application_id' => 'required|exists:applications,id',
            'services' => 'required|array|min:1',
            'services.*' => 'in:Student Admission,Job Recruitment,Visa Processing,Finance,Other',
            'fees' => 'required|numeric|min:0',
        ]);

        // Ensure the associate belongs to this subscriber.
        $this->ownedAssociates($subscriber)->findOrFail($request->associate_id);

        [$clientId, $clientName, $applicationId, $applicationName] = $this->resolveClientAndApplication($subscriber, $request);
        $services = $this->normalizeServices($request->services);

        $this->assertApplicationNotAssignedToAnyAssociate(
            $subscriber,
            $applicationId ? (int) $applicationId : null
        );

        // Auto-fill Home Country / Visa Country / Application Type from the linked
        // application and its client (these are no longer manual form fields).
        $application = Applications::where('subscriber_id', $subscriber->id)->find($request->application_id);
        $client = $clientId ? Clients::where('subscriber_id', $subscriber->id)->find($clientId) : null;

        $business = new AssociateBusiness();
        $business->subscriber_id = $subscriber->id;
        $business->associate_id = $request->associate_id;
        $business->client_id = $clientId;
        $business->client_name = $clientName;
        $business->application_id = $applicationId;
        $business->application_name = $applicationName;
        $business->services = $services;
        $business->service_provided = $services; // keep legacy display column in sync
        $business->fees = $request->fees;
        $business->application_status = $application
            ? trim((string) ($application->application_status ?? ''))
            : null;
        $business->home_country = $client ? ($client->nationality ?: $client->country) : null;
        $business->visa_country = $application ? ($application->visa_country ?: $application->application_country) : null;
        $business->application_type = $application ? ($application->application_program ?: $application->application_name) : null;
        $business->save();

        return redirect()->route('associate_business')->with('business_added', 'New Referral (Business) Entry added successfully.');
    }

    public function view_business($id)
    {
        $user = Auth::user();
        $subscriber = $this->subscriber();
        $page = 'associates';

        $business = AssociateBusiness::where('subscriber_id', $subscriber->id)
            ->with('associate')
            ->findOrFail($id);

        return view('web.associates.view_business', compact('user', 'business', 'page'));
    }

    public function edit_business($id)
    {
        $user = Auth::user();
        $subscriber = $this->subscriber();
        $page = 'associates';

        $business = AssociateBusiness::where('subscriber_id', $subscriber->id)->findOrFail($id);
        $associates = $this->ownedAssociates($subscriber)->orderBy('name')->get();
        $clients = Clients::where('subscriber_id', $subscriber->id)->orderBy('name')->get();
        $applications = $this->subscriberApplications($subscriber);
        $services = self::SERVICE_OPTIONS;
        $assignedApplicationIds = $this->assignedApplicationIds($subscriber, (int) $business->id);

        $clientIds = $clients->pluck('id')->map(fn ($clientId) => (int) $clientId)->all();
        $applications = $applications->filter(function ($application) use ($clientIds) {
            return $application->client_id && in_array((int) $application->client_id, $clientIds, true);
        })->values();

        return view('web.associates.edit_business', compact(
            'user',
            'business',
            'associates',
            'clients',
            'applications',
            'services',
            'assignedApplicationIds',
            'page'
        ));
    }

    public function update_business(Request $request)
    {
        $subscriber = $this->subscriber();
        $business = AssociateBusiness::where('subscriber_id', $subscriber->id)->findOrFail($request->id);

        $this->validate($request, [
            'associate_id' => 'required|exists:associates,id',
            'client_id' => 'required|exists:clients,id',
            'application_id' => 'required|exists:applications,id',
            'services' => 'required|array|min:1',
            'services.*' => 'in:Student Admission,Job Recruitment,Visa Processing,Finance,Other',
            'fees' => 'required|numeric|min:0',
        ]);

        $this->ownedAssociates($subscriber)->findOrFail($request->associate_id);

        [$clientId, $clientName, $applicationId, $applicationName] = $this->resolveClientAndApplication($subscriber, $request);
        $services = $this->normalizeServices($request->services);

        $this->assertApplicationNotAssignedToAnyAssociate(
            $subscriber,
            $applicationId ? (int) $applicationId : null,
            (int) $business->id
        );

        $application = Applications::where('subscriber_id', $subscriber->id)->find($request->application_id);
        $client = $clientId ? Clients::where('subscriber_id', $subscriber->id)->find($clientId) : null;

        $business->associate_id = $request->associate_id;
        $business->client_id = $clientId;
        $business->client_name = $clientName;
        $business->application_id = $applicationId;
        $business->application_name = $applicationName;
        $business->services = $services;
        $business->service_provided = $services;
        $business->fees = $request->fees;
        $business->application_status = $application
            ? trim((string) ($application->application_status ?? ''))
            : null;
        $business->home_country = $client ? ($client->nationality ?: $client->country) : null;
        $business->visa_country = $application ? ($application->visa_country ?: $application->application_country) : null;
        $business->application_type = $application ? ($application->application_program ?: $application->application_name) : null;
        $business->save();

        return redirect()->route('associate_business')->with('business_updated', 'Referral (Business) Entry updated successfully.');
    }

    /**
     * Build per-associate maps limited to clients/applications that are still invoiceable
     * (not already on a non-cancelled associate invoice for that associate + application).
     *
     * @return array{0: array<int, array<int, int>>, 1: array<int, array<int, int>>, 2: list<int>}
     */
    private function buildInvoiceableLinkMaps(User $subscriber): array
    {
        [, $associateApplicationMap] = $this->associateLinkMaps($subscriber);
        $invoicedCombinations = $this->invoicedClientApplicationCombinations($subscriber);

        $invoicedKeys = [];
        foreach ($invoicedCombinations as $row) {
            $invoicedKeys[$row['client_id'] . ':' . $row['application_id']] = true;
        }

        $links = AssociateBusiness::where('subscriber_id', $subscriber->id)
            ->whereNotNull('application_id')
            ->get(['associate_id', 'client_id', 'application_id']);

        $appClientMap = [];
        foreach ($links as $link) {
            if ($link->client_id) {
                $appClientMap[(int) $link->application_id] = (int) $link->client_id;
            }
        }

        $invoiceableApplicationMap = [];
        $invoiceableClientMap = [];

        foreach ($associateApplicationMap as $associateId => $appIds) {
            foreach ($appIds as $appId) {
                $clientId = $appClientMap[$appId] ?? null;
                if ($clientId && isset($invoicedKeys[$clientId . ':' . $appId])) {
                    continue;
                }

                $invoiceableApplicationMap[$associateId][] = (int) $appId;

                if ($clientId) {
                    $invoiceableClientMap[$associateId][$clientId] = $clientId;
                }
            }
        }

        $invoiceableApplicationMap = array_map('array_values', $invoiceableApplicationMap);
        $invoiceableClientMap = array_map('array_values', $invoiceableClientMap);
        $invoiceableAssociateIds = array_map('intval', array_keys($invoiceableApplicationMap));

        return [$invoiceableClientMap, $invoiceableApplicationMap, $invoiceableAssociateIds];
    }

    /**
     * Build per-associate lookup maps from the subscriber's business entries:
     *   [ associate_id => [client_id, ...], associate_id => [application_id, ...] ].
     * Used to restrict the invoice form's Client / Application dropdowns to the
     * clients and applications actually linked to the selected associate.
     *
     * @return array{0: array<int, array<int, int>>, 1: array<int, array<int, int>>}
     */
    private function associateLinkMaps(User $subscriber): array
    {
        $links = AssociateBusiness::where('subscriber_id', $subscriber->id)
            ->get(['associate_id', 'client_id', 'application_id']);

        $clientMap = [];
        $applicationMap = [];

        foreach ($links as $link) {
            $associateId = (int) $link->associate_id;
            if ($link->client_id) {
                $clientMap[$associateId][(int) $link->client_id] = (int) $link->client_id;
            }
            if ($link->application_id) {
                $applicationMap[$associateId][(int) $link->application_id] = (int) $link->application_id;
            }
        }

        // Reindex to plain lists of unique ids.
        $clientMap = array_map('array_values', $clientMap);
        $applicationMap = array_map('array_values', $applicationMap);

        return [$clientMap, $applicationMap];
    }

    /**
     * Business (Referrals) data for invoicing, keyed associate_id:application_id.
     *
     * @return array<string, array{fees: float, services: list<string>}>
     */
    private function businessInvoiceDataMap(User $subscriber): array
    {
        $map = [];

        $rows = AssociateBusiness::query()
            ->where('subscriber_id', $subscriber->id)
            ->whereNotNull('application_id')
            ->get(['associate_id', 'application_id', 'fees', 'services', 'service_provided']);

        foreach ($rows as $row) {
            $servicesRaw = trim((string) ($row->services ?: $row->service_provided));
            $services = array_values(array_filter(array_map('trim', explode(',', $servicesRaw))));
            $services = array_values(array_intersect($services, self::SERVICE_OPTIONS));

            $map[(int) $row->associate_id . ':' . (int) $row->application_id] = [
                'fees' => round((float) $row->fees, 2),
                'services' => $services,
            ];
        }

        return $map;
    }

    private function resolveBusinessRecord(User $subscriber, int $associateId, ?int $applicationId): ?AssociateBusiness
    {
        if (!$applicationId) {
            return null;
        }

        return AssociateBusiness::query()
            ->where('subscriber_id', $subscriber->id)
            ->where('associate_id', $associateId)
            ->where('application_id', $applicationId)
            ->first();
    }

    private function resolveBusinessFees(User $subscriber, int $associateId, ?int $applicationId): ?float
    {
        $business = $this->resolveBusinessRecord($subscriber, $associateId, $applicationId);

        if (!$business) {
            return null;
        }

        return round((float) $business->fees, 2);
    }

    private function servicesFromBusiness(AssociateBusiness $business): string
    {
        $servicesRaw = trim((string) ($business->services ?: $business->service_provided));

        return $this->normalizeServices(
            array_filter(array_map('trim', explode(',', $servicesRaw)))
        );
    }

    private function assertBusinessLinkExists(User $subscriber, int $associateId, int $applicationId): void
    {
        $exists = AssociateBusiness::query()
            ->where('subscriber_id', $subscriber->id)
            ->where('associate_id', $associateId)
            ->where('application_id', $applicationId)
            ->exists();

        if (!$exists) {
            throw ValidationException::withMessages([
                'application_id' => 'Selected application is not linked to this associate in Business (Referrals).',
            ]);
        }
    }

    /**
     * @return array{0: list<int>, 1: list<int>}
     */
    private function linkedClientAndApplicationIds(array $associateClientMap, array $associateApplicationMap): array
    {
        $clientIds = [];
        $applicationIds = [];

        foreach ($associateClientMap as $ids) {
            foreach ($ids as $id) {
                $clientIds[(int) $id] = true;
            }
        }

        foreach ($associateApplicationMap as $ids) {
            foreach ($ids as $id) {
                $applicationIds[(int) $id] = true;
            }
        }

        return [array_keys($clientIds), array_keys($applicationIds)];
    }

    private function clientsForIds(User $subscriber, array $ids)
    {
        if ($ids === []) {
            return collect();
        }

        return Clients::where('subscriber_id', $subscriber->id)
            ->whereIn('id', $ids)
            ->orderBy('name')
            ->get();
    }

    private function applicationsForIds(User $subscriber, array $ids)
    {
        if ($ids === []) {
            return collect();
        }

        return Applications::where('subscriber_id', $subscriber->id)
            ->whereIn('id', $ids)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Application ids already linked to any associate via business entries.
     *
     * @return list<int>
     */
    private function assignedApplicationIds(User $subscriber, ?int $ignoreBusinessId = null): array
    {
        $query = AssociateBusiness::query()
            ->where('subscriber_id', $subscriber->id)
            ->whereNotNull('application_id');

        if ($ignoreBusinessId) {
            $query->where('id', '!=', $ignoreBusinessId);
        }

        return $query->pluck('application_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    private function assertApplicationNotAssignedToAnyAssociate(
        User $subscriber,
        ?int $applicationId,
        ?int $ignoreBusinessId = null
    ): void {
        if (!$applicationId) {
            return;
        }

        $query = AssociateBusiness::query()
            ->where('subscriber_id', $subscriber->id)
            ->where('application_id', $applicationId);

        if ($ignoreBusinessId) {
            $query->where('id', '!=', $ignoreBusinessId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'application_id' => 'This application is already linked to another associate.',
            ]);
        }
    }

    /**
     * Resolve the effective client + linked application from the request.
     * If an application is chosen its client overrides the manual client select.
     * Returns [clientId, clientName, applicationId, applicationName].
     */
    private function resolveClientAndApplication(User $subscriber, Request $request): array
    {
        $clientId = $request->client_id;
        $clientName = null;
        $applicationId = null;
        $applicationName = null;

        if ($request->application_id) {
            $application = Applications::where('subscriber_id', $subscriber->id)->find($request->application_id);
            if ($application) {
                $applicationId = $application->id;
                $applicationName = trim($application->application_name . ' (' . $application->application_id . ')');
                // Prefer the application's own client.
                if ($application->client_id) {
                    $clientId = $application->client_id;
                    $clientName = $application->client_name;
                }
            }
        }

        if (!$clientName && $clientId) {
            $client = Clients::where('subscriber_id', $subscriber->id)->find($clientId);
            $clientName = $client ? $client->name : null;
        }

        return [$clientId, $clientName, $applicationId, $applicationName];
    }

    /**
     * Client + application pairs already present on non-cancelled invoices.
     *
     * @return list<array{client_id:int, application_id:int}>
     */
    private function invoicedClientApplicationCombinations(User $subscriber, ?int $ignoreInvoiceId = null): array
    {
        $query = AssociateInvoice::query()
            ->where('subscriber_id', $subscriber->id)
            ->where('status', '!=', 'Cancelled')
            ->whereNotNull('application_id')
            ->whereNotNull('client_id');

        if ($ignoreInvoiceId) {
            $query->where('id', '!=', $ignoreInvoiceId);
        }

        return $query->get(['client_id', 'application_id'])
            ->map(fn ($row) => [
                'client_id' => (int) $row->client_id,
                'application_id' => (int) $row->application_id,
            ])
            ->unique(fn ($row) => $row['client_id'] . ':' . $row['application_id'])
            ->values()
            ->all();
    }

    private function assertClientApplicationInvoiceAvailable(
        User $subscriber,
        ?int $clientId,
        ?int $applicationId,
        ?int $ignoreInvoiceId = null,
        bool $allowDuplicate = false
    ): void {
        if ($allowDuplicate) {
            return;
        }

        if (!$clientId || !$applicationId) {
            throw ValidationException::withMessages([
                'application_id' => 'Link an application before creating an invoice.',
            ]);
        }

        if (app(InvoiceItemService::class)->hasActiveAssociateClientApplicationInvoice(
            (int) $subscriber->id,
            $clientId,
            $applicationId,
            $ignoreInvoiceId
        )) {
            throw ValidationException::withMessages([
                'application_id' => 'An invoice already exists for this client and application. Cancel the existing invoice to invoice it again.',
            ]);
        }
    }

    public function delete_business($id)
    {
        $subscriber = $this->subscriber();
        $business = AssociateBusiness::where('subscriber_id', $subscriber->id)->findOrFail($id);
        $business->delete();

        return redirect()->route('associate_business')->with('business_deleted', "Business entry deleted successfully.");
    }

    // ------------------------------------------------------------------
    // Tab 3 — Invoices
    // ------------------------------------------------------------------

    public function invoices()
    {
        $user = Auth::user();
        $subscriber = $this->subscriber();
        $page = "associates";

        $invoices = AssociateInvoice::where('subscriber_id', $subscriber->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $associates = $this->ownedAssociates($subscriber)->get()->keyBy('id');
        $statusOptions = self::STATUS_OPTIONS;
        [, , $invoiceableAssociateIds] = $this->buildInvoiceableLinkMaps($subscriber);
        $canCreateInvoice = count($invoiceableAssociateIds) > 0;
        $invoiceStatusFilters = TableFilterCountService::countBy(
            $invoices,
            fn ($invoice) => TableFilterCountService::invoiceStatusLabel($invoice->status)
        );

        return view('web.associates.invoices', compact('user', 'invoices', 'associates', 'statusOptions', 'page', 'canCreateInvoice', 'invoiceStatusFilters'));
    }

    public function create_invoice()
    {
        $user = Auth::user();
        $subscriber = $this->subscriber();
        $page = "associates";

        $associates = $this->ownedAssociates($subscriber)->orderBy('name')->get();
        $services = self::SERVICE_OPTIONS;
        $statusOptions = self::STATUS_OPTIONS;

        [$associateClientMap, $associateApplicationMap, $invoiceableAssociateIds] = $this->buildInvoiceableLinkMaps($subscriber);
        if (empty($invoiceableAssociateIds)) {
            return redirect()->route('associate_invoices')->with(
                'all_invoices_created',
                'Invoices are created for all associates.'
            );
        }

        $associates = $associates->whereIn('id', $invoiceableAssociateIds)->values();
        [$invoiceableClientIds, $invoiceableApplicationIds] = $this->linkedClientAndApplicationIds(
            $associateClientMap,
            $associateApplicationMap
        );
        $clients = $this->clientsForIds($subscriber, $invoiceableClientIds);
        $applications = $this->applicationsForIds($subscriber, $invoiceableApplicationIds);
        $invoicedApplicationCombinations = $this->invoicedClientApplicationCombinations($subscriber);
        $businessInvoiceDataMap = $this->businessInvoiceDataMap($subscriber);

        return view('web.associates.add_invoice', compact('user', 'associates', 'clients', 'applications', 'services', 'statusOptions', 'associateClientMap', 'associateApplicationMap', 'invoicedApplicationCombinations', 'businessInvoiceDataMap', 'page'));
    }

    public function store_invoice(Request $request)
    {
        $subscriber = $this->subscriber();

        $this->validate($request, [
            'associate_id' => 'required|exists:associates,id',
            'client_id' => 'required|exists:clients,id',
            'application_id' => 'required|exists:applications,id',
            'services' => 'required|array|min:1',
            'services.*' => 'in:Student Admission,Job Recruitment,Visa Processing,Finance,Other',
            'fees' => 'required|numeric|min:0',
            'status' => 'required|in:UnPaid,Paid,PartiallyPaid,Cancelled',
            'due_date' => 'nullable|string|max:20',
        ]);

        if ($error = $this->rejectFutureDate($request->due_date, 'due_date', 'Due date')) {
            return $error;
        }

        $this->ownedAssociates($subscriber)->findOrFail($request->associate_id);

        [$clientId, $clientName, $applicationId, $applicationName] = $this->resolveClientAndApplication($subscriber, $request);

        $this->assertClientApplicationInvoiceAvailable(
            $subscriber,
            $clientId ? (int) $clientId : null,
            $applicationId ? (int) $applicationId : null,
            null,
            $request->boolean('confirm_duplicate')
        );

        $business = $this->resolveBusinessRecord(
            $subscriber,
            (int) $request->associate_id,
            $applicationId ? (int) $applicationId : null
        );

        if (!$business) {
            throw ValidationException::withMessages([
                'application_id' => 'No business referral entry found for this associate and application.',
            ]);
        }

        $this->assertBusinessLinkExists(
            $subscriber,
            (int) $request->associate_id,
            (int) $applicationId
        );

        $services = $this->servicesFromBusiness($business);
        if ($services === '') {
            throw ValidationException::withMessages([
                'services' => 'No services found on the business referral entry.',
            ]);
        }

        $businessFees = round((float) $business->fees, 2);

        $invoice = new AssociateInvoice();
        $invoice->subscriber_id = $subscriber->id;
        $invoice->associate_id = $request->associate_id;
        $invoice->client_id = $clientId;
        $invoice->client_name = $clientName;
        $invoice->application_id = $applicationId;
        $invoice->application_name = $applicationName;
        $invoice->services = $services;
        $invoice->service_provided = $services; // keep legacy display column in sync
        $invoice->fees = $businessFees;
        $invoice->status = $request->status;
        // Normalize the picker's dd-mm-yyyy value to Y-m-d for the DATE column.
        $invoice->due_date = OfferBenefitService::normalizeStorageDate($request->due_date);
        // If the invoice is created already marked Paid, treat fees as fully paid.
        $invoice->paid = $request->status === 'Paid' ? $businessFees : 0;
        $invoice->save();

        $invoice->invoice_no = 'AIN-' . str_pad($invoice->id, 5, '0', STR_PAD_LEFT);
        $invoice->save();

        $associate = Associate::find($invoice->associate_id);
        $invoiceSetting = Invoice_settings::forUser((int) $subscriber->id, Invoice_settings::RECIPIENT_ASSOCIATES);
        $mailResult = $this->sendAssociateInvoiceEmail($invoice, $associate, $subscriber, $invoiceSetting);

        if (!$mailResult['success']) {
            \Log::warning('Associate invoice email not sent', [
                'invoice_id' => $invoice->id,
                'invoice_no' => $invoice->invoice_no,
                'message' => $mailResult['message'],
            ]);
        }

        return redirect()->route('associate_invoices')->with(
            $mailResult['success'] ? 'invoice_added' : 'invoice_email_failed',
            $mailResult['success']
                ? 'Invoice created and emailed to Associate.'
                : 'Invoice created, but email was not sent: ' . $mailResult['message']
        );
    }

    public function edit_invoice($id)
    {
        $user = Auth::user();
        $subscriber = $this->subscriber();
        $page = "associates";

        $invoice = AssociateInvoice::where('subscriber_id', $subscriber->id)->findOrFail($id);
        $associates = $this->ownedAssociates($subscriber)->orderBy('name')->get();
        [$associateClientMap, $associateApplicationMap] = $this->associateLinkMaps($subscriber);
        $linkedClientIds = array_values(array_unique(array_filter(array_merge(
            $associateClientMap[(int) $invoice->associate_id] ?? [],
            [$invoice->client_id]
        ))));
        $linkedApplicationIds = array_values(array_unique(array_filter(array_merge(
            $associateApplicationMap[(int) $invoice->associate_id] ?? [],
            [$invoice->application_id]
        ))));
        $clients = $this->clientsForIds($subscriber, $linkedClientIds);
        $applications = $this->applicationsForIds($subscriber, $linkedApplicationIds);
        $services = self::SERVICE_OPTIONS;
        $statusOptions = self::STATUS_OPTIONS;
        $invoicedApplicationCombinations = $this->invoicedClientApplicationCombinations($subscriber, (int) $invoice->id);

        return view('web.associates.edit_invoice', compact(
            'user',
            'invoice',
            'associates',
            'clients',
            'applications',
            'services',
            'statusOptions',
            'associateClientMap',
            'associateApplicationMap',
            'invoicedApplicationCombinations',
            'page'
        ));
    }

    public function update_invoice(Request $request)
    {
        $subscriber = $this->subscriber();
        $invoice = AssociateInvoice::where('subscriber_id', $subscriber->id)->findOrFail($request->id);

        $this->validate($request, [
            'associate_id' => 'required|exists:associates,id',
            'client_id' => 'required|exists:clients,id',
            'application_id' => 'required|exists:applications,id',
            'services' => 'required|array|min:1',
            'services.*' => 'in:Student Admission,Job Recruitment,Visa Processing,Finance,Other',
            'fees' => 'required|numeric|min:0',
            'status' => 'required|in:UnPaid,Paid,PartiallyPaid,Cancelled',
            'due_date' => 'nullable|string|max:20',
        ]);

        if ($error = $this->rejectFutureDate($request->due_date, 'due_date', 'Due date')) {
            return $error;
        }

        $this->ownedAssociates($subscriber)->findOrFail($request->associate_id);

        [$clientId, $clientName, $applicationId, $applicationName] = $this->resolveClientAndApplication($subscriber, $request);
        $services = $this->normalizeServices($request->services);

        $this->assertClientApplicationInvoiceAvailable(
            $subscriber,
            $clientId ? (int) $clientId : null,
            $applicationId ? (int) $applicationId : null,
            (int) $invoice->id
        );

        $this->assertBusinessLinkExists(
            $subscriber,
            (int) $request->associate_id,
            (int) $applicationId
        );

        $invoice->associate_id = $request->associate_id;
        $invoice->client_id = $clientId;
        $invoice->client_name = $clientName;
        $invoice->application_id = $applicationId;
        $invoice->application_name = $applicationName;
        $invoice->services = $services;
        $invoice->service_provided = $services; // keep legacy display column in sync
        $invoice->fees = $request->fees;
        $invoice->status = $request->status;
        // Normalize the picker's dd-mm-yyyy value to Y-m-d for the DATE column.
        $invoice->due_date = OfferBenefitService::normalizeStorageDate($request->due_date);
        $invoice->save();

        return redirect()->route('associate_invoices')->with('invoice_updated', "Invoice updated successfully.");
    }

    public function view_invoice($id)
    {
        $user = Auth::user();
        $subscriber = $this->subscriber();
        $page = "associates";

        $invoice = AssociateInvoice::where('subscriber_id', $subscriber->id)->findOrFail($id);
        $associate = Associate::find($invoice->associate_id);
        $payments = AssociatePayment::where('associate_invoice_id', $invoice->id)
            ->orderBy('payment_date', 'desc')->get();
        $invoiceSetting = Invoice_settings::forUser((int) $subscriber->id, Invoice_settings::RECIPIENT_ASSOCIATES);
        $document = $this->buildAssociateInvoiceDocumentData($invoice, $associate, $subscriber, $invoiceSetting);

        return view('web.associates.view_invoice', compact(
            'user',
            'invoice',
            'associate',
            'payments',
            'page',
            'subscriber',
            'invoiceSetting',
            'document'
        ));
    }

    public function print_invoice($id)
    {
        $user = Auth::user();
        $subscriber = $this->subscriber();
        $page = "associates";

        $invoice = AssociateInvoice::where('subscriber_id', $subscriber->id)->findOrFail($id);
        $associate = Associate::find($invoice->associate_id);
        $invoiceSetting = Invoice_settings::forUser((int) $subscriber->id, Invoice_settings::RECIPIENT_ASSOCIATES);
        $document = $this->buildAssociateInvoiceDocumentData($invoice, $associate, $subscriber, $invoiceSetting);

        return view('web.associates.print_invoice', compact(
            'user',
            'invoice',
            'associate',
            'page',
            'subscriber',
            'invoiceSetting',
            'document'
        ));
    }

    public function resend_invoice_email($id)
    {
        $subscriber = $this->subscriber();
        $invoice = AssociateInvoice::where('subscriber_id', $subscriber->id)->findOrFail($id);
        $associate = Associate::find($invoice->associate_id);
        $invoiceSetting = Invoice_settings::forUser((int) $subscriber->id, Invoice_settings::RECIPIENT_ASSOCIATES);

        if (!$associate || trim((string) $associate->email) === '') {
            return redirect()->route('view_associate_invoice', $invoice->id)
                ->with('invoice_email_error', 'Associate email is missing. Cannot send invoice.');
        }

        $sent = $this->sendAssociateInvoiceEmail($invoice, $associate, $subscriber, $invoiceSetting);

        if (!$sent['success']) {
            return redirect()->route('view_associate_invoice', $invoice->id)
                ->with('invoice_email_error', $sent['message']);
        }

        return redirect()->route('view_associate_invoice', $invoice->id)
            ->with('invoice_email_sent', 'Invoice emailed to ' . $sent['recipient'] . '.');
    }

    public function delete_invoice($id)
    {
        $subscriber = $this->subscriber();
        $invoice = AssociateInvoice::where('subscriber_id', $subscriber->id)->findOrFail($id);
        AssociatePayment::where('associate_invoice_id', $invoice->id)->delete();
        $invoice->delete();

        return redirect()->route('associate_invoices')->with('invoice_deleted', "Invoice deleted successfully.");
    }

    // ------------------------------------------------------------------
    // Tab 4 — Payments
    // ------------------------------------------------------------------

    public function payments()
    {
        $user = Auth::user();
        $subscriber = $this->subscriber();
        $page = "associates";

        $payments = AssociatePayment::where('subscriber_id', $subscriber->id)
            ->orderBy('created_at', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $invoiceFees = AssociateInvoice::where('subscriber_id', $subscriber->id)
            ->whereIn('id', $payments->pluck('associate_invoice_id')->filter()->unique()->values()->all())
            ->pluck('fees', 'id');

        $grouped = $payments->groupBy(function ($row) {
            return (string) ($row->associate_invoice_id ?: ($row->invoice_no ?: $row->id));
        });

        foreach ($grouped as $group) {
            $sorted = $group->values();
            $first = $sorted->first();
            $invoiceId = optional($first)->associate_invoice_id;
            $invoiceTotal = $invoiceId && isset($invoiceFees[$invoiceId])
                ? (float) $invoiceFees[$invoiceId]
                : (float) ($sorted->max('fees') ?: optional($first)->fees);
            $runningPaid = 0.0;

            foreach ($sorted as $index => $item) {
                $openingBalance = max(0, $invoiceTotal - $runningPaid);
                $runningPaid += (float) $item->paying;
                $item->invoice_total = round($invoiceTotal, 2);
                $item->opening_balance = round($openingBalance, 2);
                // Fees column mirrors AR/AP "Amount To Pay" 2-part format.
                $item->fees_display = $index === 0
                    ? number_format($invoiceTotal, 2, '.', '')
                    : number_format($openingBalance, 2, '.', '')
                        . '/'
                        . number_format($invoiceTotal, 2, '.', '');
                $item->outstanding_balance = round(max(0, $invoiceTotal - $runningPaid), 2);
            }
        }

        $payments = $payments->sortByDesc('created_at')->values();

        $associates = $this->ownedAssociates($subscriber)->get()->keyBy('id');
        $paymentModeFilters = TableFilterCountService::countBy(
            $payments,
            fn ($payment) => trim((string) ($payment->payment_mode ?? '')) ?: 'Unspecified'
        );
        $paymentOutstandingFilters = TableFilterCountService::countByOutstandingAmountRange(
            $payments,
            fn ($payment) => $payment->outstanding_balance
                ?? max(0, (float) $payment->fees - (float) $payment->paying)
        );

        return view('web.associates.payments', compact(
            'user',
            'payments',
            'associates',
            'page',
            'paymentModeFilters',
            'paymentOutstandingFilters'
        ));
    }

    public function add_payment()
    {
        $user = Auth::user();
        $subscriber = $this->subscriber();
        $page = "associates";

        // Invoices with at least 0.01 outstanding and not cancelled.
        $invoices = AssociateInvoice::where('subscriber_id', $subscriber->id)
            ->where('status', '!=', 'Cancelled')
            ->orderBy('created_at', 'desc')
            ->get()
            ->filter(function ($invoice) {
                return $invoice->outstanding >= 0.01;
            })
            ->values();

        $paymentCounts = AssociatePayment::where('subscriber_id', $subscriber->id)
            ->whereIn('associate_invoice_id', $invoices->pluck('id')->all())
            ->selectRaw('associate_invoice_id, COUNT(*) as payment_count')
            ->groupBy('associate_invoice_id')
            ->pluck('payment_count', 'associate_invoice_id');

        foreach ($invoices as $invoice) {
            $hasPayments = ((float) $invoice->paid) > 0
                || (int) ($paymentCounts[$invoice->id] ?? 0) > 0;
            // New = first payment against this invoice; Existing = further / partial payments.
            $invoice->payment_bucket = $hasPayments ? 'existing' : 'new';
            $invoice->display_label = $this->associateInvoicePaymentLabel($invoice);
        }

        return view('web.associates.add_payment', compact('user', 'invoices', 'page'));
    }

    /**
     * AJAX: return the auto-fill payload for a selected invoice.
     */
    public function invoice_details(Request $request)
    {
        $subscriber = $this->subscriber();
        $invoice = AssociateInvoice::where('subscriber_id', $subscriber->id)->find($request->invoice_id);

        if (!$invoice) {
            return response()->json(['error' => 'Invoice not found'], 404);
        }

        $associate = Associate::find($invoice->associate_id);

        return response()->json([
            'invoice_no' => $invoice->invoice_no,
            'associate_id' => $invoice->associate_id,
            'associate_name' => $associate ? $associate->name : '',
            'associate_code' => $associate ? $associate->associate_code : '',
            'client_id' => $invoice->client_id,
            'client_name' => $invoice->client_name,
            'application_id' => $invoice->application_id,
            'application_name' => $invoice->application_name,
            'service_provided' => $invoice->services ?: $invoice->service_provided,
            'services' => $invoice->services ?: $invoice->service_provided,
            'fees' => number_format((float) $invoice->fees, 2, '.', ''),
            'paid' => number_format((float) $invoice->paid, 2, '.', ''),
            'outstanding' => number_format($invoice->outstanding, 2, '.', ''),
        ]);
    }

    public function store_payment(Request $request)
    {
        $subscriber = $this->subscriber();

        // Site date pickers submit d-m-Y; MySQL DATE columns need Y-m-d.
        $normalizedPaymentDate = OfferBenefitService::normalizeStorageDate($request->payment_date);
        $request->merge(['payment_date' => $normalizedPaymentDate]);

        $this->validate($request, [
            'associate_invoice_id' => 'required|exists:associate_invoices,id',
            'paying' => 'required|numeric|min:0.01',
            'payment_mode' => 'required|string|max:100',
            'payment_date' => 'required|date_format:Y-m-d',
        ]);

        if ($error = $this->rejectFutureDate($normalizedPaymentDate, 'payment_date', 'Payment date')) {
            return $error;
        }

        $invoice = AssociateInvoice::where('subscriber_id', $subscriber->id)
            ->findOrFail($request->associate_invoice_id);

        if ($invoice->status === 'Cancelled') {
            return redirect()->route('associate_payments')
                ->with('payment_error', 'Cannot record a payment against a cancelled invoice.');
        }

        $outstanding = $invoice->outstanding;
        if ($outstanding < 0.01) {
            return redirect()->route('add_associate_payment')
                ->withErrors(['associate_invoice_id' => 'Selected invoice has no outstanding balance to pay.'])
                ->withInput();
        }

        $paying = min((float) $request->paying, $outstanding);

        $payment = new AssociatePayment();
        $payment->subscriber_id = $subscriber->id;
        $payment->associate_invoice_id = $invoice->id;
        $payment->invoice_no = $invoice->invoice_no;
        $payment->associate_id = $invoice->associate_id;
        $payment->client_id = $invoice->client_id;
        $payment->client_name = $invoice->client_name;
        $payment->application_id = $invoice->application_id;
        $payment->application_name = $invoice->application_name;
        $payment->service_provided = $invoice->service_provided;
        $payment->services = $invoice->services ?: $invoice->service_provided;
        $payment->fees = $invoice->fees;
        $payment->paying = $paying;
        $payment->payment_mode = $request->payment_mode;
        $payment->payment_date = $normalizedPaymentDate;
        $payment->save();

        // Roll the payment up onto the invoice and recompute its status.
        $invoice->paid = (float) $invoice->paid + $paying;
        if ($invoice->paid >= (float) $invoice->fees) {
            $invoice->paid = (float) $invoice->fees;
            $invoice->status = 'Paid';
        } elseif ($invoice->paid > 0) {
            $invoice->status = 'PartiallyPaid';
        } else {
            $invoice->status = 'UnPaid';
        }
        $invoice->save();

        return redirect()->route('associate_payments')->with('payment_added', "Payment recorded successfully.");
    }

    public function view_payment($id)
    {
        $user = Auth::user();
        $subscriber = $this->subscriber();
        $page = "associates";

        $payment = AssociatePayment::where('subscriber_id', $subscriber->id)->findOrFail($id);
        $associate = Associate::find($payment->associate_id);
        $invoice = AssociateInvoice::find($payment->associate_invoice_id);

        return view('web.associates.view_payment', compact('user', 'payment', 'associate', 'invoice', 'page'));
    }

    public function delete_payment($id)
    {
        $subscriber = $this->subscriber();
        $payment = AssociatePayment::where('subscriber_id', $subscriber->id)->findOrFail($id);

        // Reverse the payment from the invoice.
        $invoice = AssociateInvoice::find($payment->associate_invoice_id);
        if ($invoice) {
            $invoice->paid = max(0, (float) $invoice->paid - (float) $payment->paying);
            if ($invoice->status !== 'Cancelled') {
                if ($invoice->paid >= (float) $invoice->fees && (float) $invoice->fees > 0) {
                    $invoice->status = 'Paid';
                } elseif ($invoice->paid > 0) {
                    $invoice->status = 'PartiallyPaid';
                } else {
                    $invoice->status = 'UnPaid';
                }
            }
            $invoice->save();
        }

        $payment->delete();

        return redirect()->route('associate_payments')->with('payment_deleted', "Payment deleted successfully.");
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    private function clientFirstName(?string $clientName): string
    {
        $clientName = trim((string) $clientName);
        if ($clientName === '') {
            return 'No client';
        }

        $parts = preg_split('/\s+/', $clientName);

        return $parts[0] ?? $clientName;
    }

    private function associateInvoicePaymentLabel(AssociateInvoice $invoice): string
    {
        $applicationOrService = trim((string) (
            $invoice->application_name
            ?: $invoice->services
            ?: $invoice->service_provided
            ?: 'Service'
        ));

        return (string) $invoice->id
            . ' - '
            . $this->clientFirstName($invoice->client_name)
            . ' - '
            . $applicationOrService;
    }

    /**
     * Build the same document payload shape used by client invoices
     * (partials.invoice_document_core via $data), billed to the Associate.
     */
    private function buildAssociateInvoiceDocumentData(
        AssociateInvoice $invoice,
        ?Associate $associate,
        User $subscriber,
        ?Invoice_settings $invoiceSetting
    ): \stdClass {
        $fees = round((float) $invoice->fees, 2);
        $discountPercent = (float) ($invoiceSetting?->discount ?? 0);
        $taxPercent = (float) ($invoiceSetting?->tax ?? 0);
        $discountAmount = round($fees * ($discountPercent / 100), 2);
        $taxable = $fees - $discountAmount;
        $taxAmount = round($taxable * ($taxPercent / 100), 2);
        $total = round($taxable + $taxAmount, 2);

        $detail = $this->formatAssociateInvoiceDetail($invoice);

        $companyName = trim((string) ($subscriber->organization ?: $subscriber->name ?: 'Adwiseri'));
        $logoFilename = trim((string) ($subscriber->organization_logo ?? ''));
        $issuerLogo = \App\Support\InvoiceIssuerLogo::resolveForSubscriber($subscriber, $logoFilename);
        $logoPath = $issuerLogo['disk_path'];
        $logoUrl = $issuerLogo['url'];
        $logoRelativePath = $issuerLogo['relative_path'];

        $qrFilename = trim((string) ($invoiceSetting?->payment_qr_code ?? ''));
        $qrPath = null;
        $qrUrl = null;
        if ($qrFilename !== '') {
            $qrDiskPath = public_path('web_assets/users/user' . $subscriber->id . '/' . $qrFilename);
            if (file_exists($qrDiskPath)) {
                $qrPath = $qrDiskPath;
                $qrUrl = asset('web_assets/users/user' . $subscriber->id . '/' . $qrFilename);
            }
        }

        $billName = $associate
            ? trim((string) ($associate->name ?: $associate->organization ?: 'Associate'))
            : 'Associate';

        $data = new \stdClass();
        $data->name = $billName;
        $data->to_email = $associate?->email ?? '';
        $data->to_address = '';
        $data->to_city = $associate?->city ?? '';
        $data->to_state = $associate?->state ?? '';
        $data->to_country = $associate?->country ?? '';
        $data->to_pincode = $associate?->pincode ?? '';
        $data->company_name = $companyName;
        $data->subscriber_name = $companyName;
        $data->from_name = $companyName;
        $data->email = $subscriber->email ?? '';
        $data->subscriber_email = $subscriber->email ?? '';
        $data->reply_to_email = $subscriber->email ?? '';
        $data->reply_to_name = $companyName;
        $data->logo = $logoFilename;
        $data->logo_path = $logoRelativePath;
        $data->logo_url = $logoUrl;
        $data->detail = $detail;
        $data->amount = $fees;
        $data->items = [[
            'detail' => $detail,
            'amount' => $fees,
        ]];
        $data->discount = $discountPercent;
        $data->tax = $taxPercent;
        $data->tax_label = Invoice_settings::resolveTaxLabel($invoiceSetting?->tax_label ?? null);
        $data->export_service_tax_exempt = false;
        $data->total = $total;
        $data->currency = trim((string) ($subscriber->currency ?: 'Rs.'));
        $data->status = $invoice->status;
        $data->invoice_no = $invoice->invoice_no;
        $data->invoice_date = $invoice->created_at;
        $data->due_date = $invoice->due_date;
        $data->invoice_note = trim((string) ($invoiceSetting?->invoice_note ?? ''));
        $data->payment_link = trim((string) ($invoiceSetting?->payment_link ?? ''));
        $data->payment_qr_path = $qrPath;
        $data->payment_qr_url = $qrUrl;
        $data->subscriber_id = $subscriber->id;
        $data->is_adwiseri = BrandedMail::isPlatformBrand($companyName);
        $data->message = 'Please find attached your invoice from ' . $companyName . '.';

        return $data;
    }

    /**
     * Email associate the same branded invoice + PDF used for client invoices.
     *
     * @return array{success:bool,message:string,recipient:?string}
     */
    private function sendAssociateInvoiceEmail(
        AssociateInvoice $invoice,
        ?Associate $associate,
        User $subscriber,
        ?Invoice_settings $invoiceSetting
    ): array {
        $toEmail = trim((string) ($associate->email ?? ''));
        if ($toEmail === '' || !filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
            return [
                'success' => false,
                'message' => 'Associate email is missing or invalid.',
                'recipient' => null,
            ];
        }

        $document = $this->buildAssociateInvoiceDocumentData($invoice, $associate, $subscriber, $invoiceSetting);
        $document->from_name = BrandedMail::sentOnBehalfOf($document->company_name ?? 'Subscriber');
        $document->subscriber_email = $subscriber->email ?? '';
        $document->reply_to_email = $subscriber->email ?? '';
        $document->reply_to_name = $document->company_name ?? 'Subscriber';

        try {
            BrandedMail::sendWithAlertsArchive($toEmail, fn () => new Invoicemail($document));
            $archive = BrandedMail::alertsBccRecipients();
            $archiveText = empty($archive) ? 'none' : implode(', ', $archive);

            return [
                'success' => true,
                'message' => 'Invoice emailed successfully (archive copy to ' . $archiveText . ').',
                'recipient' => $toEmail,
            ];
        } catch (\Throwable $e) {
            report($e);
            return [
                'success' => false,
                'message' => 'Unable to send invoice email. Please try again.',
                'recipient' => $toEmail,
            ];
        }
    }

    private function logActivity(User $subscriber, string $name, string $detail, $localTime = null): void
    {
        $activity = new Activities();
        $activity->subscriber_id = $subscriber->id;
        $activity->user_id = $subscriber->id;
        $activity->user_name = $subscriber->name;
        $activity->activity_name = $name;
        $activity->activity_detail = $detail;
        $activity->activity_icon = "user.png";
        $activity->local_time = $localTime;
        $activity->save();
    }
}
