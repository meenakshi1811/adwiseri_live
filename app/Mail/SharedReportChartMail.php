<?php

namespace App\Mail;

use App\Models\User;
use App\Support\BrandedMail;
use App\Support\ReportMailAttachment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SharedReportChartMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public array $mailData,
        public string $filePath,
        public string $fileName,
        public ?User $subscriber = null
    ) {
    }

    public function build()
    {
        $subject = trim((string) ($this->mailData['subject'] ?? pathinfo($this->fileName, PATHINFO_FILENAME)));
        $headerTitle = 'Shared Report';

        $content = BrandedMail::renderBody('emails.bodies.shared_report_chart', [
            'data' => $this->mailData,
            'fileName' => $this->fileName,
        ]);
        $content = BrandedMail::ensureResponsiveEmailHtml($content);

        $mail = BrandedMail::applyPlatformEnvelope(
            $this->subject($subject)
                ->view(BrandedMail::LAYOUT, compact('content', 'headerTitle'))
        );

        if ($this->subscriber) {
            BrandedMail::applySubscriberReplyTo($mail, $this->subscriber->email, $this->subscriber->name);
        }

        return ReportMailAttachment::attachReportPdf($mail, $this->filePath, $this->fileName);
    }
}
