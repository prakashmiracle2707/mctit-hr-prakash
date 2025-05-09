<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $fillable = [
        'project_id',
        'ticket_type_id',
        'title',
        'employee_id',
        'priority',
        'start_date',
        'end_date',
        'description',
        'ticket_code',
        'created_by',
        'ticket_created',
        'status',
    ];

    public function ticketUnread()
    {
        if(\Auth::user()->type == 'employee')
        {

            return TicketReply:: where('ticket_id', $this->id)->where('is_read', 0)->where('created_by', '!=', \Auth::user()->id)->count('id');
        }
        else
        {
            return TicketReply:: where('ticket_id', $this->id)->where('is_read', 0)->where('created_by', '!=', \Auth::user()->creatorId())->count('id');

        }
    }

    public function createdBy()
    {
        return $this->hasOne('App\Models\user', 'id', 'ticket_created');
    }

    public function getUsers()
    {
        return $this->hasOne('App\Models\user', 'id', 'employee_id');
    }

    public function project()
    {
        return $this->belongsTo(\App\Models\Project::class);
    }

    public function parent()
    {
        return $this->belongsTo(Ticket::class, 'parent_id');
    }

    public function subtasks()
    {
        return $this->hasMany(Ticket::class, 'parent_id');
    }

    public function type()
    {
        return $this->belongsTo(\App\Models\TicketType::class, 'ticket_type_id');
    }

    public function getpriority()
    {
        return $this->belongsTo(\App\Models\TicketPriority::class, 'priority'); 
    }

    public function getstatus()
    {
        return $this->belongsTo(\App\Models\TicketStatus::class, 'status'); 
    }

}
