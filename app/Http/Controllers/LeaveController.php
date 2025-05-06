<?php

namespace App\Http\Controllers;

use App\Exports\LeaveExport;
use App\Models\Employee;
use App\Models\Leave as LocalLeave;
use App\Models\LeaveType;
use App\Mail\LeaveActionSend;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\GoogleCalendar\Event as GoogleEvent;
use App\Models\FinancialYear;
use App\Models\Holiday;
use App\Models\LeaveManager;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class LeaveController extends Controller
{
    public function index()
    {
        $leaveCounts = [];
        $leaveTypes = [];
        $leaveTypesAll = [];

        $financialYears = FinancialYear::pluck('year_range', 'id');
        $activeYearId = FinancialYear::where('is_active', 1)->value('id');
        if(request('financial_year_id')){
            $selectedFinancialYearId = request('financial_year_id') ?? $activeYearId;
        }else{
           $selectedFinancialYearId = $activeYearId;
        }
        

        $selectedEmployeeId = request('employee_id');

        $financialYear = FinancialYear::find($selectedFinancialYearId);
        $employeeList = Employee::where('created_by', \Auth::user()->creatorId())
                                ->orderBy('name', 'asc')
                                ->pluck('name', 'id');
        $managerList = Employee::where('created_by', \Auth::user()->creatorId())
                                ->where('is_manager', true)
                                ->orderBy('name', 'asc')
                                ->pluck('name', 'id');

        if (\Auth::user()->can('Manage Leave')) {
            if (\Auth::user()->type == 'employee') {
                $user = \Auth::user();
                $employee = Employee::where('user_id', $user->id)->first();

                $leaves = LocalLeave::where('employee_id', $employee->id)
                    ->where(function ($query) use ($financialYear) {
                        $query->whereBetween('start_date', [$financialYear->start_date, $financialYear->end_date])
                              ->orWhereBetween('end_date', [$financialYear->start_date, $financialYear->end_date]);
                    })
                    ->orderBy('applied_on', 'desc')
                    ->get();

                    // echo "<pre>";print_r($leaves);exit;

                $leaveTypes = LeaveType::where(function ($query) {
                    $query->where('code', 'like', '%SL%')->orWhere('code', 'like', '%CL%');
                })->pluck('title', 'id','code');

                foreach ($leaveTypes as $id => $title) {
                    $leaveCounts[$id] = [
                        'Approved' => 0,
                        'Rejected' => 0,
                        'Pending' => 0,
                    ];
                }

                $LeavesList = LocalLeave::where('employee_id', $employee->id)->get();

                foreach ($LeavesList as $LeaveDetails) {
                    $leaveTypeId = $LeaveDetails->leave_type_id;
                    $status = $LeaveDetails->status;
                    $start = \Carbon\Carbon::parse($LeaveDetails->start_date);
                    $end = \Carbon\Carbon::parse($LeaveDetails->end_date);
                    $halfDayType = $LeaveDetails->half_day_type;
                    $days = 0;

                    for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
                        if (in_array($date->dayOfWeek, [\Carbon\Carbon::SATURDAY, \Carbon\Carbon::SUNDAY])) continue;

                        if ($date->between(\Carbon\Carbon::parse($financialYear->start_date), \Carbon\Carbon::parse($financialYear->end_date))) {
                            $days += ($start->equalTo($end) && $halfDayType != 'full_day') ? 0.5 : 1;
                        }
                    }

                    if (isset($leaveCounts[$leaveTypeId][$status])) {
                        $leaveCounts[$leaveTypeId][$status] += $days;
                    }
                }

                $leaveTypes = LeaveType::pluck('title', 'id','code');
                $leaveTypesAll = LeaveType::where(function ($query) {
                    $query->where('code', 'like', '%SL%')->orWhere('code', 'like', '%CL%');
                })->get()->keyBy('id');
            } else {
                // Admin / CEO / Manager
                $leavesQuery = LocalLeave::where('created_by', \Auth::user()->creatorId())
                    ->where('status', '!=', 'Draft')
                    ->where(function ($query) use ($financialYear) {
                        $query->whereBetween('start_date', [$financialYear->start_date, $financialYear->end_date])
                              ->orWhereBetween('end_date', [$financialYear->start_date, $financialYear->end_date]);
                    });

                if (!empty($selectedEmployeeId)) {
                    $leavesQuery->where('employee_id', $selectedEmployeeId);
                }

                $leaves = $leavesQuery->with(['employees', 'leaveType'])
                            ->orderByRaw("FIELD(status, 'Pending', 'Manager_Approved') DESC")
                            ->orderBy('applied_on', 'desc')
                            ->get();
            }

            foreach ($leaves as $leave) {
                if ($leave->total_leave_days == 0) {
                    $leave->total_leave_days = $this->getTotalLeaveDays(
                        $leave->start_date,
                        $leave->end_date,
                        $leave->leave_type_id,
                        $leave->half_day_type
                    );
                }
            }

            return view('leave.index', compact(
                'leaves',
                'leaveCounts',
                'leaveTypes',
                'leaveTypesAll',
                'financialYears',
                'activeYearId',
                'selectedFinancialYearId',
                'selectedEmployeeId',
                'employeeList',
                'managerList',
            ));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
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

    public function create()
    {
        if (\Auth::user()->can('Create Leave')) {
            if (Auth::user()->type == 'employee') {
                $employees = Employee::where('user_id', '=', \Auth::user()->id)->first();
            } else {
                $employees = Employee::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            }

            $employeesList = Employee::where('user_id', '!=', \Auth::user()->id)->first();
            $leavetypes      = LeaveType::where('created_by', '=', \Auth::user()->creatorId())->get();
            $leavetypes_days = LeaveType::where('created_by', '=', \Auth::user()->creatorId())->get();

            $managerList = Employee::where('created_by', \Auth::user()->creatorId())
                            ->where('user_id', '!=', \Auth::user()->id)
                            ->where('is_manager', 1)
                            ->orderBy('name', 'asc')
                            ->pluck('name', 'id');



            $defaultManagerList = [];

            if (!empty($employees->manages_id)) {
                $decoded = json_decode($employees->manages_id, true); // Convert JSON string to PHP array
                $defaultManagerList = is_array($decoded) ? array_map('intval', $decoded) : [];
            }
            
            return view('leave.create', compact('employees', 'leavetypes', 'leavetypes_days', 'employeesList','managerList','defaultManagerList'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function storeOld(Request $request)
    {
        if (\Auth::user()->can('Create Leave')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'employee_id' => 'required',
                    'leave_type_id' => 'required',
                    'start_date' => 'required',
                    'end_date' => 'required',
                    'leave_reason' => 'required',
                    // remark' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }


            // $employee = Employee::where('employee_id', '=', \Auth::user()->creatorId())->first();
            $leave_type = LeaveType::find($request->leave_type_id);

            $startDate = new \DateTime($request->start_date);
            $endDate = new \DateTime($request->end_date);
            $endDate->add(new \DateInterval('P1D'));
            // $total_leave_days = !empty($startDate->diff($endDate)) ? $startDate->diff($endDate)->days : 0;
            $date = Utility::AnnualLeaveCycle();

            if (\Auth::user()->type == 'employee') {
                // Leave day
                $leaves_used   = LocalLeave::where('employee_id', '=', $request->employee_id)->where('leave_type_id', $leave_type->id)->where('status', 'Approved')->whereBetween('created_at', [$date['start_date'], $date['end_date']])->sum('total_leave_days');

                $leaves_pending  = LocalLeave::where('employee_id', '=', $request->employee_id)->where('leave_type_id', $leave_type->id)->where('status', 'Pending')->whereBetween('created_at', [$date['start_date'], $date['end_date']])->sum('total_leave_days');
            } else {
                // Leave day
                $leaves_used   = LocalLeave::where('employee_id', '=', $request->employee_id)->where('leave_type_id', $leave_type->id)->where('status', 'Approved')->whereBetween('created_at', [$date['start_date'], $date['end_date']])->sum('total_leave_days');

                $leaves_pending  = LocalLeave::where('employee_id', '=', $request->employee_id)->where('leave_type_id', $leave_type->id)->where('status', 'Pending')->whereBetween('created_at', [$date['start_date'], $date['end_date']])->sum('total_leave_days');
            }

            $total_leave_days = !empty($startDate->diff($endDate)) ? $startDate->diff($endDate)->days : 0;

            $return = $leave_type->days - $leaves_used;
            if ($total_leave_days > $return) {
                return redirect()->back()->with('error', __('You are not eligible for leave.'));
            }

            if (!empty($leaves_pending) && $leaves_pending + $total_leave_days > $return) {
                return redirect()->back()->with('error', __('Multiple leave entry is pending.'));
            }

            if ($leave_type->days >= $total_leave_days) {
                $leave    = new LocalLeave();
                if (\Auth::user()->type == "employee") {
                    $leave->employee_id = $request->employee_id;
                } else {
                    $leave->employee_id = $request->employee_id;
                }
                $leave->leave_type_id    = $request->leave_type_id;
                $leave->applied_on       = date('Y-m-d');
                $leave->start_date       = $request->start_date;
                $leave->end_date         = $request->end_date;
                $leave->total_leave_days = $total_leave_days;
                $leave->leave_reason     = $request->leave_reason;
                $leave->remark           = $request->remark;
                $leave->status           = 'Pending';
                $leave->created_by       = \Auth::user()->creatorId();

                $leave->save();

                // Google celander
                if ($request->get('synchronize_type')  == 'google_calender') {

                    $type = 'leave';
                    $request1 = new GoogleEvent();
                    $request1->title = !empty(\Auth::user()->getLeaveType($leave->leave_type_id)) ? \Auth::user()->getLeaveType($leave->leave_type_id)->title : '';
                    $request1->start_date = $request->start_date;
                    $request1->end_date = $request->end_date;

                    Utility::addCalendarData($request1, $type);
                }

                return redirect()->route('leave.index')->with('success', __('Leave  successfully created.'));
            } else {
                return redirect()->back()->with('error', __('Leave type ' . $leave_type->name . ' is provide maximum ' . $leave_type->days . "  days please make sure your selected days is under " . $leave_type->days . ' days.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function store(Request $request)
    {
        $setings = Utility::settings();

        if (\Auth::user()->can('Create Leave')) {
            if ($request->leave_type_id == 5) {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'employee_id' => 'required',
                        'leave_type_id' => 'required',
                        'start_date' => 'required',
                        'end_date' => 'required',
                        'leave_reason' => 'required',
                        'half_day_type' => 'nullable|in:full_day,morning,afternoon', 
                        'cc_email_id' => 'required|array',
                        'cc_email_id.*' => 'exists:employees,id',
                        // 'managers' => 'array',
                        // 'managers.*' => 'exists:employees,id',
                        'leave_time' => 'required',
                    ]
                );
            }else{
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'employee_id' => 'required',
                        'leave_type_id' => 'required',
                        'start_date' => 'required',
                        'end_date' => 'required',
                        'leave_reason' => 'required',
                        'half_day_type' => 'nullable|in:full_day,morning,afternoon', 
                        'cc_email_id' => 'required|array',
                        'cc_email_id.*' => 'exists:employees,id',
                        //'managers' => 'array',
                        // 'managers.*' => 'exists:employees,id',
                    ]
                ); 
            }
            
            


            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }


            if ($request->leave_type_id == 5) {

                $startDate = \Carbon\Carbon::parse($request->start_date)->toDateString();
                $today = \Carbon\Carbon::today()->toDateString();

                if ($startDate == $today) {
                    return redirect()->back()->with('error', __('You cannot apply for Early Leave on the same day.'));
                }


                $alreadyAppliedThisMonth = LocalLeave::where('employee_id', $request->employee_id)
                    ->where('leave_type_id', 5)
                    ->whereMonth('start_date', '=', \Carbon\Carbon::parse($request->start_date)->month)
                    ->whereYear('start_date', '=', \Carbon\Carbon::parse($request->start_date)->year)
                    ->whereNotIn('status', ['Rejected', 'Cancelled'])
                    ->exists();

                if ($alreadyAppliedThisMonth) {
                    return redirect()->back()->with('error', __('You have already applied for Early Leave this month.'));
                }
            }

            
            $leaveTypeDetails=LeaveType::where('id', $request->leave_type_id)->first();
            
            // If leave type is "Optional Holiday", validate only optional holiday date
            if (Str::contains(Str::lower($leaveTypeDetails->title), 'optional holiday')) {
                $isOptionalHoliday = Holiday::where('is_optional', 1)
                    ->whereDate('start_date', $request->start_date)
                    ->exists();

                if (!$isOptionalHoliday) {
                    return redirect()->back()->with('error', __('You can only apply Optional Holiday on declared optional holiday dates.'));
                }else{
                    $leaveDate = \Carbon\Carbon::parse($request->start_date);

                    $financialYear = \App\Models\FinancialYear::where('start_date', '<=', $leaveDate)
                        ->where('end_date', '>=', $leaveDate)
                        ->first();

                    if ($financialYear) {
                        $alreadyTaken = LocalLeave::where('employee_id', $request->employee_id)
                            ->where('leave_type_id', $request->leave_type_id)
                            ->whereIn('status', ['Approved'])
                            ->whereBetween('start_date', [$financialYear->start_date, $financialYear->end_date])
                            ->exists();

                        if ($alreadyTaken) {
                            return redirect()->back()->with('error', __('You have already taken an Optional Holiday in financial year ') . $financialYear->year_range . '.');
                        }
                    }

                    
                }
            }


            

            $leave_type = LeaveType::find($request->leave_type_id);

            $startDate = new \DateTime($request->start_date);
            $endDate = new \DateTime($request->end_date);
            $endDate->add(new \DateInterval('P1D')); // Include end date in the range

            // Calculate total leave days excluding weekends (Saturday and Sunday)
            
            $total_leave_days = $this->getTotalLeaveDays($request->start_date, $request->end_date,$request->leave_type_id,$request->half_day_type);

            $date = Utility::AnnualLeaveCycle();

            /* Manager Find List START */

            $employees = Employee::where('user_id', '=', \Auth::user()->id)->first();

            $defaultManagerList = [];

            if (!empty($employees->manages_id)) {
                $decoded = json_decode($employees->manages_id, true); // Convert JSON string to PHP array
                $defaultManagerList = is_array($decoded) ? array_map('intval', $decoded) : [];
            }

            /* Manager Find List END */


            if (\Auth::user()->type == 'employee') {
                // Leave day calculation for employee
                $leaves_used = LocalLeave::where('employee_id', '=', $request->employee_id)
                    ->where('leave_type_id', $leave_type->id)
                    ->where('status', 'Approved')
                    ->whereBetween('created_at', [$date['start_date'], $date['end_date']])
                    ->sum('total_leave_days');

                $leaves_pending = LocalLeave::where('employee_id', '=', $request->employee_id)
                    ->where('leave_type_id', $leave_type->id)
                    ->where('status', 'Pending')
                    ->whereBetween('created_at', [$date['start_date'], $date['end_date']])
                    ->sum('total_leave_days');
            } else {
                // Leave day calculation for others (e.g., manager/admin)
                $leaves_used = LocalLeave::where('employee_id', '=', $request->employee_id)
                    ->where('leave_type_id', $leave_type->id)
                    ->where('status', 'Approved')
                    ->whereBetween('created_at', [$date['start_date'], $date['end_date']])
                    ->sum('total_leave_days');

                $leaves_pending = LocalLeave::where('employee_id', '=', $request->employee_id)
                    ->where('leave_type_id', $leave_type->id)
                    ->where('status', 'Pending')
                    ->whereBetween('created_at', [$date['start_date'], $date['end_date']])
                    ->sum('total_leave_days');
            }

            $return = $leave_type->days - $leaves_used;

            if ($total_leave_days > $return) {
                return redirect()->back()->with('error', __('You are not eligible for leave.'));
            }

            if (!empty($leaves_pending) && $leaves_pending + $total_leave_days > $return) {
                return redirect()->back()->with('error', __('Multiple leave entries are pending.'));
            }

            if ($leave_type->days >= $total_leave_days) {
                $leave = new LocalLeave();
                if (\Auth::user()->type == "employee") {
                    $leave->employee_id = $request->employee_id;
                } else {
                    $leave->employee_id = $request->employee_id;
                }

                // Store the selected CC emails as an array of employee IDs
                $cc_email_id = $request->cc_email_id ? $request->cc_email_id : [];
                

                // echo "<pre>";print_r($cc_email_id);exit;

                $leave->leave_type_id = $request->leave_type_id;
                $leave->applied_on = date('Y-m-d');
                $leave->start_date = $request->start_date;
                $leave->end_date = $request->end_date;
                $leave->total_leave_days = $total_leave_days;
                $leave->leave_reason = $request->leave_reason;
                $leave->remark = $request->remark;
                // $leave->status = 'Pending';

                // Set status based on the button clicked (draft or submit)
                if ($request->status == 'draft') {
                    $leave->status = 'Draft';  // Set status to 'Draft' if the save as draft button is clicked
                } else {
                    if($request->leave_type_id == 5){
                        $leave->status = 'Pre-Approved'; // Default status if not a draft
                    }else{
                        if(empty($defaultManagerList)){
                            $leave->status = 'Manager_Approved';
                        }else{
                            $leave->status = 'Pending';
                        }
                    }
                    
                }
                $leave->created_by = \Auth::user()->creatorId();
                $leave->half_day_type = $request->half_day_type; // Store the half_day_type in the database
                $leave->cc_email = $cc_email_id;
                $leave->early_time = $request->leave_time;
                $leave->save();



                // Save each assigned manager in the leave_managers table
                $managers = $defaultManagerList ? $defaultManagerList : [];
                foreach ($managers as $managerId) {
                    LeaveManager::create([
                        'leave_id' => $leave->id,
                        'manager_id' => $managerId,
                    ]);
                }

                // Google Calendar sync
                // if ($request->get('synchronize_type') == 'google_calender') {
                //     $type = 'leave';
                //     $request1 = new GoogleEvent();
                //     $request1->title = !empty(\Auth::user()->getLeaveType($leave->leave_type_id)) ? \Auth::user()->getLeaveType($leave->leave_type_id)->title : '';
                //     $request1->start_date = $request->start_date;
                //     $request1->end_date = $request->end_date;

                //     Utility::addCalendarData($request1, $type);
                // }    

                

                if($leave->status == 'Pending' || $leave->status == 'Pre-Approved' || $leave->status == 'Manager_Approved'){
                    $employee = Employee::where('id', $leave->employee_id)
                                ->where('created_by', '=', \Auth::user()->creatorId())
                                ->first();
                    $leavetype = LeaveType::find($leave->leave_type_id);


                    // Data to be passed into the email view
                    $leaveDate = "";

                    $formattedStartDate = \Carbon\Carbon::parse($leave->start_date)->format('d/m/Y');
                    $formattedEndDate = \Carbon\Carbon::parse($leave->end_date)->format('d/m/Y');

                    if($leave->start_date == $leave->end_date){
                        $leaveDate = $formattedStartDate;
                    }else{

                        if($total_leave_days > 1){
                            $leaveDate = $formattedStartDate." To ".$formattedEndDate." [".$total_leave_days." Days ]";
                        }else{
                            $leaveDate = $formattedStartDate." To ".$formattedEndDate;
                        }
                        
                    }

                    if($setings['is_email_trigger'] === 'on'){

                        $GetManagers = LeaveManager::where('leave_id', $leave->id)->pluck('manager_id')->toArray();
                        $managers = $GetManagers;

                        $ManagersEmails = Employee::whereIn('id', $managers)->pluck('email')->toArray();

                        $emails = Employee::whereIn('id', $leave->cc_email)->pluck('email')->toArray();

                        $emails[] = $setings['CFO_EMAIL'];
                        $emails[] = $employee->email;

                        

                        if ($leave->leave_type_id == 5 || empty($managers)) {
                            $AllManagersEmails = $setings['DIRECTOR_EMAIL'];
                            foreach ($ManagersEmails as $Email) {
                                $emails[] = $Email;
                            }
                        }else{
                            $AllManagersEmails = $ManagersEmails;
                        }


                        $data = [
                            'employeeName' => $employee->name,
                            'leaveId' => $leave->id,
                            'leaveType' => $leavetype->title,
                            'leaveFullHalfDay' => $this->getLeaveFullHalfDay($leave->half_day_type),
                            'half_day_type' =>$leave->half_day_type,
                            'appliedOn' => $leave->remark,
                            'leaveDate' => $leaveDate,
                            'leaveReason' => $leave->leave_reason,
                            'status' => $leave->status,
                            'remark' => $leave->remark,
                            'total_leave_days' => $total_leave_days,
                            'toEmail' => $AllManagersEmails,
                            //'toEmail' => 'ai@miraclecloud-technology.com',
                            'fromEmail' => $employee->email,
                            'fromNameEmail' => $employee->name,
                            'replyTo' => $employee->email,
                            'replyToName' => $employee->name,
                            'leaveTime' => $leave->early_time,
                        ];


                        if ($leave->leave_type_id == 5) {
                            
                            Mail::send('email.leave-early', $data, function ($message) use ($data,$emails) {
                                $subjectTxt = $data['leaveType']." on ".$data["leaveDate"];
                                $message->to($data["toEmail"])  // Manager’s email address
                                        ->subject($subjectTxt)
                                        ->from($data["fromEmail"], $data["fromNameEmail"])
                                        ->replyTo($data["replyTo"], $data["replyToName"])
                                        ->cc($emails);
                            });
                        }else{

                            if(empty($managers)){
                                $bladeName = 'email.leave-request-hr-Head';
                            }else{
                                $bladeName = 'email.leave-request-hr';
                            }
                            Mail::send($bladeName, $data, function ($message) use ($data,$emails) {
                                $subjectTxt = $data['leaveType']." Request on ".$data["leaveDate"];
                                $message->to($data["toEmail"])  // Manager’s email address
                                        ->subject($subjectTxt)
                                        ->from($data["fromEmail"], $data["fromNameEmail"])
                                        ->replyTo($data["replyTo"], $data["replyToName"])
                                        ->cc($emails);
                            }); 
                        }
                    }
                    
                    
                }


                return redirect()->route('leave.index')->with('success', __('Leave successfully created.'));
            } else {
                return redirect()->back()->with('error', __('Leave type ' . $leave_type->name . ' allows a maximum of ' . $leave_type->days . ' days. Please ensure your selected days are under ' . $leave_type->days . ' days.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    // Show leave review form
    public function reviewLeave($id)
    {
        $leave = LocalLeave::findOrFail($id);

        $employee  = Employee::find($leave->employee_id);
        $leavetype = LeaveType::find($leave->leave_type_id);

        return view('leave.review_page', compact('employee', 'leavetype', 'leave'));
    }

    public function show(LocalLeave $leave)
    {
        return redirect()->route('leave.index');
    }

    public function edit(LocalLeave $leave)
    {
        if (\Auth::user()->can('Edit Leave') || $leave->status === 'Draft') {
            if ($leave->created_by == \Auth::user()->creatorId()) {
                if (Auth::user()->type == 'employee') {
                    $employees = Employee::where('employee_id', '=', \Auth::user()->creatorId())->first();;
                } else {
                    $employees = Employee::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
                }

                // $employees  = Employee::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
                // $leavetypes = LeaveType::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('title', 'id');
                $leavetypes      = LeaveType::where('created_by', '=', \Auth::user()->creatorId())->get();

                // echo "<pre>";print_r($leave);exit;

                $employeesList = Employee::where('created_by', \Auth::user()->creatorId())
                                // ->where('is_manager', 1)
                                ->where('user_id', '!=', \Auth::user()->id)
                                ->orderBy('name', 'asc')
                                ->pluck('name', 'id');

                $managerList = Employee::where('created_by', \Auth::user()->creatorId())
                            ->where('is_manager', 1)
                            ->where('user_id', '!=', \Auth::user()->id)
                            ->orderBy('name', 'asc')
                            ->pluck('name', 'id');

                // Fetch assigned managers for this leave
                $assignedManagers = LeaveManager::where('leave_id', $leave->id)->pluck('manager_id')->toArray();

                return view('leave.edit', compact('leave', 'employees', 'leavetypes', 'employeesList','managerList','assignedManagers'));
            } else {
                return response()->json(['error' => __('Permission denied.')], 401);
            }
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function update(Request $request, $leave)
    {
        $setings = Utility::settings();
        
        $leave = LocalLeave::find($leave);
        if (\Auth::user()->can('Edit Leave') || 
                (Auth::user()->type == 'employee' && ($leave->status === 'Draft' || $leave->status === 'Pending'))) {
            if ($leave->created_by == Auth::user()->creatorId()) {
                if ($request->leave_type_id == 5) {
                    $validator = \Validator::make(
                        $request->all(),
                        [
                            'employee_id' => 'required',
                            'leave_type_id' => 'required',
                            'start_date' => 'required',
                            'end_date' => 'required',
                            'leave_reason' => 'required',
                            'half_day_type' => 'nullable|in:full_day,morning,afternoon', // Validate half_day_type
                            'cc_email_id' => 'nullable|array', // Allow cc_email_id as an array of IDs
                            'cc_email_id.*' => 'exists:employees,id', // Ensure each value is a valid employee ID
                            'leave_time' => 'required',
                            //'managers' => 'array',
                            //'managers.*' => 'exists:employees,id',
                        ]
                    );
                }else{
                    $validator = \Validator::make(
                        $request->all(),
                        [
                            'employee_id' => 'required',
                            'leave_type_id' => 'required',
                            'start_date' => 'required',
                            'end_date' => 'required',
                            'leave_reason' => 'required',
                            'half_day_type' => 'nullable|in:full_day,morning,afternoon', // Validate half_day_type
                            'cc_email_id' => 'nullable|array', // Allow cc_email_id as an array of IDs
                            'cc_email_id.*' => 'exists:employees,id', // Ensure each value is a valid employee ID
                            //'managers' => 'array',
                            //'managers.*' => 'exists:employees,id',
                        ]
                    );
                }
                

                
            

                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();
                    return redirect()->back()->with('error', $messages->first());
                }


                if ($request->leave_type_id == 5) {

                    $startDate = \Carbon\Carbon::parse($request->start_date)->toDateString();
                    $today = \Carbon\Carbon::today()->toDateString();

                    if ($startDate == $today) {
                        return redirect()->back()->with('error', __('You cannot apply for Early Leave on the same day.'));
                    }

                    $alreadyAppliedThisMonth = LocalLeave::where('employee_id', $request->employee_id)
                        ->where('leave_type_id', 5)
                        ->whereMonth('start_date', '=', \Carbon\Carbon::parse($request->start_date)->month)
                        ->whereYear('start_date', '=', \Carbon\Carbon::parse($request->start_date)->year)
                        ->whereNotIn('status', ['Rejected', 'Cancelled'])
                        ->exists();

                    if ($alreadyAppliedThisMonth) {
                        return redirect()->back()->with('error', __('You have already applied for Early Leave this month.'));
                    }

                }


                $leaveTypeDetails=LeaveType::where('id', $request->leave_type_id)->first();
            
                // If leave type is "Optional Holiday", validate only optional holiday date
                if (Str::contains(Str::lower($leaveTypeDetails->title), 'optional holiday')) {
                    $isOptionalHoliday = Holiday::where('is_optional', 1)
                        ->whereDate('start_date', $request->start_date)
                        ->exists();

                    if (!$isOptionalHoliday) {
                        return redirect()->back()->with('error', __('You can only apply Optional Holiday on declared optional holiday dates.'));
                    }else{
                        $leaveDate = \Carbon\Carbon::parse($request->start_date);

                        $financialYear = \App\Models\FinancialYear::where('start_date', '<=', $leaveDate)
                            ->where('end_date', '>=', $leaveDate)
                            ->first();

                        if ($financialYear) {
                            $alreadyTaken = LocalLeave::where('employee_id', $request->employee_id)
                                ->where('leave_type_id', $request->leave_type_id)
                                ->whereIn('status', ['Approved'])
                                ->whereBetween('start_date', [$financialYear->start_date, $financialYear->end_date])
                                ->exists();

                            if ($alreadyTaken) {
                                return redirect()->back()->with('error', __('You have already taken an Optional Holiday in financial year ') . $financialYear->year_range . '.');
                            }
                        }

                        
                    }
                }

                $leave_type = LeaveType::find($request->leave_type_id);
                $employee = Employee::where('employee_id', '=', $leave->employee_id)->first();

                
                $total_leave_days = $this->getTotalLeaveDays($request->start_date, $request->end_date,$request->leave_type_id,$request->half_day_type);

                $date = Utility::AnnualLeaveCycle();

                if (\Auth::user()->type == 'employee') {
                    // Leave day calculation for employee
                    $leaves_used = LocalLeave::whereNotIn('id', [$leave->id])
                        ->where('employee_id', '=', $employee->id)
                        ->where('leave_type_id', $leave_type->id)
                        ->where('status', 'Approved')
                        ->whereBetween('created_at', [$date['start_date'], $date['end_date']])
                        ->sum('total_leave_days');

                    $leaves_pending = LocalLeave::whereNotIn('id', [$leave->id])
                        ->where('employee_id', '=', $employee->id)
                        ->where('leave_type_id', $leave_type->id)
                        ->where('status', 'Pending')
                        ->whereBetween('created_at', [$date['start_date'], $date['end_date']])
                        ->sum('total_leave_days');
                } else {
                    // Leave day calculation for others (admin or manager)
                    $leaves_used = LocalLeave::whereNotIn('id', [$leave->id])
                        ->where('employee_id', '=', $request->employee_id)
                        ->where('leave_type_id', $leave_type->id)
                        ->where('status', 'Approved')
                        ->whereBetween('created_at', [$date['start_date'], $date['end_date']])
                        ->sum('total_leave_days');

                    $leaves_pending = LocalLeave::whereNotIn('id', [$leave->id])
                        ->where('employee_id', '=', $request->employee_id)
                        ->where('leave_type_id', $leave_type->id)
                        ->where('status', 'Pending')
                        ->whereBetween('created_at', [$date['start_date'], $date['end_date']])
                        ->sum('total_leave_days');
                }

                $return = $leave_type->days - $leaves_used;

                if ($total_leave_days > $return) {
                    return redirect()->back()->with('error', __('You are not eligible for leave.'));
                }

                if (!empty($leaves_pending) && $leaves_pending + $total_leave_days > $return) {
                    return redirect()->back()->with('error', __('Multiple leave entries are pending.'));
                }

                if ($leave_type->days >= $total_leave_days) {

                    /* Manager Find List START */

                    $employees = Employee::where('user_id', '=', \Auth::user()->id)->first();

                    $defaultManagerList = [];

                    if (!empty($employees->manages_id)) {
                        $decoded = json_decode($employees->manages_id, true); // Convert JSON string to PHP array
                        $defaultManagerList = is_array($decoded) ? array_map('intval', $decoded) : [];
                    }

                    /* Manager Find List END */


                    // Store the selected CC employee IDs as an array
                    $cc_email_ids = $request->cc_email_id ? $request->cc_email_id : [];
                    $managers = $defaultManagerList ? $defaultManagerList : [];
                    // $employeeFind=Employee::find(\Auth::user()->email);
                    $employeeFind = Employee::where('email', \Auth::user()->email)->first();
                    

                    // Update the leave with the new values
                    $leave->employee_id = (\Auth::user()->type == 'employee') ? $employeeFind->id : $request->employee_id;
                    $leave->leave_type_id = $request->leave_type_id;
                    $leave->start_date = $request->start_date;
                    $leave->end_date = $request->end_date;
                    $leave->total_leave_days = $total_leave_days;
                    $leave->leave_reason = $request->leave_reason;
                    $leave->remark = $request->remark;
                    $leave->half_day_type = $request->half_day_type; // Store the updated half_day_type in the database
                    $leave->cc_email = $cc_email_ids; // Store the CC emails (employee IDs) as an array
                    $leave->early_time = $request->leave_time;



                    // Clear previous managers
                    \DB::table('leave_managers')->where('leave_id', $leave->id)->delete();

                    // Insert updated manager list
                    $insertData = [];
                    foreach ($managers as $managerId) {
                        $insertData[] = [
                            'leave_id' => $leave->id,
                            'manager_id' => $managerId,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                    \DB::table('leave_managers')->insert($insertData);



                    // Set status based on the button clicked (draft or submit)
                    if ($request->status == 'draft') {
                        $leave->status = 'Draft';  // Set status to 'Draft' if the save as draft button is clicked
                        $leave->save();
                    } else {
                        if($request->leave_type_id == 5){
                            $leave->status = 'Pre-Approved'; // Default status if not a draft
                        }else{

                            if(empty($defaultManagerList)){
                                $leave->status = 'Manager_Approved';
                            }else{
                                $leave->status = 'Pending';
                            }
                        }
                        $leave->save();
                        /* **********************  Email Send  Start ********************** */

                        // Data to be passed into the email view
                        $leaveDate = "";

                        $formattedStartDate = \Carbon\Carbon::parse($leave->start_date)->format('d/m/Y');
                        $formattedEndDate = \Carbon\Carbon::parse($leave->end_date)->format('d/m/Y');

                        if($leave->start_date == $leave->end_date){
                            $leaveDate = $formattedStartDate;
                        }else{

                            

                            if($total_leave_days > 1){
                                $leaveDate = $formattedStartDate." To ".$formattedEndDate." [".$total_leave_days." Days ]";
                            }else{
                                $leaveDate = $formattedStartDate." To ".$formattedEndDate;
                            }
                            
                        }

                        $leavetype = LeaveType::find($leave->leave_type_id);

                        if($setings['is_email_trigger'] === 'on'){

                            $GetManagers = LeaveManager::where('leave_id', $leave->id)->pluck('manager_id')->toArray();
                            $managers = $GetManagers;

                            $ManagersEmails = Employee::whereIn('id', $managers)->pluck('email')->toArray();

                            $emails = Employee::whereIn('id', $leave->cc_email)->pluck('email')->toArray();

                            $emails[] = $setings['CFO_EMAIL'];
                            // $emails[] = $employee->email;

                            

                            if ($leave->leave_type_id == 5 || empty($managers)) {
                                $AllManagersEmails = $setings['DIRECTOR_EMAIL'];
                                foreach ($ManagersEmails as $Email) {
                                    $emails[] = $Email;
                                }
                            }else{
                                $AllManagersEmails = $ManagersEmails;
                            }


                            $data = [
                                'employeeName' => $employee->name,
                                'leaveId' => $leave->id,
                                'leaveType' => $leavetype->title,
                                'leaveFullHalfDay' => $this->getLeaveFullHalfDay($leave->half_day_type),
                                'half_day_type' =>$leave->half_day_type,
                                'appliedOn' => $leave->remark,
                                'leaveDate' => $leaveDate,
                                'leaveReason' => $leave->leave_reason,
                                'status' => $leave->status,
                                'remark' => $leave->remark,
                                'total_leave_days' => $total_leave_days,
                                'toEmail' => $AllManagersEmails,
                                //'toEmail' => 'ai@miraclecloud-technology.com',
                                'fromEmail' => $employee->email,
                                'fromNameEmail' => $employee->name,
                                'replyTo' => $employee->email,
                                'replyToName' => $employee->name,
                                'leaveTime' => $leave->early_time,
                            ];

                            $emails = Employee::whereIn('id', $leave->cc_email)->pluck('email')->toArray();

                            $emails[] = $setings['CFO_EMAIL'];
                            // $emails[] = $employee->email;

                            if ($leave->leave_type_id == 5) {
                                Mail::send('email.leave-early', $data, function ($message) use ($data,$emails) {
                                    $subjectTxt = $data['leaveType']." Request on ".$data["leaveDate"];
                                    $message->to($data["toEmail"])  // Manager’s email address
                                            ->subject($subjectTxt)
                                            ->from($data["fromEmail"], $data["fromNameEmail"])
                                            ->replyTo($data["replyTo"], $data["replyToName"])
                                            ->cc($emails);
                                });
                            }else{

                                if(empty($managers)){
                                    $bladeName = 'email.leave-request-hr-Head';
                                }else{
                                    $bladeName = 'email.leave-request-hr';
                                }
                                Mail::send($bladeName, $data, function ($message) use ($data,$emails) {
                                    $subjectTxt = $data['leaveType']." Request on ".$data["leaveDate"];
                                    $message->to($data["toEmail"])  // Manager’s email address
                                            ->subject($subjectTxt)
                                            ->from($data["fromEmail"], $data["fromNameEmail"])
                                            ->replyTo($data["replyTo"], $data["replyToName"])
                                            ->cc($emails);
                                });
                            }
                        }
                        
                       

                        /* **********************  Email Send  End ********************** */
                    }
                   
                    

                    return redirect()->route('leave.index')->with('success', __('Leave successfully updated.'));
                } else {
                    return redirect()->back()->with('error', __('Leave type ' . $leave_type->name . ' is allowed for a maximum of ' . $leave_type->days . " days. Please make sure your selected days are under " . $leave_type->days . ' days.'));
                }
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }




    public function destroy(LocalLeave $leave)
    {
        if (\Auth::user()->can('Delete Leave') || $leave->status === 'Draft') {
            if ($leave->created_by == \Auth::user()->creatorId()) {
                $leave->delete();

                return redirect()->route('leave.index')->with('success', __('Leave successfully deleted.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function action($id)
    {
        $leave     = LocalLeave::find($id);
        $employee  = Employee::find($leave->employee_id);
        $leavetype = LeaveType::find($leave->leave_type_id);

        // ✅ Fetch leave manager list for this leave
        $leaveManagers = LeaveManager::where('leave_id', $leave->id)
                        ->with('manager') 
                        ->get();


        return view('leave.action', compact('employee', 'leavetype', 'leave','leaveManagers'));
    }


    private function getLeaveFullHalfDay($halfDayType)
    {
        switch($halfDayType) {
            case 'full_day':
                return 'Full Day';
            case 'morning':
                return 'First Half (Morning)';
            case 'afternoon':
                return 'Second Half (Afternoon)';
            default:
                return 'Not Specified';  // Default if no matching type is found
        }
    }

    public function changeaction(Request $request)
    {
        $leave = LocalLeave::find($request->leave_id);

        $leave->status = $request->status;
        $leave->remark = $request->remark;
        if ($leave->status == 'Approved') {
            $startDate = new \DateTime($leave->start_date);
            $endDate = new \DateTime($leave->end_date);
            $endDate->add(new \DateInterval('P1D'));  // Adjust end date to include the last day

            // Calculate total leave days excluding Saturdays and Sundays
            $total_leave_days = 0;
            $currentDate = $startDate;

            while ($currentDate < $endDate) {
                if ($currentDate->format('N') < 6) { // Exclude Saturday (6) and Sunday (7)
                    $total_leave_days++;
                }
                $currentDate->modify('+1 day');
            }

            $leave->total_leave_days = $total_leave_days;
            $leave->status = 'Approved';
        }

        $leave->save();

        // Twilio Notification (optional code, commented out for now)
        /*$setting = Utility::settings(\Auth::user()->creatorId());
        $emp = Employee::find($leave->employee_id);
        if (isset($setting['twilio_leave_approve_notification']) && $setting['twilio_leave_approve_notification'] == 1) {
            $uArr = ['leave_status' => $leave->status];
            Utility::send_twilio_msg($emp->phone, 'leave_approve_reject', $uArr);
        }*/

        // Email notification (sending email based on leave status)
        $setings = Utility::settings();
        if ($setings['leave_status'] == 1) {
            $employee = Employee::where('id', $leave->employee_id)->first();

            // $uArr = [
            //     'leave_status_name' => $employee->name,
            //     'leave_status' => $request->status,
            //     'leave_reason' => $leave->leave_reason,
            //     'leave_start_date' => $leave->start_date,
            //     'leave_end_date' => $leave->end_date,
            //     'total_leave_days' => $leave->total_leave_days,
            // ];

            // $resp = Utility::sendEmailTemplate('leave_status', [$employee->email], $uArr);

            // Data to be passed into the email view
            $leaveDate = "";

            $formattedStartDate = \Carbon\Carbon::parse($leave->start_date)->format('d/m/Y');
            $formattedEndDate = \Carbon\Carbon::parse($leave->end_date)->format('d/m/Y');

            if($leave->start_date == $leave->end_date){
                $leaveDate = $formattedStartDate;
            }else{

                $startDate = \Carbon\Carbon::parse($leave->start_date);
                $endDate = \Carbon\Carbon::parse($leave->end_date);
                $total_leave_days = $startDate->diffInDays($endDate);

                if($total_leave_days > 1){
                    $leaveDate = $formattedStartDate." To ".$formattedEndDate." [".$leave->total_leave_days." Days ]";
                }else{
                    $leaveDate = $formattedStartDate." To ".$formattedEndDate;
                }
                
            }

            $leavetype = LeaveType::find($leave->leave_type_id);

            // $fromEmail='ai@miraclecloud-technology.com';
            // $fromName='MCT USER';
            if($setings['is_email_trigger'] === 'on'){

                $fromEmail=$setings['DIRECTOR_EMAIL'];
                $fromName='Ravi Brahmbhatt';

                $data = [
                    'employeeName' => $employee->name,
                    'leaveType' => $leavetype->title,
                    'leaveFullHalfDay' => $this->getLeaveFullHalfDay($leave->half_day_type),
                    'appliedOn' => $leave->remark,
                    'leaveDate' => $leaveDate,
                    'leaveReason' => $leave->leave_reason,
                    'status' => $leave->status,
                    'remark' => $leave->remark,
                    'total_leave_days' => $leave->total_leave_days,
                    'toEmail' => $employee->email,
                    'fromEmail' => $fromEmail,
                    'fromNameEmail' => $fromName,
                    'replyTo' => $fromEmail,
                    'replyToName' => $fromName,
                ];

            

                if($request->status == 'Approved'){
                    $emailTemp='email.leave-approved';
                }else{
                    $emailTemp='email.leave-rejected';
                }

                $GetManagers = LeaveManager::where('leave_id', $leave->id)->pluck('manager_id')->toArray();
                $managers = $GetManagers;

                $ManagersEmails = Employee::whereIn('id', $managers)->pluck('email')->toArray();

                
                


                $emails = Employee::whereIn('id', $leave->cc_email)->pluck('email')->toArray();

                $emails[] = $fromEmail;

                $emails[] = $setings['CFO_EMAIL'];

                if(!empty($managers)){
                    foreach ($ManagersEmails as $Email) {
                        $emails[] = $Email;
                    }
                }

            
            
                Mail::send($emailTemp, $data, function ($message) use ($data,$emails) {
                    $subjectTxt = $data['leaveType']." Request on ".$data["leaveDate"];
                    $message->to($data["toEmail"])  // Manager’s email address
                            ->subject($subjectTxt)
                            ->from($data["fromEmail"], $data["fromNameEmail"])
                            ->replyTo($data["replyTo"], $data["replyToName"])
                            ->cc($emails);  // CC email address
                });
            }
            
            if($request->leave_page == 'index'){
                return redirect()->route('leave.index')->with('success', __('Leave status successfully updated.') . 
                ((!empty($resp) && $resp['is_success'] == false && !empty($resp['error'])) ? 
                '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));
            }else{
                return redirect()->route('leave.review', ['id' => $request->leave_id])
                        ->with('success', __('Leave status successfully updated.') . 
                            ((!empty($resp) && $resp['is_success'] == false && !empty($resp['error'])) ? 
                            '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));
            }
            
        }

        if($request->leave_page == 'index'){
            return redirect()->route('leave.review', ['id' => $request->leave_id])->with('success', __('Leave status successfully updated.'));
        }else{

        }
        
    }


    public function jsoncount(Request $request)
    {
        // Get active financial year
        $financialYear = \DB::table('financial_years')
            ->where('is_active', 1)
            ->first();

        // If no active year, fallback to default cycle
        $startDate = $financialYear->start_date ?? Utility::AnnualLeaveCycle()['start_date'];
        $endDate   = $financialYear->end_date ?? Utility::AnnualLeaveCycle()['end_date'];

        $leave_counts = LeaveType::select(
                \DB::raw('COALESCE(SUM(leaves.total_leave_days),0) AS total_leave'),
                'leave_types.title',
                'leave_types.code',
                'leave_types.days',
                'leave_types.id'
            )
            ->leftJoin('leaves', function ($join) use ($request, $startDate, $endDate) {
                $join->on('leaves.leave_type_id', '=', 'leave_types.id')
                    ->where('leaves.employee_id', '=', $request->employee_id)
                    ->where('leaves.status', '=', 'Approved')
                    ->whereBetween('leaves.created_at', [$startDate, $endDate]);
            })
            ->where('leave_types.created_by', '=', \Auth::user()->creatorId())
            ->groupBy('leave_types.id')
            ->get();

        return $leave_counts;
    }

    public function export(Request $request)
    {
        $name = 'Leave' . date('Y-m-d i:h:s');
        $data = Excel::download(new LeaveExport(), $name . '.xlsx');

        return $data;
    }

    public function calender(Request $request)
    {
        $created_by = Auth::user()->creatorId();
        $Meetings = LocalLeave::where('created_by', $created_by)->get();
        $today_date = date('m');
        $current_month_event = LocalLeave::select('id', 'start_date', 'employee_id', 'created_at')->whereRaw('MONTH(start_date)=' . $today_date)->get();

        $arrMeeting = [];

        foreach ($Meetings as $meeting) {
            $arr['id']        = $meeting['id'];
            $arr['employee_id']     = $meeting['employee_id'];
            // $arr['leave_type_id']     = date('Y-m-d', strtotime($meeting['start_date']));
        }

        $leaves = LocalLeave::where('created_by', '=', \Auth::user()->creatorId())->get();
        if (\Auth::user()->type == 'employee') {
            $user     = \Auth::user();
            $employee = Employee::where('user_id', '=', $user->id)->first();
            $leaves   = LocalLeave::where('employee_id', '=', $employee->id)->get();
        } else {
            $leaves = LocalLeave::where('created_by', '=', \Auth::user()->creatorId())->get();
        }

        return view('leave.calender', compact('leaves'));
    }

    public function get_leave_data(Request $request)
    {
        $arrayJson = [];
        if ($request->get('calender_type') == 'google_calender') {
            $type = 'leave';
            $arrayJson =  Utility::getCalendarData($type);
        } else {
            $data = LocalLeave::get();

            foreach ($data as $val) {
                $end_date = date_create($val->end_date);
                date_add($end_date, date_interval_create_from_date_string("1 days"));
                $arrayJson[] = [
                    "id" => $val->id,
                    "title" => !empty(\Auth::user()->getLeaveType($val->leave_type_id)) ? \Auth::user()->getLeaveType($val->leave_type_id)->title : '',
                    "start" => $val->start_date,
                    "end" => date_format($end_date, "Y-m-d H:i:s"),
                    "className" => $val->color,
                    "textColor" => '#FFF',
                    "allDay" => true,
                    "url" => route('leave.action', $val['id']),
                ];
            }
        }

        return $arrayJson;
    }

    public function cancelView($id)
    {
        $leave = LocalLeave::findOrFail($id);
        return view('leave.cancel', compact('leave'));
    }

    public function cancelStore(Request $request, $id)
    {
        $leave = LocalLeave::findOrFail($id);

        $request->validate([
            'cancel_reason' => 'required',
            'other_reason' => 'required_if:cancel_reason,Other'
        ]);

        $reason = $request->cancel_reason === 'Other' ? $request->other_reason : $request->cancel_reason;

        $leave->status = 'Cancelled';
        $leave->remark_cancelled = 'Cancelled: ' . $reason;
        $leave->save();

        $employee = Employee::where('employee_id', '=', $leave->employee_id)->first();



        // Data to be passed into the email view
        $leaveDate = "";

        $formattedStartDate = \Carbon\Carbon::parse($leave->start_date)->format('d/m/Y');
        $formattedEndDate = \Carbon\Carbon::parse($leave->end_date)->format('d/m/Y');

        if($leave->start_date == $leave->end_date){
            $leaveDate = $formattedStartDate;
        }else{

            

            if($leave->total_leave_days > 1){
                $leaveDate = "Cancelled: Leave Application for".$employee->name." ".$formattedStartDate." to ".$formattedEndDate;
            }else{
                $leaveDate = "Cancelled: Leave Application for".$employee->name." ".$formattedStartDate;
            }
            
        }

        $leavetype = LeaveType::find($leave->leave_type_id);
        $data = [
            'employeeName' => $employee->name,
            'leaveId' => $leave->id,
            'leaveType' => $leavetype->title,
            'leaveDate' => $leaveDate,
            'leaveFullHalfDay' => $this->getLeaveFullHalfDay($leave->half_day_type),
            'appliedOn' => $leave->remark,
            'leaveReason' => $leave->leave_reason,
            'startDate' => $leave->start_date,
            'endDate' => $leave->end_date,
            'status' => $leave->status,
            'remark' => $leave->remark,
            'remark_cancelled' => $leave->remark_cancelled,
            'toEmail' => 'rmb@miraclecloud-technology.com',
            //'toEmail' => 'ai@miraclecloud-technology.com',
            'fromEmail' => $employee->email,
            'fromNameEmail' => $employee->name,
            'replyTo' => $employee->email,
            'replyToName' => $employee->name,
        ];

        $emails = Employee::whereIn('id', $leave->cc_email)->pluck('email')->toArray();

        $emails[] = 'nkalma@miraclecloud-technology.com';
        $emails[] = $employee->email;



        Mail::send('email.leave-Cancelled', $data, function ($message) use ($data,$emails) {
            $subjectTxt = $data['leaveType']." Request on ".$data["leaveDate"];
            $message->to($data["toEmail"])  // Manager’s email address
                    ->subject($subjectTxt)
                    ->from($data["fromEmail"], $data["fromNameEmail"])
                    ->replyTo($data["replyTo"], $data["replyToName"])
                    ->cc($emails);
        });

        // Optional: trigger email to approver/admin

        return redirect()->route('leave.index')->with('success', __('Leave application cancelled.'));
    }
}
