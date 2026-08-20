<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailAutoReplyLog extends Model
{
    protected $fillable = [
        'mailbox',
        'sender_email',
        'incoming_message_id',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];
}
