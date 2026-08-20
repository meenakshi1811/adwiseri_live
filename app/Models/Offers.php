<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Offers extends Model
{
    use HasFactory;

    protected $table ='offers';

    protected $fillable =[
        'user_id',
        'discount_type',
        'discount_value',
        'upgrade_from_plan',
        'upgrade_to_plan',
        'subscriber_type',
        'applicable_plans',
        'offer_start_date',
        'offer_end_date',
        'applied_by',
        'applied_by_name',
    ];

    protected $casts = [
        'applicable_plans' => 'array',
        'offer_start_date' => 'date:Y-m-d',
        'offer_end_date' => 'date:Y-m-d',
    ];

    public function user(){
        return $this->belongsTo(User::class,'user_id');
    }
}
