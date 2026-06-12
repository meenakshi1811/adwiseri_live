<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToCurrentSubscriber;

class Used_referrals extends Model
{
    use HasFactory, BelongsToCurrentSubscriber;
    protected $table = "used_referrals";
    protected $primaryKey = "id";
    protected $fillable = [
        'subscriber_id',
        'referral_code',
    ];
}
