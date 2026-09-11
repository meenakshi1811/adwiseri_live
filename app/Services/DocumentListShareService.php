<?php

namespace App\Services;

use App\Models\User;
use RuntimeException;

class DocumentListShareService
{
    public function __construct(
        private ApplicationDocumentListService $documentListService,
        private ReportShareService $reportShareService,
        private CountryCategorySettingsService $ccService
    ) {
    }

    /**
     * @return array{sent: int, errors: array<int, array{email?: string, message?: string}|string>, sent_to: array<int, array{name?: string, email?: string}>, recipients: int}
     */
    public function shareToStaff(
        User $sender,
        User $subscriber,
        string $country,
        string $visaCategory,
        array $recipientIds
    ): array {
        $country = trim($country);
        $visaCategory = trim($visaCategory);

        if ($country === '' || $visaCategory === '') {
            throw new \InvalidArgumentException('Country and visa category are required.');
        }

        $entry = $this->ccService->resolveDocumentListEntry($subscriber, $country, $visaCategory);
        if (!$entry) {
            throw new RuntimeException('No document list found for this country and visa category.');
        }

        $payload = $this->documentListService->buildTemplatePdfPayload($sender, $subscriber, $country, $visaCategory);
        $pdfBinary = $this->documentListService->renderPdfOutput($payload);
        $fileName = $this->documentListService->buildPdfFileName($payload['country'], $payload['category']);

        return $this->reportShareService->share($sender, $subscriber, $fileName, $pdfBinary, $recipientIds);
    }
}
