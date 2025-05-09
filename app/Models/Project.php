<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\TimeSheet;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;


class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'created_by',
        'project_manager_ids',
        'client_ids',
    ];

    protected $casts = [
        'project_manager_ids' => 'array', // Cast as array
        'client_ids' => 'array',
    ];

    /**
     * Relationship with User (Creator of the Project)
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relationship with Timesheets (A project can have multiple timesheets)
     */
    public function timesheets()
    {
        return $this->hasMany(TimeSheet::class, 'project_id', 'id');
    }

    public function employees()
    {
        return $this->belongsToMany(Employee::class, 'project_employee', 'project_id', 'employee_id');
    }

    /**
     * Project Managers relationship - returns Employee models based on stored IDs
     */
    public function projectManagers()
    {
        return Employee::whereIn('id', $this->project_manager_ids ?? [])->get();
    }
}
