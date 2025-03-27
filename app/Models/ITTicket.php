<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ITTicket extends Model
{
    protected $table = 'it_tickets';
    
    protected $fillable = [
        'employee_id', 'issue_category_id', 'issue_title_id', 'description', 'priority', 'status','remark'
    ];

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
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
