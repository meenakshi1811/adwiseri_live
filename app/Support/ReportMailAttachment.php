<?php

namespace App\Support;

use Illuminate\Mail\Mailable;
use RuntimeException;

class ReportMailAttachment
{
    /**
     * Attach report PDF last so envelope headers do not affect MIME parts.
     * Uses a temp copy + explicit Content-Disposition: attachment for Gmail/Outlook icons.
     */
    public static function attachReportPdf(Mailable $mail, string $sourcePath, string $fileName): Mailable
    {
        if (!is_readable($sourcePath)) {
            throw new RuntimeException('Scheduled report PDF is missing or unreadable.');
        }

        $bytes = file_get_contents($sourcePath);
        if (!is_string($bytes) || strlen($bytes) < 100) {
            throw new RuntimeException('Scheduled report PDF is empty or invalid.');
        }

        $tempPath = self::writeTempPdf($bytes);

        $mail->attach($tempPath, [
            'as' => $fileName,
            'mime' => 'application/pdf',
        ]);

        self::registerTempCleanup($tempPath);
        self::ensureAttachmentDisposition($mail, $fileName);

        return $mail;
    }

    private static function registerTempCleanup(string $tempPath): void
    {
        register_shutdown_function(static function () use ($tempPath) {
            if (is_string($tempPath) && is_file($tempPath)) {
                @unlink($tempPath);
            }
        });
    }

    private static function writeTempPdf(string $bytes): string
    {
        $directory = storage_path('app/temp/report-mail');
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $tempPath = $directory . DIRECTORY_SEPARATOR . uniqid('report_', true) . '.pdf';
        if (file_put_contents($tempPath, $bytes) === false) {
            throw new RuntimeException('Unable to write temporary report PDF.');
        }

        return $tempPath;
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
