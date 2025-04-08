<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimeSheet extends Model
{
    protected $fillable = [
        'employee_id',
        'project_id', // Added project_id
        'date',
        'hours',
        'remark',
        'workhours',    
        'workminutes', 
        'created_by',
    ];

    /**
     * Relationship with User (Employee)
     */
    public function employee()
    {
        return $this->belongsTo('App\Models\User', 'employee_id', 'id');
    }

    /**
     * Relationship with Employee Model
     */
    public function employees()
    {
        return $this->belongsTo('App\Models\Employee', 'employee_id', 'id');
    }

    /**
     * Relationship with Project
     */
    public function project()
    {
        return $this->belongsTo('App\Models\Project', 'project_id', 'id');
    }

    /**
     * Relationship with Creator (Admin/Manager)
     */
    public function creator()
    {
        return $this->belongsTo('App\Models\User', 'created_by', 'id');
    }


    public function milestone()
    {
        return $this->belongsTo(\App\Models\Milestone::class, 'milestone_id');
    }
}

