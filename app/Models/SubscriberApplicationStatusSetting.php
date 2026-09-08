<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriberApplicationStatusSetting extends Model
{
    use HasFactory;

    protected $table = 'subscriber_application_status_settings';

    protected $fillable = [
        'subscriber_id',
        'visa_category',
        'statuses',
        'end_date_required',
    ];

    protected $casts = [
        'statuses' => 'array',
        'end_date_required' => 'array',
    ];

    public function subscriber()
    {
        return $this->belongsTo(User::class, 'subscriber_id');
    }
}
