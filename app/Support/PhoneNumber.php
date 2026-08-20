<?php

namespace App\Support;

class PhoneNumber
{
    public const FIELDS = [
        'phone',
        'contact_no',
        'alternate_no',
        'line_manager_phone',
    ];

    /**
     * Dial codes mapped to a default ISO country (longest codes first when matching).
     */
    private const DIAL_CODE_ISO = [
        '1242' => 'bs',
        '1246' => 'bb',
        '1264' => 'ai',
        '1268' => 'ag',
        '1284' => 'vg',
        '1340' => 'vi',
        '1345' => 'ky',
        '1441' => 'bm',
        '1473' => 'gd',
        '1649' => 'tc',
        '1664' => 'ms',
        '1670' => 'mp',
        '1671' => 'gu',
        '1684' => 'as',
        '1721' => 'sx',
        '1758' => 'lc',
        '1767' => 'dm',
        '1784' => 'vc',
        '1787' => 'pr',
        '1809' => 'do',
        '1829' => 'do',
        '1849' => 'do',
        '1868' => 'tt',
        '1869' => 'kn',
        '1876' => 'jm',
        '1939' => 'pr',
        '20' => 'eg',
        '27' => 'za',
        '30' => 'gr',
        '31' => 'nl',
        '32' => 'be',
        '33' => 'fr',
        '34' => 'es',
        '36' => 'hu',
        '39' => 'it',
        '40' => 'ro',
        '41' => 'ch',
        '43' => 'at',
        '44' => 'gb',
        '45' => 'dk',
        '46' => 'se',
        '47' => 'no',
        '48' => 'pl',
        '49' => 'de',
        '51' => 'pe',
        '52' => 'mx',
        '53' => 'cu',
        '54' => 'ar',
        '55' => 'br',
        '56' => 'cl',
        '57' => 'co',
        '58' => 've',
        '60' => 'my',
        '61' => 'au',
        '62' => 'id',
        '63' => 'ph',
        '64' => 'nz',
        '65' => 'sg',
        '66' => 'th',
        '81' => 'jp',
        '82' => 'kr',
        '84' => 'vn',
        '86' => 'cn',
        '90' => 'tr',
        '91' => 'in',
        '92' => 'pk',
        '93' => 'af',
        '94' => 'lk',
        '95' => 'mm',
        '98' => 'ir',
        '212' => 'ma',
        '213' => 'dz',
        '216' => 'tn',
        '218' => 'ly',
        '220' => 'gm',
        '221' => 'sn',
        '222' => 'mr',
        '223' => 'ml',
        '224' => 'gn',
        '225' => 'ci',
        '226' => 'bf',
        '227' => 'ne',
        '228' => 'tg',
        '229' => 'bj',
        '230' => 'mu',
        '231' => 'lr',
        '232' => 'sl',
        '233' => 'gh',
        '234' => 'ng',
        '235' => 'td',
        '236' => 'cf',
        '237' => 'cm',
        '238' => 'cv',
        '239' => 'st',
        '240' => 'gq',
        '241' => 'ga',
        '242' => 'cg',
        '243' => 'cd',
        '244' => 'ao',
        '245' => 'gw',
        '246' => 'io',
        '248' => 'sc',
        '249' => 'sd',
        '250' => 'rw',
        '251' => 'et',
        '252' => 'so',
        '253' => 'dj',
        '254' => 'ke',
        '255' => 'tz',
        '256' => 'ug',
        '257' => 'bi',
        '258' => 'mz',
        '260' => 'zm',
        '261' => 'mg',
        '262' => 're',
        '263' => 'zw',
        '264' => 'na',
        '265' => 'mw',
        '266' => 'ls',
        '267' => 'bw',
        '268' => 'sz',
        '269' => 'km',
        '290' => 'sh',
        '291' => 'er',
        '297' => 'aw',
        '298' => 'fo',
        '299' => 'gl',
        '350' => 'gi',
        '351' => 'pt',
        '352' => 'lu',
        '353' => 'ie',
        '354' => 'is',
        '355' => 'al',
        '356' => 'mt',
        '357' => 'cy',
        '358' => 'fi',
        '359' => 'bg',
        '370' => 'lt',
        '371' => 'lv',
        '372' => 'ee',
        '373' => 'md',
        '374' => 'am',
        '375' => 'by',
        '376' => 'ad',
        '377' => 'mc',
        '378' => 'sm',
        '380' => 'ua',
        '381' => 'rs',
        '382' => 'me',
        '385' => 'hr',
        '386' => 'si',
        '387' => 'ba',
        '389' => 'mk',
        '420' => 'cz',
        '421' => 'sk',
        '423' => 'li',
        '500' => 'fk',
        '501' => 'bz',
        '502' => 'gt',
        '503' => 'sv',
        '504' => 'hn',
        '505' => 'ni',
        '506' => 'cr',
        '507' => 'pa',
        '508' => 'pm',
        '509' => 'ht',
        '590' => 'gp',
        '591' => 'bo',
        '592' => 'gy',
        '593' => 'ec',
        '594' => 'gf',
        '595' => 'py',
        '596' => 'mq',
        '597' => 'sr',
        '598' => 'uy',
        '599' => 'cw',
        '670' => 'tl',
        '672' => 'nf',
        '673' => 'bn',
        '674' => 'nr',
        '675' => 'pg',
        '676' => 'to',
        '677' => 'sb',
        '678' => 'vu',
        '679' => 'fj',
        '680' => 'pw',
        '681' => 'wf',
        '682' => 'ck',
        '683' => 'nu',
        '685' => 'ws',
        '686' => 'ki',
        '687' => 'nc',
        '688' => 'tv',
        '689' => 'pf',
        '690' => 'tk',
        '691' => 'fm',
        '692' => 'mh',
        '850' => 'kp',
        '852' => 'hk',
        '853' => 'mo',
        '855' => 'kh',
        '856' => 'la',
        '880' => 'bd',
        '886' => 'tw',
        '960' => 'mv',
        '961' => 'lb',
        '962' => 'jo',
        '963' => 'sy',
        '964' => 'iq',
        '965' => 'kw',
        '966' => 'sa',
        '967' => 'ye',
        '968' => 'om',
        '970' => 'ps',
        '971' => 'ae',
        '972' => 'il',
        '973' => 'bh',
        '974' => 'qa',
        '975' => 'bt',
        '976' => 'mn',
        '977' => 'np',
        '992' => 'tj',
        '993' => 'tm',
        '994' => 'az',
        '995' => 'ge',
        '996' => 'kg',
        '998' => 'uz',
        '1' => 'us',
        '7' => 'ru',
    ];

  /** Countries where trunk prefix 0 must not be stripped after dial code. */
    private const KEEP_LEADING_ZERO_ISO = [
        'it',
    ];

    public static function rule(bool $required = true): string
    {
        return ($required ? 'required|' : 'nullable|') . 'phone_intl';
    }

    /**
     * True when the value includes a recognisable international dial code.
     */
    public static function hasCountryCode(?string $phone): bool
    {
        return self::parseInternational($phone) !== null;
    }

    /**
     * Digits-only local value for numbers stored without a country code.
     */
    public static function localDigits(?string $phone): ?string
    {
        if ($phone === null) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', trim($phone));

        return $digits !== '' ? $digits : null;
    }

    /**
     * @return array{dial: string, national: string}|null
     */
    private static function parseInternational(?string $phone): ?array
    {
        if ($phone === null || trim($phone) === '') {
            return null;
        }

        $trimmed = trim($phone);
        $explicitPlus = str_starts_with($trimmed, '+');
        $rawDigits = preg_replace('/\D+/', '', $trimmed);

        if ($rawDigits === '') {
            return null;
        }

        // Up to 10 digits without "+" are treated as local numbers only.
        if (!$explicitPlus && strlen($rawDigits) <= self::NATIONAL_MAX_LENGTH) {
            return null;
        }

        $normalized = self::normalize($phone);

        if ($normalized === null) {
            return null;
        }

        $digits = substr($normalized, 1);

        foreach (self::sortedDialCodes() as $code) {
            if (!str_starts_with($digits, $code)) {
                continue;
            }

            $national = substr($digits, strlen($code));

            if ($national === '' || !ctype_digit($national) || strlen($national) > self::NATIONAL_MAX_LENGTH) {
                continue;
            }

            return [
                'dial' => $code,
                'national' => $national,
            ];
        }

        return null;
    }

    public static function normalize(?string $phone): ?string
    {
        if ($phone === null) {
            return null;
        }

        $phone = trim($phone);

        if ($phone === '') {
            return null;
        }

        $hasPlus = str_starts_with($phone, '+');
        $digits = preg_replace('/\D+/', '', $phone);

        if ($digits === '') {
            return null;
        }

        $e164 = '+' . $digits;

        return self::fixNationalLeadingZero($e164);
    }

    public static function nationalNumber(?string $phone): ?string
    {
        $parsed = self::parseInternational($phone);

        if ($parsed !== null) {
            return $parsed['national'];
        }

        return self::localDigits($phone);
    }

    /**
     * National digits only (no dial code).
     */
    public static function display(?string $phone): string
    {
        return self::nationalNumber($phone) ?? trim((string) $phone);
    }

    public static function dialCode(?string $phone): ?string
    {
        $parsed = self::parseInternational($phone);

        return $parsed !== null ? '+' . $parsed['dial'] : null;
    }

    /**
     * Dial code + national number for tables and read-only views (no flag).
     */
    public static function displayWithDialCode(?string $phone): string
    {
        if ($phone === null || trim($phone) === '') {
            return '';
        }

        $parsed = self::parseInternational($phone);

        if ($parsed !== null) {
            return '+' . $parsed['dial'] . $parsed['national'];
        }

        return self::localDigits($phone) ?? trim($phone);
    }

    /**
     * Full E.164 value for contexts without a country flag (emails, PDFs, etc.).
     */
    public static function displayE164(?string $phone): string
    {
        if ($phone === null || trim($phone) === '') {
            return '';
        }

        $parsed = self::parseInternational($phone);

        if ($parsed !== null) {
            return '+' . $parsed['dial'] . $parsed['national'];
        }

        return self::localDigits($phone) ?? trim($phone);
    }

    public static function countryIso(?string $phone): ?string
    {
        $parsed = self::parseInternational($phone);

        if ($parsed === null) {
            return null;
        }

        return self::DIAL_CODE_ISO[$parsed['dial']] ?? null;
    }

    public const NATIONAL_MAX_LENGTH = 10;

    public static function isValid(?string $phone): bool
    {
        $normalized = self::normalize($phone);

        if ($normalized === null) {
            return false;
        }

        $national = self::nationalNumber($normalized);

        if ($national === null || $national === '') {
            return false;
        }

        return ctype_digit($national) && strlen($national) <= self::NATIONAL_MAX_LENGTH;
    }

    private static function fixNationalLeadingZero(string $e164): string
    {
        $digits = substr($e164, 1);

        foreach (self::sortedDialCodes() as $code) {
            if (!str_starts_with($digits, $code)) {
                continue;
            }

            $iso = self::DIAL_CODE_ISO[$code];
            $national = substr($digits, strlen($code));

            if ($national !== '' && $national[0] === '0' && self::shouldStripLeadingZero($iso)) {
                $national = substr($national, 1);
            }

            return '+' . $code . $national;
        }

        return $e164;
    }

    private static function shouldStripLeadingZero(string $iso): bool
    {
        return !in_array($iso, self::KEEP_LEADING_ZERO_ISO, true);
    }

    /**
     * @return string[]
     */
    private static function sortedDialCodes(): array
    {
        static $codes = null;

        if ($codes === null) {
            $codes = array_keys(self::DIAL_CODE_ISO);
            usort($codes, static fn (string $a, string $b) => strlen($b) <=> strlen($a));
        }

        return $codes;
    }
}
