<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'application_country',
        'application_detail',
        'start_date',
        'end_date',
        'application_status',
        'subscriber_id',
        'visa_country'
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

    public function assignments(){
        return $this->hasMany(Application_assignments::class,'application_id','application_id');
    }

    public function scopeVisibleToUser($query, $user)
    {
        if (!$user) {
            return $query->whereRaw('1 = 0');
        }

        if (in_array($user->user_type, ['admin', 'Subscriber'])) {
            return $query;
        }

        return $query->where(function ($visibilityQuery) use ($user) {
            $visibilityQuery->where('assign_to', $user->id)
                ->orWhere(function ($unassignedQuery) {
                    $unassignedQuery->where(function ($assignToQuery) {
                        $assignToQuery->whereNull('assign_to')
                            ->orWhere('assign_to', '');
                    })->whereDoesntHave('assignments');
                })
                ->orWhereHas('assignments', function ($assignmentQuery) use ($user) {
                    $assignmentQuery->where('user_id', $user->id);
                });
        });
    }

    public function isVisibleToUser($user)
    {
        if (!$user) {
            return false;
        }

        if (in_array($user->user_type, ['admin', 'Subscriber'])) {
            return true;
        }

        if ((string) $this->assign_to === (string) $user->id) {
            return true;
        }

        $assignments = $this->relationLoaded('assignments') ? $this->assignments : $this->assignments()->get();

        if ($assignments->contains('user_id', $user->id)) {
            return true;
        }

        $hasNoAssignee = $this->assign_to === null || $this->assign_to === '';

        return $hasNoAssignee && $assignments->isEmpty();
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
