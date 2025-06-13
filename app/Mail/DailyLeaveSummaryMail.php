<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DailyLeaveSummaryMail extends Mailable
{
    use Queueable, SerializesModels;

    public $todayLeaves;
    public $nextDayLeaves;
    public $nextWorkingDay;

    public function __construct($todayLeaves, $nextDayLeaves, $nextWorkingDay)
    {
        $this->todayLeaves = $todayLeaves;
        $this->nextDayLeaves = $nextDayLeaves;
        $this->nextWorkingDay = $nextWorkingDay;
    }

    public function build()
    {
        $emails = ['mctsource@miraclecloud-technology.com'];
        $fromEmail = 'hr@miraclecloud-technology.com';
        $fromName = 'MCT IT SOLUTIONS PVT LTD';

        $todayFormatted = now()->format('d M Y');
        $nextDayFormatted = $this->nextWorkingDay->format('d M Y');
        $nextDayLabel = $this->nextWorkingDay->englishDayOfWeek; // e.g., Monday, Tuesday

        $subject = "Leave Summary | Today - $todayFormatted & $nextDayLabel - $nextDayFormatted";

        return $this->from($fromEmail, ucfirst($fromName))
                    ->replyTo($fromEmail, ucfirst($fromName))
                    ->cc($emails)
                    ->subject($subject)
                    ->view('email.daily_leave_summary')
                    ->with([
                        'todayLeaves' => $this->todayLeaves,
                        'nextDayLeaves' => $this->nextDayLeaves,
                        'nextWorkingDay' => $this->nextWorkingDay,
                    ]);
    }
}
