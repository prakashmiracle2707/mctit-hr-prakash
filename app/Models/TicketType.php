<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketType extends Model
{
    protected $fillable = [
        'name',
        'color',
        'description',
        'image',
        'logo_name',
    ];
}
