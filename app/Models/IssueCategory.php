<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IssueCategory extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function titles()
    {
        return $this->hasMany(IssueTitle::class);
    }
}
