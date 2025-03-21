<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReimbursementPaidMail extends Mailable
{
    use Queueable, SerializesModels;

    public $reimbursement;

    public function __construct($reimbursement)
    {
        $this->reimbursement = $reimbursement;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $emails = ['dharit@miraclecloud-technology.com', 'hchavda@miraclecloud-technology.com'];

        $fromEmail = 'mctsource@miraclecloud-technology.com';
        $fromName = 'MCT SOURCE';


        return $this->from($fromEmail, ucfirst($fromName)) 
                    ->replyTo($fromEmail, ucfirst($fromName))
                    ->cc($emails)
                    ->subject('Reimbursement Payment Confirmation - ' . $this->reimbursement->title.' #R00'.$this->reimbursement->id)
                    ->view('email.reimbursement_paid')
                    ->with([
                        'reimbursement' => $this->reimbursement
                    ]);
    }
}
