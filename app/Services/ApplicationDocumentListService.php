<?php

namespace App\Services;

use App\Models\Applications;
use App\Models\Client_Docs;
use App\Models\Clients;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use RuntimeException;

class ApplicationDocumentListService
{
    public function __construct(
        private CountryCategorySettingsService $ccService
    ) {
    }

    public function resolveApplicationCountry(Applications $application): string
    {
        $candidates = $this->resolveApplicationCountryCandidates($application);

        if ($candidates !== []) {
            return $candidates[0];
        }

        return '—';
    }

    public function resolveApplicationCountryCandidates(Applications $application): array
    {
        if (!$application->relationLoaded('client')) {
            $application->load('client');
        }

        return array_values(array_unique(array_filter([
            trim((string) ($application->visa_country ?? '')),
            trim((string) (optional($application->client)->visa_country ?? '')),
            trim((string) ($application->application_country ?? '')),
        ], fn ($value) => $value !== '' && $value !== '—')));
    }

    public function resolveApplicationVisaCategory(Applications $application): string
    {
        $candidates = $this->resolveApplicationCategoryCandidates($application);

        if ($candidates !== []) {
            return $candidates[0];
        }

        return '—';
    }

    public function resolveApplicationCategoryCandidates(Applications $application): array
    {
        return array_values(array_unique(array_filter([
            trim((string) ($application->application_name ?? '')),
        ], fn ($value) => $value !== '' && $value !== '—')));
    }

    public function resolveClientName(Applications $application): string
    {
        $clientName = trim((string) ($application->client_name ?? ''));

        if ($clientName !== '') {
            return $clientName;
        }

        $client = $application->relationLoaded('client')
            ? $application->client
            : Clients::find($application->client_id);

        return trim((string) ($client->name ?? '—')) ?: '—';
    }

    public function resolveSubscriberForApplication(User $user, Applications $application): User
    {
        if ($user->user_type === 'admin') {
            $subscriber = User::find($application->subscriber_id);

            return $subscriber ?: $this->ccService->resolveSubscriber($user);
        }

        return $this->ccService->resolveSubscriber($user);
    }

    public function buildPdfFileName(string $country, string $category): string
    {
        $parts = array_values(array_filter([
            $this->fileNameSegment($country),
            $this->fileNameSegment($category),
        ], fn ($part) => $part !== ''));

        if ($parts === []) {
            return 'Documents_Checklist.pdf';
        }

        return implode('_', $parts) . '_Documents_Checklist.pdf';
    }

    public function buildPdfPayload(User $user, Applications $application): array
    {
        $subscriber = $this->resolveSubscriberForApplication($user, $application);
        $country = $this->resolveApplicationCountry($application);
        $visaCategory = $this->resolveApplicationVisaCategory($application);
        $entry = $this->ccService->resolveDocumentListEntryWithCandidates(
            $subscriber,
            $this->resolveApplicationCountryCandidates($application),
            $this->resolveApplicationCategoryCandidates($application)
        );

        $uploadedDocuments = $this->getUploadedDocuments($user, $application);

        if ($entry) {
            $sections = $this->ccService->buildNumberedDocumentSections($entry);
            $country = trim((string) ($entry['country'] ?? $country));
            $visaCategory = trim((string) ($entry['visa_category'] ?? $visaCategory));
            $sections = $this->mergeUploadedPreviewsIntoSections($sections, $uploadedDocuments);
        } else {
            $sections = $this->buildSectionsFromUploadedDocuments($uploadedDocuments);
        }

        if ($sections === []) {
            throw new RuntimeException(
                'No document list is configured for ' . $country . ' / ' . $visaCategory
                . '. Please add it under Settings → Countries & Visa Categories, or upload documents to this application.'
            );
        }

        $now = now();
        if (!empty($user->timezone)) {
            try {
                $now = now()->timezone($user->timezone);
            } catch (\Exception $e) {
            }
        }

        return [
            'client_name' => $this->resolveClientName($application),
            'country' => $country,
            'category' => $visaCategory,
            'application_name' => $visaCategory,
            'given_by' => trim((string) ($user->name ?? '—')) ?: '—',
            'date' => $now->format('d/m/Y'),
            'time' => $now->format('h:i A'),
            'datetime' => $now->format('d/m/Y h:i A'),
            'sections' => $sections,
        ];
    }

    public function hasConfiguredList(User $user, Applications $application): bool
    {
        try {
            $this->buildPdfPayload($user, $application);

            return true;
        } catch (RuntimeException $e) {
            return false;
        }
    }

    /**
     * @return array<int, string>
     */
    public function resolveMissingDocuments(User $subscriber, Applications $application): array
    {
        $entry = $this->ccService->resolveDocumentListEntryWithCandidates(
            $subscriber,
            $this->resolveApplicationCountryCandidates($application),
            $this->resolveApplicationCategoryCandidates($application)
        );

        if (!$entry) {
            return [];
        }

        $uploadedDocuments = $this->getUploadedDocuments(
            User::find($subscriber->id) ?? $subscriber,
            $application
        );
        $uploadedKeys = [];

        foreach ($uploadedDocuments as $doc) {
            foreach ([$doc->doc_type ?? '', $doc->doc_name ?? ''] as $value) {
                $key = $this->normalizeMatchKey((string) $value);
                if ($key !== '') {
                    $uploadedKeys[$key] = true;
                }
            }
        }

        $missing = [];

        foreach ($this->ccService->buildNumberedDocumentSections($entry) as $section) {
            foreach ($section['items'] as $item) {
                $label = trim((string) ($item['label'] ?? ''));
                if ($label === '') {
                    continue;
                }

                if (!isset($uploadedKeys[$this->normalizeMatchKey($label)])) {
                    $missing[] = $label;
                }
            }
        }

        return $missing;
    }

    public function streamPdf(User $user, Applications $application): Response
    {
        $payload = $this->buildPdfPayload($user, $application);
        $fileName = $this->buildPdfFileName($payload['country'], $payload['category']);

        return $this->makePdf($payload)->stream($fileName);
    }

    public function downloadPdf(User $user, Applications $application): Response
    {
        $payload = $this->buildPdfPayload($user, $application);
        $fileName = $this->buildPdfFileName($payload['country'], $payload['category']);

        return $this->makePdf($payload)->download($fileName);
    }

    /**
     * Raw PDF bytes for the application's document list, for mail attachments.
     */
    public function renderPdfOutput(array $payload): string
    {
        return $this->makePdf($payload)->output();
    }

    public function getUploadedDocuments(User $user, Applications $application): Collection
    {
        $documentsQuery = Client_Docs::where('application_id', $application->application_id)
            ->whereNotNull('doc_file')
            ->where('doc_file', '!=', '');

        if ($user->user_type !== 'admin') {
            $subscriberId = $user->user_type === 'Subscriber' ? $user->id : $user->added_by;
            $documentsQuery->where('user_id', $subscriberId);
        }

        return $documentsQuery->orderBy('doc_type')->orderByDesc('created_at')->get();
    }

    public function buildSectionsFromUploadedDocuments(Collection $documents): array
    {
        if ($documents->isEmpty()) {
            return [];
        }

        $documentsByFolder = $this->ccService->groupDocumentsByFolder($documents);
        $sections = [];

        foreach ($documentsByFolder as $folder => $folderDocs) {
            $items = [];

            foreach ($folderDocs as $doc) {
                $item = $this->buildItemFromUploadedDoc($doc);
                if ($item !== null) {
                    $items[] = $item;
                }
            }

            if ($items !== []) {
                $sections[] = [
                    'title' => $folder,
                    'items' => $items,
                ];
            }
        }

        return $sections;
    }

    private function mergeUploadedPreviewsIntoSections(array $sections, Collection $uploadedDocuments): array
    {
        if ($uploadedDocuments->isEmpty()) {
            return $sections;
        }

        $docsByKey = $this->indexUploadedDocuments($uploadedDocuments);
        $usedDocIds = [];

        foreach ($sections as $sectionIndex => $section) {
            foreach ($section['items'] as $itemIndex => $item) {
                $doc = $this->findUploadedDocumentForLabel((string) ($item['label'] ?? ''), $docsByKey, $usedDocIds);
                if (!$doc) {
                    continue;
                }

                $sections[$sectionIndex]['items'][$itemIndex] = array_merge(
                    $item,
                    $this->resolveDocumentPreview($doc)
                );
                $usedDocIds[] = $doc->id;
            }
        }

        return $sections;
    }

    private function indexUploadedDocuments(Collection $uploadedDocuments): array
    {
        $docsByKey = [];

        foreach ($uploadedDocuments as $doc) {
            foreach ($this->documentMatchKeys($doc) as $key) {
                if (!isset($docsByKey[$key])) {
                    $docsByKey[$key] = $doc;
                }
            }
        }

        return $docsByKey;
    }

    private function findUploadedDocumentForLabel(string $label, array $docsByKey, array $usedDocIds): ?Client_Docs
    {
        $key = $this->normalizeMatchKey($label);
        if ($key === '' || !isset($docsByKey[$key])) {
            return null;
        }

        $doc = $docsByKey[$key];
        if (in_array($doc->id, $usedDocIds, true)) {
            return null;
        }

        return $doc;
    }

    private function buildItemFromUploadedDoc(Client_Docs $doc): ?array
    {
        $label = trim((string) ($doc->doc_name ?: $doc->doc_type ?: $doc->doc_file));
        if ($label === '') {
            return null;
        }

        return array_merge([
            'number' => 0,
            'label' => $label,
        ], $this->resolveDocumentPreview($doc));
    }

    private function documentMatchKeys(Client_Docs $doc): array
    {
        return array_values(array_unique(array_filter([
            $this->normalizeMatchKey((string) ($doc->doc_type ?? '')),
            $this->normalizeMatchKey((string) ($doc->doc_name ?? '')),
        ], fn ($key) => $key !== '')));
    }

    private function normalizeMatchKey(string $value): string
    {
        return strtolower(preg_replace('/\s+/', ' ', trim($value)));
    }

    private function resolveDocumentPreview(Client_Docs $doc): array
    {
        $filePath = public_path('web_assets/users/client' . $doc->client_id . '/docs/' . $doc->doc_file);

        if (empty($doc->doc_file) || !is_file($filePath)) {
            return [
                'preview_type' => null,
                'preview_src' => null,
                'file_label' => null,
            ];
        }

        $extension = strtolower(pathinfo($doc->doc_file, PATHINFO_EXTENSION));

        if (in_array($extension, ['jpg', 'jpeg', 'png'], true)) {
            $mime = $extension === 'png' ? 'image/png' : 'image/jpeg';
            $contents = @file_get_contents($filePath);

            if ($contents === false) {
                return [
                    'preview_type' => null,
                    'preview_src' => null,
                    'file_label' => null,
                ];
            }

            return [
                'preview_type' => 'image',
                'preview_src' => 'data:' . $mime . ';base64,' . base64_encode($contents),
                'file_label' => $doc->doc_file,
            ];
        }

        if ($extension === 'pdf') {
            return [
                'preview_type' => 'pdf',
                'preview_src' => null,
                'file_label' => $doc->doc_file,
            ];
        }

        return [
            'preview_type' => null,
            'preview_src' => null,
            'file_label' => $doc->doc_file,
        ];
    }

    private function makePdf(array $payload)
    {
        return Pdf::loadView('web.application_document_list_pdf', $payload)
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isPhpEnabled', true);
    }

    private function fileNameSegment(string $value): string
    {
        $value = preg_replace('/[^a-z0-9]+/i', '_', trim($value));
        $value = trim((string) $value, '_');

        return $value;
    }
}
