<?php

namespace App\Services;

use App\Models\PaymentReminderSetting;
use App\Models\User;
use Carbon\Carbon;

class ReminderScheduleService
{
    public function shouldRunAtScheduledTime(User $subscriber): bool
    {
        $now = now($this->resolveTimezone($subscriber));

        return (int) $now->format('G') === 9 && (int) $now->format('i') === 0;
    }

    public function shouldRunForFrequency(?Carbon $lastSentAt, string $frequency, User $subscriber): bool
    {
        $timezone = $this->resolveTimezone($subscriber);
        $now = now($timezone);
        $lastSentAt = $lastSentAt ? Carbon::parse($lastSentAt)->timezone($timezone) : null;

        if (!$lastSentAt) {
            return true;
        }

        if ($frequency === 'daily') {
            return $lastSentAt->copy()->startOfDay()->lte($now->copy()->subDay()->startOfDay());
        }

        if ($frequency === 'weekly') {
            return $lastSentAt->copy()->startOfWeek()->lte($now->copy()->subWeek()->startOfWeek());
        }

        if ($frequency === 'monthly') {
            return $lastSentAt->copy()->startOfMonth()->lte($now->copy()->subMonth()->startOfMonth());
        }

        return $lastSentAt->lte($now->copy()->subMonths(3));
    }

    public function resolveTimezone(User $subscriber): string
    {
        $timezone = $this->normalizeTimezone($subscriber->timezone ?? null);

        return $timezone ?? $this->normalizeTimezone(config('app.timezone', 'UTC')) ?? 'UTC';
    }

    private function normalizeTimezone($timezone): ?string
    {
        $timezone = is_string($timezone) ? trim($timezone) : '';

        if ($timezone === '') {
            return null;
        }

        try {
            new \DateTimeZone($timezone);

            return $timezone;
        } catch (\Exception $e) {
        }

        if (preg_match('/\((?:GMT|UTC)\s*([+\-]\d{1,2}:?\d{2})\)/i', $timezone, $offsetMatch)) {
            $normalized = $this->normalizeOffset($offsetMatch[1]);
            if ($normalized !== null) {
                return $normalized;
            }
        }

        if (preg_match('/(Asia\/[A-Za-z_]+|Europe\/[A-Za-z_]+|America\/[A-Za-z_]+|Africa\/[A-Za-z_]+|Australia\/[A-Za-z_]+|Pacific\/[A-Za-z_]+|Atlantic\/[A-Za-z_]+|Indian\/[A-Za-z_]+)/', $timezone, $identifierMatch)) {
            try {
                new \DateTimeZone($identifierMatch[1]);

                return $identifierMatch[1];
            } catch (\Exception $e) {
            }
        }

        if (preg_match('/^(?:GMT|UTC)?\s*([+\-]\d{1,2}:?\d{2})$/i', $timezone, $offsetOnlyMatch)) {
            return $this->normalizeOffset($offsetOnlyMatch[1]);
        }

        return null;
    }

    private function normalizeOffset(string $offset): ?string
    {
        $offset = str_replace(' ', '', $offset);

        if (!preg_match('/^([+\-])(\d{1,2}):?(\d{2})$/', $offset, $match)) {
            return null;
        }

        $hours = (int) $match[2];
        $minutes = (int) $match[3];
        if ($hours > 14 || $minutes > 59) {
            return null;
        }

        return sprintf('%s%02d:%02d', $match[1], $hours, $minutes);
    }

    public function firstName(string $fullName): string
    {
        $parts = preg_split('/\s+/', trim($fullName));

        return $parts[0] ?? $fullName;
    }
}
