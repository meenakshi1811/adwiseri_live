<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ErrorLog extends Model
{
    protected $table = 'error_logs';

    protected $fillable = [
        'error_type',
        'page_screen',
        'message',
        'status_code',
        'user_id',
        'ip_address',
        'stack_trace',
    ];

    protected $casts = [
        'status_code' => 'integer',
        'user_id' => 'integer',
    ];
}
