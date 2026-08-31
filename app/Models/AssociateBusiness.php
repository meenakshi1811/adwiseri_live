<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssociateBusiness extends Model
{
    use HasFactory;

    protected $table = "associate_businesses";
    protected $primaryKey = "id";

    protected $fillable = [
        'subscriber_id',
        'associate_id',
        'client_id',
        'client_name',
        'application_id',
        'application_name',
        'service_provided',
        'services',
        'other_service',
        'fees',
        'application_status',
        'home_country',
        'visa_country',
        'application_type',
    ];

    public function subscriber()
    {
        return $this->belongsTo(User::class, 'subscriber_id');
    }

    public function associate()
    {
        return $this->belongsTo(Associate::class, 'associate_id');
    }

    public function client()
    {
        return $this->belongsTo(Clients::class, 'client_id');
    }

    /**
     * Services label for list/detail views, including the custom Other service name.
     */
    public function formattedServices(): string
    {
        $servicesRaw = trim((string) ($this->services ?: $this->service_provided ?: ''));
        if ($servicesRaw === '') {
            return '-';
        }

        $parts = array_values(array_filter(array_map('trim', explode(',', $servicesRaw))));
        $otherName = trim((string) ($this->other_service ?? ''));

        $formatted = array_map(function (string $part) use ($otherName) {
            if ($part === 'Other' && $otherName !== '') {
                return 'Other (' . $otherName . ')';
            }

            return $part;
        }, $parts);

        return implode(', ', $formatted);
    }
}
