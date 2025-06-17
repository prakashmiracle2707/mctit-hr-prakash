<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Leave as LocalLeave;
use Illuminate\Support\Facades\Mail;
use App\Mail\DailyLeaveSummaryMail;
use Carbon\Carbon;

class SendDailyLeaveEmail extends Command
{
    protected $signature = 'email:daily-leave-report';
    protected $description = 'Send daily leave report/reminder email every morning at 9 AM';

    public function handle()
    {
        \Log::info('DailyLeaveEmail command triggered at ' . now());

        $today = Carbon::today();

        // Skip if today is Saturday or Sunday
        if ($today->isWeekend()) {
            \Log::info('Today is a weekend (Sat/Sun). Skipping email send.');
            return Command::SUCCESS;
        }

        // ✅ Determine next working day (skip Sat/Sun)
        $nextWorkingDay = $today->copy()->addDay();
        while ($nextWorkingDay->isWeekend()) {
            $nextWorkingDay->addDay();
        }

        // ✅ Get today's leaves
        $todayLeaves = LocalLeave::where('created_by', 1)
            ->where('status', '!=', 'Draft')
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->with(['employees', 'leaveType'])
            ->get();

        // ✅ Get next working day’s leaves
        $nextDayLeaves = LocalLeave::where('created_by', 1)
            ->where('status', '!=', 'Draft')
            ->whereDate('start_date', '<=', $nextWorkingDay)
            ->whereDate('end_date', '>=', $nextWorkingDay)
            ->with(['employees', 'leaveType'])
            ->get();

        // ✅ Calculate total leave days if needed
        foreach ([$todayLeaves, $nextDayLeaves] as $group) {
            foreach ($group as $leave) {
                if ($leave->total_leave_days == 0) {
                    $leave->total_leave_days = $this->getTotalLeaveDays(
                        $leave->start_date,
                        $leave->end_date,
                        $leave->leave_type_id,
                        $leave->half_day_type
                    );
                }
            }
        }

        // ✅ Send the mail
        $email = "rmb@miraclecloud-technology.com";
        Mail::to($email)->send(new DailyLeaveSummaryMail($todayLeaves, $nextDayLeaves, $nextWorkingDay));

        \Log::info("Email sent for leave summary (Today + Next Working Day: " . $nextWorkingDay->format('d M Y') . ")");
        $this->info("Daily leave summary email sent.");
        return Command::SUCCESS;
    }
}
