<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReimbursementResubmittingtMail extends Mailable
{
    use Queueable, SerializesModels;

    public $reimbursement;

    public function __construct($reimbursement)
    {
        $this->reimbursement = $reimbursement;
    }

    public function build()
    {
        $emails = [$this->reimbursement->employee->email,'hchavda@miraclecloud-technology.com', 'rmb@miraclecloud-technology.com'];

        $mail = $this->from($this->reimbursement->employee->email, ucfirst($this->reimbursement->employee->name)) 
                    ->replyTo($this->reimbursement->employee->email, ucfirst($this->reimbursement->employee->name))
                    ->cc($emails)
                    ->subject('Reimbursement Request – Query Response Included - ' . $this->reimbursement->title .' #R00'.$this->reimbursement->id)
                    ->view('email.reimbursement.reimbursement_resubmitting')
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

