<?php

namespace App\Support;

class DocumentFileName
{
    /**
     * Short label for document data-tables (File Name column).
     */
    public static function shorten(?string $fileName, ?string $docName = null, int $maxLength = 24): string
    {
        return self::forTable($fileName, $docName, $maxLength);
    }

    public static function forTable(?string $fileName, ?string $docName = null, int $maxLength = 24): string
    {
        $fileName = trim((string) $fileName);
        $docName = self::cleanLabel($docName);

        if ($fileName === '' && $docName === '') {
            return '-';
        }

        $extension = self::extension($fileName);
        $fromFile = self::humanizeStoredFileName($fileName);
        $meaningfulDoc = $docName !== '' && strtolower($docName) !== 'xxx';

        if ($meaningfulDoc && ($fromFile === '' || self::isTimestampStorageName($fileName, $fromFile))) {
            $display = $docName;
            if ($extension !== '' && !self::endsWithExtension($display, $extension)) {
                $display .= '.' . $extension;
            }
        } elseif ($fromFile !== '') {
            $display = $fromFile;
        } elseif ($meaningfulDoc) {
            $display = $extension !== '' ? $docName . '.' . $extension : $docName;
        } else {
            $display = $fileName;
        }

        $display = self::cleanLabel($display);

        return $display === '' ? '-' : self::truncate($display, max(12, $maxLength));
    }

    /**
     * Compact unique storage name for newly uploaded client documents.
     */
    public static function storageName(?string $docName, string $originalName): string
    {
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        if ($extension === '') {
            $extension = 'bin';
        }

        $slug = self::slug($docName);
        if ($slug === '') {
            $slug = self::slug(pathinfo($originalName, PATHINFO_FILENAME));
        }
        if ($slug === '') {
            $slug = 'doc';
        }

        return time() . '-' . substr($slug, 0, 36) . '.' . $extension;
    }

    private static function humanizeStoredFileName(string $fileName): string
    {
        $fileName = trim($fileName);
        if ($fileName === '') {
            return '';
        }

        $extension = self::extension($fileName);
        $base = pathinfo($fileName, PATHINFO_FILENAME);

        // Strip leading unix timestamp (with optional separator).
        $base = preg_replace('/^\d{10,}[-_]?/', '', $base) ?? $base;

        // Strip generated suffixes: -clientId-timestamp or trailing -digits.
        $base = preg_replace('/-\d+-\d{10,}$/', '', $base) ?? $base;
        $base = preg_replace('/-\d{10,}$/', '', $base) ?? $base;
        $base = preg_replace('/\d{10,}$/', '', $base) ?? $base;

        $base = str_replace(['_', '-'], ' ', $base);
        $base = preg_replace('/\s+/', ' ', $base) ?? $base;
        $base = trim($base);

        if ($base === '' || preg_match('/^\d+$/', $base)) {
            return '';
        }

        return $extension !== '' ? $base . '.' . $extension : $base;
    }

    private static function isTimestampStorageName(string $storedName, string $humanized): bool
    {
        if (preg_match('/^\d{10,}/', $storedName) !== 1) {
            return false;
        }

        if ($humanized === '') {
            return true;
        }

        $storedBase = pathinfo($storedName, PATHINFO_FILENAME);
        $humanBase = pathinfo($humanized, PATHINFO_FILENAME);

        return strlen($storedBase) > strlen($humanBase) + 8;
    }

    private static function truncate(string $display, int $maxLength): string
    {
        if (strlen($display) <= $maxLength) {
            return $display;
        }

        $extension = self::extension($display);
        $base = pathinfo($display, PATHINFO_FILENAME);

        if ($extension !== '') {
            $extSuffix = '.' . $extension;
            $budget = $maxLength - strlen($extSuffix) - 1;

            if ($budget < 4) {
                return substr($display, 0, max(1, $maxLength - 1)) . '…';
            }

            $front = (int) ceil($budget * 0.55);
            $back = max(1, $budget - $front);

            return substr($base, 0, $front) . '…' . substr($base, -$back) . $extSuffix;
        }

        return substr($display, 0, max(1, $maxLength - 1)) . '…';
    }

    private static function extension(string $fileName): string
    {
        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        return preg_match('/^[a-z0-9]{1,8}$/', $extension) ? $extension : '';
    }

    private static function endsWithExtension(string $label, string $extension): bool
    {
        return str_ends_with(strtolower($label), '.' . strtolower($extension));
    }

    private static function cleanLabel(?string $value): string
    {
        $value = trim((string) $value);

        return preg_replace('/\s+/', ' ', $value) ?? '';
    }

    private static function slug(?string $value): string
    {
        $value = strtolower(trim((string) $value));
        $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';
        $value = trim($value, '-');

        return $value;
    }
}
