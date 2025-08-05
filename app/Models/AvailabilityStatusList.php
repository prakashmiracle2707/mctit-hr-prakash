<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AvailabilityStatusList extends Model
{
    protected $table = 'availability_status_list';

    protected $fillable = ['name'];

    public function employees()
    {
        return $this->hasMany(Employee::class, 'availability_status');
    }
}

