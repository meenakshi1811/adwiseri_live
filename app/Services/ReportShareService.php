<?php

namespace App\Services;

use App\Mail\SharedReportChartMail;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ReportShareService
{
    public function __construct(
        private readonly EmailBroadcastService $emailBroadcastService,
        private readonly OfferBenefitService $offerBenefitService
    ) {
    }

    public function staffMembersForSubscriber(User $subscriber)
    {
        return User::query()
            ->where('added_by', $subscriber->id)
            ->where('user_type', 'User')
            ->orderBy('designation')
            ->orderBy('name')
            ->get();
    }

    public function canShareReports(User $user): bool
    {
        if ($user->user_type === 'admin') {
            return true;
        }

        if ($user->user_type === 'Subscriber') {
            return true;
        }

        $reportRoles = \App\Models\UserRoles::where('user_id', $user->id)
            ->where('module', 'Reports')
            ->first();

        return $reportRoles && ($reportRoles->read_only == 1 || $reportRoles->read_write_only == 1);
    }

    public function canShareAnalytics(User $user): bool
    {
        if ($user->user_type === 'admin') {
            return true;
        }

        if ($user->user_type === 'Subscriber') {
            return $this->offerBenefitService->hasAnalyticsAccess($user);
        }

        $subscriber = $this->offerBenefitService->resolveSubscriber($user);

        return $this->offerBenefitService->hasAnalyticsAccess($subscriber);
    }

    public function share(User $sender, User $subscriber, string $pdfName, string $pdfBinary, array $recipients): array
    {
        $resolvedRecipients = $this->emailBroadcastService->resolveStaffRecipients((int) $subscriber->id, $recipients);

        if ($resolvedRecipients === []) {
            throw new \InvalidArgumentException(
                'No valid staff recipients were found. Please select staff members who have an email address on file.'
            );
        }

        $safeFileName = $this->sanitizePdfFileName($pdfName);
        $tempPath = $this->storeTempPdf($pdfBinary, $safeFileName);

        $sent = 0;
        $errors = [];
        $sentTo = [];

        foreach ($resolvedRecipients as $recipient) {
            try {
                $mailData = [
                    'name' => $recipient['name'] ?? 'Team Member',
                    'recipient_name' => $recipient['name'] ?? 'Team Member',
                    'consultancy_name' => $subscriber->organization ?: $subscriber->name,
                    'shared_by' => $sender->name,
                    'report_name' => pathinfo($safeFileName, PATHINFO_FILENAME),
                    'subject' => pathinfo($safeFileName, PATHINFO_FILENAME),
                ];

                Mail::to($recipient['email'])->send(new SharedReportChartMail($mailData, $tempPath, $safeFileName, $subscriber));
                $sent++;
                $sentTo[] = [
                    'name' => trim((string) ($recipient['name'] ?? '')),
                    'email' => trim((string) ($recipient['email'] ?? '')),
                ];
            } catch (\Throwable $e) {
                Log::warning('Report share email failed', [
                    'recipient' => $recipient['email'] ?? null,
                    'sender_id' => $sender->id,
                    'error' => $e->getMessage(),
                ]);
                $errors[] = [
                    'email' => trim((string) ($recipient['email'] ?? '')),
                    'message' => trim($e->getMessage()) ?: 'Email delivery failed.',
                ];
            }
        }

        @unlink($tempPath);

        return [
            'sent' => $sent,
            'errors' => $errors,
            'sent_to' => $sentTo,
            'recipients' => count($resolvedRecipients),
        ];
    }

    public function readUploadedPdf($uploadedFile): string
    {
        $binary = file_get_contents($uploadedFile->getRealPath());
        if (!is_string($binary) || strlen($binary) < 100) {
            throw new \InvalidArgumentException('Invalid PDF data received.');
        }

        if (strncmp($binary, '%PDF', 4) !== 0) {
            throw new \InvalidArgumentException('Uploaded file is not a valid PDF.');
        }

        return $binary;
    }

    public function decodePdfPayload(?string $pdfData): string
    {
        $pdfData = trim((string) $pdfData);
        if ($pdfData === '') {
            throw new \InvalidArgumentException('PDF data is required.');
        }

        if (str_contains($pdfData, ',')) {
            $pdfData = substr($pdfData, strpos($pdfData, ',') + 1);
        }

        $binary = base64_decode($pdfData, true);
        if ($binary === false || strlen($binary) < 100) {
            throw new \InvalidArgumentException('Invalid PDF data received.');
        }

        if (strncmp($binary, '%PDF', 4) !== 0) {
            throw new \InvalidArgumentException('Uploaded file is not a valid PDF.');
        }

        return $binary;
    }

    private function storeTempPdf(string $pdfBinary, string $fileName): string
    {
        $directory = storage_path('app/report-share');
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $path = $directory . '/' . Str::uuid() . '_' . $fileName;
        file_put_contents($path, $pdfBinary);

        return $path;
    }

    /**
     * @param  array{sent?: int, errors?: array, sent_to?: array<int, array{name?: string, email?: string}>}  $result
     */
    public function buildShareResultMessage(string $itemLabel, array $result): string
    {
        $sentTo = $result['sent_to'] ?? [];
        $errors = $result['errors'] ?? [];
        $sentDetails = $this->formatShareRecipients($sentTo);
        $failureDetails = $this->formatShareErrors($errors);

        if ($sentDetails === '') {
            return $itemLabel . ' shared with ' . (int) ($result['sent'] ?? 0) . ' recipient(s).';
        }

        if ($failureDetails !== '') {
            return $itemLabel . ' sent to: ' . $sentDetails . '. Could not be sent to: ' . $failureDetails . '.';
        }

        return $itemLabel . ' shared with: ' . $sentDetails . '.';
    }

    /**
     * @param  array<int, array{name?: string, email?: string}>  $sentTo
     */
    public function formatShareRecipients(array $sentTo): string
    {
        if ($sentTo === []) {
            return '';
        }

        $parts = [];

        foreach ($sentTo as $recipient) {
            $name = trim((string) ($recipient['name'] ?? ''));
            $email = trim((string) ($recipient['email'] ?? ''));

            if ($name !== '' && $email !== '') {
                $parts[] = $name . ' (' . $email . ')';
            } elseif ($name !== '') {
                $parts[] = $name;
            } elseif ($email !== '') {
                $parts[] = $email;
            }
        }

        return implode(', ', $parts);
    }

    /**
     * @param  array<int, array{email?: string, message?: string}|string>  $errors
     */
    public function formatShareErrors(array $errors): string
    {
        if ($errors === []) {
            return '';
        }

        $parts = [];

        foreach ($errors as $error) {
            if (is_array($error)) {
                $email = trim((string) ($error['email'] ?? ''));
                $message = trim((string) ($error['message'] ?? ''));

                if ($email !== '' && $message !== '') {
                    $parts[] = $email . ' (' . $message . ')';
                } elseif ($email !== '') {
                    $parts[] = $email;
                } elseif ($message !== '') {
                    $parts[] = $message;
                }

                continue;
            }

            $text = trim((string) $error);
            if ($text !== '') {
                $parts[] = $text;
            }
        }

        return implode('; ', $parts);
    }

    private function sanitizePdfFileName(string $pdfName): string
    {
        $pdfName = trim($pdfName);
        if ($pdfName === '') {
            $pdfName = 'Report.pdf';
        }

        $pdfName = preg_replace('/[^\w\s\-\(\)\.]/', '', $pdfName) ?: 'Report.pdf';
        if (!Str::endsWith(strtolower($pdfName), '.pdf')) {
            $pdfName .= '.pdf';
        }

        return $pdfName;
    }
}
