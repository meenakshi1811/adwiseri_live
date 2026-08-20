<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailSubscriptions extends Model
{
    use HasFactory;

    public const STATUS_SUBSCRIBED = 'subscribed';
    public const STATUS_UNSUBSCRIBED = 'unsubscribed';

    protected $table = 'email_subscriptions';
    protected $primaryKey = 'id';
    protected $fillable = [
        'email',
        'status',
        'unsubscribed_at',
    ];

    protected $casts = [
        'unsubscribed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $subscription) {
            if (empty($subscription->status)) {
                $subscription->status = self::STATUS_SUBSCRIBED;
            }
        });
    }

    public function isSubscribed(): bool
    {
        $status = strtolower(trim((string) ($this->status ?? '')));

        if ($status === '' || $status === self::STATUS_SUBSCRIBED) {
            return true;
        }

        return $status !== self::STATUS_UNSUBSCRIBED;
    }

    public function statusLabel(): string
    {
        return $this->isSubscribed() ? 'Subscribed' : 'Unsubscribed';
    }

    public function statusBadgeClass(): string
    {
        return $this->isSubscribed() ? 'email-subscriber-status--subscribed' : 'email-subscriber-status--unsubscribed';
    }
}
