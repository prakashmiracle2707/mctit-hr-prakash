<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Complaints extends Model
{
    // Explicitly define table name
    protected $table = 'complaints_office';

    // Fillable fields
    protected $fillable = [
        'employee_id',
        'issue_category_id',
        'issue_title_id',
        'subject',
        'description',
        'priority',
        'status'
    ];

    // Relationships
    public function employee()
    {
        return $this->belongsTo(\App\Models\User::class, 'employee_id');
    }

    public function category()
    {
        return $this->belongsTo(IssueCategory::class, 'issue_category_id');
    }

    public function title()
    {
        return $this->belongsTo(IssueTitle::class, 'issue_title_id');
    }
}

