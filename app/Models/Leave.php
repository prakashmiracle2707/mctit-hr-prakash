<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Leave extends Model
{
    protected $fillable = [
        'employee_id',
        'leave_type_id',
        'applied_on',
        'start_date',
        'end_date',
        'total_leave_days',
        'leave_reason',
        'remark',
        'remark_cancelled',
        'status',
        'created_by',
        'half_day_type',
        'cc_email',
        'early_time',
    ];

    protected $casts = [
        'cc_email' => 'array', // Cast cc_email_id to an array
    ];

    public function leaveType()
    {
        return $this->hasOne('App\Models\LeaveType', 'id', 'leave_type_id');
    }

    public function employees()
    {
        return $this->hasOne('App\Models\Employee', 'id', 'employee_id');
    }

    public function managerUsers()
    {
        return $this->belongsToMany(Employee::class, 'users', 'id', 'id')
            ->whereIn('id', $this->managers ?? []);
    }

    public function managers()
    {
        return $this->hasMany(LeaveManager::class);
    }

    // In LocalLeave model
    public function leaveManagers()
    {
        return $this->hasMany(\App\Models\LeaveManager::class, 'leave_id');
    }
}
