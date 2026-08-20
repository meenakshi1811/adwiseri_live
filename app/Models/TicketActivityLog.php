<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketActivityLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'ticket_activity_logs';

    protected $fillable = [
        'ticket_id',
        'actor_user_id',
        'actor_name',
        'action',
        'detail',
        'meta',
        'created_at',
    ];

    protected $casts = [
        'meta' => 'array',
        'created_at' => 'datetime',
    ];

    public function ticket()
    {
        return $this->belongsTo(Tickets::class, 'ticket_id');
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }
}
