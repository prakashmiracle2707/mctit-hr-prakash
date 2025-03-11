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
use Carbon\Carbon;

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
                    $leaves = LocalLeave::where('employee_id', '=', $employee->id)
                        ->where(function ($query) use ($startOfMonth, $endOfMonth, $currentDate) {
                            $query->whereBetween('start_date', [$startOfMonth, $endOfMonth])
                            ->orWhere('start_date', '>', $currentDate);
                        })
                        ->orderBy('start_date', 'desc')
                        ->get();
                } else {
                    // For non-employees (e.g., admin)
                    $leaves = LocalLeave::where('created_by', '=', \Auth::user()->creatorId())
                        ->where('status', '=', 'Pending')  
                        ->with(['employees', 'leaveType']) 
                        ->orderBy('start_date', 'desc')
                        ->get();

                    $today = Carbon::today(); // Get today's date

                    // Check if today is a weekend (Saturday or Sunday)
                    if (!$today->isWeekend()) {
                        // Fetch leave records where the status is 'Approved' and today is between start_date and end_date
                        $Todayleaves = LocalLeave::where('created_by', '=', \Auth::user()->creatorId())
                            //->where('status', '=', 'Approved')  
                            ->whereDate('start_date', '<=', $today)
                            ->whereDate('end_date', '>=', $today)
                            ->with(['employees', 'leaveType']) 
                            ->orderBy('start_date', 'desc')
                            ->get();

                        // Calculate total leave days for each leave if not already calculated
                        foreach ($Todayleaves as $Todayleave) {
                            if ($Todayleave->total_leave_days == 0) {
                                $Todayleave->total_leave_days = $this->getTotalLeaveDays($Todayleave->start_date, $Todayleave->end_date);
                            }
                        }
                    }
                }

                // Calculate total leave days for each leave if not already calculated
                foreach ($leaves as $leave) {
                    if ($leave->total_leave_days == 0) {
                        $leave->total_leave_days = $this->getTotalLeaveDays($leave->start_date, $leave->end_date);
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


                return view('dashboard.dashboard', compact('arrEvents', 'announcements', 'employees', 'meetings', 'employeeAttendance', 'officeTime','disableCheckbox','isWorkFromHome','leaves','Todayleaves'));
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

                $emp           = User::where('type', '=', 'employee')->where('created_by', '=', \Auth::user()->creatorId())->get();
                $countEmployee = count($emp);

                $user      = User::where('type', '!=', 'employee')->where('created_by', '=', \Auth::user()->creatorId())->get();
                $countUser = count($user);

                $countTicket      = Ticket::where('created_by', '=', \Auth::user()->creatorId())->count();
                $countOpenTicket  = Ticket::where('status', '=', 'open')->where('created_by', '=', \Auth::user()->creatorId())->count();
                $countCloseTicket = Ticket::where('status', '=', 'close')->where('created_by', '=', \Auth::user()->creatorId())->count();

                $currentDate = date('Y-m-d');

                $countEmployee = count($emp);
                $notClockIn    = AttendanceEmployee::where('date', '=', $currentDate)->get()->pluck('employee_id');

                $notClockIns    = Employee::where('created_by', '=', \Auth::user()->creatorId())->whereNotIn('id', $notClockIn)->get();
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
                    ->orderByRaw('work_from_home DESC') // Order by work_from_home (1 first)
                    ->orderBy('updated_at', 'desc') // Then order by updated_at in descending order
                    ->get();


                /* *************** New Add End ****************************/

                return view('dashboard.dashboard', compact('arrEvents', 'announcements', 'activeJob','inActiveJOb','meetings', 'countEmployee', 'countUser', 'countTicket', 'countOpenTicket', 'countCloseTicket', 'notClockIns', 'countEmployee', 'accountBalance', 'totalPayee', 'totalPayer','attendanceEmployee','leaves','Todayleaves'));
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


    // Private function to calculate leave days excluding weekends
    private function getTotalLeaveDays($startDate, $endDate)
    {
        // Parse the start and end dates
        $startDate = \Carbon\Carbon::parse($startDate);
        $endDate = \Carbon\Carbon::parse($endDate);

        $totalLeaveDays = 0;

        // Loop from start date to end date
        for ($date = $startDate; $date <= $endDate; $date->addDay()) {
            // If the current day is not Saturday or Sunday, increment the total leave days
            if (!$date->isWeekend()) {
                $totalLeaveDays++;
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
