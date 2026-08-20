<?php

namespace App\Support;

use App\Services\CountryCategorySettingsService;

class ApplicationDocumentFolders
{
    public static function resolveForUpload(
        CountryCategorySettingsService $service,
        $requestedFolders,
        ?string $docType
    ): array {
        if (method_exists($service, 'resolveStoredDocumentFolders')) {
            return $service->resolveStoredDocumentFolders($requestedFolders, $docType);
        }

        if (method_exists($service, 'resolveStoredDocumentFolder')) {
            $folder = is_array($requestedFolders) ? ($requestedFolders[0] ?? null) : $requestedFolders;

            return [$service->resolveStoredDocumentFolder($folder, $docType)];
        }

        return [$service->resolveDocumentFolder($docType)];
    }

    public static function resolveForDoc(CountryCategorySettingsService $service, object $doc): array
    {
        if (method_exists($service, 'resolveDocumentFoldersForDoc')) {
            return $service->resolveDocumentFoldersForDoc($doc);
        }

        if (method_exists($service, 'resolveDocumentFolderForDoc')) {
            return [$service->resolveDocumentFolderForDoc($doc)];
        }

        return [$service->resolveDocumentFolder($doc->doc_type ?? null)];
    }
}
