<?php

namespace App\Support;

class ApplicationStatuses
{
    public const CLOSED = 'Closed';

    public const FLOW = [
        'Client Registered',
        'Client Counselled',
        'Preparation',
        'Appointment Booked',
        'Applied',
        'Decision',
        'Appeal Lodged',
        'Appeal Decision',
        'AR / JR Lodged',
        'AR / JR Decision',
        'Withdrawn',
        'Cancelled',
        'Closed',
    ];

    /** Applications with these statuses are not considered active. */
    public const INACTIVE = ['Withdrawn', 'Cancelled', 'Closed'];

    /** No further status changes are allowed once terminal. */
    public const TERMINAL = ['Withdrawn', 'Cancelled', 'Closed'];

    public const END_DATE_REQUIRED = [
        'Decision',
        'Appeal Decision',
        'AR / JR Decision',
        'Withdrawn',
        'Cancelled',
        'Closed',
    ];

    public static function normalize(?string $status): string
    {
        if ($status === 'Apointment Booked') {
            return 'Appointment Booked';
        }

        return $status ?: 'Client Registered';
    }

    public static function isActive(?string $status): bool
    {
        return !in_array(self::normalize($status), self::INACTIVE, true);
    }

    public static function isTerminal(?string $status): bool
    {
        return in_array(self::normalize($status), self::TERMINAL, true);
    }
}
