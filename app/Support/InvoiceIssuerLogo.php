<?php

namespace App\Support;

use App\Models\Internal_Invoices;
use App\Models\User;

class InvoiceIssuerLogo
{
    /**
     * Resolve issuer logo paths for internal (admin→subscriber, subscriber→client) invoices.
     *
     * @return array{disk_path: ?string, url: ?string, relative_path: ?string, owner_user_id: ?int}
     */
    public static function resolve(Internal_Invoices $invoice, ?User $issuer = null): array
    {
        // adwiseri's own invoices (subscription fees) carry the brand's violet logo, the one the
        // site uses, rather than whatever file was uploaded on the admin profile.
        if (BrandedMail::isPlatformBrand($invoice->name ?? '')) {
            $platform = self::platformLogo();
            if ($platform['disk_path'] !== null) {
                return $platform;
            }
        }

        $filename = trim((string) ($invoice->logo ?? ''));
        if ($filename === '') {
            return self::emptyResult();
        }

        $issuer = $issuer ?: self::resolveIssuer($invoice);
        $ownerIds = array_values(array_unique(array_filter([
            $issuer?->id,
            (int) ($invoice->subscriber_id ?? 0) ?: null,
            (int) ($invoice->user_id ?? 0) ?: null,
        ])));

        foreach ($ownerIds as $ownerId) {
            $relative = 'web_assets/users/user' . $ownerId . '/' . $filename;
            $diskPath = public_path($relative);
            if (file_exists($diskPath)) {
                return [
                    'disk_path' => $diskPath,
                    'url' => asset($relative),
                    'relative_path' => $relative,
                    'owner_user_id' => (int) $ownerId,
                ];
            }
        }

        $logosRelative = 'web_assets/users/logos/' . $filename;
        $logosDiskPath = public_path($logosRelative);
        if (file_exists($logosDiskPath)) {
            return [
                'disk_path' => $logosDiskPath,
                'url' => asset($logosRelative),
                'relative_path' => $logosRelative,
                'owner_user_id' => $issuer?->id,
            ];
        }

        return self::emptyResult();
    }

    /**
     * Resolve issuer logo for subscriber-scoped documents (associate invoices, PDF mail data).
     *
     * @return array{disk_path: ?string, url: ?string, relative_path: ?string, owner_user_id: ?int}
     */
    public static function resolveForSubscriber(User $subscriber, ?string $logoFilename): array
    {
        $filename = trim((string) $logoFilename);
        if ($filename === '') {
            return self::emptyResult();
        }

        $relative = 'web_assets/users/user' . $subscriber->id . '/' . $filename;
        $diskPath = public_path($relative);
        if (file_exists($diskPath)) {
            return [
                'disk_path' => $diskPath,
                'url' => asset($relative),
                'relative_path' => $relative,
                'owner_user_id' => (int) $subscriber->id,
            ];
        }

        $logosRelative = 'web_assets/users/logos/' . $filename;
        $logosDiskPath = public_path($logosRelative);
        if (file_exists($logosDiskPath)) {
            return [
                'disk_path' => $logosDiskPath,
                'url' => asset($logosRelative),
                'relative_path' => $logosRelative,
                'owner_user_id' => (int) $subscriber->id,
            ];
        }

        return self::emptyResult();
    }

    public static function resolveIssuer(Internal_Invoices $invoice): ?User
    {
        if (!empty($invoice->email)) {
            $byEmail = User::where('email', $invoice->email)->first();
            if ($byEmail) {
                return $byEmail;
            }
        }

        if (!empty($invoice->subscriber_id)) {
            return User::find($invoice->subscriber_id);
        }

        if (!empty($invoice->user_id)) {
            return User::find($invoice->user_id);
        }

        return null;
    }

    /** The brand logo in the theme's violet, used on invoices from adwiseri.com. */
    public const PLATFORM_LOGO = 'web_assets/images/Style2_violet.png';

    /**
     * @return array{disk_path: ?string, url: ?string, relative_path: ?string, owner_user_id: ?int}
     */
    public static function platformLogo(): array
    {
        $diskPath = public_path(self::PLATFORM_LOGO);
        if (!file_exists($diskPath)) {
            return self::emptyResult();
        }

        return [
            'disk_path' => $diskPath,
            'url' => asset(self::PLATFORM_LOGO),
            'relative_path' => self::PLATFORM_LOGO,
            'owner_user_id' => null,
        ];
    }

    /**
     * @return array{disk_path: ?string, url: ?string, relative_path: ?string, owner_user_id: ?int}
     */
    private static function emptyResult(): array
    {
        return [
            'disk_path' => null,
            'url' => null,
            'relative_path' => null,
            'owner_user_id' => null,
        ];
    }
}
