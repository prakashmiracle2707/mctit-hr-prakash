<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SecondaryRole extends Model
{
    protected $fillable = ['name'];

    public function assignments()
    {
        return $this->hasMany(SecondaryRoleAssign::class);
    }
}
