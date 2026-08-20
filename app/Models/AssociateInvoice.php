<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssociateInvoice extends Model
{
    use HasFactory;

    protected $table = "associate_invoices";
    protected $primaryKey = "id";

    protected $fillable = [
        'subscriber_id',
        'invoice_no',
        'associate_id',
        'client_id',
        'client_name',
        'application_id',
        'application_name',
        'service_provided',
        'services',
        'fees',
        'status',
        'due_date',
        'paid',
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

    public function payments()
    {
        return $this->hasMany(AssociatePayment::class, 'associate_invoice_id');
    }

    /**
     * Outstanding balance = billed fees minus amount paid so far.
     */
    public function getOutstandingAttribute()
    {
        return max(0, (float) $this->fees - (float) $this->paid);
    }
}
