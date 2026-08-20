<?php

namespace App\Mail;

use App\Services\EmailTemplateService;
use App\Support\BrandedMail;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    protected $data;

    public function __construct($maildata)
    {
        $this->data = $maildata;
    }

    public function build()
    {
        $data = $this->data;
        $templateService = app(EmailTemplateService::class);
        $owner = $templateService->resolveTemplateOwner($data);
        $template = $templateService->getTemplateForUser($owner, 'admin', 'welcome_email_admin_to_subscriber');

        $defaultSubject = 'Welcome to Adwiseri';
        $headerTitle = 'Welcome to Adwiseri';
        $content = null;

        if ($template && !empty(trim((string) $template->body))) {
            $payload = $this->buildPlaceholderData($data);
            $content = BrandedMail::replacePlaceholders($template->body, $payload);
            $subject = BrandedMail::replacePlaceholders($template->subject ?: $defaultSubject, $payload);
        } else {
            $subject = $defaultSubject;
        }

        $bodyHtml = BrandedMail::renderBody('emails.bodies.welcome', compact('data', 'content'));
        $mail = $this->subject($subject ?? $defaultSubject)
            ->view(BrandedMail::LAYOUT, [
                'content' => $bodyHtml,
                'headerTitle' => $headerTitle,
            ]);

        if (!empty($data->from_email)) {
            $mail->from($data->from_email, $this->sanitizeFromName($data->from_name ?? null));
        } else {
            BrandedMail::applyPlatformFrom($mail);
        }

        // Reply-To: care@adwiseri.com (never the From/SMTP address)
        BrandedMail::applyDefaultReplyTo($mail);

        if (!empty($data->invoice_pdf_data)) {
            $invoiceData = is_array($data->invoice_pdf_data)
                ? (object) $data->invoice_pdf_data
                : $data->invoice_pdf_data;

            $pdf = Pdf::loadView('web.invoice_pdf', ['data' => $invoiceData])
                ->setPaper('a4', 'portrait')
                ->setOption('isHtml5ParserEnabled', true)
                ->setOption('isPhpEnabled', true);
            $invoiceNo = $invoiceData->invoice_no ?? 'document';
            $mail->attachData($pdf->output(), 'Invoice-' . $invoiceNo . '.pdf', [
                'mime' => 'application/pdf',
            ]);
        }

        BrandedMail::applyAlertsBcc($mail);

        return $mail;
    }

    private function buildPlaceholderData($data): array
    {
        $map = BrandedMail::dataFromObject($data);

        if (empty($map['invoice_link']) && !empty($map['invoice_id']) && !empty($map['token'])) {
            $map['invoice_link'] = route('invoice_preview', $map['invoice_id'] . '/' . $map['token']);
        }

        if (empty($map['subscription_type']) && !empty($map['plan_name'])) {
            $map['subscription_type'] = $map['plan_name'];
        }

        $map['start_date'] = $map['start_date'] ?? '-';
        $map['end_date'] = $map['end_date'] ?? '-';
        $map['paid_amount'] = $map['paid_amount'] ?? ($map['amount'] ?? '0.00');
        $map['invoice_link'] = $map['invoice_link'] ?? '#';

        return $map;
    }

    private function sanitizeFromName(?string $fromName): ?string
    {
        if ($fromName === null) {
            return null;
        }

        $trimmed = trim($fromName);

        if ($trimmed === '') {
            return null;
        }

        return preg_replace('/\s*-\s*Alert\s*$/i', '', $trimmed);
    }
}
