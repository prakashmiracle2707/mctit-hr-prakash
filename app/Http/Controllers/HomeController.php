<?php

namespace App\Http\Controllers;

use App\Models\AccountList;
use App\Models\Announcement;
use App\Models\AttendanceEmployee;
use App\Models\Employee;
use App\Models\Event;
use App\Models\LandingPageSection;
use App\Models\Meeting;
use App\Models\Job;
use App\Models\Payees;
use App\Models\Payer;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Utility;
use Illuminate\Support\Facades\Auth;
use App\Models\Leave as LocalLeave;
use App\Models\FinancialYear;
use Carbon\Carbon;
use App\Models\LeaveType;


class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        if(Auth::check())
        {
            $user = Auth::user();
            $leaves = [];
            $Todayleaves = [];
            $NextDayLeaves = [];
            $nextWorkingDay = '';
            $attendanceEmployee = [];
            $ThisMonthattendanceCount = 0;
            $LastMonthattendanceCount = 0;
            $totalSeconds = 0;
            $hasOngoingBreak = false;
            $relievedCount = 0;

            $breakLogs = collect(); // Initialize empty collection for break logs
            $totalBreakDuration = '00:00:00'; // Default to zero time

            if (\Auth::user()->can('Manage Leave')) {
                // Get current date and current month
                // Get the current date
                $currentDate = Carbon::today();

                // Get the start and end of the current month
                $startOfMonth = $currentDate->copy()->startOfMonth()->format('Y-m-d 00:00:00');
                $endOfMonth = $currentDate->copy()->endOfMonth()->format('Y-m-d 23:59:59');

                if (\Auth::user()->type == 'employee') {
                    $user     = \Auth::user();
                    $employee = Employee::where('user_id', '=', $user->id)->first();
                    
                    // Filter leaves that are either in the current month or in the future
                    $leaves = LocalLeave::where('employee_id', '=', $employee->id)->where('status', '!=', 'Draft')
                        ->where(function ($query) use ($startOfMonth, $endOfMonth, $currentDate) {
                            $query->whereBetween('start_date', [$startOfMonth, $endOfMonth])
                            ->orWhere('start_date', '>', $currentDate);
                        })
                        ->orderBy('start_date', 'desc')
                        ->get();

                    $emp = !empty(\Auth::user()->employee) ? \Auth::user()->employee->id : 0;
                    $attendanceEmployee = AttendanceEmployee::where('employee_id', $emp);

                    // Exclude today's date
                    $attendanceEmployee = $attendanceEmployee->whereDate('date', '<>', now()->toDateString());

                    // Take the first 5 records, excluding today's date
                    // $attendanceEmployee = $attendanceEmployee->orderBy('created_at', 'desc')->take(5)->get();

                    $attendanceEmployee = AttendanceEmployee::where('employee_id', $emp)
                    ->whereDate('date', '<=', now()->toDateString())
                    ->orderBy('created_at', 'desc')
                    ->take(6)
                    ->get()
                    ->map(function ($attendance) {
                        // Fetch all completed breaks for the given attendance record
                        $breakLogs = $attendance->breaks()->whereNotNull('break_end')->get();
                        
                        $totalSeconds = 0;

                        foreach ($breakLogs as $break) {
                            if (!empty($break->break_start) && !empty($break->break_end)) {
                                try {
                                    $start = Carbon::parse($break->break_start);
                                    $end = Carbon::parse($break->break_end);

                                    // Ensure valid duration
                                    if ($end->greaterThan($start)) {
                                        $duration = $start->diffInSeconds($end);
                                        $totalSeconds += (int) round($duration);
                                    }
                                } catch (\Exception $e) {
                                    \Log::error("Error calculating break duration for attendance ID: " . $attendance->id . " - " . $e->getMessage());
                                }
                            }
                        }

                        // Convert total break duration into HH:MM:SS format
                        $attendance->totalBreakDuration = sprintf('%02d:%02d:%02d', intdiv($totalSeconds, 3600), intdiv($totalSeconds % 3600, 60), $totalSeconds % 60);
                        
                        return $attendance;
                    });



                    // Get the current month and year
                    $currentMonth = now()->month;
                    $currentYear = now()->year;

                    // Count attendance records for this month
                    $ThisMonthattendanceCount = AttendanceEmployee::where('employee_id', $emp)->whereMonth('date', $currentMonth)
                                                          ->whereYear('date', $currentYear)
                                                          ->count();

                    // Get the last month and year
                    $lastMonth = now()->subMonth()->month;
                    $lastYear = now()->subMonth()->year;

                    // Count attendance records for the last month
                    $LastMonthattendanceCount = AttendanceEmployee::where('employee_id', $emp)
                                                                   ->whereMonth('date', $lastMonth)
                                                                   ->whereYear('date', $lastYear)
                                                                   ->count();

                    // Fetch today's attendance record
                    $employeeAttendance = AttendanceEmployee::where('employee_id', $emp)
                        ->whereDate('date', now()->toDateString())
                        ->first();

                    // Fetch break logs for today's attendance
                    if ($employeeAttendance) {
                        $breakLogs = $employeeAttendance->breaks()->orderBy('break_start', 'desc')->get();

                        // Fetch all completed breaks (only where `break_end` exists)
                        // $breakLogsToday = $employeeAttendance->breaks()->whereNotNull('break_end')->get();


                        $settings = Utility::settings();
                        date_default_timezone_set($settings['timezone']);

                        foreach ($breakLogs as $break) {
                            if (!empty($break->break_start)) {
                                try {
                                    // Parse break start time
                                    $start = Carbon::parse($break->break_start);
                                    
                                    // If break has ended, use its recorded end time
                                    if (!empty($break->break_end)) {
                                        $end = Carbon::parse($break->break_end);
                                    } else {
                                        // If break is in progress, use current time as end time
                                        $end = Carbon::now();
                                        $hasOngoingBreak = true;
                                    }

                                    // echo "<br />start  => ".$start." End ".$end;
                                    // Ensure valid break duration calculation
                                    if ($end->greaterThan($start)) {
                                        $duration = $start->diffInSeconds($end);
                                        $totalSeconds += $duration; // Accumulate total duration
                                    } else {
                                        \Log::error("Invalid break duration: Break end time is before start time for break ID: " . $break->id);
                                    }
                                } catch (\Exception $e) {
                                    \Log::error("Error parsing break time for break ID: " . $break->id . " - " . $e->getMessage());
                                }
                            }
                        }
                        // exit;
                        // Ensure totalSeconds is non-negative
                        $totalSeconds = max(0, (int) round($totalSeconds)); // Round and cast to integer

                        // Convert total seconds to HH:MM:SS format
                        $totalBreakDuration = sprintf('%02d:%02d:%02d', intdiv($totalSeconds, 3600), intdiv($totalSeconds % 3600, 60), $totalSeconds % 60);
                    }

                    // echo "<pre>";print_r($hasOngoingBreak);exit;

                } else {
                    $creatorId = \Auth::user()->creatorId();
                    // For non-employees (e.g., admin)
                    $leaves = LocalLeave::where('created_by', '=', \Auth::user()->creatorId())
                        // ->where('status', '=', 'Pending')  
                        ->whereIn('status', ['Partially_Approved', 'Manager_Approved'])
                        ->with(['employees', 'leaveType']) 
                        ->orderBy('start_date', 'desc')
                        ->get();

                    $today = Carbon::today(); // Get today's date

                    // Check if today is a weekend (Saturday or Sunday)
                    if (!$today->isWeekend()) {
                        // Fetch leave records where the status is 'Approved' and today is between start_date and end_date
                        $Todayleaves = LocalLeave::where('created_by', '=', \Auth::user()->creatorId())
                                        ->where('status', '!=', 'Draft') // Exclude Draft status
                                        ->whereDate('start_date', '<=', $today)
                                        ->whereDate('end_date', '>=', $today)
                                        ->with(['employees', 'leaveType'])
                                        ->orderBy('start_date', 'desc')
                                        ->get();

                        // Calculate total leave days for each leave if not already calculated
                        foreach ($Todayleaves as $Todayleave) {
                            if ($Todayleave->total_leave_days == 0) {
                                $Todayleave->total_leave_days = $this->getTotalLeaveDays($Todayleave->start_date, $Todayleave->end_date,$Todayleave->leave_type_id,$Todayleave->half_day_type);
                            }
                        }
                    }
                }

                // Calculate total leave days for each leave if not already calculated
                foreach ($leaves as $leave) {
                    if ($leave->total_leave_days == 0) {
                        $leave->total_leave_days = $this->getTotalLeaveDays($leave->start_date, $leave->end_date,$leave->leave_type_id,$leave->half_day_type);
                    }
                }

                $today = Carbon::today();
                $creatorId = \Auth::user()->creatorId();

                // ✅ Determine next working day (skipping weekends)
                $nextWorkingDay = $today->copy()->addDay();
                while ($nextWorkingDay->isWeekend()) {
                    $nextWorkingDay->addDay();
                }

                // ✅ Fetch next working day's approved leaves
                $NextDayLeaves = LocalLeave::where('created_by', $creatorId)
                    ->where('status', '!=', 'Draft')
                    ->whereDate('start_date', '<=', $nextWorkingDay)
                    ->whereDate('end_date', '>=', $nextWorkingDay)
                    ->with(['employees', 'leaveType'])
                    ->orderBy('start_date', 'desc')
                    ->get();

                foreach ($NextDayLeaves as $leave) {
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


            if($user->type == 'employee')
            {

                $emp = Employee::where('user_id', '=', $user->id)->first();

                $announcements = Announcement::orderBy('announcements.id', 'desc')->take(5)->leftjoin('announcement_employees', 'announcements.id', '=', 'announcement_employees.announcement_id')->where('announcement_employees.employee_id', '=', $emp->id)->orWhere(
                    function ($q){
                        $q->where('announcements.department_id', '["0"]')->where('announcements.employee_id', '["0"]');
                    }
                )->get();

                $employees = Employee::get();
                $meetings  = Meeting::orderBy('meetings.id', 'desc')->take(5)->leftjoin('meeting_employees', 'meetings.id', '=', 'meeting_employees.meeting_id')->where('meeting_employees.employee_id', '=', $emp->id)->orWhere(
                    function ($q){
                        $q->where('meetings.department_id', '["0"]')->where('meetings.employee_id', '["0"]'); 
                    }
                )->get();

                $events    = Event::select('events.*','events.id as event_id_pk','event_employees.*')
                ->leftjoin('event_employees', 'events.id', '=', 'event_employees.event_id')
                ->where('event_employees.employee_id', '=', $emp->id)
                ->orWhere(
                    function ($q){
                        $q->where('events.department_id', '["0"]')->where('events.employee_id', '["0"]');
                    }
                )->get();
                
                $arrEvents = [];
                foreach($events as $event)
                {

                    $arr['id']              = $event['id'];
                    $arr['title']           = $event['title'];
                    $arr['start']           = $event['start_date'];
                    $arr['end']             = $event['end_date'];
                    $arr['className']       = $event['color'];
                    // $arr['borderColor']     = "#fff";
                    $arr['url']             = route('eventsshow', (!empty($event['event_id_pk'])) ? $event['event_id_pk'] : '' );
                    // $arr['textColor']       = "white";

                    $arrEvents[] = $arr;
                }

                $date               = date("Y-m-d");
                $time               = date("H:i:s");
                $employeeAttendance = AttendanceEmployee::orderBy('id', 'desc')->where('employee_id', '=', !empty(\Auth::user()->employee) ? \Auth::user()->employee->id : 0)->where('date', '=', $date)->first();

                // echo "<pre>";print_r($employeeAttendance);exit;

                // Determine if the Work from Home checkbox should be disabled
                $disableCheckbox = !empty($employeeAttendance) && $employeeAttendance->clock_in !== null;

                // Determine if the Work from Home checkbox should be checked
                $isWorkFromHome = !empty($employeeAttendance) && $employeeAttendance->work_from_home == 1;

                $officeTime['startTime'] = Utility::getValByName('company_start_time');
                $officeTime['endTime']   = Utility::getValByName('company_end_time');


                /* ******************* Leave calculation start ******************* */
                $activeYear = FinancialYear::where('is_active', 1)->first();
                $user = Auth::user();
                $employee = Employee::where('user_id', $user->id)->first();

                // All leave types
                $leaveTypes = LeaveType::where(function ($query) {
                                    $query->where('code', 'like', '%SL%')
                                          ->orWhere('code', 'like', '%CL%');
                                })->pluck('title', 'id');
                $leaveCounts = [];

                // Initialize structure
                foreach ($leaveTypes as $id => $title) {
                    $leaveCounts[$id] = [
                        'Approved' => 0,
                        'Rejected' => 0,
                        'Pending' => 0,
                    ];
                }

                // Get all leaves
                $LeavesList = LocalLeave::where('employee_id', $employee->id)->get();

                foreach ($LeavesList as $LeaveDetails) {
                    $leaveTypeId = $LeaveDetails->leave_type_id;
                    $status = $LeaveDetails->status;

                    $start = Carbon::parse($LeaveDetails->start_date);
                    $end = Carbon::parse($LeaveDetails->end_date);

                    $halfDayType = $LeaveDetails->half_day_type;

                    $days = 0;

                    // Loop each date in range
                    for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
                        $isWeekend = in_array($date->dayOfWeek, [Carbon::SATURDAY, Carbon::SUNDAY]);

                        if ($isWeekend) {
                            continue;
                        }

                        // Count only if date within financial year
                        if ($date->between(Carbon::parse($activeYear->start_date), Carbon::parse($activeYear->end_date))) {
                            // Count 0.5 for half-day (only if single day leave)
                            if ($halfDayType != 'full_day') {
                                $days += 0.5;
                            } else {
                                $days += 1;
                            }
                        }
                    }

                    // Add to counter
                    if (isset($leaveCounts[$leaveTypeId][$status])) {
                        $leaveCounts[$leaveTypeId][$status] += $days;
                    }
                }

                $leaveTypes = LeaveType::pluck('title', 'id');



                $leaveTypesAll = LeaveType::where(function ($query) {
                                    $query->where('code', 'like', '%SL%')
                                          ->orWhere('code', 'like', '%CL%');
                                })->get()->keyBy('id');

                
                /* ******************* Leave calculation end ******************* */
                $user = \Auth::user();
                $leaves_cc = LocalLeave::query()
                                ->where(function ($query) use ($user) {
                                    $query->whereJsonContains('cc_email', (string) ($user->employee->id ?? 0));
                                })
                                ->where('status', '!=', 'Draft')
                                ->with(['employees', 'leaveType'])
                                ->orderBy('start_date', 'desc')
                                ->get();

                /* *************** New Add Start ****************************/
                $employeeCheck = Employee::select('id')->where('created_by', \Auth::user()->creatorId())->where('id', '!=', \Auth::user()->employee->id);

                if (!empty($request->branch)) {
                    $employeeCheck->where('branch_id', $request->branch);
                }

                if (!empty($request->department)) {
                    $employeeCheck->where('department_id', $request->department);
                }

                $employeeCheck = $employeeCheck->get()->pluck('id');

                // echo "<pre>";print_r($employeeCheck);exit;

                // Get only today's attendance
                $today = Carbon::today()->toDateString();

                $FindOnBreakEmployee = AttendanceEmployee::whereIn('employee_id', $employeeCheck)
                    ->whereDate('date', $today)
                    ->orderByRaw('work_from_home DESC')
                    ->orderBy('updated_at', 'desc')
                    ->get()
                    ->map(function ($attendance) {
                        $breakLogs = $attendance->breaks()->get();
                        $totalSeconds = 0;
                        $isInBreak = false;

                        foreach ($breakLogs as $break) {
                            if (!empty($break->break_start)) {
                                try {
                                    $start = Carbon::parse($break->break_start);

                                    if (empty($break->break_end)) {
                                        $isInBreak = true; // currently on break
                                    } else {
                                        $end = Carbon::parse($break->break_end);
                                        if ($end->greaterThan($start)) {
                                            $duration = $start->diffInSeconds($end);
                                            $totalSeconds += (int) round($duration);
                                        }
                                    }
                                } catch (\Exception $e) {
                                    \Log::error("Error calculating break duration for attendance ID: " . $attendance->id . " - " . $e->getMessage());
                                }
                            }
                        }

                        $attendance->totalBreakDuration = sprintf('%02d:%02d:%02d', intdiv($totalSeconds, 3600), intdiv($totalSeconds % 3600, 60), $totalSeconds % 60);
                        $attendance->isInBreak = $isInBreak;

                        return $attendance;
                    })
                    ->filter(function ($attendance) {
                        return $attendance->isInBreak === true; // only return those currently on break
                    })
                    ->values();


                // attendanceEmployee
                /* *************** New Add End ****************************/
                $employeesinfo = Employee::where('user_id', '=', $user->id)->first();
                
                return view('dashboard.dashboard', compact('arrEvents', 'announcements', 'employees', 'meetings', 'employeeAttendance','relievedCount', 'officeTime','disableCheckbox','isWorkFromHome','leaves','Todayleaves','attendanceEmployee','ThisMonthattendanceCount', 'LastMonthattendanceCount','breakLogs', 'totalBreakDuration','totalSeconds','nextWorkingDay','NextDayLeaves','hasOngoingBreak','leaveCounts','leaveTypes','leaveTypesAll','leaves_cc','FindOnBreakEmployee','employeesinfo'));
            }
            else
            {
                $events    = Event::where('created_by', '=', \Auth::user()->creatorId())->get();
                $arrEvents = [];

                foreach($events as $event)
                {
                    $arr['id']    = $event['id'];
                    $arr['title'] = $event['title'];
                    $arr['start'] = $event['start_date'];
                    $arr['end']   = $event['end_date'];

                    $arr['className'] = $event['color'];
                    // $arr['borderColor']     = "#fff";
                    // $arr['textColor']       = "white";
                    $arr['url']             = route('event.edit', $event['id']);

                    $arrEvents[] = $arr;
                }

                $announcements = Announcement::orderBy('announcements.id', 'desc')->take(5)->where('created_by', '=', \Auth::user()->creatorId())->get();

                $emp = User::where('type', '=', 'employee')->where('created_by', '=', \Auth::user()->creatorId())->get();
                $countEmployee = count($emp);

                $user      = User::where('type', '!=', 'employee')->where('created_by', '=', \Auth::user()->creatorId())->get();
                $countUser = count($user);

                $countTicket      = Ticket::where('created_by', '=', \Auth::user()->creatorId())->count();
                $countOpenTicket  = Ticket::where('status', '=', 'open')->where('created_by', '=', \Auth::user()->creatorId())->count();
                $countCloseTicket = Ticket::where('status', '=', 'close')->where('created_by', '=', \Auth::user()->creatorId())->count();

                $currentDate = date('Y-m-d');

                $countEmployee = count($emp);
                $notClockIn    = AttendanceEmployee::where('date', '=', $currentDate)->get()->pluck('employee_id');

                $relievedCount = Employee::where('created_by', \Auth::user()->creatorId())
                                    ->whereNotNull('relieving_date')
                                    ->where('relieving_date', '<=', now())
                                    ->count();

                $notClockIns    = Employee::where('created_by', '=', \Auth::user()->creatorId())->whereNull('relieving_date')->orderBy('name', 'asc')->whereNotIn('id', $notClockIn)->get();


                /* ****************************************************************** */


                // notClockIns



                $today = Carbon::today()->toDateString();
                $notClockInDetails = [];

                foreach ($notClockIns as $employee) {
                    // Check if employee is on leave today
                    $leave = LocalLeave::where('employee_id', $employee->id)
                        ->whereDate('start_date', '<=', $today)
                        ->whereDate('end_date', '>=', $today)
                        ->whereIn('leave_type_id', [1, 2])
                        ->with('leaveType') // assumes relation is defined
                        ->first();

                    // Get availability status (safe check)
                    $availabilityStatus = optional($employee->availabilityStatus)->name;

                    // Determine leave type
                    $leaveType = 'Absent'; // default
                    $leaveStatus = '-';

                    if ($leave) {
                        $leaveType = $leave->leaveType->title ?? 'N/A';
                        $leaveStatus = $leave->status ?? 'N/A';
                    } elseif ($availabilityStatus !== 'Available') {
                        // If not on leave and not Available, don't show Absent
                        $leaveType = $availabilityStatus ?? 'N/A';
                    }

                    $notClockInDetails[] = [
                        'employee_id' => $employee->id,
                        'employee_name' => $employee->name,
                        'is_on_leave' => $leave ? true : false,
                        'leave_type' => $leaveType,
                        'leave_status' => $leaveStatus,
                        'employee' => $employee, // pass full employee model if needed in view
                    ];
                }


                /* ****************************************************************** */
                $accountBalance = AccountList::where('created_by', '=', \Auth::user()->creatorId())->sum('initial_balance');
                
                $activeJob   = Job::where('status', 'active')->where('created_by', '=', \Auth::user()->creatorId())->count();
                $inActiveJOb = Job::where('status', 'in_active')->where('created_by', '=', \Auth::user()->creatorId())->count();

                $totalPayee = Payees::where('created_by', '=', \Auth::user()->creatorId())->count();
                $totalPayer = Payer::where('created_by', '=', \Auth::user()->creatorId())->count();

                $meetings = Meeting::where('created_by', '=', \Auth::user()->creatorId())->limit(5)->get();

                /* *************** New Add Start ****************************/
                $employee = Employee::select('id')->where('created_by', \Auth::user()->creatorId());

                if (!empty($request->branch)) {
                    $employee->where('branch_id', $request->branch);
                }

                if (!empty($request->department)) {
                    $employee->where('department_id', $request->department);
                }

                $employee = $employee->get()->pluck('id');

                // Get only today's attendance
                $today = Carbon::today()->toDateString();

                $attendanceEmployee = AttendanceEmployee::whereIn('employee_id', $employee)
                ->whereDate('date', $today)
                ->orderByRaw('work_from_home DESC')
                ->orderBy('updated_at', 'desc')
                ->get()
                ->map(function ($attendance) {
                    // Fetch all breaks for this attendance record
                    $breakLogs = $attendance->breaks()->get();
                    $totalSeconds = 0;
                    $isInBreak = false; // Flag to track if the employee is currently in a break

                    foreach ($breakLogs as $break) {
                        if (!empty($break->break_start)) {
                            try {
                                $start = Carbon::parse($break->break_start);

                                if (empty($break->break_end)) {
                                    // If break_end is NULL, employee is currently on break
                                    $isInBreak = true;
                                } else {
                                    $end = Carbon::parse($break->break_end);
                                    if ($end->greaterThan($start)) {
                                        $duration = $start->diffInSeconds($end);
                                        $totalSeconds += (int) round($duration);
                                    }
                                }
                            } catch (\Exception $e) {
                                \Log::error("Error calculating break duration for attendance ID: " . $attendance->id . " - " . $e->getMessage());
                            }
                        }
                    }

                    // Convert total break duration into HH:MM:SS format
                    $attendance->totalBreakDuration = sprintf('%02d:%02d:%02d', intdiv($totalSeconds, 3600), intdiv($totalSeconds % 3600, 60), $totalSeconds % 60);
                    $attendance->isInBreak = $isInBreak; // Assign break status

                    return $attendance;
                });

                // attendanceEmployee Todayleaves
                /* *************** New Add End ****************************/

                return view('dashboard.dashboard', compact('arrEvents', 'announcements', 'activeJob','inActiveJOb','meetings', 'countEmployee','relievedCount', 'countUser', 'countTicket', 'countOpenTicket', 'countCloseTicket', 'notClockIns', 'countEmployee', 'accountBalance', 'totalPayee', 'totalPayer','attendanceEmployee','leaves','Todayleaves','nextWorkingDay','NextDayLeaves','breakLogs', 'totalBreakDuration', 'totalSeconds','hasOngoingBreak','notClockInDetails'));
            }
        }
        else
        {
            if(!file_exists(storage_path() . "/installed"))
            {
                header('location:install');
                die;
            }
            else
            {
                $settings = Utility::settings();
                if($settings['display_landing_page'] == 'on' && \Schema::hasTable('landing_page_settings'))
                {
                    $get_section = LandingPageSection::orderBy('section_order', 'ASC')->get();
                    return view('landingpage::layouts.landingpage',compact('get_section'));
                }
                else
                {
                    return redirect('login');
                }

            }
        }
    }

    public function dashboard_hr()
    {
        
        $user = \Auth::user();

        // Check if user has 'Complaint-Reviewer' role
        $isReviewer = $user->secondaryRoleAssignments()
            ->whereHas('role', fn($q) => $q->where('name', 'Dashboard-Reviewer'))
            ->exists();

        $user = Auth::user();
        $leaves = [];
        $Todayleaves = [];
        $attendanceEmployee = [];
        $ThisMonthattendanceCount = 0;
        $LastMonthattendanceCount = 0;
        $totalSeconds = 0;
        $hasOngoingBreak = false;
        $relievedCount = 0;

        $breakLogs = collect(); // Initialize empty collection for break logs
        $totalBreakDuration = '00:00:00'; // Default to zero time

        if (\Auth::user()->can('Manage Leave')) {
            // Get current date and current month
            // Get the current date notClockInDetails
            $currentDate = Carbon::today();

            // Get the start and end of the current month
            $startOfMonth = $currentDate->copy()->startOfMonth()->format('Y-m-d 00:00:00');
            $endOfMonth = $currentDate->copy()->endOfMonth()->format('Y-m-d 23:59:59');

            // For non-employees (e.g., admin)
            $leaves = LocalLeave::where('created_by', '=', \Auth::user()->creatorId())
                // ->where('status', '=', 'Pending')  
                ->whereIn('status', ['Partially_Approved', 'Manager_Approved'])
                ->with(['employees', 'leaveType']) 
                ->orderBy('start_date', 'desc')
                ->get();

            $today = Carbon::today(); // Get today's date

            // Check if today is a weekend (Saturday or Sunday)
            if (!$today->isWeekend()) {
                // Fetch leave records where the status is 'Approved' and today is between start_date and end_date
                $Todayleaves = LocalLeave::where('created_by', '=', \Auth::user()->creatorId())
                                ->where('status', '!=', 'Draft') // Exclude Draft status
                                ->whereDate('start_date', '<=', $today)
                                ->whereDate('end_date', '>=', $today)
                                ->with(['employees', 'leaveType'])
                                ->orderBy('start_date', 'desc')
                                ->get();

                // Calculate total leave days for each leave if not already calculated
                foreach ($Todayleaves as $Todayleave) {
                    if ($Todayleave->total_leave_days == 0) {
                        $Todayleave->total_leave_days = $this->getTotalLeaveDays($Todayleave->start_date, $Todayleave->end_date,$Todayleave->leave_type_id,$Todayleave->half_day_type);
                    }
                }
            }

            // Calculate total leave days for each leave if not already calculated
            foreach ($leaves as $leave) {
                if ($leave->total_leave_days == 0) {
                    $leave->total_leave_days = $this->getTotalLeaveDays($leave->start_date, $leave->end_date,$leave->leave_type_id,$leave->half_day_type);
                }
            }
        }


        
        
        $events    = Event::where('created_by', '=', \Auth::user()->creatorId())->get();
        $arrEvents = [];

        foreach($events as $event)
        {
            $arr['id']    = $event['id'];
            $arr['title'] = $event['title'];
            $arr['start'] = $event['start_date'];
            $arr['end']   = $event['end_date'];

            $arr['className'] = $event['color'];
            // $arr['borderColor']     = "#fff";
            // $arr['textColor']       = "white";
            $arr['url']             = route('event.edit', $event['id']);

            $arrEvents[] = $arr;
        }

        $announcements = Announcement::orderBy('announcements.id', 'desc')->take(5)->where('created_by', '=', \Auth::user()->creatorId())->get();

        $emp = User::where('type', '=', 'employee')->where('created_by', '=', \Auth::user()->creatorId())->get();
        $countEmployee = count($emp);

        $user      = User::where('type', '!=', 'employee')->where('created_by', '=', \Auth::user()->creatorId())->get();
        $countUser = count($user);

        $countTicket      = Ticket::where('created_by', '=', \Auth::user()->creatorId())->count();
        $countOpenTicket  = Ticket::where('status', '=', 'open')->where('created_by', '=', \Auth::user()->creatorId())->count();
        $countCloseTicket = Ticket::where('status', '=', 'close')->where('created_by', '=', \Auth::user()->creatorId())->count();

        $currentDate = date('Y-m-d');

        $countEmployee = count($emp);
        $notClockIn    = AttendanceEmployee::where('date', '=', $currentDate)->get()->pluck('employee_id');

        $relievedCount = Employee::where('created_by', \Auth::user()->creatorId())->whereNotNull('relieving_date')->where('relieving_date', '<=', now())->count();

        $notClockIns    = Employee::where('created_by', '=', \Auth::user()->creatorId())->whereNotIn('id', $notClockIn)->whereNull('relieving_date')->orderBy('name', 'asc')->get();


        /* ****************************************************************** */


        // notClockIns



        $today = Carbon::today()->toDateString();
        $notClockInDetails = [];

        foreach ($notClockIns as $employee) {
            // Check if employee is on leave today
            $leave = LocalLeave::where('employee_id', $employee->id)
                ->whereDate('start_date', '<=', $today)
                ->whereDate('end_date', '>=', $today)
                ->whereIn('leave_type_id', [1, 2])
                ->with('leaveType') // assumes relation is defined
                ->first();

            // Get availability status (safe check)
            $availabilityStatus = optional($employee->availabilityStatus)->name;

            // Determine leave type
            $leaveType = 'Absent'; // default
            $leaveStatus = '-';

            if ($leave) {
                $leaveType = $leave->leaveType->title ?? 'N/A';
                $leaveStatus = $leave->status ?? 'N/A';
            } elseif ($availabilityStatus !== 'Available') {
                // If not on leave and not Available, don't show Absent
                $leaveType = $availabilityStatus ?? 'N/A';
            }

            $notClockInDetails[] = [
                'employee_id' => $employee->id,
                'employee_name' => $employee->name,
                'is_on_leave' => $leave ? true : false,
                'leave_type' => $leaveType,
                'leave_status' => $leaveStatus,
                'employee' => $employee, // pass full employee model if needed in view
            ];
        }


        /* ****************************************************************** */
        $accountBalance = AccountList::where('created_by', '=', \Auth::user()->creatorId())->sum('initial_balance');
        
        $activeJob   = Job::where('status', 'active')->where('created_by', '=', \Auth::user()->creatorId())->count();
        $inActiveJOb = Job::where('status', 'in_active')->where('created_by', '=', \Auth::user()->creatorId())->count();

        $totalPayee = Payees::where('created_by', '=', \Auth::user()->creatorId())->count();
        $totalPayer = Payer::where('created_by', '=', \Auth::user()->creatorId())->count();

        $meetings = Meeting::where('created_by', '=', \Auth::user()->creatorId())->limit(5)->get();

        /* *************** New Add Start ****************************/
        $employee = Employee::select('id')->where('created_by', \Auth::user()->creatorId());

        if (!empty($request->branch)) {
            $employee->where('branch_id', $request->branch);
        }

        if (!empty($request->department)) {
            $employee->where('department_id', $request->department);
        }

        $employee = $employee->get()->pluck('id');

        // Get only today's attendance
        $today = Carbon::today()->toDateString();

        $attendanceEmployee = AttendanceEmployee::whereIn('employee_id', $employee)
        ->whereDate('date', $today)
        ->orderByRaw('work_from_home DESC')
        ->orderBy('updated_at', 'desc')
        ->get()
        ->map(function ($attendance) {
            // Fetch all breaks for this attendance record
            $breakLogs = $attendance->breaks()->get();
            $totalSeconds = 0;
            $isInBreak = false; // Flag to track if the employee is currently in a break

            foreach ($breakLogs as $break) {
                if (!empty($break->break_start)) {
                    try {
                        $start = Carbon::parse($break->break_start);

                        if (empty($break->break_end)) {
                            // If break_end is NULL, employee is currently on break
                            $isInBreak = true;
                        } else {
                            $end = Carbon::parse($break->break_end);
                            if ($end->greaterThan($start)) {
                                $duration = $start->diffInSeconds($end);
                                $totalSeconds += (int) round($duration);
                            }
                        }
                    } catch (\Exception $e) {
                        \Log::error("Error calculating break duration for attendance ID: " . $attendance->id . " - " . $e->getMessage());
                    }
                }
            }

            // Convert total break duration into HH:MM:SS format
            $attendance->totalBreakDuration = sprintf('%02d:%02d:%02d', intdiv($totalSeconds, 3600), intdiv($totalSeconds % 3600, 60), $totalSeconds % 60);
            $attendance->isInBreak = $isInBreak; // Assign break status

            return $attendance;
        });

        // attendanceEmployee  leaves
        /* *************** New Add End ****************************/

        return view('dashboard.dashboard-hr', compact('arrEvents', 'announcements', 'activeJob','inActiveJOb','meetings', 'countEmployee','relievedCount', 'countUser', 'countTicket', 'countOpenTicket', 'countCloseTicket', 'notClockIns', 'countEmployee', 'accountBalance', 'totalPayee', 'totalPayer','attendanceEmployee','leaves','Todayleaves','breakLogs', 'totalBreakDuration', 'totalSeconds','hasOngoingBreak','notClockInDetails','isReviewer'));
        
        
    }


    // Private function to calculate leave days excluding weekends
    private function getTotalLeaveDays($startDate, $endDate,$leave_type_id,$half_day_type)
    {
        $startDate = \Carbon\Carbon::parse($startDate);
        $endDate = \Carbon\Carbon::parse($endDate);

        $totalLeaveDays = 0;

        if($leave_type_id != 5){
            // Fetch all holidays in the date range
            $holidays = \App\Models\Holiday::where('is_optional', 0)
                        ->pluck('start_date')
                        ->map(fn($date) => \Carbon\Carbon::parse($date)->format('Y-m-d'))
                        ->toArray();

            // echo "<pre>";print_r($holidays);exit;

            for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
                $formattedDate = $date->format('Y-m-d');

                // Skip weekends and holidays
                if (!$date->isWeekend() && !in_array($formattedDate, $holidays)) {
                    if(($leave_type_id == 1 || $leave_type_id == 2) && $half_day_type != 'full_day'){
                       $totalLeaveDays = $totalLeaveDays + 0.5; 
                    }else{
                       $totalLeaveDays++; 
                    }
                    
                }
            }
        }

        return $totalLeaveDays;
    }

    public function getOrderChart($arrParam)
    {
        $arrDuration = [];
        if($arrParam['duration'])
        {
            if($arrParam['duration'] == 'week')
            {
                $previous_week = strtotime("-2 week +1 day");
                for($i = 0; $i < 14; $i++)
                {
                    $arrDuration[date('Y-m-d', $previous_week)] = date('d-M', $previous_week);
                    $previous_week                              = strtotime(date('Y-m-d', $previous_week) . " +1 day");
                }
            }
        }

        $arrTask          = [];
        $arrTask['label'] = [];
        $arrTask['data']  = [];
        foreach($arrDuration as $date => $label)
        {

            $data               = Order::select(\DB::raw('count(*) as total'))->whereDate('created_at', '=', $date)->first();
            $arrTask['label'][] = $label;
            $arrTask['data'][]  = $data->total;
        }

        return $arrTask;
    }
}
