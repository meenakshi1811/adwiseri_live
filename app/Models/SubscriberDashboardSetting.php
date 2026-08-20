<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriberDashboardSetting extends Model
{
    use HasFactory;

    protected $table = 'subscriber_dashboard_settings';

    protected $fillable = [
        'subscriber_id',
        'headers',
        'charts',
        'chart_count',
    ];

    protected $casts = [
        'headers' => 'array',
        'charts' => 'array',
        'chart_count' => 'integer',
    ];

    public function subscriber()
    {
        return $this->belongsTo(User::class, 'subscriber_id');
    }
}
