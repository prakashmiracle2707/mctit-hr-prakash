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
        'approved_by',
        'approved_type',
        'approved_at',
    ];

    protected $casts = [
        'cc_email' => 'array',
        'approved_at' => 'datetime', // Ensures carbon instance
    ];

    public function leaveType()
    {
        return $this->hasOne('App\Models\LeaveType', 'id', 'leave_type_id');
    }

    public function employees()
    {
        return $this->hasOne('App\Models\Employee', 'id', 'employee_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'approved_by');
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

    public function leaveManagers()
    {
        return $this->hasMany(\App\Models\LeaveManager::class, 'leave_id');
    }

    public function leaveDays()
    {
        return $this->hasMany(\App\Models\LeaveDay::class, 'leave_id');
    }
}
