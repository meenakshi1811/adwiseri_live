<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_DENIED = 'denied';
    public const STATUS_CANCELED = 'canceled';
    public const STATUS_COMPLETED = 'completed';

    protected $casts = [
        'appointment_date' => 'date',
    ];

    protected $fillable = [
        'client_id',
        'subscriber_id',
        'user_id',
        'appointment_date',
        'appointment_time',
        'remarks',
        'send_via',
        'calendly_link',
        'calendly_event_uri',
        'status'
    ];

    public function scheduledAt(?string $timezone = null): ?Carbon
    {
        if (empty($this->appointment_date) || empty($this->appointment_time)) {
            return null;
        }

        $timezone = $timezone ?: config('app.timezone');
        $date = Carbon::parse($this->appointment_date)->format('Y-m-d');
        $time = Carbon::parse($this->appointment_time)->format('H:i:s');

        return Carbon::parse($date . ' ' . $time, $timezone);
    }

    public function isPast(?string $timezone = null): bool
    {
        $scheduledAt = $this->scheduledAt($timezone);

        if (!$scheduledAt) {
            return false;
        }

        $timezone = $timezone ?: config('app.timezone');

        return now($timezone)->gte($scheduledAt);
    }

    public static function scheduledAtFromInput(string $date, string $time, ?string $timezone = null): ?Carbon
    {
        $timezone = $timezone ?: config('app.timezone');

        try {
            $normalizedTime = Carbon::createFromFormat('H:i', $time)->format('H:i:s');
        } catch (\Throwable $exception) {
            try {
                $normalizedTime = Carbon::parse($time)->format('H:i:s');
            } catch (\Throwable $exception) {
                return null;
            }
        }

        return Carbon::parse(
            Carbon::parse($date)->format('Y-m-d') . ' ' . $normalizedTime,
            $timezone
        );
    }

    public function formattedDate(string $format = 'd-m-Y'): string
    {
        return !empty($this->appointment_date)
            ? Carbon::parse($this->appointment_date)->format($format)
            : 'N/A';
    }

    public function formattedTime(string $format = 'h:i A'): string
    {
        return !empty($this->appointment_time)
            ? Carbon::parse($this->appointment_time)->format($format)
            : 'N/A';
    }

    public function client()
    {
        return $this->belongsTo(Clients::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subscriber()
    {
        return $this->belongsTo(User::class,'subscriber_id');
    }
}
