<?php

namespace App\Services;

use App\Models\Activities;
use App\Models\Applications;
use App\Models\Client_Docs;
use App\Models\Clients;
use App\Models\User;
use App\Support\ApplicationDocumentFolders;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RuntimeException;

class DocumentChecklistUploadService
{
    public function __construct(
        private ApplicationDocumentListService $documentListService,
        private CountryCategorySettingsService $ccService,
        private DocumentReminderService $documentReminderService
    ) {
    }

    public function tokenColumnExists(): bool
    {
        return Schema::hasTable('applications')
            && Schema::hasColumn('applications', 'document_checklist_upload_token');
    }

    public function ensureUploadToken(Applications $application): string
    {
        $existing = trim((string) ($application->document_checklist_upload_token ?? ''));
        if ($existing !== '') {
            return $existing;
        }

        if (!$this->tokenColumnExists()) {
            return '';
        }

        $token = Str::random(48);
        $application->document_checklist_upload_token = $token;
        $application->save();

        return $token;
    }

    public function buildUploadUrl(Applications $application): string
    {
        $token = $this->ensureUploadToken($application);
        if ($token === '') {
            return '';
        }

        return route('document_checklist_upload', [
            'application' => $application->id,
            'token' => $token,
        ]);
    }

    public function resolveApplication(int $applicationId, string $token): ?Applications
    {
        if (!$this->tokenColumnExists()) {
            return null;
        }

        $token = trim($token);
        if ($token === '') {
            return null;
        }

        return Applications::query()
            ->where('id', $applicationId)
            ->where('document_checklist_upload_token', $token)
            ->first();
    }

    /**
     * @return array{
     *     application: Applications,
     *     client_name: string,
     *     subscriber_name: string,
     *     country: string,
     *     category: string,
     *     application_ref: string,
     *     checklist_items: array<int, array{label: string, section: string, status: string}>
     * }
     */
    public function buildUploadPageData(Applications $application): array
    {
        $application->loadMissing('client', 'subscriber');
        $subscriber = User::find($application->subscriber_id);
        if (!$subscriber) {
            throw new RuntimeException('Unable to load the application owner.');
        }

        $checklistItems = $this->documentReminderService->buildDocumentChecklistItems($subscriber, $application);

        return [
            'application' => $application,
            'client_name' => $this->documentListService->resolveClientName($application),
            'subscriber_name' => trim((string) ($subscriber->name ?? '')),
            'country' => $this->documentListService->resolveApplicationCountry($application),
            'category' => $this->documentListService->resolveApplicationVisaCategory($application),
            'application_ref' => trim((string) ($application->application_id ?? '')),
            'checklist_items' => $checklistItems,
            'document_folders' => $this->ccService->getDocumentFolders(),
        ];
    }

    public function handleUpload(Applications $application, Request $request): array
    {
        $application->loadMissing('client');
        $client = Clients::find($application->client_id);
        if (!$client) {
            return [
                'success' => false,
                'message' => 'Client record not found for this application.',
            ];
        }

        $subscriber = User::find($application->subscriber_id);
        if (!$subscriber) {
            return [
                'success' => false,
                'message' => 'Unable to process the upload right now.',
            ];
        }

        $request->validate([
            'doc_file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:4096',
            'doc_type' => 'required|string|max:100',
            'doc_name' => 'required|string|min:3|max:100',
            'doc_folder' => 'required|string|max:120',
        ], [
            'doc_file.mimes' => 'Please select a valid file format (jpg, jpeg, png, pdf).',
            'doc_file.max' => 'Please select a file up to 4 MB.',
        ]);

        $docFolders = ApplicationDocumentFolders::resolveForUpload(
            $this->ccService,
            $request->input('doc_folder'),
            $request->input('doc_type')
        );
        $docFolder = $docFolders[0] ?? 'Other';

        $document = new Client_Docs();
        $document->client_id = $client->id;
        $document->application_id = $application->application_id;
        $document->user_id = $subscriber->id;
        $document->doc_name = trim((string) $request->input('doc_name'));
        $document->doc_type = trim((string) $request->input('doc_type'));
        $document->doc_folder = $docFolder;
        $document->doc_folders = $docFolders;

        $file = $request->file('doc_file');
        $directory = public_path('web_assets/users/client' . $client->id . '/docs');
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $filename = \App\Support\DocumentFileName::storageName($document->doc_name, $file->getClientOriginalName());
        $file->move($directory, $filename);
        $document->doc_file = $filename;
        $document->save();

        $activity = new Activities();
        $activity->subscriber_id = $subscriber->id;
        $activity->client_id = $client->id;
        $activity->activity_name = 'Document Added';
        $activity->activity_detail = 'Client uploaded '
            . $document->doc_name
            . ' via documents checklist link for application '
            . ($application->application_id ?? $application->id)
            . '.';
        $activity->activity_icon = 'user.png';
        $activity->save();

        return [
            'success' => true,
            'message' => $document->doc_name . ' uploaded successfully.',
        ];
    }
}
