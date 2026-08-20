<?php

namespace App\Mail;

use App\Services\EmailTemplateService;
use App\Support\BrandedMail;
use App\Support\ReportMailAttachment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ScheduledReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public array $mailData,
        public string $filePath,
        public string $fileName
    ) {
    }

    public function build()
    {
        try {
            $template = app(EmailTemplateService::class)->getTemplateForUser(null, 'subscriber', 'reports');
        } catch (\Throwable $e) {
            $template = null;
        }
        $defaultSubject = $this->mailData['subject'] ?? 'Reports';
        $headerTitle = 'Scheduled Report';

        if ($template && !empty(trim((string) $template->body))) {
            $content = BrandedMail::replacePlaceholders($template->body, $this->mailData);
        } else {
            $content = BrandedMail::renderBody('emails.bodies.scheduled_report', ['data' => $this->mailData]);
        }

        $content = $this->ensureDownloadSection($content);
        $content = BrandedMail::ensureResponsiveEmailHtml($content);

        $subject = !empty($this->mailData['subject'])
            ? $this->mailData['subject']
            : BrandedMail::replacePlaceholders(
                ($template && $template->subject) ? $template->subject : $defaultSubject,
                $this->mailData
            );

        $mail = BrandedMail::applyPlatformEnvelope(
            $this->subject($subject)
                ->view(BrandedMail::LAYOUT, compact('content', 'headerTitle'))
        );

        return ReportMailAttachment::attachReportPdf($mail, $this->filePath, $this->fileName);
    }

    private function ensureDownloadSection(string $content): string
    {
        $downloadLink = trim((string) ($this->mailData['download_link'] ?? ''));
        if ($downloadLink === '') {
            return $content;
        }

        if (str_contains($content, $downloadLink)) {
            return $content;
        }

        return $content . BrandedMail::renderBody('emails.partials.report_download', [
            'downloadLink' => $downloadLink,
            'fileName' => $this->fileName,
        ]);
    }
}
