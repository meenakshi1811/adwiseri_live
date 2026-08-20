<?php

namespace App\Models;

use App\Services\OfferBenefitService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Referrals extends Model
{
    use HasFactory;
    protected $table = "referrals";
    protected $primaryKey = "id";
    protected $fillable = [
        'userid',
        'user_name',
        'total_amount',
        'amount_added',
        'referral_code',
        'previous_balance',
        'wallet_balance',
        'type',
        'offer_id',
        'applied_by',
        'applied_by_name',
        'debit_amount',
    ];
    public function user(){
        return $this->belongsTo(User::class,'userid');
    }
    public function user_name()
    {
        return $this->belongsTo(User::class,'referral_code','referral');
    }
    public function getUser()
    {
        return $this->hasOne(User::class,'id','userid');
    }
    public function getRefferedByUser(){
        return $this->hasOne(User::class,'referral','referral_code');
    }
    public function refferedBy(){
        return $this->belongsTo(User::class,'referral_code','referral_code');
    }

    public function getFormattedCreatedAtAttribute()
    {
        // Get the user's country code (you can modify how you fetch the country code)
        $countryCode = (auth()->user()->country == 'United States') ? 'US' : '';


        // Define date formats based on the country
        $dateFormat = match (strtoupper($countryCode)) {
            'US' => 'd-m-Y H:i:s',
            default => 'd-m-Y H:i:s',
        };

        // Format and return the `dob` field
        return $this->created_at ? Carbon::parse($this->created_at)->format($dateFormat) : null;
    }
    public function offer(){
        return $this->belongsTo(Offers::class,'offer_id');
    }

    public function scopeWalletTableVisible($query)
    {
        return $query->where(function ($inner) {
            $inner->whereIn('type', OfferBenefitService::walletTableReferralTypes())
                ->orWhere('type', 'like', 'Plan Upgrade %')
                ->orWhere('type', 'like', 'Plan Renewal %');
        });
    }
}
