<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Associate extends Model
{
    use HasFactory;

    protected $table = "associates";
    protected $primaryKey = "id";

    protected $fillable = [
        'added_by',
        'associate_code',
        'name',
        'email',
        'phone',
        'organization',
        'country',
        'state',
        'city',
        'pincode',
        'home_country',
        'visa_country',
        'application_type',
        'currency',
        'status',
    ];

    public function subscriber()
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function businesses()
    {
        return $this->hasMany(AssociateBusiness::class, 'associate_id');
    }

    public function invoices()
    {
        return $this->hasMany(AssociateInvoice::class, 'associate_id');
    }

    public function payments()
    {
        return $this->hasMany(AssociatePayment::class, 'associate_id');
    }
}
