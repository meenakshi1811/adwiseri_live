<?php

namespace App\Support;

use App\Models\Countries;

class SubscriberLicensedCountry
{
    private const RULES = [
        [
            'keywords' => ['us immigration attorney', 'usa visas immigration attorney'],
            'country' => 'United States',
        ],
        [
            'keywords' => ['uk oisc', 'oisc iaa', 'oisc immigration', 'iaa immigration'],
            'country' => 'United Kingdom',
        ],
        [
            'keywords' => ['iccrc immigration', 'canada iccrc'],
            'country' => 'Canada',
        ],
        [
            'keywords' => ['mara immigration', 'australia mara'],
            'country' => 'Australia',
        ],
    ];

    public static function resolveCountryName(?string $subCategory): ?string
    {
        $normalized = self::normalize((string) $subCategory);
        if ($normalized === '') {
            return null;
        }

        foreach (self::RULES as $rule) {
            if (self::containsAny($normalized, $rule['keywords'])) {
                return $rule['country'];
            }
        }

        return null;
    }

    public static function resolveCountryId(?string $subCategory): ?int
    {
        $countryName = self::resolveCountryName($subCategory);
        if ($countryName === null) {
            return null;
        }

        $id = Countries::query()
            ->where('country_name', $countryName)
            ->value('id');

        return $id !== null ? (int) $id : null;
    }

    public static function isLocked(?string $subCategory): bool
    {
        return self::resolveCountryName($subCategory) !== null;
    }

    public static function resolveCountryForRequest(?string $subCategory, $requestedCountryId): ?Countries
    {
        $lockedName = self::resolveCountryName($subCategory);
        if ($lockedName !== null) {
            return Countries::query()->where('country_name', $lockedName)->first();
        }

        if ($requestedCountryId === null || $requestedCountryId === '') {
            return null;
        }

        return Countries::query()->find($requestedCountryId);
    }

    private static function normalize(string $value): string
    {
        $value = strtolower(trim($value));
        $value = str_replace(['/', '-', '(', ')', '.', ',', ':'], ' ', $value);
        $value = preg_replace('/\s+/', ' ', $value);

        return trim((string) $value);
    }

    private static function containsAny(string $text, array $keywords): bool
    {
        foreach ($keywords as $keyword) {
            $keyword = self::normalize((string) $keyword);
            if ($keyword !== '' && str_contains($text, $keyword)) {
                return true;
            }
        }

        return false;
    }
}
