<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketStatusHistory extends Model
{
    protected $fillable = [
        'ticket_id',
        'old_status_id',
        'new_status_id',
        'changed_by',
        'note',
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }


    // user who made the change
    public function changedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'changed_by');
    }

    public function oldStatus()
    {
        return $this->belongsTo(TicketStatus::class, 'old_status_id');
    }

    public function newStatus()
    {
        return $this->belongsTo(TicketStatus::class, 'new_status_id');
    }

    public function changer()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
