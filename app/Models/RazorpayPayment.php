<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RazorpayPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'dr_id',
        'appointment_id',
        'razorpay_payment_id',
        'razorpay_order_id',
        'razorpay_signature',
        'amount',
        'currency',
        'status',
        'name',
        'email',
        'contact',
        'notes',
        'paid_at',
    ];

    protected $casts = [
        'notes' => 'array',
        'paid_at' => 'datetime',
    ];
}
