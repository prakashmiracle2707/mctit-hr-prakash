<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IssueTitle extends Model
{
    use HasFactory;

    protected $fillable = ['issue_category_id', 'name'];

    public function category()
    {
        return $this->belongsTo(IssueCategory::class, 'issue_category_id');
    }
}
