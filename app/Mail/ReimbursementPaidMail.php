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
        // $emails = ['rmb@miraclecloud-technology.com', 'hchavda@miraclecloud-technology.com'];
        // $emails = ['nishadprakash2699@gmail.com'];
        
        if ($this->reimbursement->payment_type == 'UPI' && $this->reimbursement->paid_by == 35) {
            $emails = ['rmb@miraclecloud-technology.com', 'hchavda@miraclecloud-technology.com','nkalma@miraclecloud-technology.com'];
            $fromName = 'MCT IT SOLUTIONS PVT. LTD';
            $fromEmail = 'hr@miraclecloud-technology.com';
        }else{
            $emails = ['rmb@miraclecloud-technology.com', 'hchavda@miraclecloud-technology.com'];
            $fromName = 'Nilesh Kalma';
            $fromEmail = 'nkalma@miraclecloud-technology.com';
        }
        

        $email = $this->from($fromEmail, ucfirst($fromName))
                      ->replyTo($fromEmail, ucfirst($fromName))
                      ->cc($emails)
                      ->subject('Reimbursement Payment Confirmation - ' . $this->reimbursement->title . ' #R00' . $this->reimbursement->id)
                      ->view('email.reimbursement.reimbursement_paid')
                      ->with([
                          'reimbursement' => $this->reimbursement
                      ]);

        // ✅ Attach file if it exists
        if (!empty($this->reimbursement->paid_receipt)) {
            $filePath = public_path('uploads/reimbursements/' . $this->reimbursement->paid_receipt);

            if (file_exists($filePath)) {
                $email->attach($filePath, [
                    'as' => 'Paid_Receipt_' . $this->reimbursement->id . '.' . pathinfo($filePath, PATHINFO_EXTENSION),
                    'mime' => mime_content_type($filePath),
                ]);
            }
        }

        return $email;
    }
}
