<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserJourneyLog extends Model
{
    protected $table = 'user_journey_logs';

    protected $fillable = [
        'subscriber_id',
        'user_id',
        'user_name',
        'user_type',
        'event_category',
        'event_type',
        'event_detail',
        'page_url',
        'http_method',
        'ip_address',
        'user_agent',
        'metadata',
        'local_time',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function subscriber()
    {
        return $this->belongsTo(User::class, 'subscriber_id');
    }
}
