<?php

namespace App\Support;

/**
 * Payment modes that are too broad for chart breakdowns (Wire, UPI, Link, etc. are shown separately).
 */
class PaymentModeChartFilter
{
    private const EXCLUDED_MODES = ['online'];

    public static function isExcluded(?string $mode): bool
    {
        return in_array(strtolower(trim((string) $mode)), self::EXCLUDED_MODES, true);
    }

    /**
     * Exclude broad payment modes from chart queries.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder  $query
     */
    public static function applyToQuery($query, string $column, ?string $table = null): void
    {
        $qualified = $table ? $table . '.' . $column : $column;
        $placeholders = implode(', ', array_fill(0, count(self::EXCLUDED_MODES), '?'));

        $query->whereRaw(
            'LOWER(TRIM(' . $qualified . ')) NOT IN (' . $placeholders . ')',
            self::EXCLUDED_MODES
        );
    }

    /**
     * @param  array<int, string>  $labels
     * @param  array<int, int|float>  $values
     * @return array{labels: array, values: array}
     */
    public static function filterSeries(array $labels, array $values): array
    {
        $filteredLabels = [];
        $filteredValues = [];

        foreach ($labels as $index => $label) {
            if (self::isExcluded($label)) {
                continue;
            }

            $filteredLabels[] = $label;
            $filteredValues[] = $values[$index] ?? 0;
        }

        return [
            'labels' => $filteredLabels,
            'values' => $filteredValues,
        ];
    }

    /**
     * @param  iterable<mixed>  $rows
     * @param  callable(mixed): ?string  $modeResolver
     * @return array<int, mixed>
     */
    public static function filterRows(iterable $rows, callable $modeResolver): array
    {
        $filtered = [];

        foreach ($rows as $row) {
            if (self::isExcluded($modeResolver($row))) {
                continue;
            }

            $filtered[] = $row;
        }

        return $filtered;
    }
}
