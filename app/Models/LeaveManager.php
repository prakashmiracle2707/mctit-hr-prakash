<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveManager extends Model
{
    protected $fillable = [
        'leave_id',
        'manager_id',
        'status',
        'remark',
        'action_date',
    ];

    public function leave()
    {
        return $this->belongsTo(Leave::class);
    }

    public function manager()
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }
}
