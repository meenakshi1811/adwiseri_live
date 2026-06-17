<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Email_broadcasts extends Model
{
    use HasFactory;

    protected $table = 'email_broadcasts';

    protected $fillable = [
        'broadcast_id',
        'subscriber_id',
        'user_id',
        'sender_name',
        'sender_email',
        'communicate_type',
        'subject',
        'body',
        'status',
        'total_recipients',
        'recipient_labels',
        'recipient_payload',
        'sent_count',
        'failed_count',
        'queued_at',
        'started_at',
        'completed_at',
        'error_message',
    ];

    protected $casts = [
        'recipient_labels' => 'array',
        'recipient_payload' => 'array',
        'queued_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];
}
