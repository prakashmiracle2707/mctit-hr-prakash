<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SecondaryRoleAssign extends Model
{
    protected $table = 'secondary_role_assign';

    protected $fillable = ['user_id', 'secondary_role_id'];

    public function role()
    {
        return $this->belongsTo(SecondaryRole::class, 'secondary_role_id');
    }

    public function employee()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
