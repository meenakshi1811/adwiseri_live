<?php

namespace App\Services;

use App\Models\Clients;
use App\Models\LeadFollowUpLog;
use App\Models\User;
use App\Models\VisaEnquiry;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LeadEnquiryService
{
    public const SOURCES = [
        'Walk-in',
        'Social Media',
        'Lead Generation',
        'Affiliates',
        'Website',
        'Events',
    ];

    public const STATUSES = [
        'Open',
        'Contacted',
        'Followup',
        'Converted',
        'Closed',
        'Reopen',
    ];

    private const SOURCE_ALIASES = [
        'walk-in' => 'Walk-in',
        'walkin' => 'Walk-in',
        'walk_in' => 'Walk-in',
        'enquiry' => 'Walk-in',
        'enquiries' => 'Walk-in',
        'office' => 'Walk-in',
        'social media' => 'Social Media',
        'social_media' => 'Social Media',
        'social-media' => 'Social Media',
        'facebook' => 'Social Media',
        'instagram' => 'Social Media',
        'linkedin' => 'Social Media',
        'twitter' => 'Social Media',
        'x' => 'Social Media',
        'lead generation' => 'Lead Generation',
        'lead_generation' => 'Lead Generation',
        'lead-generation' => 'Lead Generation',
        'lead gen' => 'Lead Generation',
        'leadgen' => 'Lead Generation',
        'affiliate' => 'Affiliates',
        'affiliates' => 'Affiliates',
        'referral' => 'Affiliates',
        'referrals' => 'Affiliates',
        'website' => 'Website',
        'web' => 'Website',
        'online' => 'Website',
        'qr' => 'Website',
        'qr code' => 'Website',
        'form' => 'Walk-in',
        'enquiry form' => 'Walk-in',
        'event' => 'Events',
        'events' => 'Events',
        'exhibition' => 'Events',
        'seminar' => 'Events',
    ];

    public function sources(): array
    {
        return self::SOURCES;
    }

    public function statuses(): array
    {
        return self::STATUSES;
    }

    public function normalizeSource(?string $source, string $default = 'Walk-in'): string
    {
        $value = trim(strtolower((string) $source));

        if ($value === '') {
            return $default;
        }

        if (isset(self::SOURCE_ALIASES[$value])) {
            return self::SOURCE_ALIASES[$value];
        }

        foreach (self::SOURCES as $canonicalSource) {
            if (strcasecmp($canonicalSource, (string) $source) === 0) {
                return $canonicalSource;
            }
        }

        return $default;
    }

    public function normalizeStatus(?string $status, string $default = 'Open'): string
    {
        $value = trim(strtolower((string) $status));

        if ($value === '') {
            return $default;
        }

        foreach (self::STATUSES as $canonicalStatus) {
            if (strcasecmp($canonicalStatus, (string) $status) === 0 || strcasecmp($canonicalStatus, $value) === 0) {
                return $canonicalStatus;
            }
        }

        if (in_array($value, ['follow-up', 'follow up', 'follow_up'], true)) {
            return 'Followup';
        }

        if (in_array($value, ['re-open', 're open', 're_open'], true)) {
            return 'Reopen';
        }

        return $default;
    }

    public function resolveSourceFromRequest(Request $request, bool $isStaffEntry = false): string
    {
        $explicitSource = $request->input('lead_source')
            ?: $request->query('source')
            ?: $request->query('lead_source');

        if (!empty($explicitSource)) {
            return $this->normalizeSource($explicitSource, 'Walk-in');
        }

        return 'Walk-in';
    }

    public function leadFollowUpFieldsForEnquiryForm(Request $request, ?User $actingUser = null): array
    {
        return $this->leadFollowUpFieldsForCreate($request, $actingUser, true);
    }

    public function leadFollowUpFieldsForCreate(Request $request, ?User $actingUser = null, bool $isStaffEntry = false): array
    {
        $fields = [
            'lead_source' => $this->resolveSourceFromRequest($request, $isStaffEntry),
            'lead_status' => $this->normalizeStatus($request->input('lead_status'), 'Open'),
        ];

        if ($actingUser) {
            $fields['lead_worked_by_user_id'] = $actingUser->id;
            $fields['lead_worked_at'] = now();
        }

        return $fields;
    }

    /**
     * Normalize external lead payloads (affiliates, social campaigns, imports, etc.)
     * into visa_enquiries column format.
     */
    public function buildEnquiryPayloadFromExternalSource(array $external, int $subscriberId, ?User $actingUser = null): array
    {
        $fullName = trim((string) ($external['full_name'] ?? $external['name'] ?? $external['client_name'] ?? ''));
        $email = trim((string) ($external['email'] ?? $external['email_address'] ?? ''));
        $phone = trim((string) ($external['contact_no'] ?? $external['phone'] ?? $external['mobile'] ?? $external['contact'] ?? ''));
        $address = trim((string) ($external['address'] ?? $external['location'] ?? 'Not provided'));
        $country = trim((string) ($external['country'] ?? $external['home_country'] ?? ''));
        $visaCategory = trim((string) ($external['visa_category'] ?? $external['service'] ?? $external['visa_type'] ?? 'General Enquiry'));

        $countryPreferences = collect($external['country_pref'] ?? $external['country_preferences'] ?? [])
            ->when(empty($external['country_pref'] ?? null) && empty($external['country_preferences'] ?? null), function ($collection) use ($external) {
                return collect([
                    $external['country_pref_1'] ?? null,
                    $external['country_pref_2'] ?? null,
                    $external['country_pref_3'] ?? null,
                    $external['destination_country'] ?? null,
                    $external['preferred_country'] ?? null,
                ]);
            })
            ->map(fn ($countryName) => trim((string) $countryName))
            ->filter()
            ->unique()
            ->values();

        $payload = [
            'subscriber_id' => $subscriberId,
            'full_name' => $fullName !== '' ? $fullName : 'Unknown Lead',
            'email' => $email !== '' ? $email : ('lead-' . uniqid() . '@placeholder.local'),
            'contact_no' => $phone !== '' ? $phone : '0000000000',
            'address' => $address !== '' ? $address : 'Not provided',
            'country' => $country !== '' ? $country : 'Not specified',
            'country_pref_1' => $countryPreferences[0] ?? ($country !== '' ? $country : 'Not specified'),
            'country_pref_2' => $countryPreferences[1] ?? null,
            'country_pref_3' => $countryPreferences[2] ?? null,
            'visa_category' => $visaCategory !== '' ? $visaCategory : 'General Enquiry',
            'lead_source' => $this->normalizeSource($external['lead_source'] ?? $external['source'] ?? null),
            'lead_status' => $this->normalizeStatus($external['lead_status'] ?? $external['status'] ?? null, 'Open'),
        ];

        if ($actingUser) {
            $payload['lead_worked_by_user_id'] = $actingUser->id;
            $payload['lead_worked_at'] = now();
        } elseif (!empty($external['lead_worked_by_user_id'])) {
            $payload['lead_worked_by_user_id'] = (int) $external['lead_worked_by_user_id'];
            $payload['lead_worked_at'] = !empty($external['lead_worked_at'])
                ? Carbon::parse($external['lead_worked_at'])
                : now();
        }

        return $payload;
    }

    public function createFromExternalSource(array $external, int $subscriberId, ?User $actingUser = null): VisaEnquiry
    {
        $enquiry = VisaEnquiry::create(
            $this->buildEnquiryPayloadFromExternalSource($external, $subscriberId, $actingUser)
        );

        if ($actingUser || !empty($enquiry->lead_worked_at)) {
            $this->recordFollowUpLog(
                $enquiry,
                $actingUser,
                $this->buildInitialFollowUpDescription($enquiry),
                null,
                $enquiry->lead_worked_at
            );
        }

        return $enquiry;
    }

    public function applyLeadFollowUpUpdate(VisaEnquiry $enquiry, array $input, User $actingUser): VisaEnquiry
    {
        $before = [
            'lead_source' => (string) ($enquiry->lead_source ?: 'Walk-in'),
            'lead_status' => (string) ($enquiry->lead_status ?: 'Open'),
            'lead_worked_by_user_id' => $enquiry->lead_worked_by_user_id,
        ];

        if (array_key_exists('lead_source', $input)) {
            $enquiry->lead_source = $this->normalizeSource($input['lead_source'], (string) ($enquiry->lead_source ?: 'Walk-in'));
        }

        if (array_key_exists('lead_status', $input)) {
            $enquiry->lead_status = $this->normalizeStatus($input['lead_status'], (string) ($enquiry->lead_status ?: 'Open'));
        }

        if (array_key_exists('lead_worked_by_user_id', $input)) {
            $enquiry->lead_worked_by_user_id = !empty($input['lead_worked_by_user_id'])
                ? (int) $input['lead_worked_by_user_id']
                : null;
        } else {
            $enquiry->lead_worked_by_user_id = $actingUser->id;
        }

        $enquiry->lead_worked_at = now();
        $enquiry->save();

        $after = [
            'lead_source' => (string) $enquiry->lead_source,
            'lead_status' => (string) $enquiry->lead_status,
            'lead_worked_by_user_id' => $enquiry->lead_worked_by_user_id,
        ];

        $this->recordFollowUpLog(
            $enquiry->fresh(['workedByUser']),
            $actingUser,
            $this->buildFollowUpChangeDescription($before, $after, $enquiry->fresh(['workedByUser'])),
            $this->resolveClientIdForEnquiry($enquiry)
        );

        return $enquiry->fresh(['workedByUser']);
    }

    public function syncLeadStatusOnConversion(VisaEnquiry $enquiry, User $user, ?int $clientId = null): void
    {
        $enquiry->lead_status = 'Converted';
        $enquiry->lead_worked_by_user_id = $user->id;
        $enquiry->lead_worked_at = now();
        $enquiry->save();

        $this->recordFollowUpLog(
            $enquiry,
            $user,
            'Lead converted to client.',
            $clientId
        );
    }

    /**
     * @return array<int, array{id:int,user:string,client:string,description:string,datetime:string}>
     */
    public function followUpHistoryRows(VisaEnquiry $enquiry): array
    {
        return LeadFollowUpLog::with('user')
            ->where('enquiry_id', $enquiry->id)
            ->where('subscriber_id', $enquiry->subscriber_id)
            ->orderByDesc('logged_at')
            ->orderByDesc('id')
            ->get()
            ->map(function (LeadFollowUpLog $log) use ($enquiry) {
                $clientId = $log->client_id ?: $this->resolveClientIdForEnquiry($enquiry);
                $clientLabel = trim((string) ($log->client_name ?: $enquiry->full_name));

                return [
                    'id' => $log->id,
                    'user' => $log->user
                        ? trim($log->user->name) . ' (' . $log->user_id . ')'
                        : 'System',
                    'client' => $clientLabel . ' (' . ($clientId ?: $enquiry->id) . ')',
                    'description' => $log->description,
                    'datetime' => optional($log->logged_at)->format('d-m-Y H:i:s') ?: '-',
                ];
            })
            ->values()
            ->all();
    }

    public function recordFollowUpLog(
        VisaEnquiry $enquiry,
        ?User $actingUser,
        string $description,
        ?int $clientId = null,
        $loggedAt = null
    ): LeadFollowUpLog {
        return LeadFollowUpLog::create([
            'enquiry_id' => $enquiry->id,
            'subscriber_id' => $enquiry->subscriber_id,
            'user_id' => $actingUser?->id ?? $enquiry->lead_worked_by_user_id,
            'client_id' => $clientId,
            'client_name' => $enquiry->full_name,
            'description' => trim($description) !== '' ? trim($description) : 'Follow-up recorded.',
            'logged_at' => $loggedAt ? Carbon::parse($loggedAt) : now(),
        ]);
    }

    public function resolveClientIdForEnquiry(VisaEnquiry $enquiry): ?int
    {
        if ((int) $enquiry->status !== 1) {
            return null;
        }

        $query = Clients::where('subscriber_id', $enquiry->subscriber_id);

        if (empty($enquiry->email) && empty($enquiry->contact_no)) {
            return null;
        }

        return $query->where(function ($builder) use ($enquiry) {
            if (!empty($enquiry->email)) {
                $builder->orWhere('email', $enquiry->email);
            }

            if (!empty($enquiry->contact_no)) {
                $builder->orWhere('phone', $enquiry->contact_no);
            }
        })->value('id');
    }

    private function buildInitialFollowUpDescription(VisaEnquiry $enquiry): string
    {
        return sprintf(
            'Lead created. Source: %s; Status: %s.',
            $enquiry->lead_source ?: 'Walk-in',
            $enquiry->lead_status ?: 'Open'
        );
    }

    private function buildFollowUpChangeDescription(array $before, array $after, VisaEnquiry $enquiry): string
    {
        $changes = [];

        if ($before['lead_source'] !== $after['lead_source']) {
            $changes[] = 'Source: ' . $before['lead_source'] . ' -> ' . $after['lead_source'];
        }

        if ($before['lead_status'] !== $after['lead_status']) {
            $changes[] = 'Status: ' . $before['lead_status'] . ' -> ' . $after['lead_status'];
        }

        if ((int) ($before['lead_worked_by_user_id'] ?? 0) !== (int) ($after['lead_worked_by_user_id'] ?? 0)) {
            $beforeName = $this->resolveWorkedByLabel($before['lead_worked_by_user_id']);
            $afterName = $this->resolveWorkedByLabel($after['lead_worked_by_user_id'], $enquiry);
            $changes[] = 'Worked by: ' . $beforeName . ' -> ' . $afterName;
        }

        if ($changes === []) {
            return 'Follow-up details refreshed.';
        }

        return implode('; ', $changes);
    }

    private function resolveWorkedByLabel(?int $userId, ?VisaEnquiry $enquiry = null): string
    {
        if (!$userId) {
            return 'Unassigned';
        }

        if ($enquiry && (int) $enquiry->lead_worked_by_user_id === (int) $userId && $enquiry->relationLoaded('workedByUser') && $enquiry->workedByUser) {
            return trim($enquiry->workedByUser->name) . ' (' . $userId . ')';
        }

        $user = User::find($userId);

        return $user ? trim($user->name) . ' (' . $userId . ')' : 'User (' . $userId . ')';
    }
}
