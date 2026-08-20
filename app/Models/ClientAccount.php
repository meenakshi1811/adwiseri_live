<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientAccount extends Model
{
    use HasFactory;

    protected $table = 'client_accounts';

    protected $fillable = [
        'subscriber_id',
        'user_id',
        'client_id',
        'application_id',
        'trans_type',
        'amount',
        'description',
        'prev_balance',
        'total',
        'transaction_date',
        'trans_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'prev_balance' => 'decimal:2',
        'total' => 'decimal:2',
        'transaction_date' => 'date',
    ];

    public const EXPENSE_TYPES = [
        'Deposit / Advance Collected',
        'Registration Fee',
        'Admin Charges',
        'Couriers / Mails',
        'Telephone Calls',
        'Printing / Fax',
        'Application Fee',
        'License Fee',
        'Travel Cost',
        'Stamp Duty',
        'Court Fees',
        'Miscellaneous',
        'Other',
    ];

    public const CREDIT_DESCRIPTION_TYPES = [
        'Deposit / Advance Collected',
        'Other',
    ];

    public static function debitDescriptionTypes(): array
    {
        return array_values(array_filter(
            self::EXPENSE_TYPES,
            fn (string $type) => $type !== 'Deposit / Advance Collected'
        ));
    }

    public static function descriptionTypesForTransType(string $transType): array
    {
        return strcasecmp($transType, 'Credit') === 0
            ? self::CREDIT_DESCRIPTION_TYPES
            : self::debitDescriptionTypes();
    }

    public function client()
    {
        return $this->belongsTo(Clients::class, 'client_id');
    }

    public function application()
    {
        return $this->belongsTo(Applications::class, 'application_id');
    }

    public function subscriber()
    {
        return $this->belongsTo(User::class, 'subscriber_id');
    }

    public function enteredBy()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public static function normalizeDescriptionOption(string $description): string
    {
        if ($description === 'Advance Collection') {
            return 'Deposit / Advance Collected';
        }

        return in_array($description, self::EXPENSE_TYPES, true) ? $description : 'Other';
    }
}
