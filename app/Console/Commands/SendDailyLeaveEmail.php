<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Leave as LocalLeave;
use App\Models\Holiday;
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

        // If today is weekend -> skip
        if ($today->isWeekend()) {
            \Log::info('Today is a weekend (Sat/Sun). Skipping email send. Date: ' . $today->toDateString());
            return Command::SUCCESS;
        }

        // If today is a mandatory holiday (is_optional = 0) -> skip
        if ($this->isMandatoryHoliday($today, $holiday)) {
            $occasion = $holiday->occasion ?? 'Unknown';
            \Log::info("Today is a mandatory holiday ({$occasion}). Skipping email send. Date: " . $today->toDateString());
            return Command::SUCCESS;
        }

        // Compute the next working day (skip weekends and mandatory holidays)
        $nextWorkingDay = $today->copy()->addDay();
        while ($nextWorkingDay->isWeekend() || $this->isMandatoryHoliday($nextWorkingDay, $holidayForNextDay)) {
            $note = $nextWorkingDay->isWeekend() ? 'weekend' : ('mandatory holiday: ' . ($holidayForNextDay->occasion ?? 'Unknown'));
            \Log::info('Skipping nextWorkingDay candidate: ' . $nextWorkingDay->toDateString() . ' (' . $note . ')');
            $nextWorkingDay->addDay();
        }

        \Log::info('Next working day determined as: ' . $nextWorkingDay->toDateString());

        // Get today's leaves
        $todayLeaves = LocalLeave::where('created_by', 1)
            ->where('status', '!=', 'Draft')
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->with(['employees', 'leaveType'])
            ->get();

        // Get next working day's leaves
        $nextDayLeaves = LocalLeave::where('created_by', 1)
            ->where('status', '!=', 'Draft')
            ->whereDate('start_date', '<=', $nextWorkingDay)
            ->whereDate('end_date', '>=', $nextWorkingDay)
            ->with(['employees', 'leaveType'])
            ->get();

        // Calculate total leave days if needed (ensure getTotalLeaveDays exists)
        foreach ([$todayLeaves, $nextDayLeaves] as $group) {
            foreach ($group as $leave) {
                if (empty($leave->total_leave_days) || $leave->total_leave_days == 0) {
                    // Assuming getTotalLeaveDays is a global helper or available in scope
                    $leave->total_leave_days = getTotalLeaveDays(
                        $leave->start_date,
                        $leave->end_date,
                        $leave->leave_type_id,
                        $leave->half_day_type
                    );
                }
            }
        }

        // Send the mail (adjust recipient as needed)
        $email = "rmb@miraclecloud-technology.com";
        Mail::to($email)->send(new DailyLeaveSummaryMail($todayLeaves, $nextDayLeaves, $nextWorkingDay));

        \Log::info("Email sent for leave summary (Today + Next Working Day: " . $nextWorkingDay->format('d M Y') . ")");
        $this->info("Daily leave summary email sent.");

        return Command::SUCCESS;
    }

    /**
     * Check if the given date is a mandatory holiday (is_optional = 0).
     * If $holidayOut is provided it will be populated with the Holiday model found.
     *
     * @param Carbon $date
     * @param Holiday|null &$holidayOut
     * @return bool
     */
    private function isMandatoryHoliday(Carbon $date, ?Holiday &$holidayOut = null): bool
    {
        $holiday = Holiday::where('is_optional', 0)
            ->whereDate('start_date', '<=', $date->toDateString())
            ->whereDate('end_date', '>=', $date->toDateString())
            ->first();

        if ($holiday) {
            $holidayOut = $holiday;
            return true;
        }

        $holidayOut = null;
        return false;
    }
}
