<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Internal_Invoices extends Model
{
    use HasFactory;

    public const ADWISERI_VENDOR_ID = '1';
    public const ADWISERI_VENDOR_NAME = 'adwiseri.com';

    protected $table = "internal_invoices";
    protected $primaryKey = "id";
    protected $fillable = [
        'invoice_no',
        'name',
        'email',
        'phone',
        'country',
        'state',
        'city',
        'pincode',
        'to_name',
        'to_email',
        'to_phone',
        'to_country',
        'to_state',
        'to_city',
        'to_pincode',
        'status',
        'amount',
        'discount',
        'tax',
        'tax_label',
        'export_service_tax_exempt',
        'payment_link',
        'payment_qr_code',
        'invoice_note',
        'total',
        'due_date',
        'type',
        'uploaded_invoice',
        'vendor_id',
        'subscriber_id',
        'user_id',
        'address',
        'logo',
        'detail',
        'token',
        'to_address',
        'created_by',
        'created_by_name',
        'updated_by',
        'updated_by_name',
    ];
    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class, 'invoice_id')->orderBy('sort_order');
    }

    public function lineItems(): Collection
    {
        return app(\App\Services\InvoiceItemService::class)->displayItems($this);
    }

    public function getFormattedDueDateAttribute()
    {
        // Get the user's country code (you can modify how you fetch the country code)
        $countryCode = (auth()->user()->country == 'United States') ? 'US' : '';


        // Define date formats based on the country
        $dateFormat = match (strtoupper($countryCode)) {
            'US' => 'd-m-Y', // MM/DD/YYYY for US
            default => 'd-m-Y', // DD-MM-YYYY for other countries
        };

        // Format and return the `dob` field
        return $this->due_date ? Carbon::parse($this->due_date)->format($dateFormat) : null;
    }

    public function isExportServiceTaxExempt(): bool
    {
        return (bool) ($this->export_service_tax_exempt ?? false);
    }

    public function displaysTaxLine(): bool
    {
        if ($this->isSubscriptionPackageInvoice()) {
            return false;
        }

        if ($this->isExportServiceTaxExempt()) {
            return false;
        }

        return (float) ($this->tax ?? 0) > 0;
    }

    /**
     * Packaged yearly subscription fees (purchase / upgrade / renewal).
     * These use fixed plan prices and must never pick up Invoice Settings tax/discount.
     */
    public static function isSubscriptionPackageDetail(?string $detail): bool
    {
        $text = strtolower(trim((string) $detail));
        if ($text === '') {
            return false;
        }

        if (str_starts_with($text, 'subscription fees')) {
            return true;
        }

        // Common free-text variants admins may enter on manual invoices.
        return (bool) preg_match(
            '/\b(subscription|membership)\b.*\b(fee|fees|plan|renewal|upgrade|purchase)\b|\b(plan|membership)\b.*\b(renewal|upgrade|purchase)\b/',
            $text
        );
    }

    /**
     * @param  array<int, array{detail?: mixed}>|Collection  $items
     */
    public static function itemsLookLikeSubscriptionPackage($items): bool
    {
        foreach ($items as $item) {
            $detail = is_array($item)
                ? ($item['detail'] ?? '')
                : (is_object($item) ? ($item->detail ?? '') : '');

            if (self::isSubscriptionPackageDetail((string) $detail)) {
                return true;
            }
        }

        return false;
    }

    public function isSubscriptionPackageInvoice(): bool
    {
        if (self::isSubscriptionPackageDetail($this->detail ?? null)) {
            return true;
        }

        $lineItems = $this->relationLoaded('items')
            ? $this->items
            : $this->items()->get(['detail']);

        return self::itemsLookLikeSubscriptionPackage($lineItems);
    }

    public function isSubscriptionApInvoice(): bool
    {
        return strtolower((string) $this->type) === 'ap'
            && self::isSubscriptionPackageDetail($this->detail ?? null);
    }

    public function apVendorName(): string
    {
        if ($this->isSubscriptionApInvoice()) {
            return self::ADWISERI_VENDOR_NAME;
        }

        return trim((string) ($this->to_name ?? ''));
    }

    public function apVendorId(): string
    {
        if ($this->isSubscriptionApInvoice()) {
            return self::ADWISERI_VENDOR_ID;
        }

        return trim((string) ($this->vendor_id ?? ''));
    }

    public function apVendorDisplay(): string
    {
        return self::formatVendorDisplay($this->apVendorName(), $this->apVendorId());
    }

    public static function formatVendorDisplay(?string $vendorName, ?string $vendorId): string
    {
        $name = trim((string) $vendorName);
        if ($name === '') {
            return '';
        }

        $id = trim((string) $vendorId);

        return $id !== '' ? $name . ' (' . $id . ')' : $name;
    }

    public static function resolveVendorIdForPayment(?string $invoiceNo, ?int $subscriberId, ?string $serviceProvider = null): string
    {
        if (!empty($invoiceNo) && !empty($subscriberId)) {
            $vendorId = static::query()
                ->where('invoice_no', $invoiceNo)
                ->where('subscriber_id', $subscriberId)
                ->value('vendor_id');

            if (!empty($vendorId)) {
                return (string) $vendorId;
            }
        }

        if (strtolower(trim((string) $serviceProvider)) === self::ADWISERI_VENDOR_NAME) {
            return self::ADWISERI_VENDOR_ID;
        }

        return self::ADWISERI_VENDOR_ID;
    }
}
