<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CookieConsentLog extends Model
{
    protected $fillable = [
        'user_id',
        'subscriber_id',
        'consent_action',
        'page_url',
        'ip_address',
        'user_agent',
        'accepted_at',
    ];

    protected $casts = [
        'accepted_at' => 'datetime',
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
