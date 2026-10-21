<?php

namespace App\Services;

use App\Models\Applications;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AnalyticsClientChartService
{
    public function __construct(
        private readonly CountryCategorySettingsService $countryCategorySettingsService
    ) {
    }

    /**
     * @return array<string, bool>
     */
    public function visaDetailFilterAvailability(int $subscriberId): array
    {
        $keys = ['by_university', 'by_course', 'by_course_duration', 'by_intake', 'by_employer', 'by_job_role'];

        if ($subscriberId <= 0) {
            return array_fill_keys($keys, false);
        }

        $base = Applications::query()->where('subscriber_id', $subscriberId);

        return [
            'by_university' => $this->hasMeaningfulValues((clone $base), 'institution', 'study'),
            'by_course' => $this->hasMeaningfulValues((clone $base), 'course_name', 'study'),
            'by_course_duration' => $this->hasMeaningfulValues((clone $base), 'course_duration', 'study'),
            'by_intake' => Schema::hasColumn('applications', 'intake')
                ? $this->hasMeaningfulValues((clone $base), 'intake', 'study')
                : $this->hasStudyIntakeFromStartDate(clone $base),
            'by_employer' => $this->hasMeaningfulValues((clone $base), 'employer_name', 'work'),
            'by_job_role' => $this->hasMeaningfulValues((clone $base), 'employment_role', 'work'),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function aggregateClientsByVisaDetailField(
        int $subscriberId,
        string $column,
        string $visaScope,
        Carbon $startDate,
        Carbon $endDate
    ): array {
        if ($subscriberId <= 0 || !Schema::hasColumn('applications', $column)) {
            return [];
        }

        if ($column === 'intake' && !Schema::hasColumn('applications', 'intake')) {
            return $this->aggregateClientsByStudyStartDateIntake($subscriberId, $startDate, $endDate);
        }

        $query = Applications::query()
            ->where('subscriber_id', $subscriberId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('client_id');

        $this->applyVisaScope($query, $visaScope);
        $this->applyMeaningfulValueScope($query, $column);

        $rows = $query
            ->select($column, DB::raw('COUNT(DISTINCT client_id) as total_clients'))
            ->groupBy($column)
            ->orderByDesc('total_clients')
            ->get();

        return $this->orderRows($rows
            ->filter(fn ($row) => (int) $row->total_clients > 0)
            ->map(fn ($row) => [
                $column => (string) $row->{$column},
                'total_clients' => (int) $row->total_clients,
            ])
            ->values()
            ->all(), $column);
    }

    /**
     * Numeric categories read from low to high: intakes in date order, course durations shortest first.
     * Other fields stay most-clients-first. Values that can't be read go last, alphabetically.
     *
     * @param  list<array<string, mixed>>  $rows
     * @return list<array<string, mixed>>
     */
    private function orderRows(array $rows, string $column): array
    {
        $sortKey = match ($column) {
            'intake' => fn (string $label) => $this->intakeSortKey($label),
            'course_duration' => fn (string $label) => $this->durationInMonths($label),
            default => null,
        };

        if ($sortKey === null) {
            return $rows;
        }

        usort($rows, function (array $a, array $b) use ($column, $sortKey) {
            $keyA = $sortKey((string) $a[$column]);
            $keyB = $sortKey((string) $b[$column]);

            if ($keyA === null || $keyB === null) {
                if ($keyA !== $keyB) {
                    return $keyA === null ? 1 : -1;
                }
            } elseif ($keyA != $keyB) {
                return $keyA <=> $keyB;
            }

            return strnatcasecmp((string) $a[$column], (string) $b[$column]);
        });

        return $rows;
    }

    /**
     * "September 2026", "Sep 2026", "Fall 2026", "2026-09" or "09/2026" → 202609; a bare year sorts before its months.
     */
    private function intakeSortKey(string $label): ?int
    {
        $text = strtolower(trim($label));

        if (preg_match('/\b(\d{1,2})[\/\-.]((?:19|20)\d{2})\b/', $text, $match)) {
            return (int) $match[2] * 100 + min(12, (int) $match[1]);
        }
        if (preg_match('/\b((?:19|20)\d{2})[\/\-.](\d{1,2})\b/', $text, $match)) {
            return (int) $match[1] * 100 + min(12, (int) $match[2]);
        }
        if (!preg_match('/\b((?:19|20)\d{2})\b/', $text, $year)) {
            return null;
        }

        $months = [
            'jan' => 1, 'feb' => 2, 'mar' => 3, 'apr' => 4, 'may' => 5, 'jun' => 6,
            'jul' => 7, 'aug' => 8, 'sep' => 9, 'oct' => 10, 'nov' => 11, 'dec' => 12,
            'winter' => 1, 'spring' => 3, 'summer' => 6, 'autumn' => 9, 'fall' => 9,
        ];
        foreach ($months as $word => $month) {
            if (preg_match('/\b' . $word . '/', $text)) {
                return (int) $year[1] * 100 + $month;
            }
        }

        return (int) $year[1] * 100;
    }

    /**
     * "12 months", "2 years", "1 year 6 months", "1.5 yrs", "16 weeks" → length in months.
     */
    private function durationInMonths(string $label): ?float
    {
        $monthsPerUnit = [
            'year' => 12, 'yr' => 12, 'semester' => 6, 'quarter' => 3, 'term' => 4,
            'month' => 1, 'mo' => 1, 'week' => 12 / 52, 'wk' => 12 / 52, 'day' => 12 / 365,
        ];

        $pattern = '/(\d+(?:\.\d+)?)\s*(years?|yrs?|semesters?|quarters?|terms?|months?|mos?|weeks?|wks?|days?)\b/';
        if (!preg_match_all($pattern, strtolower($label), $matches, PREG_SET_ORDER)) {
            return null;
        }

        $total = 0.0;
        foreach ($matches as $match) {
            $total += (float) $match[1] * $monthsPerUnit[rtrim($match[2], 's')];
        }

        return $total;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function aggregateClientsByStudyStartDateIntake(
        int $subscriberId,
        Carbon $startDate,
        Carbon $endDate
    ): array {
        if (!Schema::hasColumn('applications', 'start_date')) {
            return [];
        }

        $query = Applications::query()
            ->where('subscriber_id', $subscriberId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('client_id');

        $this->applyVisaScope($query, 'study');
        $query->whereNotNull('start_date')
            ->where('start_date', '!=', '')
            ->where('start_date', '!=', '0000-00-00');

        $counts = [];

        foreach ($query->get(['start_date', 'client_id']) as $application) {
            $label = $this->formatIntakeLabel($application->start_date);
            if ($label === null) {
                continue;
            }

            $counts[$label] = $counts[$label] ?? [];
            $counts[$label][(int) $application->client_id] = true;
        }

        $rows = collect($counts)
            ->map(fn (array $clientIds, string $label) => [
                'intake' => $label,
                'total_clients' => count($clientIds),
            ])
            ->values()
            ->all();

        return $this->orderRows($rows, 'intake');
    }

    private function hasMeaningfulValues(Builder $query, string $column, string $visaScope): bool
    {
        if (!Schema::hasColumn('applications', $column)) {
            return false;
        }

        $this->applyVisaScope($query, $visaScope);
        $this->applyMeaningfulValueScope($query, $column);

        return $query->exists();
    }

    private function hasStudyIntakeFromStartDate(Builder $query): bool
    {
        if (!Schema::hasColumn('applications', 'start_date')) {
            return false;
        }

        $this->applyVisaScope($query, 'study');

        return $query->whereNotNull('start_date')
            ->where('start_date', '!=', '')
            ->where('start_date', '!=', '0000-00-00')
            ->exists();
    }

    private function applyMeaningfulValueScope(Builder $query, string $column): void
    {
        $query->whereNotNull($column)
            ->where($column, '!=', '')
            ->where($column, '!=', 'NA');
    }

    private function applyVisaScope(Builder $query, string $visaScope): void
    {
        if ($visaScope === 'study') {
            $query->where(function (Builder $inner) {
                $inner->whereRaw('LOWER(COALESCE(application_name, "")) LIKE ?', ['%study%'])
                    ->orWhereRaw('LOWER(COALESCE(application_name, "")) LIKE ?', ['%student%']);
            });

            return;
        }

        if ($visaScope === 'work') {
            $this->countryCategorySettingsService->applyWorkVisaApplicationScope($query);
        }
    }

    private function formatIntakeLabel(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);
        if ($value === '' || $value === '0000-00-00') {
            return null;
        }

        foreach (['Y-m-d', 'd-m-Y', 'm-d-Y', 'd/m/Y', 'Y/m/d'] as $format) {
            try {
                $date = Carbon::createFromFormat($format, $value);
                if ($date && $date->format($format) === $value) {
                    return $date->format('M Y');
                }
            } catch (\Throwable $e) {
            }
        }

        try {
            return Carbon::parse($value)->format('M Y');
        } catch (\Throwable $e) {
            return $value;
        }
    }
}
