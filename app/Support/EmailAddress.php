<?php

namespace App\Support;

class EmailAddress
{
    /**
     * Simple recipient check: valid format and domain contains a dot.
     */
    public static function isValidRecipient(?string $email): bool
    {
        $email = trim((string) $email);

        if ($email === '' || strpos($email, '@') === false) {
            return false;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $domain = substr(strrchr($email, '@'), 1);

        return is_string($domain) && $domain !== '' && strpos($domain, '.') !== false;
    }

    public static function normalize(?string $email): string
    {
        return strtolower(trim((string) $email));
    }

    public static function domain(?string $email): string
    {
        $email = self::normalize($email);

        if ($email === '' || strpos($email, '@') === false) {
            return '';
        }

        return substr(strrchr($email, '@'), 1) ?: '';
    }

    /**
     * True when the address belongs to an internal Adwiseri mailbox domain.
     */
    public static function isInternalDomain(?string $email, ?array $internalDomains = null): bool
    {
        $domain = self::domain($email);

        if ($domain === '') {
            return false;
        }

        $internalDomains = $internalDomains ?? config('email_auto_replies.internal_domains', ['adwiseri.com']);

        foreach ($internalDomains as $internalDomain) {
            $internalDomain = strtolower(trim((string) $internalDomain));
            if ($internalDomain === '') {
                continue;
            }

            if ($domain === $internalDomain || substr($domain, -strlen('.' . $internalDomain)) === '.' . $internalDomain) {
                return true;
            }
        }

        return false;
    }

    public static function extractFromHeader(?string $value): string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return '';
        }

        if (preg_match('/<([^>]+)>/', $value, $matches)) {
            return self::normalize($matches[1]);
        }

        if (preg_match('/[A-Z0-9._%+\'-]+@[A-Z0-9.-]+\.[A-Z]{2,}/i', $value, $matches)) {
            return self::normalize($matches[0]);
        }

        return self::normalize($value);
    }
}
