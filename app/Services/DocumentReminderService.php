<?php

namespace App\Services;

use App\Models\Applications;
use App\Models\User;
use App\Support\ApplicationStatuses;
use Illuminate\Support\Collection;

class DocumentReminderService
{
    public function __construct(
        private ApplicationDocumentListService $documentListService,
        private CountryCategorySettingsService $ccService
    ) {
    }

    /**
     * @return array<int, array{application: Applications, client_name: string, client_email: string, missing_documents: array<int, string>}>
     */
    public function applicationsWithMissingDocuments(User $subscriber): array
    {
        $applications = Applications::query()
            ->where('subscriber_id', $subscriber->id)
            ->whereNotIn('application_status', ApplicationStatuses::INACTIVE)
            ->with('client')
            ->orderByDesc('updated_at')
            ->get();

        $results = [];

        foreach ($applications as $application) {
            $missing = $this->documentListService->resolveMissingDocuments($subscriber, $application);
            if ($missing === []) {
                continue;
            }

            $clientEmail = trim((string) optional($application->client)->email);
            if ($clientEmail === '') {
                continue;
            }

            $results[] = [
                'application' => $application,
                'client_name' => $this->documentListService->resolveClientName($application),
                'client_email' => $clientEmail,
                'missing_documents' => $missing,
            ];
        }

        return $results;
    }

    /**
     * @return array<int, array{label: string, status: string}>
     */
    public function buildDocumentChecklistItems(User $user, Applications $application): array
    {
        $subscriber = $this->documentListService->resolveSubscriberForApplication($user, $application);
        $entry = $this->ccService->resolveDocumentListEntryWithCandidates(
            $subscriber,
            $this->documentListService->resolveApplicationCountryCandidates($application),
            $this->documentListService->resolveApplicationCategoryCandidates($application)
        );

        if (!$entry) {
            return [];
        }

        $uploadedDocuments = $this->documentListService->getUploadedDocuments($user, $application);
        $uploadedKeys = $this->indexUploadedDocumentKeys($uploadedDocuments);
        $items = [];

        foreach ($this->ccService->buildNumberedDocumentSections($entry) as $section) {
            foreach ($section['items'] as $item) {
                $label = trim((string) ($item['label'] ?? ''));
                if ($label === '') {
                    continue;
                }

                $items[] = [
                    'label' => $label,
                    'section' => (string) ($section['title'] ?? ''),
                    'status' => $this->isDocumentUploaded($label, $uploadedKeys) ? 'received' : 'missing',
                ];
            }
        }

        return $items;
    }

    private function indexUploadedDocumentKeys(Collection $uploadedDocuments): array
    {
        $keys = [];

        foreach ($uploadedDocuments as $doc) {
            foreach ([$doc->doc_type ?? '', $doc->doc_name ?? ''] as $value) {
                $key = $this->normalizeKey((string) $value);
                if ($key !== '') {
                    $keys[$key] = true;
                }
            }
        }

        return $keys;
    }

    private function isDocumentUploaded(string $label, array $uploadedKeys): bool
    {
        return isset($uploadedKeys[$this->normalizeKey($label)]);
    }

    private function normalizeKey(string $value): string
    {
        return strtolower(preg_replace('/\s+/', ' ', trim($value)));
    }
}
