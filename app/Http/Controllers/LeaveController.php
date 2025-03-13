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


class LeaveController extends Controller
{
    public function index()
    {

        if (\Auth::user()->can('Manage Leave')) {
            if (\Auth::user()->type == 'employee') {
                $user     = \Auth::user();
                $employee = Employee::where('user_id', '=', $user->id)->first();
                $leaves   = LocalLeave::where('employee_id', '=', $employee->id)->orderBy('applied_on', 'desc')->get();
            } else {
                // $leaves = LocalLeave::where('created_by', '=', \Auth::user()->creatorId())->with(['employees', 'leaveType'])->get();
                $leaves = LocalLeave::where('created_by', '=', \Auth::user()->creatorId())
                            ->where('status', '!=', 'Draft')  // Exclude 'Draft' status
                            ->with(['employees', 'leaveType'])  // Eager load related models
                            ->orderByRaw("FIELD(status, 'Pending') DESC")
                            ->orderBy('applied_on', 'desc')
                            ->get();
            }

            foreach ($leaves as $leave) {
                if ($leave->total_leave_days == 0) {
                    // $startDate = \Carbon\Carbon::parse($leave->start_date);
                    // $endDate = \Carbon\Carbon::parse($leave->end_date);
                    $leave->total_leave_days = $this->getTotalLeaveDays($leave->start_date, $leave->end_date);
                }
            }

            return view('leave.index', compact('leaves'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
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

            return view('leave.create', compact('employees', 'leavetypes', 'leavetypes_days', 'employeesList'));
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
        if (\Auth::user()->can('Create Leave')) {
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
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $leave_type = LeaveType::find($request->leave_type_id);

            // Check if the leave type is "work from home", and set total_leave_days to 0 if true
            /*if ($leave_type->title == 'Work from home (WFH)') {
                $total_leave_days = 0;
            } else {*/
                $startDate = new \DateTime($request->start_date);
                $endDate = new \DateTime($request->end_date);
                $endDate->add(new \DateInterval('P1D')); // Include end date in the range

                // Calculate total leave days excluding weekends (Saturday and Sunday)
                $total_leave_days = 0;
                $currentDate = $startDate;

                while ($currentDate < $endDate) {
                    if ($currentDate->format('N') < 6) { // Exclude Saturday (6) and Sunday (7)
                        $total_leave_days++;
                    }
                    $currentDate->modify('+1 day');
                }

                // Adjust for half-day leave
                if ($request->half_day_type != 'full_day') {
                    $total_leave_days = 0.5; // If it's a half day, adjust total leave days accordingly
                }
            /*}*/

            $date = Utility::AnnualLeaveCycle();

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
                    $leave->status = 'Pending'; // Default status if not a draft
                }
                $leave->created_by = \Auth::user()->creatorId();
                $leave->half_day_type = $request->half_day_type; // Store the half_day_type in the database
                $leave->cc_email = $cc_email_id;

                $leave->save();

                // Google Calendar sync
                // if ($request->get('synchronize_type') == 'google_calender') {
                //     $type = 'leave';
                //     $request1 = new GoogleEvent();
                //     $request1->title = !empty(\Auth::user()->getLeaveType($leave->leave_type_id)) ? \Auth::user()->getLeaveType($leave->leave_type_id)->title : '';
                //     $request1->start_date = $request->start_date;
                //     $request1->end_date = $request->end_date;

                //     Utility::addCalendarData($request1, $type);
                // }    

                $total_leave_days = $this->getTotalLeaveDays($leave->start_date, $leave->end_date);

                if($leave->status == 'Pending'){
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

                    $data = [
                        'employeeName' => $employee->name,
                        'leaveId' => $leave->id,
                        'leaveType' => $leavetype->title,
                        'leaveFullHalfDay' => $this->getLeaveFullHalfDay($leave->half_day_type),
                        'appliedOn' => $leave->remark,
                        'leaveDate' => $leaveDate,
                        'leaveReason' => $leave->leave_reason,
                        'status' => $leave->status,
                        'remark' => $leave->remark,
                        'total_leave_days' => $total_leave_days,
                        // 'toEmail' => 'somit@miraclecloud-technology.com',
                        'toEmail' => 'prakashn@miraclecloud-technology.com',
                        'fromEmail' => $employee->email,
                        'fromNameEmail' => $employee->name,
                        'replyTo' => $employee->email,
                        'replyToName' => $employee->name,
                    ];

                    $emails = Employee::whereIn('id', $leave->cc_email)->pluck('email')->toArray();

                    // $emails[] = $employee->email;

                    /*Mail::send('email.leave-request', $data, function ($message) use ($data,$emails) {
                        $subjectTxt = $data['leaveType']." Request on ".$data["leaveDate"];
                        $message->to($data["toEmail"])  // Manager’s email address
                                ->subject($subjectTxt)
                                ->from($data["fromEmail"], $data["fromNameEmail"])
                                ->replyTo($data["replyTo"], $data["replyToName"])
                                ->cc($emails);
                    });*/

                    Mail::send('email.leave-request-hr', $data, function ($message) use ($data,$emails) {
                        $subjectTxt = $data['leaveType']." Request on ".$data["leaveDate"];
                        $message->to($data["toEmail"])  // Manager’s email address
                                ->subject($subjectTxt)
                                ->from($data["fromEmail"], $data["fromNameEmail"])
                                ->replyTo($data["replyTo"], $data["replyToName"]);
                                ->cc($emails);
                    });
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

                $employeesList = Employee::where('user_id', '!=', \Auth::user()->id)->first();

                return view('leave.edit', compact('leave', 'employees', 'leavetypes', 'employeesList'));
            } else {
                return response()->json(['error' => __('Permission denied.')], 401);
            }
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function update(Request $request, $leave)
    {
        $leave = LocalLeave::find($leave);
        if (\Auth::user()->can('Edit Leave') || 
                (Auth::user()->type == 'employee' && ($leave->status === 'Draft' || $leave->status === 'Pending'))) {
            if ($leave->created_by == Auth::user()->creatorId()) {
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
                    ]
                );

                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();
                    return redirect()->back()->with('error', $messages->first());
                }

                $leave_type = LeaveType::find($request->leave_type_id);
                $employee = Employee::where('employee_id', '=', \Auth::user()->creatorId())->first();

                // If the leave type is "work from home", set total_leave_days to 0
                /*if ($leave_type->title == 'Work from home (WFH)') {
                    $total_leave_days = 0;
                } else {*/
                    $startDate = new \DateTime($request->start_date);
                    $endDate = new \DateTime($request->end_date);
                    $endDate->add(new \DateInterval('P1D')); // Adjust end date to include the last day

                    // Calculate total leave days excluding weekends (Saturday and Sunday)
                    $total_leave_days = 0;
                    $currentDate = $startDate;

                    // Loop through each date from start to end date
                    while ($currentDate < $endDate) {
                        if ($currentDate->format('N') < 6) { // Exclude Saturday (6) and Sunday (7)
                            $total_leave_days++;
                        }
                        $currentDate->modify('+1 day'); // Increment to the next day
                    }

                    // Adjust total leave days for half-day type
                    if ($request->half_day_type != 'full_day') {
                        $total_leave_days = 0.5; // If it's a half day, adjust the total leave days
                    }
                /*}*/

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

                    // Store the selected CC employee IDs as an array
                    $cc_email_ids = $request->cc_email_id ? $request->cc_email_id : [];


                    // Update the leave with the new values
                    $leave->employee_id = (\Auth::user()->type == 'employee') ? $employee->id : $request->employee_id;
                    $leave->leave_type_id = $request->leave_type_id;
                    $leave->start_date = $request->start_date;
                    $leave->end_date = $request->end_date;
                    $leave->total_leave_days = $total_leave_days;
                    $leave->leave_reason = $request->leave_reason;
                    $leave->remark = $request->remark;
                    $leave->half_day_type = $request->half_day_type; // Store the updated half_day_type in the database
                    $leave->cc_email = $cc_email_ids; // Store the CC emails (employee IDs) as an array


                    // Set status based on the button clicked (draft or submit)
                    if ($request->status == 'draft') {
                        $leave->status = 'Draft';  // Set status to 'Draft' if the save as draft button is clicked
                    } else {
                        $leave->status = 'Pending'; // Default status if not a draft
                    }

                    $leave->save();

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

        return view('leave.action', compact('employee', 'leavetype', 'leave'));
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

            $fromEmail='ai@miraclecloud-technology.com';
            $fromName='MCT USER';

            // $fromEmail='rmb@miraclecloud-technology.com';
            // $fromName='Ravi Brahmbhatt';

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

            // Send email with the data
            /*Mail::send('email.leave-request', $data, function ($message) {
                $message->to('prakashn@miraclecloud-technology.com')
                        ->subject('Leave Request Details');
            });*/

            if($request->status == 'Approved'){
                $emailTemp='email.leave-approved';
            }else{
                $emailTemp='email.leave-rejected';
            }

            $emails = Employee::whereIn('id', $leave->cc_email)->pluck('email')->toArray();

            $emails[] = $fromEmail;

            Mail::send($emailTemp, $data, function ($message) use ($data,$emails) {
                $subjectTxt = $data['leaveType']." Request on ".$data["leaveDate"];
                $message->to($data["toEmail"])  // Manager’s email address
                        ->subject($subjectTxt)
                        ->from($data["fromEmail"], $data["fromNameEmail"])
                        ->replyTo($data["replyTo"], $data["replyToName"])
                        ->cc($emails);  // CC email address
            });
            
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
        $date = Utility::AnnualLeaveCycle();
        $leave_counts = LeaveType::select(\DB::raw('COALESCE(SUM(leaves.total_leave_days),0) AS total_leave, leave_types.title, leave_types.days,leave_types.id'))
            ->leftjoin(
                'leaves',
                function ($join) use ($request, $date) {
                    $join->on('leaves.leave_type_id', '=', 'leave_types.id');
                    $join->where('leaves.employee_id', '=', $request->employee_id);
                    $join->where('leaves.status', '=', 'Approved');
                    $join->whereBetween('leaves.created_at', [$date['start_date'],$date['end_date']]);
                }
                )->where('leave_types.created_by', '=', \Auth::user()->creatorId())->groupBy('leave_types.id')->get();

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
}
