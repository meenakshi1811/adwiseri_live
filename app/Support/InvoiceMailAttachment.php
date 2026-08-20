<?php

namespace App\Support;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Mail\Mailable;
use RuntimeException;

class InvoiceMailAttachment
{
    /**
     * Attach invoice PDF last so Cc / Reply-To headers do not affect MIME parts.
     * Uses a temp file + explicit attachment disposition for Gmail/Outlook inbox icons.
     */
    public static function attachInvoicePdf(Mailable $mail, object $data): Mailable
    {
        $fileName = self::fileName($data);
        $bytes = self::pdfBytes($data);
        $tempPath = self::writeTempPdf($bytes);

        $mail->attach($tempPath, [
            'as' => $fileName,
            'mime' => 'application/pdf',
        ]);

        self::ensureAttachmentDisposition($mail, $fileName);

        if (method_exists($mail, 'withSwiftMessage')) {
            $mail->withSwiftMessage(static function ($message) use ($tempPath) {
                register_shutdown_function(static function () use ($tempPath) {
                    if (is_string($tempPath) && is_file($tempPath)) {
                        @unlink($tempPath);
                    }
                });
            });
        }

        return $mail;
    }

    private static function writeTempPdf(string $bytes): string
    {
        $directory = storage_path('app/temp/invoice-mail');
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $tempPath = $directory . DIRECTORY_SEPARATOR . uniqid('invoice_', true) . '.pdf';
        if (file_put_contents($tempPath, $bytes) === false) {
            throw new RuntimeException('Unable to write temporary invoice PDF.');
        }

        return $tempPath;
    }

    public static function fileName(object $data): string
    {
        $invoiceNo = preg_replace('/[^a-zA-Z0-9._-]+/', '-', (string) ($data->invoice_no ?? 'document'));

        return 'Invoice-' . trim($invoiceNo, '-') . '.pdf';
    }

    private static function pdfBytes(object $data): string
    {
        $uploadedPath = trim((string) ($data->uploaded_invoice_path ?? ''));
        if ($uploadedPath !== '' && is_readable($uploadedPath)) {
            $bytes = file_get_contents($uploadedPath);
            if (is_string($bytes) && strlen($bytes) >= 100) {
                return $bytes;
            }
        }

        $pdf = Pdf::loadView('web.invoice_pdf', ['data' => $data])
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isPhpEnabled', true);

        $bytes = $pdf->output();
        if (!is_string($bytes) || strlen($bytes) < 100) {
            throw new RuntimeException('Invoice PDF generation produced empty output.');
        }

        return $bytes;
    }

    private static function ensureAttachmentDisposition(Mailable $mail, string $fileName): void
    {
        if (method_exists($mail, 'withSwiftMessage')) {
            $mail->withSwiftMessage(static function ($message) use ($fileName) {
                BrandedMail::ensureAttachmentDispositionOnMessage($message, $fileName);
            });

            return;
        }

        if (method_exists($mail, 'withSymfonyMessage')) {
            $mail->withSymfonyMessage(static function ($message) use ($fileName) {
                BrandedMail::ensureAttachmentDispositionOnMessage($message, $fileName);
            });
        }
    }
}
