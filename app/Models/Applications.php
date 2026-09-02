<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Support\ApplicationStatuses;

class Applications extends Model
{
    use HasFactory;
    protected $table = "applications";
    protected $primaryKey = "id";
    protected $fillable = [
        'client_id',
        'client_name',
        'application_id',
        'application_name',
        'application_program',
        'course_name',
        'course_duration',
        'institution',
        'intake',
        'admission_number',
        'employer_name',
        'employment_role',
        'permit_duration',
        'sponsor_number',
        'application_country',
        'application_detail',
        'start_date',
        'end_date',
        'application_status',
        'subscriber_id',
        'visa_country',
        'document_checklist_sent_at',
        'document_checklist_sent_to',
    ];
    public function client(){
        return $this->belongsTo(Clients::class,'client_id');
    }
    public function subscriber(){
        return $this->belongsTo(User::class,'subscriber_id');
    }
    public function docs(){
        return $this->hasMany(Client_Docs::class,'application_id','application_id');
    }

    public function scopeActive($query)
    {
        return $query->whereNotIn('application_status', ApplicationStatuses::INACTIVE);
    }


    public function getFormattedStartDateAttribute()
    {
        // Get the user's country code (you can modify how you fetch the country code)
        $countryCode = (auth()->user()->country == 'United States') ? 'US' : '';


        // Define date formats based on the country
        $dateFormat = match (strtoupper($countryCode)) {
            'US' => 'd-m-Y', // MM/DD/YYYY for US
            default => 'd-m-Y', // DD-MM-YYYY for other countries
        };

        // Format and return the `dob` field
        $date = $this->normalizeDateForDisplay($this->start_date);
        return $date ? $date->format($dateFormat) : null;
    }
    public function getFormattedEndDateAttribute()
    {
        // Get the user's country code (you can modify how you fetch the country code)
        $countryCode = (auth()->user()->country == 'United States') ? 'US' : '';


        // Define date formats based on the country
        $dateFormat = match (strtoupper($countryCode)) {
            'US' => 'd-m-Y', // MM/DD/YYYY for US
            default => 'd-m-Y', // DD-MM-YYYY for other countries
        };

        // Format and return the `dob` field
        $date = $this->normalizeDateForDisplay($this->end_date);
        return $date ? $date->format($dateFormat) : null;
    }

    public function getStartDateInputAttribute()
    {
        $date = $this->normalizeDateForDisplay($this->start_date);
        return $date ? $date->format('Y-m-d') : null;
    }

    public function getEndDateInputAttribute()
    {
        $date = $this->normalizeDateForDisplay($this->end_date);
        return $date ? $date->format('Y-m-d') : null;
    }

    private function normalizeDateForDisplay($value)
    {
        if (!$value) {
            return null;
        }
        $value = trim((string) $value);
        foreach (['Y-m-d', 'd-m-Y', 'm-d-Y'] as $format) {
            try {
                return Carbon::createFromFormat($format, $value);
            } catch (\Exception $e) {
            }
        }
        try {
            return Carbon::parse($value);
        } catch (\Exception $e) {
            return null;
        }
    }
}
