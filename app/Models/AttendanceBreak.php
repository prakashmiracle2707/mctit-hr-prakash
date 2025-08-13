<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceBreak extends Model
{
    protected $fillable = ['attendance_id', 'break_start', 'break_end', 'break_start_date', 'break_end_date'];

    public function attendance()
    {
        return $this->belongsTo(AttendanceEmployee::class, 'attendance_id');
    }
}
