<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceEmployee extends Model
{
    protected $fillable = [
        'employee_id',
        'date',
        'status',
        'clock_in',
        'clock_out',
        'checkout_date',
        'late',
        'early_leaving',
        'overtime',
        'total_rest',
        'created_by',
        'checkout_time_diff',
        'work_from_home',
    ];

    public function employees()
    {
        return $this->hasOne('App\Models\Employee', 'user_id', 'employee_id');
    }

    public function employee()
    {
        return $this->hasOne('App\Models\Employee', 'id', 'employee_id');
    }

    public function breaks()
    {
        return $this->hasMany(AttendanceBreak::class, 'attendance_id');
    }
}
