<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
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
