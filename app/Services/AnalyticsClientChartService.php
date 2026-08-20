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
        $keys = ['by_university', 'by_course', 'by_intake', 'by_employer', 'by_job_role'];

        if ($subscriberId <= 0) {
            return array_fill_keys($keys, false);
        }

        $base = Applications::query()->where('subscriber_id', $subscriberId);

        return [
            'by_university' => $this->hasMeaningfulValues((clone $base), 'institution', 'study'),
            'by_course' => $this->hasMeaningfulValues((clone $base), 'course_name', 'study'),
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

        return $rows
            ->filter(fn ($row) => (int) $row->total_clients > 0)
            ->map(fn ($row) => [
                $column => (string) $row->{$column},
                'total_clients' => (int) $row->total_clients,
            ])
            ->values()
            ->all();
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
            ->sortByDesc('total_clients')
            ->values()
            ->all();

        return $rows;
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
