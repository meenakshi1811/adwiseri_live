<?php

namespace App\Services;

use App\Mail\DocumentListMail;
use App\Models\Activities;
use App\Models\Applications;
use App\Models\User;
use App\Support\BrandedMail;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class DocumentChecklistMailService
{
    public function __construct(
        private ApplicationDocumentListService $documentListService
    ) {
    }

    public function recipientEmail(Applications $application, ?string $override = null): ?string
    {
        $application->loadMissing('client');
        $email = trim((string) ($override ?? optional($application->client)->email ?? ''));

        return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
    }

    public function send(
        Applications $application,
        User $actingUser,
        ?User $subscriber = null,
        ?string $toEmail = null,
        ?string $customMessage = null
    ): array {
        $application->loadMissing('client');
        $subscriber = $subscriber ?? $this->documentListService->resolveSubscriberForApplication($actingUser, $application);

        $recipient = $this->recipientEmail($application, $toEmail);
        if ($recipient === null) {
            return [
                'success' => false,
                'skipped' => true,
                'message' => 'No valid client email address is on record.',
                'recipient' => null,
            ];
        }

        if (!$this->documentListService->hasConfiguredList($actingUser, $application)) {
            return [
                'success' => false,
                'skipped' => true,
                'message' => 'No document list is configured for this application\'s country and visa category.',
                'recipient' => $recipient,
            ];
        }

        try {
            $payload = $this->documentListService->buildPdfPayload($actingUser, $application);
        } catch (RuntimeException $e) {
            return [
                'success' => false,
                'skipped' => true,
                'message' => $e->getMessage(),
                'recipient' => $recipient,
            ];
        }

        $payload['application_id'] = $application->application_id;
        $payload['subscriber_name'] = $subscriber->name ?? '';
        $payload['subscriber_email'] = $subscriber->email ?? '';
        if ($customMessage !== null) {
            $payload['custom_message'] = trim($customMessage);
        }

        $fileName = $this->documentListService->buildPdfFileName($payload['country'], $payload['category']);

        try {
            $pdfContents = $this->documentListService->renderPdfOutput($payload);
            BrandedMail::sendWithAlertsArchive(
                $recipient,
                fn () => new DocumentListMail($payload, $pdfContents, $fileName)
            );

            $this->logSentActivity($application, $actingUser, $subscriber, $recipient, $payload);
            $this->markChecklistSent($application, $recipient);

            return [
                'success' => true,
                'skipped' => false,
                'message' => 'Documents checklist emailed to ' . $recipient . '.',
                'recipient' => $recipient,
            ];
        } catch (Throwable $e) {
            Log::error('Document checklist email failed', [
                'application_id' => $application->id,
                'recipient' => $recipient,
                'message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'skipped' => false,
                'message' => 'The documents checklist could not be emailed right now. Please try again, or download the PDF and send it manually.',
                'recipient' => $recipient,
            ];
        }
    }

    public function sendOnApplicationCreated(Applications $application, User $actingUser, ?User $subscriber = null): array
    {
        return $this->send($application, $actingUser, $subscriber);
    }

    private function markChecklistSent(Applications $application, string $recipient): void
    {
        $application->document_checklist_sent_at = now();
        $application->document_checklist_sent_to = $recipient;
        $application->save();
    }

    private function logSentActivity(
        Applications $application,
        User $actingUser,
        User $subscriber,
        string $recipient,
        array $payload
    ): void {
        $activity = new Activities();
        $activity->subscriber_id = $application->subscriber_id;
        $activity->user_id = $actingUser->id;
        $activity->client_id = $application->client_id;
        $activity->activity_name = 'Sent Documents List';
        $activity->activity_detail = 'Emailed the '
            . ($payload['country'] ?? '')
            . ' / '
            . ($payload['category'] ?? '')
            . ' documents list to '
            . $recipient
            . '.';
        $activity->save();
    }
}
