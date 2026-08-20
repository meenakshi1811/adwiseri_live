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
}
