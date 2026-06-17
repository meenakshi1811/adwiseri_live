<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicationStatusTrack extends Model
{
    use HasFactory;

    protected $table = 'application_status_tracks';

    protected $fillable = [
        'application_id',
        'status',
        'updated_by',
        'updated_by_name',
        'changed_at',
    ];
}

