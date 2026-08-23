<?php

namespace App\Http\Controllers;

use App\Models\Activities;
use App\Models\Applications;
use App\Models\ClientAccount;
use App\Models\Clients;
use App\Models\User;
use App\Models\UserRoles;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientAccountController extends Controller
{
    protected function resolveSubscriberId($user): int
    {
        return $user->user_type === 'Subscriber'
            ? (int) $user->id
            : (int) $user->added_by;
    }

    protected function resolveClients($user)
    {
        if ($user->user_type === 'Subscriber') {
            return Clients::where('subscriber_id', $user->id)->orderBy('name')->get();
        }

        return Clients::where('user_id', $user->id)->orderBy('name')->get();
    }

    protected function clientBelongsToUser($user, int $clientId): bool
    {
        $client = Clients::find($clientId);
        if (!$client) {
            return false;
        }

        if ($user->user_type === 'Subscriber') {
            return (int) $client->subscriber_id === (int) $user->id;
        }

        return (int) $client->user_id === (int) $user->id;
    }

    protected function findAccountForUser($user, int $id): ?ClientAccount
    {
        return ClientAccount::where('subscriber_id', $this->resolveSubscriberId($user))
            ->where('id', $id)
            ->first();
    }

    protected function accountsQueryForCombo(int $subscriberId, int $clientId, ?int $applicationId)
    {
        $query = ClientAccount::where('subscriber_id', $subscriberId)
            ->where('client_id', $clientId);

        if ($applicationId) {
            $query->where('application_id', $applicationId);
        } else {
            $query->whereNull('application_id');
        }

        return $query;
    }

    protected function getComboTotals(int $subscriberId, int $clientId, ?int $applicationId): array
    {
        $rows = $this->accountsQueryForCombo($subscriberId, $clientId, $applicationId)->get(['trans_type', 'amount']);

        $credit = 0.0;
        $debit = 0.0;
        foreach ($rows as $row) {
            if (strcasecmp((string) $row->trans_type, 'Credit') === 0) {
                $credit += (float) $row->amount;
            } else {
                $debit += (float) $row->amount;
            }
        }

        $credit = round($credit, 2);
        $debit = round($debit, 2);

        return [
            'credit' => $credit,
            'debit' => $debit,
            'balance' => round($credit - $debit, 2),
        ];
    }

    protected function recalculateBalances(int $clientId, ?int $applicationId, int $subscriberId): void
    {
        $transactions = $this->accountsQueryForCombo($subscriberId, $clientId, $applicationId)
            ->orderBy('transaction_date')
            ->orderBy('id')
            ->get();

        $balance = 0.0;
        foreach ($transactions as $transaction) {
            $transaction->prev_balance = $balance;
            if (strcasecmp($transaction->trans_type, 'Credit') === 0) {
                $balance += (float) $transaction->amount;
            } else {
                $balance -= (float) $transaction->amount;
            }
            $transaction->total = round($balance, 2);
            $transaction->saveQuietly();
        }
    }

    protected function resolveDescription(Request $request): string
    {
        $description = trim((string) $request->input('description', ''));
        if ($description === 'Other') {
            $other = trim((string) $request->input('description_other', ''));
            return $other !== '' ? $other : 'Other';
        }

        return $description;
    }

    protected function validateDescriptionForTransType(Request $request): void
    {
        $allowed = ClientAccount::descriptionTypesForTransType((string) $request->input('trans_type', ''));

        if (!in_array($request->input('description'), $allowed, true)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'description' => 'The selected description is not valid for the chosen transaction type.',
            ]);
        }

        if ($request->input('description') === 'Other' && trim((string) $request->input('description_other', '')) === '') {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'description_other' => 'Please specify the other description.',
            ]);
        }
    }

    protected function checkMembership($user)
    {
        if ($user->user_type === 'admin') {
            return null;
        }

        $expiryUser = $user->user_type === 'Subscriber'
            ? $user
            : User::find($user->added_by);

        if (!$expiryUser || (new DateTime($expiryUser->membership_expiry_date)) < (new DateTime('now'))) {
            return redirect()->route('user_membership')
                ->with('price_plan_expiry', 'Please renew or upgrade your subscription plan.');
        }

        return null;
    }

    protected function logActivity(int $subscriberId, string $activityName, string $detail, ?string $localTime = null): void
    {
        $activity = new Activities();
        $activity->subscriber_id = $subscriberId;
        $activity->user_id = Auth::id();
        $activity->user_name = Auth::user()->name;
        $activity->activity_name = $activityName;
        $activity->activity_detail = $detail;
        $activity->activity_icon = 'invoice.jpg';
        $activity->local_time = $localTime;
        $activity->save();
    }

    protected function buildAccountFilterData(int $subscriberId): array
    {
        $accountMeta = ClientAccount::where('subscriber_id', $subscriberId)
            ->select('client_id', 'application_id')
            ->get();

        if ($accountMeta->isEmpty()) {
            return [
                'clients' => collect(),
                'applicationsByClient' => [],
            ];
        }

        $clientIds = $accountMeta->pluck('client_id')->unique()->filter()->values();
        $clients = Clients::whereIn('id', $clientIds)->orderBy('name')->get();

        $applicationsByClient = [];
        foreach ($accountMeta->groupBy('client_id') as $clientId => $rows) {
            $appIds = $rows->pluck('application_id')->unique()->filter()->values();
            $applications = Applications::whereIn('id', $appIds)
                ->orderBy('application_name')
                ->get(['id', 'application_name']);

            $applicationsByClient[(int) $clientId] = $applications->map(function ($application) {
                return [
                    'id' => (int) $application->id,
                    'name' => $application->application_name,
                ];
            })->values()->all();
        }

        return [
            'clients' => $clients,
            'applicationsByClient' => $applicationsByClient,
        ];
    }

    protected function accountsQueryForFilters(int $subscriberId, int $clientId, ?int $applicationId = null)
    {
        $query = ClientAccount::with(['client', 'application'])
            ->where('subscriber_id', $subscriberId)
            ->where('client_id', $clientId);

        if ($applicationId) {
            $query->where('application_id', $applicationId);
        }

        return $query->orderBy('transaction_date')->orderBy('id');
    }

    protected function buildPdfAccountGroups($accounts, bool $allApplications): array
    {
        if (!$allApplications) {
            return [[
                'label' => null,
                'accounts' => $accounts,
            ]];
        }

        $groups = [];
        foreach ($accounts->groupBy('application_id') as $applicationId => $groupAccounts) {
            $sorted = $groupAccounts->sortBy([
                ['transaction_date', 'asc'],
                ['id', 'asc'],
            ])->values();

            if ($applicationId) {
                $application = $sorted->first()->application;
                $label = ($application ? $application->application_name : 'Application') . ' (' . $applicationId . ')';
                $withoutApplication = false;
            } else {
                $label = 'Transactions W/O Applications';
                $withoutApplication = true;
            }

            $groups[] = [
                'label' => $label,
                'accounts' => $sorted,
                'without_application' => $withoutApplication,
            ];
        }

        usort($groups, function ($a, $b) {
            if ($a['without_application'] !== $b['without_application']) {
                return $a['without_application'] ? 1 : -1;
            }

            return strcmp((string) $a['label'], (string) $b['label']);
        });

        return $groups;
    }

    public function index()
    {
        $user = Auth::user();
        $redirect = $this->checkMembership($user);
        if ($redirect) {
            return $redirect;
        }

        $subscriberId = $this->resolveSubscriberId($user);
        $accounts = ClientAccount::with(['client', 'application'])
            ->where('subscriber_id', $subscriberId)
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->get();

        $filterData = $this->buildAccountFilterData($subscriberId);
        $clientsWithAccounts = $filterData['clients'];
        $applicationsByClient = $filterData['applicationsByClient'];
        $clientCount = $user->user_type === 'Subscriber'
            ? Clients::where('subscriber_id', $subscriberId)->count()
            : Clients::where('user_id', $user->id)->count();
        $clientRoles = UserRoles::where('user_id', $user->id)->where('module', 'Clients')->first();
        $page = 'clients';

        return view('web.client_accounts', compact(
            'user',
            'accounts',
            'clientsWithAccounts',
            'applicationsByClient',
            'page',
            'clientRoles',
            'clientCount'
        ));
    }

    public function downloadPdf(Request $request)
    {
        $user = Auth::user();
        $redirect = $this->checkMembership($user);
        if ($redirect) {
            return $redirect;
        }

        $request->validate([
            'client_id' => 'required|integer|exists:clients,id',
            'application_id' => 'nullable|integer|exists:applications,id',
        ]);

        if (!$this->clientBelongsToUser($user, (int) $request->client_id)) {
            abort(403);
        }

        $subscriberId = $this->resolveSubscriberId($user);
        $allApplications = !$request->filled('application_id');
        $accounts = $this->accountsQueryForFilters(
            $subscriberId,
            (int) $request->client_id,
            $request->filled('application_id') ? (int) $request->application_id : null
        )->get();

        if ($accounts->isEmpty()) {
            return redirect()->route('client_accounts')
                ->with('client_account_error', 'No account entries found for the selected client/application combination.');
        }

        $client = Clients::find($request->client_id);
        $application = $request->filled('application_id')
            ? Applications::find($request->application_id)
            : null;

        $clientLabel = $client
            ? $client->name . ' (' . $client->id . ')'
            : '—';
        $applicationLabel = $application
            ? $application->application_name . ' (' . $application->id . ')'
            : 'All Applications';

        $accountGroups = $this->buildPdfAccountGroups($accounts, $allApplications);

        $generatedAt = now();
        if (!empty($user->timezone)) {
            try {
                $generatedAt = now()->timezone($user->timezone);
            } catch (\Exception $e) {
            }
        }
        $generatedAt = $generatedAt->format('d-m-Y H:i');

        $fileSlug = preg_replace('/[^a-z0-9]+/i', '-', strtolower($client->name ?? 'client'));
        $fileName = 'client-accounts-report-' . $fileSlug . '.pdf';

        $pdf = Pdf::loadView('web.client_accounts_pdf', compact(
            'accountGroups',
            'clientLabel',
            'applicationLabel',
            'allApplications',
            'generatedAt',
            'user'
        ))
            ->setPaper('a4', 'landscape')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isPhpEnabled', true);

        return $pdf->download($fileName);
    }

    public function create()
    {
        $user = Auth::user();
        $redirect = $this->checkMembership($user);
        if ($redirect) {
            return $redirect;
        }

        $clients = $this->resolveClients($user);
        if ($redirect = \App\Support\NoClientGuard::redirectIfNoClients($user)) {
            return $redirect;
        }
        $creditDescriptionTypes = ClientAccount::CREDIT_DESCRIPTION_TYPES;
        $debitDescriptionTypes = ClientAccount::debitDescriptionTypes();
        $page = 'clients';

        return view('web.add_client_account', compact(
            'user',
            'clients',
            'creditDescriptionTypes',
            'debitDescriptionTypes',
            'page'
        ));
    }

    public function balance(Request $request)
    {
        $user = Auth::user();
        $redirect = $this->checkMembership($user);
        if ($redirect) {
            return $redirect;
        }

        $request->validate([
            'client_id' => 'required|integer|exists:clients,id',
            'application_id' => 'nullable|integer|exists:applications,id',
        ]);

        if (!$this->clientBelongsToUser($user, (int) $request->client_id)) {
            abort(403);
        }

        $totals = $this->getComboTotals(
            $this->resolveSubscriberId($user),
            (int) $request->client_id,
            $request->filled('application_id') ? (int) $request->application_id : null
        );

        return response()->json($totals);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $redirect = $this->checkMembership($user);
        if ($redirect) {
            return $redirect;
        }

        $request->validate([
            'client_id' => 'required|integer|exists:clients,id',
            'application_id' => 'nullable|integer|exists:applications,id',
            'trans_type' => 'required|in:Credit,Debit',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:255',
            'description_other' => 'nullable|string|max:255',
            'transaction_date' => 'required|date_format:d-m-Y',
        ]);

        $this->validateDescriptionForTransType($request);

        if (!$this->clientBelongsToUser($user, (int) $request->client_id)) {
            abort(403);
        }

        $subscriberId = $this->resolveSubscriberId($user);
        $description = $this->resolveDescription($request);

        ClientAccount::create([
            'subscriber_id' => $subscriberId,
            'user_id' => $user->id,
            'client_id' => $request->client_id,
            'application_id' => $request->application_id ?: null,
            'trans_type' => $request->trans_type,
            'amount' => $request->amount,
            'description' => $description,
            'transaction_date' => Carbon::createFromFormat('d-m-Y', $request->transaction_date)->format('Y-m-d'),
            'trans_by' => $user->name,
        ]);

        $this->recalculateBalances(
            (int) $request->client_id,
            $request->application_id ? (int) $request->application_id : null,
            $subscriberId
        );

        $this->logActivity(
            $subscriberId,
            'Client account record added',
            'Client account ' . strtolower($request->trans_type) . ' record added by ' . $user->name . ' at ' . $request->local_time,
            $request->local_time
        );

        return redirect()->route('client_accounts')
            ->with('client_account_success', 'Account entry created successfully.');
    }

    public function show($id)
    {
        $user = Auth::user();
        $account = $this->findAccountForUser($user, (int) $id);
        if (!$account) {
            abort(404);
        }

        $account->load(['client', 'application']);
        $clientRoles = UserRoles::where('user_id', $user->id)->where('module', 'Clients')->first();
        $page = 'clients';

        return view('web.view_client_account', compact('user', 'account', 'page', 'clientRoles'));
    }

    public function edit($id)
    {
        $user = Auth::user();
        $redirect = $this->checkMembership($user);
        if ($redirect) {
            return $redirect;
        }

        $account = $this->findAccountForUser($user, (int) $id);
        if (!$account) {
            abort(404);
        }

        $clients = $this->resolveClients($user);
        $applications = Applications::where('client_id', $account->client_id)->get(['id', 'application_name']);
        $creditDescriptionTypes = ClientAccount::CREDIT_DESCRIPTION_TYPES;
        $debitDescriptionTypes = ClientAccount::debitDescriptionTypes();
        $clientRoles = UserRoles::where('user_id', $user->id)->where('module', 'Clients')->first();
        $page = 'clients';

        $selectedDescription = ClientAccount::normalizeDescriptionOption($account->description);
        $descriptionOther = $selectedDescription === 'Other' ? $account->description : '';

        return view('web.edit_client_account', compact(
            'user',
            'account',
            'clients',
            'applications',
            'creditDescriptionTypes',
            'debitDescriptionTypes',
            'page',
            'clientRoles',
            'selectedDescription',
            'descriptionOther'
        ));
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $redirect = $this->checkMembership($user);
        if ($redirect) {
            return $redirect;
        }

        $account = $this->findAccountForUser($user, (int) $id);
        if (!$account) {
            abort(404);
        }

        $request->validate([
            'client_id' => 'required|integer|exists:clients,id',
            'application_id' => 'nullable|integer|exists:applications,id',
            'trans_type' => 'required|in:Credit,Debit',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:255',
            'description_other' => 'nullable|string|max:255',
            'transaction_date' => 'required|date_format:d-m-Y',
        ]);

        $this->validateDescriptionForTransType($request);

        if (!$this->clientBelongsToUser($user, (int) $request->client_id)) {
            abort(403);
        }

        $subscriberId = $this->resolveSubscriberId($user);
        $oldClientId = (int) $account->client_id;
        $oldApplicationId = $account->application_id ? (int) $account->application_id : null;

        $account->update([
            'client_id' => $request->client_id,
            'application_id' => $request->application_id ?: null,
            'trans_type' => $request->trans_type,
            'amount' => $request->amount,
            'description' => $this->resolveDescription($request),
            'transaction_date' => Carbon::createFromFormat('d-m-Y', $request->transaction_date)->format('Y-m-d'),
            'trans_by' => $user->name,
        ]);

        $this->recalculateBalances($oldClientId, $oldApplicationId, $subscriberId);

        $newClientId = (int) $request->client_id;
        $newApplicationId = $request->application_id ? (int) $request->application_id : null;
        if ($newClientId !== $oldClientId || $newApplicationId !== $oldApplicationId) {
            $this->recalculateBalances($newClientId, $newApplicationId, $subscriberId);
        }

        $this->logActivity(
            $subscriberId,
            'Client account record updated',
            'Client account record #' . $account->id . ' updated by ' . $user->name . ' at ' . $request->local_time,
            $request->local_time
        );

        return redirect()->route('client_accounts')
            ->with('client_account_success', 'Account entry updated successfully.');
    }

    public function destroy($id, $localtime = null)
    {
        $user = Auth::user();
        $account = $this->findAccountForUser($user, (int) $id);
        if (!$account) {
            abort(404);
        }

        $subscriberId = $this->resolveSubscriberId($user);
        $clientId = (int) $account->client_id;
        $applicationId = $account->application_id ? (int) $account->application_id : null;
        $accountId = $account->id;

        $account->delete();

        $this->recalculateBalances($clientId, $applicationId, $subscriberId);

        $this->logActivity(
            $subscriberId,
            'Client account record deleted',
            'Client account record #' . $accountId . ' deleted by ' . $user->name . ' at ' . $localtime,
            $localtime
        );

        return redirect()->route('client_accounts')
            ->with('client_account_success', 'Account entry deleted successfully.');
    }
}
