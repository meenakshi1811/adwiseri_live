<?php

namespace App\Support;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\File;
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
        $tempPath = self::writeTempPdf($data);

        $mail->attach($tempPath, [
            'as' => $fileName,
            'mime' => 'application/pdf',
        ]);

        self::registerTempCleanup($tempPath);
        self::ensureAttachmentDisposition($mail, $fileName);

        return $mail;
    }

    public static function fileName(object $data): string
    {
        $invoiceNo = preg_replace('/[^a-zA-Z0-9._-]+/', '-', (string) ($data->invoice_no ?? 'document'));

        return 'Invoice-' . trim($invoiceNo, '-') . '.pdf';
    }

    private static function writeTempPdf(object $data): string
    {
        $uploadedPath = trim((string) ($data->uploaded_invoice_path ?? ''));
        if ($uploadedPath !== '' && is_readable($uploadedPath)) {
            $bytes = file_get_contents($uploadedPath);
            if ($bytes !== false && strlen($bytes) >= 100) {
                return self::persistTempFile($bytes);
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

        return self::persistTempFile($bytes);
    }

    private static function persistTempFile(string $bytes): string
    {
        $directory = storage_path('app/mail-attachments');
        if (!File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $tempPath = $directory . DIRECTORY_SEPARATOR . uniqid('invoice_', true) . '.pdf';
        if (file_put_contents($tempPath, $bytes) === false) {
            throw new RuntimeException('Unable to write invoice PDF attachment.');
        }

        return $tempPath;
    }

    private static function registerTempCleanup(string $tempPath): void
    {
        register_shutdown_function(static function () use ($tempPath) {
            if (is_file($tempPath)) {
                @unlink($tempPath);
            }
        });
    }

    private static function ensureAttachmentDisposition(Mailable $mail, string $fileName): void
    {
        if (method_exists($mail, 'withSwiftMessage')) {
            $mail->withSwiftMessage(static function ($message) use ($fileName) {
                foreach ($message->getChildren() as $child) {
                    if ($child instanceof \Swift_Attachment) {
                        $child->setDisposition('attachment');
                        $child->setFilename($fileName);
                    }
                }
            });

            return;
        }

        if (method_exists($mail, 'withSymfonyMessage')) {
            $mail->withSymfonyMessage(static function ($message) use ($fileName) {
                foreach ($message->attachParts() as $part) {
                    if (method_exists($part, 'asAttachment')) {
                        $part->asAttachment();
                    }
                    if (method_exists($part, 'setName')) {
                        $part->setName($fileName);
                    }
                }
            });
        }
    }
}
