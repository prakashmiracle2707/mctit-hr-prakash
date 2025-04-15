<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReimbursementQueryRaisedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $reimbursement;

    public function __construct($reimbursement)
    {
        $this->reimbursement = $reimbursement;
    }

    public function build()
    {
        $emails = ['rmb@miraclecloud-technology.com', 'hchavda@miraclecloud-technology.com', 'nkalma@miraclecloud-technology.com'];
        $fromEmail='nkalma@miraclecloud-technology.com';
        $fromName='Nilesh Kalma';

        $mail = $this->from($fromEmail, ucfirst($fromName)) 
                    ->replyTo($fromEmail, ucfirst($fromName))
                    ->cc($emails)
                    ->subject('Query Raised: Action Required on Your Reimbursement Request - ' . $this->reimbursement->title .' #R00'.$this->reimbursement->id)
                    ->view('email.reimbursement.reimbursement_QueryRaised')
                    ->with([
                        'reimbursement' => $this->reimbursement
                    ]);

        
        if (!empty($this->reimbursement->file_path)) {
            
            $filePath = public_path('uploads/reimbursements/' . $this->reimbursement->file_path);

            if (file_exists($filePath)) {
                $mail->attach($filePath);
            }
        }

        return $mail;
    }
}

