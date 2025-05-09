<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketPriority extends Model
{
    protected $fillable = [
        'name',
        'color',
        'description',
    ];
}
