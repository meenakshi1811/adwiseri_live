<?php

namespace App\Support;

use Illuminate\Mail\Mailable;
use RuntimeException;

class ReportMailAttachment
{
    /**
     * Attach report PDF last so envelope headers do not affect MIME parts.
     * Embeds bytes directly so clients show the paperclip on first open (no temp-file race).
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

        $mail->attachData($bytes, $fileName, [
            'mime' => 'application/pdf',
        ]);

        self::ensureAttachmentDisposition($mail, $fileName);

        return $mail;
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
