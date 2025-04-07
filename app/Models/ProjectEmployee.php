<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectEmployee extends Model
{
    use HasFactory;

    protected $table = 'project_employee';
    public $timestamps = true; 

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
