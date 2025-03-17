<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceBreak extends Model
{
    protected $fillable = ['attendance_id', 'break_start', 'break_end'];

    public function attendance()
    {
        return $this->belongsTo(AttendanceEmployee::class, 'attendance_id');
    }
}
