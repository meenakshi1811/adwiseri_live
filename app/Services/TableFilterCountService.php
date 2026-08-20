<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class TableFilterCountService
{
    /**
     * @param  callable(mixed):(?string)  $resolver
     * @return array<int, array{key:string,label:string,count:int,value:string}>
     */
    public static function countBy(
        Collection $items,
        callable $resolver,
        string $emptyLabel = 'Unspecified',
        bool $sortByCount = true
    ): array {
        $counts = [];

        foreach ($items as $item) {
            $value = trim((string) ($resolver($item) ?? ''));
            if ($value === '') {
                $value = $emptyLabel;
            }

            $counts[$value] = ($counts[$value] ?? 0) + 1;
        }

        return self::toFilterItems($counts, $sortByCount);
    }

    /**
     * @return array<int, array{key:string,label:string,count:int,value:string}>
     */
    public static function toFilterItems(array $counts, bool $sortByCount = true): array
    {
        $items = [];

        foreach ($counts as $label => $count) {
            $items[] = [
                'key' => self::keyFor($label),
                'label' => (string) $label,
                'count' => (int) $count,
                'value' => (string) $label,
            ];
        }

        if ($sortByCount) {
            usort($items, function (array $a, array $b): int {
                $countCompare = $b['count'] <=> $a['count'];

                return $countCompare !== 0 ? $countCompare : strcasecmp($a['label'], $b['label']);
            });
        }

        return $items;
    }

    public static function keyFor(string $value): string
    {
        $slug = Str::slug($value, '_');

        return $slug !== '' ? $slug : 'unspecified';
    }

    public static function clientVisaCountry($client, string $emptyLabel = 'Unspecified'): string
    {
        if (!$client) {
            return $emptyLabel;
        }

        if ($client->relationLoaded('applications')) {
            $visaCountry = $client->applications
                ->pluck('visa_country')
                ->map(fn ($country) => trim((string) $country))
                ->filter()
                ->first();

            if ($visaCountry) {
                return $visaCountry;
            }
        }

        return $emptyLabel;
    }

    public static function associateLocationLabel($associate, string $emptyLabel = 'Unspecified'): string
    {
        $city = trim((string) ($associate->city ?? ''));
        $country = trim((string) ($associate->country ?? ''));

        if ($city !== '' && $country !== '') {
            return $city . ', ' . $country;
        }

        if ($city !== '') {
            return $city;
        }

        if ($country !== '') {
            return $country;
        }

        return $emptyLabel;
    }

    public static function invoiceStatusLabel(?string $status): string
    {
        return match (trim((string) $status)) {
            'Paid' => 'Paid',
            'UnPaid' => 'Unpaid',
            'PartiallyPaid' => 'Partially Paid',
            'Cancelled' => 'Cancelled',
            default => 'Unspecified',
        };
    }

    /**
     * @return array<int, string>
     */
    public static function outstandingAmountRangeOrder(): array
    {
        return [
            'Fully Paid',
            '1-99',
            '100-249',
            '250-499',
            '500-999',
            '1000-2499',
            '2500-4999',
            '5000-9999',
            '10,000+',
        ];
    }

    public static function outstandingAmountRangeLabel(float $amount): string
    {
        if ($amount <= 0) {
            return 'Fully Paid';
        }

        if ($amount <= 99) {
            return '1-99';
        }

        if ($amount <= 249) {
            return '100-249';
        }

        if ($amount <= 499) {
            return '250-499';
        }

        if ($amount <= 999) {
            return '500-999';
        }

        if ($amount <= 2499) {
            return '1000-2499';
        }

        if ($amount <= 4999) {
            return '2500-4999';
        }

        if ($amount <= 9999) {
            return '5000-9999';
        }

        return '10,000+';
    }

    /**
     * @param  callable(mixed):(?float|int|string)  $resolver
     * @return array<int, array{key:string,label:string,count:int,value:string}>
     */
    public static function countByOutstandingAmountRange(Collection $items, callable $resolver): array
    {
        $counts = [];

        foreach ($items as $item) {
            $label = self::outstandingAmountRangeLabel((float) ($resolver($item) ?? 0));
            $counts[$label] = ($counts[$label] ?? 0) + 1;
        }

        $items = self::toFilterItems($counts, false);
        $order = array_flip(self::outstandingAmountRangeOrder());

        usort($items, function (array $a, array $b) use ($order): int {
            $left = $order[$a['label']] ?? PHP_INT_MAX;
            $right = $order[$b['label']] ?? PHP_INT_MAX;

            return $left <=> $right;
        });

        return $items;
    }
}
