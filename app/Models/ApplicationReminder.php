<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationReminder extends Model
{
    public const EMAIL_TO_USER_ONLY = 'user_only';

    public const EMAIL_TO_USER_BCC_SUBSCRIBER = 'user_bcc_subscriber';

    protected $fillable = [
        'user_id',
        'client_id',
        'application_id',
        'subject',
        'description',
        'deadline',
        'email_frequency',
        'email_to',
        'notify_user_id',
        'last_sent_at',
        'is_active',
    ];

    protected $casts = [
        'deadline' => 'date',
        'last_sent_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public static function allowedEmailToValues(): array
    {
        return [
            self::EMAIL_TO_USER_ONLY,
            self::EMAIL_TO_USER_BCC_SUBSCRIBER,
        ];
    }

    public function subscriber(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Clients::class, 'client_id');
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Applications::class, 'application_id');
    }

    public function notifyUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'notify_user_id');
    }

    public function bccSubscriber(): bool
    {
        return $this->email_to === self::EMAIL_TO_USER_BCC_SUBSCRIBER;
    }
}
