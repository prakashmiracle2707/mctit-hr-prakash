<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReimbursementReminderApprovalMail extends Mailable
{
    use Queueable, SerializesModels;

    public $reimbursement;

    public function __construct($reimbursement)
    {
        $this->reimbursement = $reimbursement;
    }

    public function build()
    {

        $emails = ['nkalma@miraclecloud-technology.com', 'hchavda@miraclecloud-technology.com'];
        
        $emails[] = $this->reimbursement->employee->email;
        //$emails = ['prakashn@miraclecloud-technology.com'];

        $fromEmail = $this->reimbursement->employee->email;
        $fromName = $this->reimbursement->employee->name;

        // $fromEmail = 'mctsource@miraclecloud-technology.com';
        // $fromName = 'MCTSOURCE';


        return $this->from($fromEmail, ucfirst($fromName)) 
                    ->replyTo($fromEmail, ucfirst($fromName))
                    ->cc($emails)
                    ->subject('Follow-Up: Approval Pending for Reimbursement Request - ' . $this->reimbursement->title.' #R00'.$this->reimbursement->id)
                    ->view('email.reimbursement.reimbursement_Reminder_Approval')
                    ->with([
                        'reimbursement' => $this->reimbursement
                    ]);
    }
}

