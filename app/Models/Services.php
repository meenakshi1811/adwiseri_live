<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Services extends Model
{
    use HasFactory;

    public const COUNTRY_NA = 'NA';

    protected $table ='services';

    protected $fillable =[
        'subscriber_id',
        'country',
        'user_id',
        'service_name',
        'fees',
        'status'
    ];

    public function subscriber(){
        return $this->belongsTo(User::class,'subscriber_id');
    }

    public function user(){
        return $this->belongsTo(User::class,'user_id');
    }

    // A service can have multiple invoices
    public function invoices()
    {
        return $this->hasMany(Invoices::class, 'user_id', 'user_id');
    }

    public static function normalizeCountry(?string $country): string
    {
        $country = trim((string) $country);

        return $country === '' ? self::COUNTRY_NA : $country;
    }

    public static function normalizeName(?string $serviceName): string
    {
        return trim((string) $serviceName);
    }

    public static function normalizeKey(?string $value): string
    {
        return strtolower(preg_replace('/\s+/', ' ', trim((string) $value)));
    }

    public function displayCountry(): string
    {
        return self::normalizeCountry($this->country);
    }

    public static function duplicateExists(int $subscriberId, string $country, string $serviceName, ?int $ignoreId = null): bool
    {
        $countryKey = self::normalizeKey(self::normalizeCountry($country));
        $nameKey = self::normalizeKey($serviceName);

        if ($nameKey === '') {
            return false;
        }

        $query = self::query()->where('subscriber_id', $subscriberId);

        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        return $query->get(['country', 'service_name'])->contains(function ($service) use ($countryKey, $nameKey) {
            return self::normalizeKey(self::normalizeCountry($service->country)) === $countryKey
                && self::normalizeKey($service->service_name) === $nameKey;
        });
    }
}
