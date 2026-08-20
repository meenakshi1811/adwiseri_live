<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class SubscriberTableFilterService
{
    public const DURATION_ORDER = [
        'under_1_year' => 'Under 1 Year',
        '1_year' => '1 Year',
        '2_years' => '2 Years',
        '3_years' => '3 Years',
        '4_years' => '4 Years',
        '5_years' => '5 Years',
    ];

    public const EXPIRY_ORDER = [
        '1_day' => '1 Day',
        '1_week' => '1 Week',
        '1_month' => '1 Month',
        '1_quarter' => '1 Quarter',
    ];

    public function planKey(?string $membership): string
    {
        $normalized = Str::lower(trim((string) $membership));

        return $normalized !== '' ? $normalized : 'unknown';
    }

    public function planLabel(?string $membership): string
    {
        $label = trim((string) $membership);

        return $label !== '' ? $label : 'Unknown';
    }

    public function durationKey(User $subscriber): ?string
    {
        if (empty($subscriber->membership_start_date) || empty($subscriber->membership_expiry_date)) {
            return null;
        }

        $years = Carbon::parse($subscriber->membership_start_date)
            ->diffInYears(Carbon::parse($subscriber->membership_expiry_date));

        return match (true) {
            $years === 0 => 'under_1_year',
            $years === 1 => '1_year',
            $years === 2 => '2_years',
            $years === 3 => '3_years',
            $years === 4 => '4_years',
            $years >= 5 => '5_years',
            default => null,
        };
    }

    public function durationLabel(?string $key): ?string
    {
        return $key ? (self::DURATION_ORDER[$key] ?? null) : null;
    }

    public function expiryKey(User $subscriber): ?string
    {
        if (empty($subscriber->membership_expiry_date)) {
            return null;
        }

        $expiry = Carbon::parse($subscriber->membership_expiry_date)->startOfDay();
        $today = Carbon::today();

        if ($expiry->lt($today)) {
            return null;
        }

        $daysUntil = $today->diffInDays($expiry);

        return match (true) {
            $daysUntil <= 1 => '1_day',
            $daysUntil <= 7 => '1_week',
            $daysUntil <= 30 => '1_month',
            $daysUntil <= 90 => '1_quarter',
            default => null,
        };
    }

    public function expiryLabel(?string $key): ?string
    {
        return $key ? (self::EXPIRY_ORDER[$key] ?? null) : null;
    }

    public function buildFilterSummary(Collection $subscribers): array
    {
        $planCounts = [];
        $durationCounts = array_fill_keys(array_keys(self::DURATION_ORDER), 0);
        $expiryCounts = array_fill_keys(array_keys(self::EXPIRY_ORDER), 0);

        foreach ($subscribers as $subscriber) {
            $planKey = $this->planKey($subscriber->membership);
            if (!isset($planCounts[$planKey])) {
                $planCounts[$planKey] = [
                    'key' => $planKey,
                    'label' => $this->planLabel($subscriber->membership),
                    'count' => 0,
                ];
            }
            $planCounts[$planKey]['count']++;

            $durationKey = $this->durationKey($subscriber);
            if ($durationKey && isset($durationCounts[$durationKey])) {
                $durationCounts[$durationKey]++;
            }

            $expiryKey = $this->expiryKey($subscriber);
            if ($expiryKey && isset($expiryCounts[$expiryKey])) {
                $expiryCounts[$expiryKey]++;
            }
        }

        $plans = collect($planCounts)
            ->sortBy('label')
            ->values()
            ->all();

        $durations = collect(self::DURATION_ORDER)
            ->map(function ($label, $key) use ($durationCounts) {
                return [
                    'key' => $key,
                    'label' => $label,
                    'count' => $durationCounts[$key] ?? 0,
                ];
            })
            ->values()
            ->all();

        $expiries = collect(self::EXPIRY_ORDER)
            ->map(function ($label, $key) use ($expiryCounts) {
                return [
                    'key' => $key,
                    'label' => $label,
                    'count' => $expiryCounts[$key] ?? 0,
                ];
            })
            ->values()
            ->all();

        return [
            'plans' => $plans,
            'durations' => $durations,
            'expiries' => $expiries,
        ];
    }
}
