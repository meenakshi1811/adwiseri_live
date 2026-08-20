<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Invoice_settings extends Model
{
    use HasFactory;

    public const TAX_LABELS = ['VAT', 'Tax', 'GST'];

    public const RECIPIENT_CLIENTS = 'clients';
    public const RECIPIENT_ASSOCIATES = 'associates';

    protected $table = "invoice_settings";
    protected $primaryKey = "id";
    protected $fillable = [
        'user_id',
        'recipient_type',
        'name',
        'phone',
        'email',
        'country',
        'state',
        'city',
        'pincode',
        'tax',
        'tax_label',
        'discount',
        'description',
        'payment_link',
        'payment_qr_code',
        'invoice_note',
    ];

    public static function taxLabelOptions(): array
    {
        return self::TAX_LABELS;
    }

    public static function resolveTaxLabel(?string $label): string
    {
        return in_array($label, self::TAX_LABELS, true) ? $label : 'Tax';
    }

    public static function normalizeRecipientType(?string $type): string
    {
        $type = strtolower(trim((string) $type));

        return $type === self::RECIPIENT_ASSOCIATES
            ? self::RECIPIENT_ASSOCIATES
            : self::RECIPIENT_CLIENTS;
    }

    public static function hasRecipientTypeColumn(): bool
    {
        static $hasColumn = null;

        if ($hasColumn === null) {
            $hasColumn = Schema::hasTable('invoice_settings')
                && Schema::hasColumn('invoice_settings', 'recipient_type');
        }

        return $hasColumn;
    }

    /**
     * Invoice defaults for a subscriber (or admin), scoped by recipient audience.
     */
    public static function forUser(int $userId, ?string $recipientType = self::RECIPIENT_CLIENTS): ?self
    {
        if (!self::hasRecipientTypeColumn()) {
            return self::where('user_id', $userId)->orderBy('id')->first();
        }

        $type = self::normalizeRecipientType($recipientType);

        $setting = self::where('user_id', $userId)
            ->where('recipient_type', $type)
            ->first();

        // Legacy rows created before recipient_type may lack the column value.
        if ($setting === null && $type === self::RECIPIENT_CLIENTS) {
            $setting = self::where('user_id', $userId)
                ->where(function ($query) {
                    $query->whereNull('recipient_type')
                        ->orWhere('recipient_type', '')
                        ->orWhere('recipient_type', self::RECIPIENT_CLIENTS);
                })
                ->orderBy('id')
                ->first();
        }

        return $setting;
    }

    public function resolvedTaxLabel(): string
    {
        return self::resolveTaxLabel($this->tax_label);
    }

    public function toSettingsArray(): array
    {
        return [
            'tax' => $this->tax ?? '',
            'tax_label' => self::resolveTaxLabel($this->tax_label ?? null),
            'discount' => $this->discount ?? '',
            'payment_link' => $this->payment_link ?? '',
            'payment_qr_code' => $this->payment_qr_code ?? '',
            'payment_qr_url' => !empty($this->payment_qr_code)
                ? asset('web_assets/users/user' . $this->user_id . '/' . $this->payment_qr_code)
                : '',
            'invoice_note' => $this->invoice_note ?? '',
        ];
    }
}
