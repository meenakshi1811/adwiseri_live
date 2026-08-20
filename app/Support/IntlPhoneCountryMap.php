<?php

namespace App\Support;

use App\Models\Countries;

class IntlPhoneCountryMap
{
    /**
     * ISO 3166-1 alpha-2 (intl-tel-input) => countries.id
     *
     * @return array<string, int>
     */
    public static function isoToCountryId(): array
    {
        static $map = null;

        if ($map !== null) {
            return $map;
        }

        $map = [];

        Countries::query()
            ->whereNotNull('country_code')
            ->where('country_code', '!=', '')
            ->get(['id', 'country_code'])
            ->each(function (Countries $country) use (&$map) {
                $map[strtolower($country->country_code)] = (int) $country->id;
            });

        if (isset($map['uk']) && !isset($map['gb'])) {
            $map['gb'] = $map['uk'];
        }

        if (isset($map['gb']) && !isset($map['uk'])) {
            $map['uk'] = $map['gb'];
        }

        return $map;
    }

    /**
     * countries.id => ISO 3166-1 alpha-2 (intl-tel-input)
     *
     * @return array<int, string>
     */
    public static function countryIdToIso(): array
    {
        static $map = null;

        if ($map !== null) {
            return $map;
        }

        $map = [];

        Countries::query()
            ->whereNotNull('country_code')
            ->where('country_code', '!=', '')
            ->get(['id', 'country_code'])
            ->each(function (Countries $country) use (&$map) {
                $map[(int) $country->id] = self::normalizeIso((string) $country->country_code);
            });

        return $map;
    }

    /**
     * Lowercase country name => ISO 3166-1 alpha-2 (intl-tel-input)
     *
     * @return array<string, string>
     */
    public static function countryNameToIso(): array
    {
        static $map = null;

        if ($map !== null) {
            return $map;
        }

        $map = [];

        Countries::query()
            ->whereNotNull('country_code')
            ->where('country_code', '!=', '')
            ->get(['country_name', 'country_code'])
            ->each(function (Countries $country) use (&$map) {
                $map[strtolower(trim($country->country_name))] = self::normalizeIso((string) $country->country_code);
            });

        return $map;
    }

    /**
     * Lowercase country name => countries.id
     *
     * @return array<string, int>
     */
    public static function nameToCountryId(): array
    {
        static $map = null;

        if ($map !== null) {
            return $map;
        }

        $map = [];

        Countries::query()
            ->get(['id', 'country_name'])
            ->each(function (Countries $country) use (&$map) {
                $map[strtolower(trim($country->country_name))] = (int) $country->id;
            });

        return $map;
    }

    public static function countryIdFromPhone(?string $phone): ?int
    {
        $iso = PhoneNumber::countryIso($phone);

        if ($iso === null) {
            return null;
        }

        $map = self::isoToCountryId();

        return $map[$iso] ?? null;
    }

    /**
     * Resolve which country option should be selected on a form.
     * Priority: old() input → saved value → phone country code.
     */
    public static function selectedCountryId(?string $phone, $savedCountry = null, bool $savedIsCountryName = false): ?int
    {
        $old = old('country');

        if ($old !== null && $old !== '') {
            if (is_numeric($old)) {
                return (int) $old;
            }

            return self::nameToCountryId()[strtolower(trim((string) $old))] ?? null;
        }

        if ($savedCountry !== null && $savedCountry !== '') {
            if ($savedIsCountryName) {
                return self::nameToCountryId()[strtolower(trim((string) $savedCountry))] ?? null;
            }

            if (is_numeric($savedCountry)) {
                return (int) $savedCountry;
            }
        }

        return self::countryIdFromPhone($phone);
    }

    public static function isCountrySelected(int $countryId, ?string $phone, $savedCountry = null, bool $savedIsCountryName = false): bool
    {
        $selectedId = self::selectedCountryId($phone, $savedCountry, $savedIsCountryName);

        return $selectedId !== null && $selectedId === $countryId;
    }

    public static function selectedCountryName(?string $phone, $savedCountry = null): ?string
    {
        $old = old('country');
        if ($old !== null && $old !== '') {
            return (string) $old;
        }

        if ($savedCountry !== null && $savedCountry !== '') {
            return (string) $savedCountry;
        }

        $countryId = self::countryIdFromPhone($phone);

        if ($countryId === null) {
            return null;
        }

        return Countries::query()->whereKey($countryId)->value('country_name');
    }

    private static function normalizeIso(string $iso): string
    {
        $iso = strtolower(trim($iso));

        return $iso === 'uk' ? 'gb' : $iso;
    }
}
