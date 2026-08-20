<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class PaymentReminderSetting extends Model
{
    use HasFactory;

    public const REMINDERS_TO_CLIENTS = 'clients';

    public const REMINDERS_TO_ASSOCIATES = 'associates';

    public const TYPE_PAYMENTS = 'payments';

    public const TYPE_DOCUMENTS = 'documents';

    public const EMAIL_TO_CLIENT_ONLY = 'client_only';

    public const EMAIL_TO_CLIENT_BCC_SUBSCRIBER = 'client_bcc_subscriber';

    public const EMAIL_TO_ASSOCIATE_ONLY = 'associate_only';

    public const EMAIL_TO_ASSOCIATE_BCC_SUBSCRIBER = 'associate_bcc_subscriber';

    /** @deprecated Use EMAIL_TO_ASSOCIATE_BCC_SUBSCRIBER */
    public const EMAIL_TO_ASSOCIATE_BCC_SUBSCRIBER_ALERTS = 'associate_bcc_subscriber_alerts';

    public const CLIENT_GROUP_ALL = 'all';

    /** @var array<string, float> */
    public const CLIENT_GROUP_THRESHOLDS = [
        'over_100' => 100,
        'over_500' => 500,
        'over_1000' => 1000,
        'over_5000' => 5000,
        'over_10000' => 10000,
        'over_25000' => 25000,
        'over_50000' => 50000,
        'over_100000' => 100000,
    ];

    protected $table = 'payment_reminder_settings';

    protected $fillable = [
        'user_id',
        'reminder_type',
        'reminders_to',
        'client_group',
        'email_frequency',
        'email_to',
        'last_sent_at',
    ];

    protected $casts = [
        'last_sent_at' => 'datetime',
    ];

    public static function hasReminderTypeColumn(): bool
    {
        static $hasColumn = null;

        if ($hasColumn === null) {
            $hasColumn = Schema::hasTable('payment_reminder_settings')
                && Schema::hasColumn('payment_reminder_settings', 'reminder_type');
        }

        return $hasColumn;
    }

    public static function forUserPayments(int $userId): ?self
    {
        $query = static::query()->where('user_id', $userId);
        self::applyPaymentsTypeScope($query);

        return $query->first();
    }

    public static function forUserDocuments(int $userId): ?self
    {
        if (!self::hasReminderTypeColumn()) {
            return null;
        }

        return static::query()
            ->where('user_id', $userId)
            ->where('reminder_type', self::TYPE_DOCUMENTS)
            ->first();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public static function saveForType(int $userId, string $reminderType, array $attributes): self
    {
        if (!self::hasReminderTypeColumn()) {
            if ($reminderType === self::TYPE_DOCUMENTS) {
                throw new \InvalidArgumentException('Documents reminder settings require a database update.');
            }

            return static::query()->updateOrCreate(
                ['user_id' => $userId],
                $attributes
            );
        }

        return static::query()->updateOrCreate(
            ['user_id' => $userId, 'reminder_type' => $reminderType],
            $attributes
        );
    }

    public function scopePayments(Builder $query): Builder
    {
        self::applyPaymentsTypeScope($query);

        return $query;
    }

    public function scopeDocuments(Builder $query): Builder
    {
        if (!self::hasReminderTypeColumn()) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where('reminder_type', self::TYPE_DOCUMENTS);
    }

    private static function applyPaymentsTypeScope(Builder $query): void
    {
        if (!self::hasReminderTypeColumn()) {
            return;
        }

        $query->where(function (Builder $inner) {
            $inner->where('reminder_type', self::TYPE_PAYMENTS)
                ->orWhereNull('reminder_type');
        });
    }

    public static function allowedClientGroups(): array
    {
        return array_merge([self::CLIENT_GROUP_ALL], array_keys(self::CLIENT_GROUP_THRESHOLDS));
    }

    public static function allowedScheduleTypes(): array
    {
        return [
            self::TYPE_PAYMENTS,
            self::TYPE_DOCUMENTS,
        ];
    }

    public static function allowedRemindersToValues(): array
    {
        return [
            self::REMINDERS_TO_CLIENTS,
            self::REMINDERS_TO_ASSOCIATES,
        ];
    }

    public static function allowedEmailToValuesForRemindersTo(string $remindersTo): array
    {
        if ($remindersTo === self::REMINDERS_TO_ASSOCIATES) {
            return [
                self::EMAIL_TO_ASSOCIATE_ONLY,
                self::EMAIL_TO_ASSOCIATE_BCC_SUBSCRIBER,
            ];
        }

        return [
            self::EMAIL_TO_CLIENT_ONLY,
            self::EMAIL_TO_CLIENT_BCC_SUBSCRIBER,
        ];
    }

    public static function allowedEmailToValues(): array
    {
        return array_merge(
            self::allowedEmailToValuesForRemindersTo(self::REMINDERS_TO_CLIENTS),
            self::allowedEmailToValuesForRemindersTo(self::REMINDERS_TO_ASSOCIATES)
        );
    }

    public static function normalizeRemindersTo(?string $value, ?string $emailTo = null): string
    {
        $value = trim((string) $value);
        if (in_array($value, self::allowedRemindersToValues(), true)) {
            return $value;
        }

        $emailTo = self::normalizeEmailTo($emailTo);

        if (in_array($emailTo, [
            self::EMAIL_TO_ASSOCIATE_ONLY,
            self::EMAIL_TO_ASSOCIATE_BCC_SUBSCRIBER,
            self::EMAIL_TO_ASSOCIATE_BCC_SUBSCRIBER_ALERTS,
        ], true)) {
            return self::REMINDERS_TO_ASSOCIATES;
        }

        return self::REMINDERS_TO_CLIENTS;
    }

    public static function defaultEmailToForRemindersTo(string $remindersTo): string
    {
        $options = self::allowedEmailToValuesForRemindersTo($remindersTo);

        return $options[0] ?? self::EMAIL_TO_CLIENT_ONLY;
    }

    public static function groupFieldLabel(string $remindersTo): string
    {
        return $remindersTo === self::REMINDERS_TO_ASSOCIATES
            ? 'Select Associate Group(s)'
            : 'Select Client Group(s)';
    }

    public static function normalizeEmailTo(?string $value): string
    {
        if ($value === self::EMAIL_TO_ASSOCIATE_BCC_SUBSCRIBER_ALERTS) {
            return self::EMAIL_TO_ASSOCIATE_BCC_SUBSCRIBER;
        }

        return $value ?: self::EMAIL_TO_CLIENT_ONLY;
    }

    public static function clientGroupLabel(string $value): string
    {
        if ($value === self::CLIENT_GROUP_ALL) {
            return 'All';
        }

        if (isset(self::CLIENT_GROUP_THRESHOLDS[$value])) {
            return 'Outstanding Payment Over ' . number_format(self::CLIENT_GROUP_THRESHOLDS[$value]);
        }

        return ucwords(str_replace('_', ' ', $value));
    }

    public function sendsToAssociates(): bool
    {
        return self::normalizeRemindersTo($this->reminders_to, $this->email_to) === self::REMINDERS_TO_ASSOCIATES;
    }

    public function bccSubscriber(): bool
    {
        return in_array($this->email_to, [
            self::EMAIL_TO_CLIENT_BCC_SUBSCRIBER,
            self::EMAIL_TO_ASSOCIATE_BCC_SUBSCRIBER,
            self::EMAIL_TO_ASSOCIATE_BCC_SUBSCRIBER_ALERTS,
        ], true);
    }

    public function amountThreshold(): ?float
    {
        return self::CLIENT_GROUP_THRESHOLDS[$this->client_group] ?? null;
    }
}
