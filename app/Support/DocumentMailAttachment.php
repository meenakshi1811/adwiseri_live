<?php

namespace App\Support;

use Illuminate\Mail\Mailable;
use RuntimeException;

class DocumentMailAttachment
{
    /**
     * Attach checklist PDF last so envelope headers do not affect MIME parts.
     * Embeds bytes directly so clients show the paperclip on first open.
     */
    public static function attachChecklistPdf(Mailable $mail, string $pdfContents, string $fileName): Mailable
    {
        if (!is_string($pdfContents) || strlen($pdfContents) < 100 || substr($pdfContents, 0, 4) !== '%PDF') {
            throw new RuntimeException('Documents checklist PDF is empty or invalid.');
        }

        $mail->attachData($pdfContents, $fileName, [
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
