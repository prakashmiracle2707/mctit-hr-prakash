<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reimbursement extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'title',        // Added Title
        'amount',
        'description',
        'remark',       // Added Remark
        'status',
        'file_path',
        'assign_to',
        'approved_at',
        'expense_date',
        'payment_type',
        'paid_receipt',
        'follow_up_email',
        'accountant_comment',
        'self_receipt',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assign_to', 'id');
    }

    public function payer()
    {
        return $this->belongsTo(User::class, 'paid_by');
    }
}
