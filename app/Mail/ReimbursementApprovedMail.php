<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReimbursementApprovedMail extends Mailable
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

        $fromEmail = 'rmb@miraclecloud-technology.com';
        $fromName = 'Ravi Brahmbhatt';

        // $fromEmail = 'mctsource@miraclecloud-technology.com';
        // $fromName = 'MCTSOURCE';


        return $this->from($fromEmail, ucfirst($fromName)) 
                    ->replyTo($fromEmail, ucfirst($fromName))
                    ->cc($emails)
                    ->subject('Reimbursement Approved - ' . $this->reimbursement->title.' #R00'.$this->reimbursement->id)
                    ->view('email.reimbursement.reimbursement_approved')
                    ->with([
                        'reimbursement' => $this->reimbursement
                    ]);
    }
}

