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
            $replacement = (string) $value;
            $content = preg_replace('/{{\s*' . $quotedKey . '\s*}}/i', $replacement, $content);
            $content = preg_replace('/\{\s*' . $quotedKey . '\s*\}/i', $replacement, $content);
            $content = preg_replace('/<\s*' . $quotedKey . '\s*>/i', $replacement, $content);
        }

        return $content;
    }

    /**
     * Ensure template paragraphs wrap correctly in narrow email clients.
     */
    public static function ensureResponsiveEmailHtml(string $html): string
    {
        if (trim($html) === '') {
            return $html;
        }

        $responsiveParagraphStyle = 'margin:0 0 14px 0;word-wrap:break-word;overflow-wrap:break-word;word-break:break-word;max-width:100%;';

        $html = preg_replace_callback('/<p\b([^>]*)>/i', function (array $matches) use ($responsiveParagraphStyle) {
            $attributes = $matches[1];

            if (stripos($attributes, 'word-wrap:') !== false || stripos($attributes, 'overflow-wrap:') !== false) {
                return $matches[0];
            }

            if (preg_match('/style\s*=\s*"([^"]*)"/i', $attributes, $styleMatch)) {
                $style = rtrim($styleMatch[1], '; ') . ';' . $responsiveParagraphStyle;

                return preg_replace('/style\s*=\s*"([^"]*)"/i', 'style="' . $style . '"', $matches[0], 1);
            }

            if (preg_match("/style\s*=\s*'([^']*)'/i", $attributes, $styleMatch)) {
                $style = rtrim($styleMatch[1], '; ') . ';' . $responsiveParagraphStyle;

                return preg_replace("/style\s*=\s*'([^']*)'/i", "style='" . $style . "'", $matches[0], 1);
            }

            return '<p style="' . $responsiveParagraphStyle . '"' . $attributes . '>';
        }, $html);

        $html = preg_replace_callback('/<table\b([^>]*)>/i', function (array $matches) {
            $attributes = $matches[1];

            if (stripos($attributes, 'width=') !== false && stripos($attributes, 'table-layout') !== false) {
                return $matches[0];
            }

            $tableStyle = 'width:100%;max-width:100%;table-layout:fixed;border-collapse:collapse;';

            if (preg_match('/style\s*=\s*"([^"]*)"/i', $attributes, $styleMatch)) {
                $style = rtrim($styleMatch[1], '; ') . ';' . $tableStyle;

                return preg_replace('/style\s*=\s*"([^"]*)"/i', 'style="' . $style . '"', $matches[0], 1);
            }

            return '<table style="' . $tableStyle . '"' . $attributes . '>';
        }, $html);

        return preg_replace_callback('/<a\b([^>]*)>/i', function (array $matches) {
            $attributes = $matches[1];
            $isCta = preg_match('/class\s*=\s*"([^"]*)"/i', $attributes, $classMatch)
                && stripos($classMatch[1], 'email-cta') !== false;

            $linkStyle = $isCta
                ? 'display:block;width:100%;max-width:100%;box-sizing:border-box;word-wrap:break-word;overflow-wrap:break-word;word-break:normal;'
                : 'word-wrap:break-word;overflow-wrap:anywhere;word-break:break-all;white-space:normal;max-width:100%;';

            if (preg_match('/style\s*=\s*"([^"]*)"/i', $attributes, $styleMatch)) {
                $style = rtrim($styleMatch[1], '; ') . ';' . $linkStyle;

                if (!$isCta && (stripos($styleMatch[1], 'display:inline-block') !== false || stripos($styleMatch[1], 'display: inline-block') !== false)) {
                    $style .= 'display:block;width:100%;box-sizing:border-box;';
                }

                return preg_replace('/style\s*=\s*"([^"]*)"/i', 'style="' . $style . '"', $matches[0], 1);
            }

            return '<a style="' . $linkStyle . '"' . $attributes . '>';
        }, $html);
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

    /**
     * Bcc admin archive mailbox on outbound emails (invoices, welcome mail, etc.).
     * Skips addresses that are already the primary recipient.
     */
    public static function applyAlertsBcc(Mailable $mail, ?string $primaryRecipient = null): Mailable
    {
        $primaryRecipient = strtolower(trim((string) $primaryRecipient));

        foreach (self::alertsBccRecipients() as $bccEmail) {
            $bccEmail = trim((string) $bccEmail);
            if ($bccEmail === '') {
                continue;
            }

            if ($primaryRecipient !== '' && strtolower($bccEmail) === $primaryRecipient) {
                continue;
            }

            $mail->bcc($bccEmail);
        }

        if (method_exists($mail, 'withSwiftMessage')) {
            $mail->withSwiftMessage(function ($message) use ($primaryRecipient) {
                self::ensureAlertsBccOnMessage($message, $primaryRecipient);
            });
        }

        return $mail;
    }

    /**
     * Force archive Bcc headers on the underlying Swift message right before send.
     */
    public static function ensureAlertsBccOnMessage($message, ?string $primaryRecipient = null): void
    {
        if (!is_object($message) || !method_exists($message, 'addBcc')) {
            return;
        }

        $primaryRecipient = strtolower(trim((string) $primaryRecipient));
        $toAddresses = self::messageAddresses($message, 'getTo');
        $existingBcc = self::messageAddresses($message, 'getBcc');

        if ($primaryRecipient === '' && !empty($toAddresses)) {
            $primaryRecipient = $toAddresses[0];
        }

        foreach (self::alertsBccRecipients() as $bccEmail) {
            $bccEmail = trim((string) $bccEmail);
            if ($bccEmail === '') {
                continue;
            }

            $bccLower = strtolower($bccEmail);
            if ($primaryRecipient !== '' && $bccLower === $primaryRecipient) {
                continue;
            }

            if (in_array($bccLower, $toAddresses, true) || in_array($bccLower, $existingBcc, true)) {
                continue;
            }

            $message->addBcc($bccEmail);
            $existingBcc[] = $bccLower;
        }
    }

    /**
     * @return list<string>
     */
    private static function messageAddresses($message, string $getter): array
    {
        if (!method_exists($message, $getter)) {
            return [];
        }

        $addresses = [];
        foreach ($message->{$getter}() ?? [] as $address) {
            if (is_object($address) && method_exists($address, 'getAddress')) {
                $addresses[] = strtolower(trim((string) $address->getAddress()));
            } elseif (is_string($address)) {
                $addresses[] = strtolower(trim($address));
            }
        }

        return $addresses;
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

    /**
     * Build footer context for subscriber-originated broadcast emails.
     *
     * @return array{organization: string, address: string, website: string, website_url: string, email: string, copyright: string}
     */
    public static function subscriberFooterContext($subscriber): array
    {
        $organization = trim((string) ($subscriber->organization ?? ''));
        if ($organization === '') {
            $organization = trim((string) ($subscriber->name ?? 'Subscriber'));
        }

        $addressParts = array_values(array_filter(array_map(
            static fn ($value) => trim((string) $value),
            [
                $subscriber->address_line ?? '',
                $subscriber->city ?? '',
                $subscriber->state ?? '',
                $subscriber->pincode ?? '',
                $subscriber->country ?? '',
            ]
        )));

        $website = trim((string) ($subscriber->website ?? ''));
        $websiteUrl = self::normalizeWebsiteUrl($website);
        $email = trim((string) ($subscriber->email ?? ''));

        return [
            'organization' => $organization,
            'address' => implode(', ', $addressParts),
            'website' => $website,
            'website_url' => $websiteUrl,
            'email' => $email,
            'copyright' => self::copyrightNotice($organization),
        ];
    }

    public static function normalizeWebsiteUrl(?string $website): string
    {
        $website = trim((string) $website);
        if ($website === '') {
            return '';
        }

        if (!preg_match('#^https?://#i', $website)) {
            $website = 'https://' . ltrim($website, '/');
        }

        return $website;
    }

    public static function formatBroadcastBody(?string $body): string
    {
        $body = trim((string) $body);
        if ($body === '') {
            return '';
        }

        if (!preg_match('/<[^>]+>/', $body)) {
            return nl2br(e($body));
        }

        return self::sanitizeBroadcastHtml(self::normalizeBroadcastHtml($body));
    }

    public static function normalizeBroadcastHtml(string $html): string
    {
        $html = preg_replace('/<figure[^>]*>(.*?)<\/figure>/is', '$1', $html);
        $html = preg_replace('/<figcaption[^>]*>.*?<\/figcaption>/is', '', $html);
        $html = preg_replace('/<p>(?:\s|&nbsp;|<br\s*\/?>)*<\/p>/i', '', $html);

        $html = preg_replace_callback(
            '/<img\b([^>]*)\ssrc=(["\'])(?!https?:|data:)([^"\']+)\2/i',
            static function (array $matches): string {
                $src = trim($matches[3]);
                if ($src === '') {
                    return $matches[0];
                }

                if (!preg_match('#^https?://#i', $src)) {
                    $src = url(ltrim($src, '/'));
                }

                return '<img' . $matches[1] . ' src="' . $src . '"';
            },
            $html
        );

        return $html;
    }

    public static function sanitizeBroadcastHtml(string $html): string
    {
        $allowedTags = '<p><br><strong><b><em><i><u><s><strike><h1><h2><h3><h4><h5><h6><ul><ol><li><a><img><table><thead><tbody><tr><th><td><span><div><blockquote><hr><font><figure><figcaption><sub><sup><center>';
        $html = strip_tags($html, $allowedTags);

        if (!class_exists(\DOMDocument::class)) {
            return $html;
        }

        $document = new \DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML(
            '<?xml encoding="UTF-8"><div>' . $html . '</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $wrapper = $document->getElementsByTagName('div')->item(0);
        if (!$wrapper) {
            return $html;
        }

        self::sanitizeBroadcastNode($wrapper);

        $sanitized = '';
        foreach ($wrapper->childNodes as $child) {
            $sanitized .= $document->saveHTML($child);
        }

        return $sanitized;
    }

    private static function sanitizeBroadcastNode(\DOMNode $node): void
    {
        if ($node->nodeType !== XML_ELEMENT_NODE) {
            return;
        }

        /** @var \DOMElement $node */
        $allowedStyleProps = [
            'color',
            'background',
            'background-color',
            'font-family',
            'font-size',
            'font-weight',
            'font-style',
            'text-align',
            'text-decoration',
            'line-height',
            'letter-spacing',
            'margin',
            'margin-top',
            'margin-right',
            'margin-bottom',
            'margin-left',
            'padding',
            'padding-top',
            'padding-right',
            'padding-bottom',
            'padding-left',
            'width',
            'max-width',
            'min-width',
            'height',
            'max-height',
            'border',
            'border-radius',
            'display',
            'vertical-align',
        ];

        if ($node->hasAttributes()) {
            $remove = [];
            foreach ($node->attributes as $attribute) {
                $name = strtolower($attribute->name);
                $value = trim((string) $attribute->value);

                if (str_starts_with($name, 'on')) {
                    $remove[] = $name;
                    continue;
                }

                if ($name === 'style') {
                    $safeStyles = [];
                    foreach (explode(';', $value) as $rule) {
                        $rule = trim($rule);
                        if ($rule === '' || !str_contains($rule, ':')) {
                            continue;
                        }

                        [$property, $propertyValue] = array_map('trim', explode(':', $rule, 2));
                        $property = strtolower($property);
                        if (!in_array($property, $allowedStyleProps, true)) {
                            continue;
                        }

                        if (preg_match('/expression|javascript:|url\s*\(\s*["\']?\s*javascript:/i', $propertyValue)) {
                            continue;
                        }

                        $safeStyles[] = $property . ': ' . $propertyValue;
                    }

                    if ($safeStyles === []) {
                        $remove[] = $name;
                    } else {
                        $node->setAttribute('style', implode('; ', $safeStyles));
                    }

                    continue;
                }

                if ($name === 'href') {
                    if (!preg_match('#^(https?://|mailto:|tel:)#i', $value)) {
                        $remove[] = $name;
                    }
                    continue;
                }

                if ($name === 'src') {
                    if (!preg_match('#^(https?://|data:image/|/)#i', $value) && !preg_match('#^web_assets/#i', $value)) {
                        $remove[] = $name;
                    }
                    continue;
                }

                if ($name === 'face' || $name === 'size' || $name === 'color') {
                    continue;
                }

                if (!in_array($name, ['href', 'src', 'alt', 'title', 'target', 'rel', 'width', 'height', 'align', 'colspan', 'rowspan', 'class'], true)) {
                    $remove[] = $name;
                }
            }

            foreach ($remove as $attributeName) {
                $node->removeAttribute($attributeName);
            }
        }

        if ($node->childNodes->length > 0) {
            foreach (iterator_to_array($node->childNodes) as $child) {
                self::sanitizeBroadcastNode($child);
            }
        }
    }

    /**
     * Deliver to the primary recipient, then send explicit archive copies to alerts@.
     *
     * Bcc to alerts@ is unreliable when SMTP sends from the same mailbox; a second
     * delivery to the archive address lands in the alerts@ Inbox (not Sent).
     * Outbound SMTP may still appear in Sent depending on the mail server.
     *
     * @param  callable(): Mailable  $mailableFactory
     * @param  callable(\Illuminate\Mail\PendingMail): void|null  $configurePrimaryMail
     */
    public static function sendWithAlertsArchive(
        string $primaryRecipient,
        callable $mailableFactory,
        ?callable $configurePrimaryMail = null
    ): void {
        $primaryMail = \Illuminate\Support\Facades\Mail::to($primaryRecipient);
        if ($configurePrimaryMail !== null) {
            $configurePrimaryMail($primaryMail);
        }
        $primaryMail->send($mailableFactory());

        foreach (self::alertsBccRecipients() as $archiveEmail) {
            $archiveEmail = trim((string) $archiveEmail);
            if ($archiveEmail === '' || strcasecmp($archiveEmail, $primaryRecipient) === 0) {
                continue;
            }

            \Illuminate\Support\Facades\Mail::to($archiveEmail)->send($mailableFactory());
        }
    }

    /**
     * Force Content-Disposition: attachment on PDF parts (Gmail / Outlook paperclip).
     */
    public static function ensureAttachmentDispositionOnMessage($message, ?string $fileName = null): void
    {
        if (!is_object($message)) {
            return;
        }

        self::walkMessageParts($message, static function ($part) use ($fileName) {
            self::markPdfPartAsAttachment($part, $fileName);
        });

        if (method_exists($message, 'getAttachments')) {
            foreach ($message->getAttachments() as $attachment) {
                if (method_exists($attachment, 'asAttachment')) {
                    $attachment->asAttachment();
                }

                if ($fileName !== null && method_exists($attachment, 'filename')) {
                    $attachment->filename($fileName);
                }
            }
        }
    }

    private static function markPdfPartAsAttachment($part, ?string $fileName): void
    {
        if (!is_object($part) || !self::isPdfMimePart($part)) {
            return;
        }

        $resolvedName = $fileName;
        if (($resolvedName === null || $resolvedName === '') && method_exists($part, 'getFilename')) {
            $resolvedName = trim((string) $part->getFilename());
        }

        if (class_exists(\Swift_Mime_Attachment::class) && $part instanceof \Swift_Mime_Attachment) {
            $part->setDisposition('attachment');
            if ($resolvedName !== null && $resolvedName !== '') {
                $part->setFilename($resolvedName);
                self::setSwiftAttachmentHeaders($part, $resolvedName);
            }

            return;
        }

        if (method_exists($part, 'setDisposition')) {
            $part->setDisposition('attachment');
        }

        if ($resolvedName !== null && $resolvedName !== '' && method_exists($part, 'setFilename')) {
            $part->setFilename($resolvedName);
        }
    }

    private static function isPdfMimePart($part): bool
    {
        if (class_exists(\Swift_Mime_Attachment::class) && $part instanceof \Swift_Mime_Attachment) {
            return true;
        }

        if (method_exists($part, 'getContentType')) {
            $contentType = (string) $part->getContentType();
            if (stripos($contentType, 'pdf') !== false) {
                return true;
            }
        }

        if (method_exists($part, 'getFilename')) {
            $name = (string) $part->getFilename();

            return $name !== '' && preg_match('/\.pdf$/i', $name);
        }

        if (method_exists($part, 'getName')) {
            $name = (string) $part->getName();

            return $name !== '' && preg_match('/\.pdf$/i', $name);
        }

        return false;
    }

    private static function setSwiftAttachmentHeaders(\Swift_Mime_Attachment $part, string $fileName): void
    {
        if (!method_exists($part, 'getHeaders')) {
            return;
        }

        $headers = $part->getHeaders();
        if ($headers === null) {
            return;
        }

        if (method_exists($headers, 'removeAll')) {
            $headers->removeAll('Content-Disposition');
        }

        if (method_exists($headers, 'addParameterizedHeader')) {
            $headers->addParameterizedHeader('Content-Disposition', 'attachment', [
                'filename' => $fileName,
            ]);
        }

        if (method_exists($headers, 'removeAll')) {
            $headers->removeAll('Content-Type');
        }

        if (method_exists($headers, 'addParameterizedHeader')) {
            $headers->addParameterizedHeader('Content-Type', 'application/pdf', [
                'name' => $fileName,
            ]);
        }
    }

    private static function walkMessageParts($part, callable $callback): void
    {
        $callback($part);

        if (!method_exists($part, 'getChildren')) {
            return;
        }

        foreach ($part->getChildren() as $child) {
            self::walkMessageParts($child, $callback);
        }
    }
}
