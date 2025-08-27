<?php

use Jenssegers\Agent\Agent;
use Carbon\Carbon;
use App\Models\AttendanceEmployee;
use App\Models\LeaveType;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;


if (!function_exists('format_currency')) {
    function format_currency($amount, $currency = 'INR')
    {
        return number_format((float)$amount, 2) . ' ' . $currency;
    }
}

if (!function_exists('generate_uuid')) {
    function generate_uuid()
    {
        return (string) \Illuminate\Support\Str::uuid();
    }
}

if (!function_exists('Get_LeaveId')) {
    function Get_LeaveId($LeaveId)
    {
        return '#L00' . $LeaveId;
    }
}

if (!function_exists('Get_Device_Type')) {
    function Get_Device_Type()
    {
        $agent = new Agent();
        $device = $agent->device();

        if (in_array(strtolower($device), ['iphone','ipad','android','mobile'])) {
            return 'mobile';
        }
        return 'desktop';
    }
}

if (!function_exists('Get_Device_Type_Icon')) {
    function Get_Device_Type_Icon($TypeIcon, $UserId)
    {
        if ($TypeIcon === 'mobile' && ($UserId == 3 || $UserId == 1)) {
            //return "<i class='ti ti-device-mobile' style='color:red;' title='Mobile'></i>";
            return false;
        }
        return false;
    }
}

if (!function_exists('GetStatusName')) {
    function GetStatusName($Status, $approved_type = null)
    {
        switch ($Status) {
            case 'Pending':
                return 'Pending';
            case 'In_Process':
                return 'In-Process';
            case 'Manager_Approved':
                return 'Awaiting Director Approval';
            case 'Manager_Rejected':
                return 'Manager-Rejected';
            case 'Partially_Approved':
                return 'Partially-Approved';
            case 'Approved':
                return ($approved_type === 'auto') ? 'System – Auto Approved' : 'Approved';
            case 'Reject':
            case 'Draft':
            case 'Cancelled':
            case 'Pre-Approved':
                return $Status;
            default:
                return (string)$Status;
        }
    }
}

if (!function_exists('Get_MaxCheckOutTime')) {
    function Get_MaxCheckOutTime()
    {
        return 6;
    }
}

/* ---------------------- Attendance Utilities ---------------------- */

if (!function_exists('seconds_to_hms')) {
    /**
     * Format seconds to HH:MM:SS
     */
    function seconds_to_hms(int $seconds): string
    {
        $h = intdiv($seconds, 3600);
        $m = intdiv($seconds % 3600, 60);
        $s = $seconds % 60;
        return sprintf('%02d:%02d:%02d', $h, $m, $s);
    }
}

if (!function_exists('get_employee_monthly_work_stats')) {
    /**
     * Compute monthly working-hour stats for an employee.
     *
     * - Considers rows with a valid clock_out (not NULL/'00:00:00').
     * - Uses checkout_date when present to support cross-day shifts.
     * - Subtracts only completed breaks (both start and end present).
     *
     * @param int $empId
     * @param int $month 1..12
     * @param int $year  e.g., 2025
     * @return array{
     *   employee_id:int, month:int, year:int, days_counted:int,
     *   total_gross_seconds:int, total_break_seconds:int, total_net_seconds:int,
     *   total_gross_hhmmss:string, total_break_hhmmss:string, total_net_hhmmss:string,
     *   records: array<int, array{
     *      date:string, clock_in:string|null, clock_out:string|null,
     *      gross_hhmmss:string, break_hhmmss:string, net_hhmmss:string
     *   }>
     * }
     */
    function get_employee_monthly_work_stats(int $empId, int $month, int $year): array
    {
        $start = Carbon::create($year, $month, 1)->startOfDay();
        $end   = (clone $start)->endOfMonth()->endOfDay();

        $rows = AttendanceEmployee::with(['breaks' => function ($q) {
                $q->whereNotNull('break_start')
                  ->whereNotNull('break_end');
            }])
            ->where('employee_id', $empId)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->orderBy('date')
            ->get();

        $totalGross = 0;
        $totalBreak = 0;
        $records = [];

        foreach ($rows as $row) {
            try {
                $inDate = $row->date;
                $in     = Carbon::parse("{$inDate} {$row->clock_in}");

                // ✅ If today & no clock_out → use current time
                if ((empty($row->clock_out) || $row->clock_out === '00:00:00') 
                    && Carbon::parse($row->date)->isToday()) {
                    $out = Carbon::now();
                    $clockOutForDisplay = 'In Progress';
                } elseif (!empty($row->clock_out) && $row->clock_out !== '00:00:00') {
                    $outDate = !empty($row->checkout_date) ? $row->checkout_date : $row->date;
                    $out     = Carbon::parse("{$outDate} {$row->clock_out}");
                    $clockOutForDisplay = $row->clock_out;
                } else {
                    // Skip past records without clock_out
                    continue;
                }

                if ($out->lessThanOrEqualTo($in)) {
                    continue;
                }

                // Gross working time
                $gross = $in->diffInSeconds($out);

                // Completed breaks
                $breakSeconds = 0;
                foreach ($row->breaks as $br) {
                    if (!empty($br->break_start) && !empty($br->break_end)) {
                        $bStart = Carbon::parse("{$br->break_start_date} {$br->break_start}");
                        $bEnd   = Carbon::parse("{$br->break_end_date} {$br->break_end}");
                        if ($bEnd->greaterThan($bStart)) {
                            $breakSeconds += $bStart->diffInSeconds($bEnd);
                        }
                    }
                }

                $net = max(0, $gross - $breakSeconds);

                // Totals
                $totalGross += $gross;
                $totalBreak += $breakSeconds;

                $records[] = [
                    'date'          => $row->date,
                    'clock_in'      => $row->clock_in,
                    'clock_out'     => $clockOutForDisplay,
                    'gross_hhmmss'  => seconds_to_hms($gross),
                    'break_hhmmss'  => seconds_to_hms($breakSeconds),
                    'net_hhmmss'    => seconds_to_hms($net),
                ];
            } catch (\Throwable $e) {
                \Log::warning("Work calc error for attendance #{$row->id}: " . $e->getMessage());
                continue;
            }
        }

        $totalNet = max(0, $totalGross - $totalBreak);

        return [
            'employee_id'          => $empId,
            'month'                => $month,
            'year'                 => $year,
            'days_counted'         => count($records),
            'total_gross_seconds'  => $totalGross,
            'total_break_seconds'  => $totalBreak,
            'total_net_seconds'    => $totalNet,
            'total_gross_hhmmss'   => seconds_to_hms($totalGross),
            'total_break_hhmmss'   => seconds_to_hms($totalBreak),
            'total_net_hhmmss'     => seconds_to_hms($totalNet),
            'records'              => $records,
        ];
    }

}


if (!function_exists('get_allowed_leave_per_employee')) {
    function get_allowed_leave_per_employee($empId,$financialYear)
    {
        // $empId = 37;
        // Which leave types to consider (expects a Collection keyed by id: [id => title])
        $leaveTypes = getLeaveTypes(['SL', 'CL', 'WFH', 'OH']);
        $typeIds    = $leaveTypes->keys()->all();
        if (empty($typeIds)) {
            return collect();
        }

        // Active financial year (fallback to utility)
        $fy = DB::table('financial_years')->where('id', $financialYear->id)->first();
        $fyStart = Carbon::parse($fy->start_date ?? Utility::AnnualLeaveCycle()['start_date'])->startOfMonth();
        $fyEnd   = Carbon::parse($fy->end_date   ?? Utility::AnnualLeaveCycle()['end_date'])->endOfMonth();

        // Employee (by user_id)
        $employee = Employee::where('id', $empId)->first();
        if (!$employee) {
            return collect();
        }

        $monthsWorked = 0;
        // Months worked within FY (inclusive)
        if ($employee->company_doj) {
            $join = Carbon::parse($employee->company_doj)->startOfMonth();

            if ($join->lt($fyStart)) {
                // Joined before FY start → full year
                $monthsWorked = 12; // usually 12
            } elseif ($join->gt($fyEnd)) {
                // Joined after FY end → 0 months
                $monthsWorked = 0;
            } else {
                // Joined during FY → prorated
                $monthsWorked = round($join->diffInMonths($fyEnd));
            }
        } else {
            // No DOJ (default full year)
            $monthsWorked = 12;
        }

        // Fetch leave type rows once, compute allowed leave
        $leaveTypesAll = LeaveType::whereIn('id', $typeIds)->get()
            ->map(function ($type) use ($monthsWorked) {
                $days = (int) ($type->days ?? 0);

                $cal1 = $days / 12;
                $type->cal1 = $cal1;
                $type->allowed_leave = roundToHalf($cal1 * $monthsWorked);
                $type->monthsWorked = $monthsWorked;
                return $type;
            })
            ->keyBy('id');
        /*if($empId == 42){
            echo "<pre>";print_r($leaveTypesAll);exit;
        }*/
        return $leaveTypesAll;
    }
}

if (!function_exists('getLeaveTypes')) {
    function getLeaveTypes(array $codes)
    {
        return LeaveType::where(function ($query) use ($codes) {
            foreach ($codes as $code) {
                $query->orWhere('code', 'like', "%$code%");
            }
        })->pluck('title', 'id');
    }
}

if (!function_exists('roundToHalf')) {
    function roundToHalf($value)
    {
        return floor($value * 2) / 2;
    }
}
    

?>
