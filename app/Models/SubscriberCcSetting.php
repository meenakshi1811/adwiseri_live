<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriberCcSetting extends Model
{
    use HasFactory;

    protected $table = 'subscriber_cc_settings';

    protected $fillable = [
        'subscriber_id',
        'countries',
        'visa_categories',
        'document_lists',
    ];

    protected $casts = [
        'countries' => 'array',
        'visa_categories' => 'array',
        'document_lists' => 'array',
    ];

    public function subscriber()
    {
        return $this->belongsTo(User::class, 'subscriber_id');
    }
}
