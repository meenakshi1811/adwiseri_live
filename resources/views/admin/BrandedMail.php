<?php

namespace App\Support;

use Illuminate\Mail\Mailable;

class BrandedMail
{
    public const LAYOUT = 'web.dynamic_email_template';

    public const COPYRIGHT_START_YEAR = 2023;

    public static function copyrightYears(?int $startYear = null): string
    {
        $start = $startYear ?? self::COPYRIGHT_START_YEAR;
        $current = (int) date('Y');

        return $current <= $start ? (string) $start : $start . '-' . $current;
    }

    public static function copyrightNotice(string $brand = 'Adwiseri'): string
    {
        return '© ' . self::copyrightYears() . ' ' . $brand . '. All rights reserved.';
    }

    public static function supportEmail(): string
    {
        $email = trim((string) config('mail.reply_to.address', ''));

        return $email !== '' ? $email : 'care@adwiseri.com';
    }

    public static function supportName(): string
    {
        $name = trim((string) config('mail.reply_to.name', ''));

        return $name !== '' ? $name : 'Adwiseri Support';
    }

    public static function emailSignatureHtml(): string
    {
        return 'Sincerely,<br><strong>Adwiseri</strong>';
    }

    public static function emailSignaturePlain(): string
    {
        return "Sincerely,\nAdwiseri";
    }

    public static function replacePlaceholders(?string $text, array $data): string
    {
        $content = (string) $text;

        foreach ($data as $key => $value) {
            if (!is_scalar($value) && !is_null($value)) {
                continue;
            }

            $quotedKey = preg_quote((string) $key, '/');
            $content = preg_replace('/{{\s*' . $quotedKey . '\s*}}/i', (string) $value, $content);
            $content = preg_replace('/<\s*' . $quotedKey . '\s*>/i', (string) $value, $content);
        }

        return $content;
    }

    public static function dataFromObject($data): array
    {
        if (is_array($data)) {
            return $data;
        }

        if (is_object($data)) {
            return (array) $data;
        }

        return [];
    }

    public static function adminNotificationRecipients(): array
    {
        $recipients = config('mail.notifications.admin_recipients', []);

        return array_values(array_filter(array_unique(array_map('trim', $recipients))));
    }

    public static function alertsFromAddress(): string
    {
        return (string) config('mail.notifications.alerts_from', config('mail.from.address'));
    }

    /**
     * Explicit Sender (From) for platform auto-emails: alerts@adwiseri.com
     */
    public static function applyPlatformFrom(Mailable $mail, ?string $displayName = null): Mailable
    {
        $mail->from(self::alertsFromAddress(), self::alertsFromName($displayName ?: 'Adwiseri'));

        return $mail;
    }

    /**
     * Platform auto-email envelope:
     * Sender (From) = alerts@adwiseri.com
     * Reply-To = care@adwiseri.com
     */
    public static function applyPlatformEnvelope(Mailable $mail, ?string $displayName = null): Mailable
    {
        self::applyPlatformFrom($mail, $displayName);

        return self::applyDefaultReplyTo($mail);
    }

    public static function alertsBccRecipients(): array
    {
        return array_values(array_filter(array_map(
            'trim',
            explode(',', (string) config('mail.notifications.alerts_bcc', 'alerts@adwiseri.com'))
        )));
    }

    public static function alertsFromName(?string $subscriberName = null): string
    {
        $name = self::stripSentOnBehalfPrefix($subscriberName);

        if ($name !== '') {
            if (self::isPlatformBrand($name)) {
                return 'Adwiseri';
            }

            return self::sentOnBehalfOf($name);
        }

        return (string) config('mail.from.name', 'Adwiseri');
    }

    public static function stripSentOnBehalfPrefix(?string $name): string
    {
        $name = trim((string) $name);

        if (str_starts_with(strtolower($name), 'sent on behalf of ')) {
            $name = trim(substr($name, strlen('Sent on behalf of ')));
        }

        return $name;
    }

    public static function isPlatformBrand(?string $name): bool
    {
        $name = strtolower(self::stripSentOnBehalfPrefix($name));

        return $name === 'adwiseri' || $name === 'adwiseri team';
    }

    public static function sentOnBehalfOf(string $name): string
    {
        $name = trim($name);

        return 'Sent on behalf of ' . ($name !== '' ? $name : 'Subscriber');
    }

    public static function applyAlertsBcc(Mailable $mail): Mailable
    {
        foreach (self::alertsBccRecipients() as $bccEmail) {
            if ($bccEmail !== '') {
                $mail->bcc($bccEmail);
            }
        }

        return $mail;
    }

    /**
     * Force platform Reply-To to care@adwiseri.com (or configured support address).
     * Uses Swift setReplyTo so the header replaces any prior value instead of stacking.
     */
    public static function applyDefaultReplyTo(Mailable $mail): Mailable
    {
        $address = self::supportEmail();
        $name = self::supportName();

        $mail->replyTo = [];
        $mail->replyTo($address, $name);

        $mail->withSwiftMessage(function ($message) use ($address, $name) {
            $message->setReplyTo([$address => $name]);
        });

        return $mail;
    }

    /**
     * Force Reply-To to a subscriber / sender address (consultancy emails).
     */
    public static function applySubscriberReplyTo(Mailable $mail, ?string $email, ?string $name = null): Mailable
    {
        $email = trim((string) $email);
        if ($email === '') {
            return self::applyDefaultReplyTo($mail);
        }

        $displayName = trim((string) ($name ?? ''));
        $mail->replyTo = [];
        $mail->replyTo($email, $displayName !== '' ? $displayName : null);

        $mail->withSwiftMessage(function ($message) use ($email, $displayName) {
            $message->setReplyTo(
                $displayName !== '' ? [$email => $displayName] : [$email]
            );
        });

        return $mail;
    }

    public static function renderBody(string $view, array $data = []): string
    {
        return view($view, $data)->render();
    }
}
