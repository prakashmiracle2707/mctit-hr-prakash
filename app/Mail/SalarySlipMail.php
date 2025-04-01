<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SalarySlipMail extends Mailable
{
    use Queueable, SerializesModels;

    public $employee;
    public $month;
    public $year;
    public $filePath;

    public function __construct($employee, $month, $year, $filePath)
    {
        $this->employee = $employee;
        $this->month = $month;
        $this->year = $year;
        $this->filePath = $filePath;
    }

    public function build()
    {
        // Extract first name from full name
        $employeeName = ucfirst(strtok($this->employee->name, ' ')); // "Sunny .H. Macwan" -> "Sunny"

        // Format month and year
        $month = strtoupper(substr($this->month, 0, 3)); // "January" -> "JAN"
        $year = substr($this->year, -2); // "2025" -> "25"

        // Construct new file name pattern: "Sunny_JAN_25.pdf"
        $extension = pathinfo($this->filePath, PATHINFO_EXTENSION);
        $newFileName = "{$employeeName}_{$month}_{$year}.{$extension}";

        // Define the correct file path (assuming files are stored in public/uploads/salary-slips/)
        $filePath = public_path("uploads/salary-slips/{$this->filePath}");

        // Ensure file exists before attaching to avoid errors
        if (!file_exists($filePath)) {
            \Log::error("Salary Slip file not found: " . $filePath);
            return $this->from('hr@miraclecloud-technology.com', 'HR Team')
                        ->replyTo('hr@miraclecloud-technology.com', 'HR Team')
                        ->cc(['nkalma@miraclecloud-technology.com','rmb@miraclecloud-technology.com'])
                        ->subject("Salary Slip - {$this->month} {$this->year}")
                        ->view('email.salary_slip');
        }

        return $this->from('hr@miraclecloud-technology.com', 'HR Team')
                    ->replyTo('hr@miraclecloud-technology.com', 'HR Team')
                    ->cc(['nkalma@miraclecloud-technology.com','rmb@miraclecloud-technology.com']) // Add multiple CCs as needed
                    ->subject("Salary Slip - {$this->month} {$this->year}")
                    ->view('email.salary_slip')
                    ->attach($filePath, [
                        'as' => $newFileName, // Rename the attachment
                        'mime' => mime_content_type($filePath),
                    ]);
    }

}

