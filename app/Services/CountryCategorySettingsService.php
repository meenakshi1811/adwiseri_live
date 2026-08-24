<?php

namespace App\Services;

use App\Models\Applications;
use App\Models\Client_jobs;
use App\Models\Countries;
use App\Models\Services;
use App\Models\SubscriberCcSetting;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class CountryCategorySettingsService
{
    public const DEFAULT_VISA_CATEGORIES = [
        'Visit',
        'Training',
        'Study',
        'Work',
        'Dependent',
        'PR',
        'Business',
        'Investor',
    ];

    public const DOCUMENT_TYPES = [
        'Photo',
        'Passport',
        'Birth Certificate',
        'Marriage Certificate',
        'National ID',
        'Driving Licence',
        'Current Visa',
        'BRP Card',
        'E-visa Approval',
        'Application Form',
        'Application Fees Receipt',
        'Appointment Confirmation Letter',
        'Annexure',
        'Cover Letter',
        '10th Marksheet',
        '12th Marksheet',
        'School Transcript',
        'College Transcript',
        'Statement of Purpose',
        'Letter of Recommendation',
        'Degree Certificate',
        'School Leaving Certificate',
        'Diploma',
        'Employment Letter',
        'Experience Certificate',
        'Resume/CV',
        'Payslip(s)',
        'Balance Certificate',
        'Loan Sanction Letter',
        'Bank Statement',
        'Income Tax Return(s)',
        'Financial Statement',
        'Affidavit of Support',
        'Photo (Dependant)',
        'Marriage Invitation Card',
        'Picture (Group / Occasion)',
        'Dependant Passport',
        'Dependant Birth Certificate',
        'Relationship Evidence',
        'Decision',
        'Appeal Copy',
        'Appeal',
        'Appeal Decision',
        'Admin/Judicial Review',
        'AR/JR Decision',
        'Supporting Document 1',
        'Supporting Document 2',
        'Supporting Document 3',
        'Supporting Document 4',
        'Supporting Document 5',
        'Supporting Document 6',
        'Supporting Document 7',
        'Supporting Document 8',
        'Supporting Document 9',
        'Supporting Document 10',
        'Other',
    ];

    public const DOCUMENT_FOLDERS = [
        'Identity & Personal' => [
            'Photo',
            'Passport',
            'Birth Certificate',
            'National ID',
            'Driving Licence',
            'Current Visa',
            'BRP Card',
            'E-visa Approval',
        ],
        'Application Form(s)' => [
            'Application Form',
            'Application Fees Receipt',
            'Appointment Confirmation Letter',
            'Annexure',
            'Cover Letter',
        ],
        'Educational' => [
            '10th Marksheet',
            '12th Marksheet',
            'School Transcript',
            'College Transcript',
            'Statement of Purpose',
            'Letter of Recommendation',
            'Degree Certificate',
            'School Leaving Certificate',
            'Diploma',
        ],
        'Work Experience' => [
            'Employment Letter',
            'Experience Certificate',
            'Resume/CV',
            'Payslip(s)',
        ],
        'Financial Evidences' => [
            'Balance Certificate',
            'Loan Sanction Letter',
            'Bank Statement',
            'Income Tax Return(s)',
            'Financial Statement',
            'Affidavit of Support',
        ],
        'Spouse/Dependants' => [
            'Photo (Dependant)',
            'Marriage Invitation Card',
            'Picture (Group / Occasion)',
            'Marriage Certificate',
            'Dependant Passport',
            'Dependant Birth Certificate',
            'Relationship Evidence',
        ],
        'Decisions & Appeals' => [
            'Decision',
            'Appeal Copy',
            'Appeal',
            'Appeal Decision',
            'Admin/Judicial Review',
            'AR/JR Decision',
        ],
        'Supporting Documents' => [
            'Supporting Document 1',
            'Supporting Document 2',
            'Supporting Document 3',
            'Supporting Document 4',
            'Supporting Document 5',
            'Supporting Document 6',
            'Supporting Document 7',
            'Supporting Document 8',
            'Supporting Document 9',
            'Supporting Document 10',
        ],
        'Other' => [
            'Other',
        ],
    ];

    private const LEGACY_FOLDER_MAP = [
        'Application & Forms' => 'Application Form(s)',
    ];

    private const LEGACY_TYPE_FOLDER_MAP = [
        'Transcript' => 'Educational',
        'School Certificate' => 'Educational',
        'Payslip' => 'Work Experience',
        'Tax Return' => 'Financial Evidences',
        'Sponsor Letter' => 'Financial Evidences',
    ];

    public const TRAVEL_AGENT_APPLICATION_TYPES = [
        'Visit Visa - Leisure / Tourism',
        'Visit Visa - Business / Medical',
        'Transit Visa',
        'Passport (New)',
        'Passport (Renewal)',
        'TOC',
        'Appeal',
        'Other',
    ];

    public function getTravelAgentApplicationTypes(): array
    {
        return self::TRAVEL_AGENT_APPLICATION_TYPES;
    }

    public function isTravelAgentSubscriber(User $subscriber): bool
    {
        $category = trim((string) ($subscriber->category ?? ''));

        return $this->isTravelAgencyCategory($category, $this->normalizeLookupText($category));
    }

    public function getDocumentTypes(): array
    {
        return self::DOCUMENT_TYPES;
    }

    public function getDocumentFolders(): array
    {
        return self::DOCUMENT_FOLDERS;
    }

    public function normalizeFolderName(?string $folder): string
    {
        $folder = trim((string) $folder);

        if ($folder === '') {
            return 'Other';
        }

        if (array_key_exists($folder, self::DOCUMENT_FOLDERS)) {
            return $folder;
        }

        return self::LEGACY_FOLDER_MAP[$folder] ?? $folder;
    }

    public function resolveDocumentFolder(?string $docType): string
    {
        $docType = trim((string) $docType);

        foreach (self::DOCUMENT_FOLDERS as $folder => $types) {
            if (in_array($docType, $types, true)) {
                return $folder;
            }
        }

        if (array_key_exists($docType, self::LEGACY_TYPE_FOLDER_MAP)) {
            return self::LEGACY_TYPE_FOLDER_MAP[$docType];
        }

        return $docType !== '' ? $docType : 'Other';
    }

    public function resolveDocumentFolderForDoc(object $doc): string
    {
        $folders = $this->resolveDocumentFoldersForDoc($doc);

        return $folders[0] ?? 'Other';
    }

    public function resolveDocumentFoldersForDoc(object $doc): array
    {
        $folder = trim((string) ($doc->doc_folder ?? ''));

        if ($folder === '') {
            $storedList = $doc->doc_folders ?? null;
            if (is_string($storedList)) {
                $storedList = json_decode($storedList, true);
            }
            if (is_array($storedList) && !empty($storedList)) {
                $folder = trim((string) $storedList[0]);
            }
        }

        if ($folder === '') {
            $folder = $this->resolveDocumentFolder($doc->doc_type ?? null);
        }

        return [$this->normalizeFolderName($folder)];
    }

    public function resolveStoredDocumentFolder(?string $requestedFolder, ?string $docType): string
    {
        $folders = $this->resolveStoredDocumentFolders(
            $requestedFolder !== null && $requestedFolder !== '' ? [$requestedFolder] : [],
            $docType
        );

        return $folders[0] ?? 'Other';
    }

    public function resolveStoredDocumentFolders($requestedFolders, ?string $docType): array
    {
        $input = is_array($requestedFolders) ? ($requestedFolders[0] ?? null) : $requestedFolders;
        $folder = $this->normalizeFolderName(trim((string) $input));

        if ($folder !== '' && array_key_exists($folder, self::DOCUMENT_FOLDERS)) {
            return [$folder];
        }

        return [$this->resolveDocumentFolder($docType)];
    }

    public function groupDocumentsByFolder(Collection $documents): Collection
    {
        $bucket = [];

        foreach (array_keys(self::DOCUMENT_FOLDERS) as $folder) {
            $bucket[$folder] = collect();
        }

        foreach ($documents as $doc) {
            foreach ($this->resolveDocumentFoldersForDoc($doc) as $folder) {
                if (!isset($bucket[$folder])) {
                    $bucket[$folder] = collect();
                }
                $bucket[$folder]->push($doc);
            }
        }

        $ordered = collect();

        foreach (array_keys(self::DOCUMENT_FOLDERS) as $folder) {
            if (!empty($bucket[$folder]) && $bucket[$folder]->isNotEmpty()) {
                $ordered->put($folder, $bucket[$folder]->unique('id')->values());
            }
        }

        foreach ($bucket as $folder => $docs) {
            if ($ordered->has($folder) || $docs->isEmpty()) {
                continue;
            }
            $ordered->put($folder, $docs->unique('id')->values());
        }

        return $ordered;
    }
    public function resolveSubscriber(User $user): User
    {
        if (strtolower((string) $user->user_type) === 'subscriber') {
            return $user;
        }

        $subscriber = User::find($user->added_by);

        return $subscriber ?: $user;
    }

    public function getSetting(User $subscriber): ?SubscriberCcSetting
    {
        if (!$this->settingsTableExists()) {
            return null;
        }

        return SubscriberCcSetting::where('subscriber_id', $subscriber->id)->first();
    }

    public function settingsTableExists(): bool
    {
        return Schema::hasTable('subscriber_cc_settings');
    }

    public function ensureSettingsTableExists(): void
    {
        if (!$this->settingsTableExists()) {
            throw new \RuntimeException(
                'Countries & Categories storage is not set up. Please run: php artisan migrate --path=database/migrations/2026_06_18_000001_create_subscriber_cc_settings_table.php'
            );
        }
    }

    public function getDefaultCountryNames(User $subscriber): Collection
    {
        $ruleBased = $this->getRuleBasedSubscriberCountryOptions($subscriber);

        if ($ruleBased !== null) {
            return $ruleBased;
        }

        $profileCountries = $this->getProfileMappedDestinationCountries($subscriber);

        if ($profileCountries->isNotEmpty()) {
            return $profileCountries;
        }

        return Countries::orderBy('country_name', 'asc')->pluck('country_name');
    }

    public function getAllAvailableVisaCategoryNames(User $subscriber): Collection
    {
        return $this->queryClientJobs($subscriber)->pluck('job')->filter()->unique()->values();
    }

    /**
     * Visa categories shown in Settings → Countries & Categories picker.
     * New subscribers may have no client_jobs rows yet; include standard defaults
     * plus any categories already saved in their settings.
     */
    public function getSelectableVisaCategoryNames(User $subscriber): Collection
    {
        if ($this->isTravelAgentSubscriber($subscriber)) {
            return collect(self::TRAVEL_AGENT_APPLICATION_TYPES);
        }

        $fromJobs = $this->getAllAvailableVisaCategoryNames($subscriber);
        $candidates = $fromJobs->isNotEmpty()
            ? $fromJobs
            : collect(self::DEFAULT_VISA_CATEGORIES);

        if (strcasecmp((string) $subscriber->sub_category, 'Other') === 0) {
            $other = trim((string) ($subscriber->other_subcategory ?? ''));

            if ($other !== '') {
                $candidates = $candidates->prepend($other);
            }
        }

        $setting = $this->getSetting($subscriber);
        $saved = ($setting && is_array($setting->visa_categories))
            ? collect($setting->visa_categories)
            : collect();

        return $candidates
            ->merge($saved)
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->unique()
            ->values();
    }

    public function getClientJobsForSubscriber(User $subscriber)
    {
        return $this->queryClientJobs($subscriber);
    }

    public function getDefaultVisaCategoryNames(User $subscriber): Collection
    {
        if ($this->isTravelAgentSubscriber($subscriber)) {
            return collect(self::TRAVEL_AGENT_APPLICATION_TYPES);
        }

        if (strcasecmp((string) $subscriber->sub_category, 'Other') === 0) {
            $other = trim((string) ($subscriber->other_subcategory ?? ''));

            if ($other !== '') {
                return collect([$other]);
            }

            return collect(self::DEFAULT_VISA_CATEGORIES);
        }

        $fromJobs = $this->getAllAvailableVisaCategoryNames($subscriber);

        if ($fromJobs->isNotEmpty()) {
            return $fromJobs;
        }

        return collect(self::DEFAULT_VISA_CATEGORIES);
    }

    public function resolveCountryNames(User $subscriber, array $extraSelected = []): Collection
    {
        $setting = $this->getSetting($subscriber);

        if ($setting && is_array($setting->countries) && count($setting->countries) > 0) {
            $names = collect($setting->countries);
        } else {
            $names = $this->getDefaultCountryNames($subscriber);
        }

        return $names
            ->merge($extraSelected)
            ->map(fn ($country) => trim((string) $country))
            ->filter()
            ->unique()
            ->values();
    }

    public function resolveVisaCategoryNames(User $subscriber): Collection
    {
        if ($this->isTravelAgentSubscriber($subscriber)) {
            return collect(self::TRAVEL_AGENT_APPLICATION_TYPES);
        }

        $setting = $this->getSetting($subscriber);

        if ($setting && is_array($setting->visa_categories) && count($setting->visa_categories) > 0) {
            return collect($setting->visa_categories)->filter()->unique()->values();
        }

        $defaults = $this->getDefaultVisaCategoryNames($subscriber);

        if ($defaults->isNotEmpty()) {
            return $defaults;
        }

        return collect(self::DEFAULT_VISA_CATEGORIES);
    }

    public function hasSavedCcSelection(User $subscriber): bool
    {
        $setting = $this->getSetting($subscriber);

        if (!$setting) {
            return false;
        }

        $countries = is_array($setting->countries) ? count($setting->countries) : 0;
        $categories = is_array($setting->visa_categories) ? count($setting->visa_categories) : 0;

        return $countries > 0 || $categories > 0;
    }

    public function getCommonDocumentSet(): array
    {
        return [
            'Photo',
            'Passport',
            'Birth Certificate',
            'Degree Certificate',
            'Application Form',
        ];
    }

    public function buildSuggestedDocumentLists(User $subscriber): array
    {
        $countries = $this->resolveCountryNames($subscriber)->all();
        $visaCategories = $this->resolveVisaCategoryNames($subscriber)->all();
        $existing = collect($this->getDocumentLists($subscriber));
        $commonDocuments = $this->getCommonDocumentSet();
        $suggested = [];

        foreach ($countries as $country) {
            foreach ($visaCategories as $visaCategory) {
                $alreadyExists = $existing->contains(function ($entry) use ($country, $visaCategory) {
                    return ($entry['country'] ?? '') === $country
                        && ($entry['visa_category'] ?? '') === $visaCategory;
                });

                if ($alreadyExists) {
                    continue;
                }

                $suggested[] = [
                    'country' => $country,
                    'visa_category' => $visaCategory,
                    'sections' => [[
                        'title' => 'Required Documents',
                        'documents' => $commonDocuments,
                    ]],
                    'documents' => $commonDocuments,
                ];
            }
        }

        return $this->normalizeDocumentLists(
            $existing->merge($suggested)->values()->all(),
            $countries,
            $visaCategories
        );
    }

    public function resolveCountriesForDropdown(User $subscriber, array $extraSelected = [])
    {
        $countryNames = $this->resolveCountryNames($subscriber, $extraSelected);

        if ($countryNames->isEmpty()) {
            return Countries::orderBy('country_name', 'asc')->get();
        }

        return Countries::whereIn('country_name', $countryNames->all())
            ->get()
            ->sortBy(function ($country) use ($countryNames) {
                $position = $countryNames->search($country->country_name);

                return $position === false ? PHP_INT_MAX : $position;
            })
            ->values();
    }

    public function resolveServiceCountryOptions(User $subscriber, array $extraSelected = []): Collection
    {
        // Services must only offer countries the subscriber explicitly saved in C & C.
        // Never fall back to the full world countries list.
        $setting = $this->getSetting($subscriber);
        $names = ($setting && is_array($setting->countries) && count($setting->countries) > 0)
            ? collect($setting->countries)
            : collect();

        return $names
            ->merge($extraSelected)
            ->map(fn ($country) => trim((string) $country))
            ->filter(fn ($country) => $country !== '' && strcasecmp($country, Services::COUNTRY_NA) !== 0)
            ->unique(fn ($country) => strtolower($country))
            ->values();
    }

    public function resolveServiceNameOptions(User $subscriber, array $extraSelected = []): Collection
    {
        // Services must only offer visa categories the subscriber explicitly saved in C & C
        // (plus standalone Consultation). Travel-agent accounts keep their fixed type list.
        if ($this->isTravelAgentSubscriber($subscriber)) {
            $categories = collect(self::TRAVEL_AGENT_APPLICATION_TYPES);
        } else {
            $setting = $this->getSetting($subscriber);
            $categories = ($setting && is_array($setting->visa_categories) && count($setting->visa_categories) > 0)
                ? collect($setting->visa_categories)
                : collect();
        }

        $categories = $categories
            ->merge($extraSelected)
            ->map(fn ($name) => trim((string) $name))
            ->filter()
            ->reject(fn ($name) => strcasecmp($name, 'Consultation') === 0);

        return collect(['Consultation'])
            ->merge($categories)
            ->unique(fn ($name) => strtolower($name))
            ->values();
    }

    /**
     * Saved C & C preferences used by the Services tab summary panel.
     */
    public function resolveSavedServicePreferences(User $subscriber): array
    {
        $setting = $this->getSetting($subscriber);
        $countries = ($setting && is_array($setting->countries))
            ? collect($setting->countries)->map(fn ($v) => trim((string) $v))->filter()->unique()->values()
            : collect();
        $categories = ($setting && is_array($setting->visa_categories))
            ? collect($setting->visa_categories)->map(fn ($v) => trim((string) $v))->filter()->unique()->values()
            : collect();

        if ($this->isTravelAgentSubscriber($subscriber) && $categories->isEmpty()) {
            $categories = collect(self::TRAVEL_AGENT_APPLICATION_TYPES)->values();
        }

        return [
            'countries' => $countries,
            'visa_categories' => $categories,
            'has_saved' => $countries->isNotEmpty() && $categories->isNotEmpty(),
        ];
    }

    public function resolveSavedOrConfiguredCountryNames(User $subscriber, array $extraSelected = []): Collection
    {
        $setting = $this->getSetting($subscriber);

        if ($setting && is_array($setting->countries) && count($setting->countries) > 0) {
            $names = collect($setting->countries);
        } else {
            $names = $this->getDefaultCountryNames($subscriber);
        }

        return $names
            ->merge($extraSelected)
            ->map(fn ($country) => trim((string) $country))
            ->filter()
            ->unique()
            ->values();
    }

    public function resolveSavedOrConfiguredVisaCategoryNames(User $subscriber): Collection
    {
        if ($this->isTravelAgentSubscriber($subscriber)) {
            return collect(self::TRAVEL_AGENT_APPLICATION_TYPES);
        }

        $setting = $this->getSetting($subscriber);

        if ($setting && is_array($setting->visa_categories) && count($setting->visa_categories) > 0) {
            return collect($setting->visa_categories)->filter()->unique()->values();
        }

        return $this->getDefaultVisaCategoryNames($subscriber);
    }

    public function getSubscriberCountryOptions(int $subscriberId, array $selectedCountries = [])
    {
        $subscriber = User::find($subscriberId);

        if (!$subscriber) {
            return Countries::orderBy('country_name', 'asc')->get();
        }

        $countryNames = $this->resolveCountryNames($subscriber, $selectedCountries);

        if ($countryNames->isEmpty()) {
            return Countries::orderBy('country_name', 'asc')->get();
        }

        return Countries::whereIn('country_name', $countryNames->all())
            ->get()
            ->sortBy(function ($country) use ($countryNames) {
                $position = $countryNames->search($country->country_name);

                return $position === false ? PHP_INT_MAX : $position;
            })
            ->values();
    }

    public function saveSettings(User $subscriber, array $countries, array $visaCategories, ?array $documentLists = null): SubscriberCcSetting
    {
        $this->ensureSettingsTableExists();

        $countries = collect($countries)->map(fn ($value) => trim((string) $value))->filter()->unique()->values()->all();
        $visaCategories = collect($visaCategories)->map(fn ($value) => trim((string) $value))->filter()->unique()->values()->all();

        if ($countries === [] || $visaCategories === []) {
            throw new \InvalidArgumentException('Please select at least one country and one visa category.');
        }

        $payload = [
            'countries' => $countries,
            'visa_categories' => $visaCategories,
        ];

        if ($documentLists !== null) {
            $payload['document_lists'] = $this->normalizeDocumentLists($documentLists, $countries, $visaCategories);
        } else {
            $existing = $this->getSetting($subscriber);
            if ($existing && is_array($existing->document_lists)) {
                $payload['document_lists'] = $this->normalizeDocumentLists(
                    $existing->document_lists,
                    $countries,
                    $visaCategories
                );
            }
        }

        return SubscriberCcSetting::updateOrCreate(
            ['subscriber_id' => $subscriber->id],
            $payload
        );
    }

    public function getDocumentLists(User $subscriber): array
    {
        $setting = $this->getSetting($subscriber);

        if (!$setting || !is_array($setting->document_lists)) {
            return [];
        }

        $countries = $this->resolveCountryNames($subscriber)->all();
        $visaCategories = $this->resolveVisaCategoryNames($subscriber)->all();

        return $this->normalizeDocumentLists($setting->document_lists, $countries, $visaCategories);
    }

    public function getAllStoredDocumentLists(User $subscriber): array
    {
        $setting = $this->getSetting($subscriber);

        if (!$setting || !is_array($setting->document_lists)) {
            return [];
        }

        return $this->normalizeDocumentLists($setting->document_lists, [], []);
    }

    public function saveDocumentLists(User $subscriber, array $documentLists): SubscriberCcSetting
    {
        $this->ensureSettingsTableExists();

        $countries = $this->resolveCountryNames($subscriber)->all();
        $visaCategories = $this->resolveVisaCategoryNames($subscriber)->all();
        $normalized = $this->normalizeDocumentLists($documentLists, $countries, $visaCategories);

        $setting = $this->getSetting($subscriber);

        if ($setting) {
            $setting->document_lists = $normalized;
            $setting->save();

            return $setting;
        }

        return SubscriberCcSetting::create([
            'subscriber_id' => $subscriber->id,
            'countries' => $countries,
            'visa_categories' => $visaCategories,
            'document_lists' => $normalized,
        ]);
    }

    public function resolveDocumentsForCombination(User $subscriber, string $country, string $visaCategory): array
    {
        $entry = $this->resolveDocumentListEntry($subscriber, $country, $visaCategory);

        if (!$entry) {
            return [];
        }

        $documents = [];
        foreach ($this->normalizeEntrySections($entry) as $section) {
            foreach ($section['documents'] as $document) {
                $documents[] = $document;
            }
        }

        return $documents;
    }

    public function resolveDocumentListEntry(User $subscriber, string $country, string $visaCategory): ?array
    {
        return $this->resolveDocumentListEntryFromLists(
            $this->getAllStoredDocumentLists($subscriber),
            $country,
            $visaCategory
        );
    }

    public function resolveDocumentListEntryWithCandidates(User $subscriber, array $countries, array $categories): ?array
    {
        $lists = $this->getAllStoredDocumentLists($subscriber);
        $countries = collect($countries)
            ->map(fn ($value) => trim((string) $value))
            ->filter(fn ($value) => $value !== '' && $value !== '—')
            ->unique()
            ->values();
        $categories = collect($categories)
            ->map(fn ($value) => trim((string) $value))
            ->filter(fn ($value) => $value !== '' && $value !== '—')
            ->unique()
            ->values();

        foreach ($countries as $country) {
            foreach ($categories as $visaCategory) {
                $entry = $this->resolveDocumentListEntryFromLists($lists, $country, $visaCategory);
                if ($entry) {
                    return $entry;
                }
            }
        }

        return null;
    }

    public function resolveDocumentListEntryFromLists(array $lists, string $country, string $visaCategory): ?array
    {
        $country = trim($country);
        $visaCategory = trim($visaCategory);

        if ($country === '' || $visaCategory === '' || $country === '—' || $visaCategory === '—') {
            return null;
        }

        $countryKey = $this->normalizeDocumentListMatchKey($country);
        $categoryKey = $this->normalizeDocumentListMatchKey($visaCategory);

        foreach ($lists as $entry) {
            if ($this->normalizeDocumentListMatchKey((string) ($entry['country'] ?? '')) === $countryKey
                && $this->normalizeDocumentListMatchKey((string) ($entry['visa_category'] ?? '')) === $categoryKey) {
                return $entry;
            }
        }

        return null;
    }

    private function normalizeDocumentListMatchKey(string $value): string
    {
        return strtolower(preg_replace('/\s+/', ' ', trim($value)));
    }

    public function buildNumberedDocumentSections(array $entry): array
    {
        $numbered = [];
        $counter = 1;

        foreach ($this->normalizeEntrySections($entry) as $section) {
            $items = [];
            foreach ($section['documents'] as $document) {
                $items[] = [
                    'number' => $counter,
                    'label' => $document,
                ];
                $counter++;
            }

            if (count($items) === 0) {
                continue;
            }

            $numbered[] = [
                'title' => $section['title'],
                'items' => $items,
            ];
        }

        return $numbered;
    }

    public function normalizeEntrySections(array $entry): array
    {
        $sections = collect($entry['sections'] ?? [])
            ->map(function ($section) {
                if (!is_array($section)) {
                    return null;
                }

                $title = trim((string) ($section['title'] ?? ''));
                $documents = collect($section['documents'] ?? [])
                    ->map(fn ($doc) => trim((string) $doc))
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();

                if (count($documents) === 0) {
                    return null;
                }

                return [
                    'title' => $title !== '' ? $title : 'Documents',
                    'documents' => $documents,
                ];
            })
            ->filter()
            ->values()
            ->all();

        if (count($sections) > 0) {
            return $sections;
        }

        $documents = collect($entry['documents'] ?? [])
            ->map(fn ($doc) => trim((string) $doc))
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (count($documents) === 0) {
            return [];
        }

        return [[
            'title' => 'Required Documents',
            'documents' => $documents,
        ]];
    }

    public function normalizeDocumentLists(array $documentLists, array $allowedCountries = [], array $allowedVisaCategories = []): array
    {
        $allowedCountries = collect($allowedCountries)->map(fn ($value) => trim((string) $value))->filter()->values();
        $allowedVisaCategories = collect($allowedVisaCategories)->map(fn ($value) => trim((string) $value))->filter()->values();

        return collect($documentLists)
            ->map(function ($entry) {
                if (!is_array($entry)) {
                    return null;
                }

                $country = trim((string) ($entry['country'] ?? ''));
                $visaCategory = trim((string) ($entry['visa_category'] ?? ''));
                $sections = $this->normalizeEntrySections([
                    'sections' => $entry['sections'] ?? [],
                    'documents' => $entry['documents'] ?? [],
                ]);

                if ($country === '' || $visaCategory === '' || count($sections) === 0) {
                    return null;
                }

                $documents = [];
                foreach ($sections as $section) {
                    foreach ($section['documents'] as $document) {
                        $documents[] = $document;
                    }
                }

                return [
                    'country' => $country,
                    'visa_category' => $visaCategory,
                    'sections' => $sections,
                    'documents' => $documents,
                ];
            })
            ->filter()
            ->unique(fn ($entry) => $entry['country'] . '|' . $entry['visa_category'])
            ->filter(function ($entry) use ($allowedCountries, $allowedVisaCategories) {
                if ($allowedCountries->isNotEmpty() && !$allowedCountries->contains($entry['country'])) {
                    return false;
                }

                if ($allowedVisaCategories->isNotEmpty() && !$allowedVisaCategories->contains($entry['visa_category'])) {
                    return false;
                }

                return true;
            })
            ->sortBy(fn ($entry) => $entry['country'] . ' ' . $entry['visa_category'])
            ->values()
            ->all();
    }

    public function resetToDefaults(User $subscriber): void
    {
        if (!$this->settingsTableExists()) {
            return;
        }

        SubscriberCcSetting::where('subscriber_id', $subscriber->id)->delete();
    }

    public function formatApplicationServiceName(?string $country, ?string $applicationType): string
    {
        $country = trim((string) $country);
        $applicationType = trim((string) $applicationType);

        if ($applicationType === '') {
            return $country;
        }

        // Already in "Country - Type" form (or a plain standalone service name with separator)
        if (str_contains($applicationType, ' - ')) {
            return $applicationType;
        }

        if ($country === '') {
            return $applicationType;
        }

        return $country . ' - ' . $applicationType;
    }

    public function resolveServiceFee(User $subscriber, string $applicationType, ?string $country = null): ?float
    {
        $applicationType = trim($applicationType);
        $country = trim((string) $country);

        if ($applicationType === '') {
            return null;
        }

        if (str_contains($applicationType, ' - ') && $country === '') {
            $parts = explode(' - ', $applicationType, 2);
            $country = trim((string) ($parts[0] ?? ''));
            $applicationType = trim((string) ($parts[1] ?? $applicationType));
        }

        $services = Services::where('subscriber_id', $subscriber->id)
            ->where('status', true)
            ->orderByDesc('updated_at')
            ->get(['country', 'service_name', 'fees']);

        if ($services->isEmpty()) {
            return null;
        }

        $normalize = static function (string $value): string {
            return Services::normalizeKey($value);
        };

        $matchByCountryAndName = function (?string $matchCountry, string $matchName) use ($services, $normalize) {
            $countryKey = $normalize(Services::normalizeCountry($matchCountry));
            $nameKey = $normalize($matchName);

            if ($nameKey === '') {
                return null;
            }

            return $services->first(function ($service) use ($normalize, $countryKey, $nameKey) {
                return $normalize(Services::normalizeCountry($service->country)) === $countryKey
                    && $normalize((string) $service->service_name) === $nameKey;
            });
        };

        $primaryMatch = $matchByCountryAndName($country, $applicationType);
        if ($primaryMatch) {
            return (float) $primaryMatch->fees;
        }

        // Legacy rows may still store "Country - Type" inside service_name.
        $candidates = [];

        if (str_contains($applicationType, ' - ')) {
            $candidates[] = $applicationType;
            $parts = explode(' - ', $applicationType, 2);
            $typeOnly = trim((string) ($parts[1] ?? ''));
            if ($typeOnly !== '') {
                $candidates[] = $typeOnly;
            }
        } else {
            if ($country !== '') {
                $candidates[] = $this->formatApplicationServiceName($country, $applicationType);
            }
            $candidates[] = $applicationType;
        }

        $candidates = array_values(array_unique(array_filter(array_map(
            fn ($value) => trim((string) $value),
            $candidates
        ), fn ($value) => $value !== '')));

        foreach ($candidates as $candidate) {
            $needle = $normalize($candidate);
            $legacyMatch = $services->first(function ($service) use ($normalize, $needle) {
                return $normalize((string) $service->service_name) === $needle;
            });

            if ($legacyMatch) {
                return (float) $legacyMatch->fees;
            }
        }

        if ($country === '') {
            $naMatch = $matchByCountryAndName(Services::COUNTRY_NA, $applicationType);
            if ($naMatch) {
                return (float) $naMatch->fees;
            }
        }

        // Last resort: match by service name regardless of country (first active row).
        $nameOnlyKey = $normalize($applicationType);
        if ($nameOnlyKey !== '') {
            $nameOnlyMatch = $services->first(function ($service) use ($normalize, $nameOnlyKey) {
                return $normalize((string) $service->service_name) === $nameOnlyKey;
            });

            if ($nameOnlyMatch) {
                return (float) $nameOnlyMatch->fees;
            }
        }

        return null;
    }

    public function buildJobRoleOptions(User $subscriber, ?string $selected = null): string
    {
        $allowedCategories = $this->resolveVisaCategoryNames($subscriber);
        $selected = trim((string) ($selected ?? ''));

        if (strcasecmp((string) $subscriber->sub_category, 'Other') === 0) {
            $other = trim((string) ($subscriber->other_subcategory ?? ''));

            if ($other === '') {
                return '<option value="">Select Application Type</option>';
            }

            if ($allowedCategories->isNotEmpty() && !$allowedCategories->contains($other)) {
                return '<option value="">Select Application Type</option>';
            }

            $selectedAttr = $selected === $other ? ' selected' : '';

            return '<option value="">Select Application Type</option>'
                . '<option value="' . e($other) . '"' . $selectedAttr . '>' . e($other) . '</option>';
        }

        $allClientJobs = $this->queryClientJobs($subscriber);
        $clientJobs = $allClientJobs
            ->filter(function ($job) use ($allowedCategories) {
                if ($allowedCategories->isEmpty()) {
                    return true;
                }

                return $allowedCategories->contains($job->job);
            });

        if ($clientJobs->isEmpty()) {
            if ($allClientJobs->isNotEmpty()) {
                $clientJobs = $allClientJobs;
            } elseif ($allowedCategories->isNotEmpty()) {
                $clientJobs = $allowedCategories
                    ->map(fn ($name) => (object) ['job' => (string) $name])
                    ->values();
            } else {
                $clientJobs = collect(self::DEFAULT_VISA_CATEGORIES)
                    ->map(fn ($name) => (object) ['job' => $name])
                    ->values();
            }
        }

        if ($selected !== '' && !$clientJobs->contains(fn ($job) => (string) $job->job === $selected) && $allowedCategories->contains($selected)) {
            $clientJobs = $clientJobs->push((object) ['job' => $selected]);
        }

        $html = '<option value="">Select Application Type</option>';

        foreach ($clientJobs as $job) {
            $jobName = (string) $job->job;
            $selectedAttr = $selected === $jobName ? ' selected' : '';
            $html .= '<option value="' . e($jobName) . '"' . $selectedAttr . '>' . e($jobName) . '</option>';
        }

        return $html;
    }

    public function buildInvoiceServiceTypeOptions(User $subscriber, $applications, array $excludeApplicationIds = [], ?string $selected = null): string
    {
        $emptyLabel = 'Select Application/Service Type';
        $selected = trim((string) ($selected ?? ''));
        $html = '<option value="">' . e($emptyLabel) . '</option>';
        $listedNames = [];

        foreach ($applications as $app) {
            if (in_array($app->id, $excludeApplicationIds, true)) {
                continue;
            }

            $typeName = trim((string) ($app->application_name ?? ''));
            $country = trim((string) ($app->visa_country ?? ''));
            $name = $this->formatApplicationServiceName($country, $typeName);
            if ($name === '') {
                continue;
            }

            $listedNames[] = strtolower($name);
            $fee = $this->resolveServiceFee($subscriber, $typeName, $country);
            $value = (string) ($app->application_id ?? '');
            if ($value === '') {
                $value = $name;
            }
            $selectedAttr = ($selected !== '' && ($selected === $value || $selected === $name || $selected === $typeName)) ? ' selected' : '';
            $html .= '<option value="' . e($value) . '" data-name="' . e($name) . '" data-country="' . e($country) . '" data-type="' . e($typeName) . '" data-fee="' . e($fee ?? '') . '"' . $selectedAttr . '>';
            $html .= e($name) . ' (' . e($app->application_id) . ')';
            $html .= '</option>';
        }

        if (strcasecmp((string) $subscriber->sub_category, 'Other') === 0) {
            $other = trim((string) ($subscriber->other_subcategory ?? ''));

            if ($other !== '' && !in_array(strtolower($other), $listedNames, true)) {
                $fee = $this->resolveServiceFee($subscriber, $other);
                $selectedAttr = $selected === $other ? ' selected' : '';
                $html .= '<option value="' . e($other) . '" data-name="' . e($other) . '" data-country="' . e(Services::COUNTRY_NA) . '" data-type="' . e($other) . '" data-fee="' . e($fee ?? '') . '"' . $selectedAttr . '>' . e($other) . '</option>';
                $listedNames[] = strtolower($other);
            }
        } else {
            $allClientJobs = $this->queryClientJobs($subscriber);
            $allowedCategories = $this->resolveVisaCategoryNames($subscriber);
            $clientJobs = $allClientJobs->filter(function ($job) use ($allowedCategories) {
                if ($allowedCategories->isEmpty()) {
                    return true;
                }

                return $allowedCategories->contains($job->job);
            });

            if ($clientJobs->isEmpty() && $allClientJobs->isNotEmpty()) {
                $clientJobs = $allClientJobs;
            }

            foreach ($clientJobs as $job) {
                $jobName = trim((string) $job->job);

                // Standalone services (Consultation, Admin Fees, etc.) — no country prefix
                if ($jobName === '' || in_array(strtolower($jobName), $listedNames, true)) {
                    continue;
                }

                $fee = $this->resolveServiceFee($subscriber, $jobName);
                $selectedAttr = $selected === $jobName ? ' selected' : '';
                $html .= '<option value="' . e($jobName) . '" data-name="' . e($jobName) . '" data-country="' . e(Services::COUNTRY_NA) . '" data-type="' . e($jobName) . '" data-fee="' . e($fee ?? '') . '"' . $selectedAttr . '>';
                $html .= e($jobName);
                $html .= '</option>';
                $listedNames[] = strtolower($jobName);
            }
        }

        $selectedAttr = $selected === 'Other' ? ' selected' : '';
        $html .= '<option value="Other" data-name="" data-fee=""' . $selectedAttr . '>Other</option>';

        return $html;
    }

    public function buildVisaCategoryOptions(User $subscriber, ?string $selected = null, string $emptyLabel = 'Visa Category'): string
    {
        $categories = $this->resolveVisaCategoryNames($subscriber);
        $selected = trim((string) ($selected ?? ''));

        if ($selected !== '' && !$categories->contains($selected)) {
            $categories = $categories->push($selected)->unique()->values();
        }

        $html = '<option value="">' . e($emptyLabel) . '</option>';

        foreach ($categories as $category) {
            $selectedAttr = $selected === (string) $category ? ' selected' : '';
            $html .= '<option value="' . e($category) . '"' . $selectedAttr . '>' . e($category) . '</option>';
        }

        return $html;
    }

    public function buildCountryOptionsHtml(
        User $subscriber,
        array $extraSelected = [],
        ?string $selected = null,
        string $emptyLabel = 'Select Visa Country'
    ): string {
        $countries = $this->resolveCountriesForDropdown($subscriber, $extraSelected);
        $selected = trim((string) ($selected ?? ''));

        if ($selected === '' && $countries->count() === 1) {
            $selected = (string) $countries->first()->country_name;
        }

        $html = '<option value="">' . e($emptyLabel) . '</option>';

        foreach ($countries as $country) {
            $countryName = (string) $country->country_name;
            $selectedAttr = $selected === $countryName ? ' selected' : '';
            $html .= '<option value="' . e($countryName) . '"' . $selectedAttr . '>' . e($countryName) . '</option>';
        }

        return $html;
    }

    public function validateEntrySelection(User $subscriber, ?string $country = null, ?string $visaCategory = null): array
    {
        $errors = [];
        $country = trim((string) ($country ?? ''));
        $visaCategory = trim((string) ($visaCategory ?? ''));

        if ($country !== '') {
            if (is_numeric($country)) {
                $countryModel = Countries::find((int) $country);
                $country = $countryModel ? (string) $countryModel->country_name : '';
            }

            if ($country !== '' && !$this->resolveCountryNames($subscriber, [$country])->contains($country)) {
                $errors['visa_country'] = 'The selected visa country is not enabled in your Countries & Categories settings.';
            }
        }

        if ($visaCategory !== '' && !$this->resolveVisaCategoryNames($subscriber)->contains($visaCategory)) {
            $errors['job_role'] = 'The selected application type is not enabled in your Countries & Categories settings.';
            $errors['visa_category'] = 'The selected visa category is not enabled in your Countries & Categories settings.';
        }

        return $errors;
    }

    public function validateEnquirySelection(User $subscriber, array $countryPreferences, ?string $visaCategory): array
    {
        $errors = [];
        $allowedCountries = $this->resolveCountryNames($subscriber);
        $allowedCategories = $this->resolveVisaCategoryNames($subscriber);

        foreach ($countryPreferences as $index => $country) {
            $country = trim((string) $country);
            if ($country === '') {
                continue;
            }

            if (!$allowedCountries->contains($country)) {
                $errors['country_pref.' . $index] = 'One or more preferred countries are not enabled in your Countries & Categories settings.';
                break;
            }
        }

        $visaCategory = trim((string) ($visaCategory ?? ''));
        if ($visaCategory !== '' && !$allowedCategories->contains($visaCategory)) {
            $errors['visa_category'] = 'The selected visa category is not enabled in your Countries & Categories settings.';
        }

        return $errors;
    }

    private function queryClientJobs(User $subscriber)
    {
        $category = trim((string) ($subscriber->category ?? ''));
        $normalized = $this->normalizeLookupText($category);

        if ($this->isTravelAgencyCategory($category, $normalized)) {
            return $this->resolveTravelAgentClientJobs();
        }

        $resolvedCategory = $this->resolveClientJobCategory($subscriber);

        if ($this->isLawFirmCategory($category, $normalized)) {
            return Client_jobs::where('category', '=', $resolvedCategory)->get();
        }

        $jobs = Client_jobs::where('category', '=', $resolvedCategory)
            ->where('sub_category', '=', $subscriber->sub_category)
            ->get();

        if ($jobs->isEmpty()) {
            $jobs = Client_jobs::where('category', '=', $resolvedCategory)->get();
        }

        return $jobs;
    }

    private function resolveTravelAgentClientJobs()
    {
        return collect(self::TRAVEL_AGENT_APPLICATION_TYPES)->map(fn ($jobName) => (object) [
            'job' => $jobName,
            'category' => 'Travel Agent',
            'sub_category' => null,
        ]);
    }

    private function resolveClientJobCategory(User $subscriber): string
    {
        $category = trim((string) ($subscriber->category ?? ''));
        $normalized = $this->normalizeLookupText($category);

        if ($this->isLawFirmCategory($category, $normalized)) {
            return 'Law Firm';
        }

        if ($this->isTravelAgencyCategory($category, $normalized)) {
            return 'Travel Agent';
        }

        return $category;
    }

    private function usesBroadClientJobCategory(User $subscriber): bool
    {
        $category = trim((string) ($subscriber->category ?? ''));
        $normalized = $this->normalizeLookupText($category);

        return $this->isLawFirmCategory($category, $normalized)
            || $this->isTravelAgencyCategory($category, $normalized);
    }

    private function isLawFirmCategory(string $category, ?string $normalized = null): bool
    {
        $normalized = $normalized ?? $this->normalizeLookupText($category);

        return $category === 'Law Firm' || str_contains($normalized, 'law firm');
    }

    private function isTravelAgencyCategory(string $category, ?string $normalized = null): bool
    {
        $normalized = $normalized ?? $this->normalizeLookupText($category);

        if (in_array($category, ['Travel Agency', 'Travel Agent'], true)) {
            return true;
        }

        if (str_contains($normalized, 'travel agency') || str_contains($normalized, 'travel agent')) {
            return true;
        }

        return str_contains($normalized, 'visit visa') && str_contains($normalized, 'travel');
    }

    private function getRuleBasedSubscriberCountryOptions(?User $subscriber)
    {
        if (!$subscriber) {
            return null;
        }

        $normalizedCategory = $this->normalizeLookupText((string) ($subscriber->category ?? ''));
        $normalizedSubCategory = $this->normalizeLookupText((string) ($subscriber->sub_category ?? ''));
        $fullCategoryText = trim($normalizedCategory . ' ' . $normalizedSubCategory);

        if (!str_contains($fullCategoryText, 'visa')) {
            return null;
        }

        $allCountries = Countries::orderBy('country_name', 'asc')->pluck('country_name')->values();

        $allCountriesWithPriorityPr = $this->prependPriorityCountries($allCountries, [
            'Canada',
            'Australia',
            'New Zealand',
        ]);

        $subCategoryRules = [
            ['keywords' => ['general all countries'], 'countries' => 'all'],
            ['keywords' => ['usa visas immigration attorney', 'us immigration attorney'], 'countries' => ['United States']],
            ['keywords' => ['uk oisc immigration solicitor', 'oisc', 'iaa'], 'countries' => ['United Kingdom']],
            ['keywords' => ['canada iccrc immigration lawyer', 'iccrc', 'cicc', 'rcic'], 'countries' => ['Canada']],
            ['keywords' => ['australia mara immigration lawyer', 'mara'], 'countries' => ['Australia']],
            ['keywords' => ['cbi citizenship by investment consultants', 'cbi', 'citizenship by investment'], 'countries' => [
                'United States',
                'Portugal',
                'Turkey',
                'Grenada',
                'Dominica',
                'United Arab Emirates',
            ]],
            ['keywords' => ['abroad education consultants only study visas', 'study abroad consultant'], 'countries' => 'all'],
            ['keywords' => ['mbbs md dentist medical study visa', 'mbbs'], 'countries' => [
                'China',
                'Philippines',
                'Dominica',
                'Russia',
                'Georgia',
            ]],
            ['keywords' => ['work visa', 'business visa'], 'countries' => 'all'],
            ['keywords' => ['immigration law firm'], 'countries' => 'all'],
            ['keywords' => ['pr', 'settlement visa'], 'countries' => $allCountriesWithPriorityPr->all()],
            ['keywords' => ['other', 'new', 'non listed'], 'countries' => 'all'],
        ];

        foreach ($subCategoryRules as $rule) {
            if (!$this->containsAnyKeyword($normalizedSubCategory, $rule['keywords'])) {
                continue;
            }

            if ($rule['countries'] === 'all') {
                return $allCountries;
            }

            return $this->resolveCountriesByNames($rule['countries']);
        }

        return $allCountries;
    }

    private function getProfileMappedDestinationCountries(?User $subscriber)
    {
        if (!$subscriber) {
            return collect();
        }

        $profileText = collect([
            $subscriber->category ?? null,
            $subscriber->sub_category ?? null,
            $subscriber->organization ?? null,
            $subscriber->designation ?? null,
        ])->filter()->implode(' ');

        if (trim($profileText) === '') {
            return collect();
        }

        $normalizedProfileText = strtolower($profileText);

        $keywordToCountryMap = [
            'Australia' => ['mara'],
            'Canada' => ['iccrc', 'cicc', 'rcic'],
            'United Kingdom' => ['oisc', 'iaa', 'immigration advice authority'],
        ];

        return collect($keywordToCountryMap)
            ->filter(function ($keywords) use ($normalizedProfileText) {
                foreach ($keywords as $keyword) {
                    if (str_contains($normalizedProfileText, $keyword)) {
                        return true;
                    }
                }

                return false;
            })
            ->keys()
            ->values();
    }

    private function normalizeLookupText(string $value): string
    {
        $value = strtolower(trim($value));
        $value = str_replace(['/', '-', '(', ')', '.', ',', ':'], ' ', $value);
        $value = preg_replace('/\s+/', ' ', $value);

        return trim((string) $value);
    }

    private function containsAnyKeyword(string $text, array $keywords): bool
    {
        foreach ($keywords as $keyword) {
            if (str_contains($text, $this->normalizeLookupText((string) $keyword))) {
                return true;
            }
        }

        return false;
    }

    private function resolveCountriesByNames(array $countryNames)
    {
        $synonyms = [
            'United States' => ['United States', 'United States of America', 'USA', 'US'],
            'United Kingdom' => ['United Kingdom', 'UK', 'Great Britain', 'Britain'],
            'United Arab Emirates' => ['United Arab Emirates', 'UAE'],
            'Philippines' => ['Philippines', 'Phillipines'],
        ];

        $allCountries = Countries::orderBy('country_name', 'asc')->pluck('country_name')->values();
        $resolved = collect();

        foreach ($countryNames as $countryName) {
            $countryName = trim((string) $countryName);
            if ($countryName === '') {
                continue;
            }

            $variants = $synonyms[$countryName] ?? [$countryName];
            $foundCountry = $allCountries->first(function ($availableCountry) use ($variants) {
                foreach ($variants as $variant) {
                    if (strcasecmp($availableCountry, $variant) === 0) {
                        return true;
                    }
                }

                return false;
            });

            if ($foundCountry) {
                $resolved->push($foundCountry);
            }
        }

        return $resolved->unique()->values();
    }

    private function prependPriorityCountries($allCountries, array $priorityCountries)
    {
        $resolvedPriority = $this->resolveCountriesByNames($priorityCountries);

        return $resolvedPriority
            ->merge($allCountries->reject(function ($country) use ($resolvedPriority) {
                return $resolvedPriority->contains($country);
            }))
            ->values();
    }

    public function isStudyVisaCategory(?string $applicationType): bool
    {
        $normalized = strtolower(trim((string) $applicationType));
        if ($normalized === '') {
            return false;
        }

        return str_contains($normalized, 'study') || str_contains($normalized, 'student');
    }

    public function isWorkVisaCategory(?string $applicationType): bool
    {
        $normalized = strtolower(trim((string) $applicationType));
        if ($normalized === '') {
            return false;
        }

        if ($this->isNonSponsoredWorkRelatedCategory($normalized)) {
            return false;
        }

        return str_contains($normalized, 'work visa')
            || str_contains($normalized, 'employment')
            || str_contains($normalized, 'sponsored')
            || str_contains($normalized, 'work permit');
    }

    public function applyWorkVisaApplicationScope(Builder $query, string $column = 'application_name'): void
    {
        $query->where(function (Builder $inner) use ($column) {
            $inner->whereRaw('LOWER(COALESCE(' . $column . ', "")) LIKE ?', ['%work visa%'])
                ->orWhereRaw('LOWER(COALESCE(' . $column . ', "")) LIKE ?', ['%employment%'])
                ->orWhereRaw('LOWER(COALESCE(' . $column . ', "")) LIKE ?', ['%sponsored%'])
                ->orWhereRaw('LOWER(COALESCE(' . $column . ', "")) LIKE ?', ['%work permit%']);
        })
            ->whereRaw('LOWER(COALESCE(' . $column . ', "")) NOT LIKE ?', ['%working holiday%'])
            ->whereRaw('LOWER(COALESCE(' . $column . ', "")) NOT LIKE ?', ['%holiday maker%']);
    }

    private function isNonSponsoredWorkRelatedCategory(string $normalized): bool
    {
        return str_contains($normalized, 'working holiday')
            || str_contains($normalized, 'holiday maker');
    }

    public function normalizeVisaDetailValue(?string $value): string
    {
        $value = trim((string) $value);

        return $value !== '' ? $value : 'NA';
    }

    /**
     * @param  array<string, mixed>  $input
     */
    public function applyVisaDetailFields(\App\Models\Applications $application, ?string $applicationType, array $input): void
    {
        $application->course_name = 'NA';
        $application->course_duration = 'NA';
        $application->institution = 'NA';
        $application->intake = 'NA';
        $application->admission_number = 'NA';
        $application->employer_name = 'NA';
        $application->employment_role = 'NA';
        $application->permit_duration = 'NA';
        $application->sponsor_number = 'NA';

        if ($this->isStudyVisaCategory($applicationType)) {
            $application->course_name = $this->normalizeVisaDetailValue($input['course_name'] ?? null);
            $application->course_duration = $this->normalizeVisaDetailValue($input['course_duration'] ?? null);
            $application->institution = $this->normalizeVisaDetailValue($input['institution'] ?? null);
            $application->intake = $this->normalizeVisaDetailValue($input['intake'] ?? null);
            $application->admission_number = $this->normalizeVisaDetailValue($input['admission_number'] ?? null);

            return;
        }

        if ($this->isWorkVisaCategory($applicationType)) {
            $application->employer_name = $this->normalizeVisaDetailValue($input['employer_name'] ?? null);
            $application->employment_role = $this->normalizeVisaDetailValue($input['employment_role'] ?? null);
            $application->permit_duration = $this->normalizeVisaDetailValue($input['permit_duration'] ?? null);
            $application->sponsor_number = $this->normalizeVisaDetailValue($input['sponsor_number'] ?? null);
        }
    }

    /**
     * @return array<string, string>
     */
    public function visaDetailValidationRules(?string $applicationType): array
    {
        $stringRule = 'nullable|string|max:255';

        if ($this->isStudyVisaCategory($applicationType)) {
            return [
                'course_name' => $stringRule,
                'course_duration' => $stringRule,
                'institution' => $stringRule,
                'intake' => $stringRule,
                'admission_number' => $stringRule,
            ];
        }

        if ($this->isWorkVisaCategory($applicationType)) {
            return [
                'employer_name' => $stringRule,
                'employment_role' => $stringRule,
                'permit_duration' => $stringRule,
                'sponsor_number' => $stringRule,
            ];
        }

        return [];
    }
}
