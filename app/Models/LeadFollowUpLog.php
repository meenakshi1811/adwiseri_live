<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeadFollowUpLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'enquiry_id',
        'subscriber_id',
        'user_id',
        'client_id',
        'client_name',
        'description',
        'logged_at',
    ];

    protected $casts = [
        'logged_at' => 'datetime',
    ];

    public function enquiry()
    {
        return $this->belongsTo(VisaEnquiry::class, 'enquiry_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function client()
    {
        return $this->belongsTo(Clients::class, 'client_id');
    }
}
