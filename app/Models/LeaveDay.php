<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveDay extends Model
{
    protected $fillable = [
        'leave_id',
        'date',
        'leave_units',
        'leave_type_id',
        'half_day_type',
        'status',
    ];

    public function leave()
    {
        return $this->belongsTo(Leave::class);
    }
}
