<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinancialYear extends Model
{
    protected $fillable = ['year_range', 'start_date', 'end_date', 'is_active'];
}
