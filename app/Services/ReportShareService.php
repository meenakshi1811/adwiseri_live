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
            throw new \InvalidArgumentException('Please select at least one valid staff recipient.');
        }

        $safeFileName = $this->sanitizePdfFileName($pdfName);
        $tempPath = $this->storeTempPdf($pdfBinary, $safeFileName);

        $sent = 0;
        $errors = [];

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
            } catch (\Throwable $e) {
                Log::warning('Report share email failed', [
                    'recipient' => $recipient['email'] ?? null,
                    'sender_id' => $sender->id,
                    'error' => $e->getMessage(),
                ]);
                $errors[] = $recipient['email'] ?? 'unknown recipient';
            }
        }

        @unlink($tempPath);

        return [
            'sent' => $sent,
            'errors' => $errors,
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
