<?php

namespace App\Http\Controllers;

use App\Imports\AttendanceImport;
use App\Models\AttendanceEmployee;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\IpRestrict;
use App\Models\User;
use App\Models\Utility;
use App\Models\AttendanceBreak;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AttendanceEmployeeController extends Controller
{
    public function indexOld(Request $request)
    {
        if (\Auth::user()->can('Manage Attendance')) {
            $branch = Branch::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $branch->prepend('Select Branch', '');

            $department = Department::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $department->prepend('All', '');

            if (\Auth::user()->type == 'employee') {

                $emp = !empty(\Auth::user()->employee) ? \Auth::user()->employee->id : 0;

                $attendanceEmployee = AttendanceEmployee::where('employee_id', $emp);

                if ($request->type == 'monthly' && !empty($request->month)) {
                    $month = date('m', strtotime($request->month));
                    $year  = date('Y', strtotime($request->month));

                    $start_date = date($year . '-' . $month . '-01');
                    $end_date = date('Y-m-t', strtotime('01-' . $month . '-' . $year));

                    // old date
                    // $end_date   = date($year . '-' . $month . '-t');

                    $attendanceEmployee->whereBetween(
                        'date',
                        [
                            $start_date,
                            $end_date,
                        ]
                    );
                } elseif ($request->type == 'daily' && !empty($request->date)) {
                    $attendanceEmployee->where('date', $request->date);
                } else {
                    $month      = date('m');
                    $year       = date('Y');
                    $start_date = date($year . '-' . $month . '-01');
                    $end_date = date('Y-m-t', strtotime('01-' . $month . '-' . $year));

                    // old date
                    // $end_date   = date($year . '-' . $month . '-t');

                    $attendanceEmployee->whereBetween(
                        'date',
                        [
                            $start_date,
                            $end_date,
                        ]
                    );
                }
                $attendanceEmployee = $attendanceEmployee->orderBy('created_at', 'desc')->get();
            } else {
                $employee = Employee::select('id')->where('created_by', \Auth::user()->creatorId());
                if (!empty($request->branch)) {
                    $employee->where('branch_id', $request->branch);
                }

                if (!empty($request->department)) {
                    $employee->where('department_id', $request->department);
                }

                $employee = $employee->get()->pluck('id');

                $attendanceEmployee = AttendanceEmployee::whereIn('employee_id', $employee);

                if ($request->type == 'monthly' && !empty($request->month)) {
                    $month = date('m', strtotime($request->month));
                    $year  = date('Y', strtotime($request->month));

                    $start_date = date($year . '-' . $month . '-01');
                    $end_date = date('Y-m-t', strtotime('01-' . $month . '-' . $year));

                    // old date
                    // $end_date   = date($year . '-' . $month . '-t');

                    $attendanceEmployee->whereBetween(
                        'date',
                        [
                            $start_date,
                            $end_date,
                        ]
                    );
                } elseif ($request->type == 'daily' && !empty($request->date)) {
                    $attendanceEmployee->where('date', $request->date);
                } else {
                    $month      = date('m');
                    $year       = date('Y');
                    $start_date = date($year . '-' . $month . '-01');
                    $end_date = date('Y-m-t', strtotime('01-' . $month . '-' . $year));

                    // olda date
                    // $end_date   = date($year . '-' . $month . '-t');

                    $attendanceEmployee->whereBetween(
                        'date',
                        [
                            $start_date,
                            $end_date,
                        ]
                    );
                }


                $attendanceEmployee = $attendanceEmployee->orderBy('created_at', 'desc')->get();
            }

            // Add Total Break Log Calculation
            $attendanceEmployee = $attendanceEmployee->map(function ($attendance) {
                // Fetch all completed breaks for this attendance record
                $breakLogs = $attendance->breaks()->whereNotNull('break_end')->get();
                $totalSeconds = 0;

                foreach ($breakLogs as $break) {
                    if (!empty($break->break_start) && !empty($break->break_end)) {
                        try {

                            $startTime = $break->break_start_date.' '.$break->break_start; 
                            $endTime = $break->break_end_date.' '.$break->break_end;
                            $start = Carbon::parse($startTime);
                            $end = Carbon::parse($endTime);

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

            // echo "<pre>";print_r($attendanceEmployee);exit;

            return view('attendance.index', compact('attendanceEmployee', 'branch', 'department'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function index(Request $request)
    {
        if (\Auth::user()->can('Manage Attendance')) {

            // Employee dropdown data
            $employeeList = Employee::where('created_by', \Auth::user()->creatorId())
                ->orderBy('name', 'asc')
                ->pluck('name', 'id');

            if (\Auth::user()->type == 'employee') {
                $emp = !empty(\Auth::user()->employee) ? \Auth::user()->employee->id : 0;
                $attendanceEmployee = AttendanceEmployee::where('employee_id', $emp);
            } else {
                $employee = Employee::select('id')->where('created_by', \Auth::user()->creatorId());

                // ✅ Replace branch/department filter with employee filter
                if (!empty($request->employee_id)) {
                    $employee->where('id', $request->employee_id);
                }

                $employee = $employee->get()->pluck('id');
                $attendanceEmployee = AttendanceEmployee::whereIn('employee_id', $employee);
            }

            // ✅ Apply date filters
            if ($request->type == 'monthly' && !empty($request->month)) {
                $month = date('m', strtotime($request->month));
                $year  = date('Y', strtotime($request->month));
                $start_date = date($year . '-' . $month . '-01');
                $end_date   = date('Y-m-t', strtotime('01-' . $month . '-' . $year));
                $attendanceEmployee->whereBetween('date', [$start_date, $end_date]);
            } elseif ($request->type == 'daily' && !empty($request->date)) {
                $attendanceEmployee->where('date', $request->date);
            } else {
                $month = date('m');
                $year  = date('Y');
                $start_date = date($year . '-' . $month . '-01');
                $end_date   = date('Y-m-t', strtotime('01-' . $month . '-' . $year));
                $attendanceEmployee->whereBetween('date', [$start_date, $end_date]);
            }

            $attendanceEmployee = $attendanceEmployee->orderBy('created_at', 'desc')->get();

            // ✅ Add break calculation (unchanged)
            $attendanceEmployee = $attendanceEmployee->map(function ($attendance) {
                $breakLogs = $attendance->breaks()->whereNotNull('break_end')->get();
                $totalSeconds = 0;
                foreach ($breakLogs as $break) {
                    if (!empty($break->break_start) && !empty($break->break_end)) {
                        try {
                            $startTime = $break->break_start_date.' '.$break->break_start; 
                            $endTime   = $break->break_end_date.' '.$break->break_end;
                            $start = Carbon::parse($startTime);
                            $end   = Carbon::parse($endTime);
                            if ($end->greaterThan($start)) {
                                $totalSeconds += $start->diffInSeconds($end);
                            }
                        } catch (\Exception $e) {
                            \Log::error("Break calculation failed for Attendance ID: {$attendance->id} - " . $e->getMessage());
                        }
                    }
                }
                $attendance->totalBreakDuration = sprintf(
                    '%02d:%02d:%02d',
                    intdiv($totalSeconds, 3600),
                    intdiv($totalSeconds % 3600, 60),
                    $totalSeconds % 60
                );
                return $attendance;
            });

            return view('attendance.index', compact('attendanceEmployee', 'employeeList'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    

    public function create()
    {
        if (\Auth::user()->can('Create Attendance')) {
            // $employees = User::where('created_by', '=', Auth::user()->creatorId())->where('type', '=', "employee")->get()->pluck('name', 'id');
            $employees          = Employee::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');

            return view('attendance.create', compact('employees'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function store(Request $request)
    {
        if (\Auth::user()->can('Create Attendance')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'employee_id' => 'required',
                    'date' => 'required',
                    'clock_in' => 'required',
                    'clock_out' => 'required',
                    'checkout_date' => 'required|date',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $startTime  = Utility::getValByName('company_start_time');
            $endTime    = Utility::getValByName('company_end_time');
            $attendance = AttendanceEmployee::where('employee_id', '=', $request->employee_id)->where('date', '=', $request->date)->where('clock_out', '=', '00:00:00')->get()->toArray();
            if ($attendance) {
                return redirect()->route('attendanceemployee.index')->with('error', __('Employee Attendance Already Created.'));
            } else {
                $date = date("Y-m-d");

                $totalLateSeconds = strtotime($request->clock_in) - strtotime($date . $startTime);

                $hours = floor($totalLateSeconds / 3600);
                $mins  = floor($totalLateSeconds / 60 % 60);
                $secs  = floor($totalLateSeconds % 60);
                $late  = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

                //early Leaving
                $totalEarlyLeavingSeconds = strtotime($date . $endTime) - strtotime($request->clock_out);
                $hours                    = floor($totalEarlyLeavingSeconds / 3600);
                $mins                     = floor($totalEarlyLeavingSeconds / 60 % 60);
                $secs                     = floor($totalEarlyLeavingSeconds % 60);
                $earlyLeaving             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);


                if (strtotime($request->clock_out) > strtotime($date . $endTime)) {
                    //Overtime
                    $totalOvertimeSeconds = strtotime($request->clock_out) - strtotime($date . $endTime);
                    $hours                = floor($totalOvertimeSeconds / 3600);
                    $mins                 = floor($totalOvertimeSeconds / 60 % 60);
                    $secs                 = floor($totalOvertimeSeconds % 60);
                    $overtime             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
                } else {
                    $overtime = '00:00:00';
                }

                $clockInDateTime  = Carbon::parse($request->date . ' ' . $request->clock_in);
                $clockOutDateTime = Carbon::parse($request->checkout_date . ' ' . $request->clock_out);

                if ($clockOutDateTime->greaterThan($clockInDateTime)) {
                    $checkoutSeconds = $clockInDateTime->diffInSeconds($clockOutDateTime);
                    $hours   = floor($checkoutSeconds / 3600);
                    $minutes = floor(($checkoutSeconds % 3600) / 60);
                    $seconds = $checkoutSeconds % 60;
                    $checkoutTimeDiff = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
                } else {
                    $checkoutTimeDiff = '00:00:00';
                }

                // checkout_date

                $employeeAttendance                = new AttendanceEmployee();
                $employeeAttendance->employee_id   = $request->employee_id;
                $employeeAttendance->date          = $request->date;
                $employeeAttendance->device_type_clockin        = 'desktop';
                $employeeAttendance->clock_in      = $request->clock_in . ':00';
                $employeeAttendance->clock_out     = $request->clock_out . ':00';
                $employeeAttendance->late          = $late;
                $employeeAttendance->checkout_date = $request->checkout_date;
                $employeeAttendance->early_leaving = $earlyLeaving;
                $employeeAttendance->overtime      = $overtime;
                $employeeAttendance->total_rest    = '00:00:00';
                $employeeAttendance->status        = 'Present';
                $employeeAttendance->created_by    = \Auth::user()->creatorId();
                $employeeAttendance->checkout_time_diff    = $checkoutTimeDiff;
                $employeeAttendance->work_from_home   = $request->has('work_from_home') ? 1 : 0;
                $employeeAttendance->save();

                return redirect()->route('attendanceemployee.index')->with('success', __('Employee attendance successfully created.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
    public function show(Request $request)
    {
        // return redirect()->back();
        return redirect()->route('attendanceemployee.index');
    }

    public function edit($id)
    {
        if (\Auth::user()->can('Edit Attendance')) {
            $attendanceEmployee = AttendanceEmployee::where('id', $id)->first();
            $employees          = Employee::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');

            return view('attendance.edit', compact('attendanceEmployee', 'employees'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    // public function update(Request $request, $id)
    // {
    //     if (\Auth::user()->type == 'company' || \Auth::user()->type == 'hr') {
    //         $employeeId      = AttendanceEmployee::where('employee_id', $request->employee_id)->first();
    //         $check = AttendanceEmployee::where('employee_id', '=', $request->employee_id)->where('date', $request->date)->first();

    //         $startTime = Utility::getValByName('company_start_time');
    //         $endTime   = Utility::getValByName('company_end_time');

    //         $clockIn = $request->clock_in;
    //         $clockOut = $request->clock_out;

    //         if ($clockIn) {
    //             $status = "present";
    //         } else {
    //             $status = "leave";
    //         }

    //         $totalLateSeconds = strtotime($clockIn) - strtotime($startTime);

    //         $hours = floor($totalLateSeconds / 3600);
    //         $mins  = floor($totalLateSeconds / 60 % 60);
    //         $secs  = floor($totalLateSeconds % 60);
    //         $late  = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

    //         $totalEarlyLeavingSeconds = strtotime($endTime) - strtotime($clockOut);
    //         $hours                    = floor($totalEarlyLeavingSeconds / 3600);
    //         $mins                     = floor($totalEarlyLeavingSeconds / 60 % 60);
    //         $secs                     = floor($totalEarlyLeavingSeconds % 60);
    //         $earlyLeaving             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

    //         if (strtotime($clockOut) > strtotime($endTime)) {
    //             //Overtime
    //             $totalOvertimeSeconds = strtotime($clockOut) - strtotime($endTime);
    //             $hours                = floor($totalOvertimeSeconds / 3600);
    //             $mins                 = floor($totalOvertimeSeconds / 60 % 60);
    //             $secs                 = floor($totalOvertimeSeconds % 60);
    //             $overtime             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
    //         } else {
    //             $overtime = '00:00:00';
    //         }
    //         if ($check->date == date('Y-m-d')) {
    //             $check->update([
    //                 'late' => $late,
    //                 'early_leaving' => ($earlyLeaving > 0) ? $earlyLeaving : '00:00:00',
    //                 'overtime' => $overtime,
    //                 'clock_in' => $clockIn,
    //                 'clock_out' => $clockOut
    //             ]);

    //             return redirect()->route('attendanceemployee.index')->with('success', __('Employee attendance successfully updated.'));
    //         } else {
    //             return redirect()->route('attendanceemployee.index')->with('error', __('You can only update current day attendance'));
    //         }
    //     }

    //     $employeeId      = !empty(\Auth::user()->employee) ? \Auth::user()->employee->id : 0;
    //     $todayAttendance = AttendanceEmployee::where('employee_id', '=', $employeeId)->where('date', date('Y-m-d'))->first();
    //     if(!empty($todayAttendance) && $todayAttendance->clock_out == '00:00:00')
    //     {
    //         $startTime = Utility::getValByName('company_start_time');
    //         $endTime   = Utility::getValByName('company_end_time');
    //         if(Auth::user()->type == 'employee')
    //         {

    //             $date = date("Y-m-d");
    //             $time = date("H:i:s");

    //             //early Leaving
    //             $totalEarlyLeavingSeconds = strtotime($date . $endTime) - time();
    //             $hours                    = floor($totalEarlyLeavingSeconds / 3600);
    //             $mins                     = floor($totalEarlyLeavingSeconds / 60 % 60);
    //             $secs                     = floor($totalEarlyLeavingSeconds % 60);
    //             $earlyLeaving             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

    //             if(time() > strtotime($date . $endTime))
    //             {
    //                 //Overtime
    //                 $totalOvertimeSeconds = time() - strtotime($date . $endTime);
    //                 $hours                = floor($totalOvertimeSeconds / 3600);
    //                 $mins                 = floor($totalOvertimeSeconds / 60 % 60);
    //                 $secs                 = floor($totalOvertimeSeconds % 60);
    //                 $overtime             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
    //             }
    //             else
    //             {
    //                 $overtime = '00:00:00';
    //             }

    //             $attendanceEmployee                = AttendanceEmployee::find($id);
    //             $attendanceEmployee->clock_out     = $time;
    //             $attendanceEmployee->early_leaving = $earlyLeaving;
    //             $attendanceEmployee->overtime      = $overtime;
    //             $attendanceEmployee->save();

    //             return redirect()->route('home')->with('success', __('Employee successfully clock Out.'));
    //         }
    //         else
    //         {
    //             $date = date("Y-m-d");
    //             //late
    //             $totalLateSeconds = strtotime($request->clock_in) - strtotime($date . $startTime);

    //             $hours = floor($totalLateSeconds / 3600);
    //             $mins  = floor($totalLateSeconds / 60 % 60);
    //             $secs  = floor($totalLateSeconds % 60);
    //             $late  = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

    //             //early Leaving
    //             $totalEarlyLeavingSeconds = strtotime($date . $endTime) - strtotime($request->clock_out);
    //             $hours                    = floor($totalEarlyLeavingSeconds / 3600);
    //             $mins                     = floor($totalEarlyLeavingSeconds / 60 % 60);
    //             $secs                     = floor($totalEarlyLeavingSeconds % 60);
    //             $earlyLeaving             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);


    //             if(strtotime($request->clock_out) > strtotime($date . $endTime))
    //             {
    //                 //Overtime
    //                 $totalOvertimeSeconds = strtotime($request->clock_out) - strtotime($date . $endTime);
    //                 $hours                = floor($totalOvertimeSeconds / 3600);
    //                 $mins                 = floor($totalOvertimeSeconds / 60 % 60);
    //                 $secs                 = floor($totalOvertimeSeconds % 60);
    //                 $overtime             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
    //             }
    //             else
    //             {
    //                 $overtime = '00:00:00';
    //             }

    //             $attendanceEmployee                = AttendanceEmployee::find($id);
    //             $attendanceEmployee->employee_id   = $request->employee_id;
    //             $attendanceEmployee->date          = $request->date;
    //             $attendanceEmployee->clock_in      = $request->clock_in;
    //             $attendanceEmployee->clock_out     = $request->clock_out;
    //             $attendanceEmployee->late          = $late;
    //             $attendanceEmployee->early_leaving = $earlyLeaving;
    //             $attendanceEmployee->overtime      = $overtime;
    //             $attendanceEmployee->total_rest    = '00:00:00';

    //             $attendanceEmployee->save();

    //             return redirect()->route('attendanceemployee.index')->with('success', __('Employee attendance successfully updated.'));
    //         }
    //     }
    //     else
    //     {
    //         return redirect()->back()->with('error', __('Employee are not allow multiple time clock in & clock for every day.'));
    //     }
    // }

    public function updateWorkFromHome(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:attendance_employees,id',
            'work_from_home' => 'required|boolean',
        ]);

        $attendance = AttendanceEmployee::find($request->id);
        $attendance->work_from_home = $request->work_from_home;
        $attendance->save();

        return response()->json(['success' => true, 'message' => 'Work from Home status updated successfully.']);
    }


    public function updateTime(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:attendance_employees,id',
            'clock_in' => 'required|date_format:H:i',
            'clock_out' => 'nullable|date_format:H:i',
            'checkout_date' => 'nullable|date',
            'date' => 'nullable|date' // optional: update attendance date
        ]);

        // Permission check - adjust to your needs
        if (!\Auth::user()->can('Edit Attendance') && !in_array(\Auth::user()->type, ['company','hr'])) {
            return response()->json(['success' => false, 'message' => 'Permission denied.'], 403);
        }

        $attendance = AttendanceEmployee::find($request->id);
        if (!$attendance) {
            return response()->json(['success' => false, 'message' => 'Attendance not found.'], 404);
        }

        // Normalize & assign
        $attendance->clock_in = $request->clock_in . ':00';

        if ($request->filled('clock_out')) {
            $attendance->clock_out = $request->clock_out . ':00';
        } else {
            // default
            $attendance->clock_out = '00:00:00';
        }

        // checkout_date: set null if not provided
        $attendance->checkout_date = $request->filled('checkout_date') ? $request->checkout_date : null;

        // optionally update the attendance date if present in request
        if ($request->filled('date')) {
            $attendance->date = $request->date;
        }

        $attendance->save();

        // Recompute checkout_time_diff if possible
        $checkoutTimeDiff = '00:00:00';
        if (
            !empty($attendance->clock_in) && $attendance->clock_in !== '00:00:00' &&
            !empty($attendance->clock_out) && $attendance->clock_out !== '00:00:00' &&
            !empty($attendance->checkout_date)
        ) {
            try {
                $clockInDate = $attendance->date ?? date('Y-m-d');
                $clockInDT = Carbon::parse($clockInDate . ' ' . $attendance->clock_in);
                $clockOutDT = Carbon::parse($attendance->checkout_date . ' ' . $attendance->clock_out);

                // If clock_out datetime is earlier than clock_in, assume next day (adjust if your business logic differs)
                if ($clockOutDT->lessThan($clockInDT)) {
                    $clockOutDT->addDay();
                }

                if ($clockOutDT->greaterThan($clockInDT)) {
                    $sec = $clockInDT->diffInSeconds($clockOutDT);
                    $checkoutTimeDiff = sprintf('%02d:%02d:%02d', intdiv($sec,3600), intdiv($sec % 3600, 60), $sec % 60);
                } else {
                    $checkoutTimeDiff = '00:00:00';
                }
            } catch (\Exception $e) {
                \Log::error("checkout_time_diff calc error for attendance {$attendance->id}: ".$e->getMessage());
                $checkoutTimeDiff = '00:00:00';
            }

            $attendance->checkout_time_diff = $checkoutTimeDiff;
            $attendance->save();
        } else {
            // ensure default if not both values present
            $attendance->checkout_time_diff = '00:00:00';
            $attendance->save();
        }

        // Prepare display values (use your user's formatter if available)
        $clockInDisplay = \Auth::user()
            ? \Auth::user()->timeFormat($attendance->clock_in)
            : Carbon::createFromFormat('H:i:s', $attendance->clock_in)->format('h:i A');

        $clockOutDisplay = ($attendance->clock_out && $attendance->clock_out !== '00:00:00')
            ? (\Auth::user() ? \Auth::user()->timeFormat($attendance->clock_out) : Carbon::createFromFormat('H:i:s', $attendance->clock_out)->format('h:i A'))
            : '00:00';

        $checkoutDateDisplay = $attendance->checkout_date ? Carbon::parse($attendance->checkout_date)->format('Y-m-d') : null;

        return response()->json([
            'success' => true,
            'message' => 'Attendance updated successfully.',
            'clock_in_24' => Carbon::createFromFormat('H:i:s', $attendance->clock_in)->format('H:i'),
            'clock_in_display' => $clockInDisplay,
            'clock_out_24' => $attendance->clock_out ? Carbon::createFromFormat('H:i:s', $attendance->clock_out)->format('H:i') : '00:00',
            'clock_out_display' => $clockOutDisplay,
            'checkout_date' => $checkoutDateDisplay,
            'checkout_time_diff' => $attendance->checkout_time_diff ?? '00:00:00',
        ]);
    }


    public function update(Request $request, $id)
    {
        $settings = Utility::settings();

        date_default_timezone_set($settings['timezone']); // Set timezone

        if (\Auth::user()->type == 'company' || \Auth::user()->type == 'hr') {
            $employeeId      = AttendanceEmployee::where('employee_id', $request->employee_id)->first();
            $check = AttendanceEmployee::where('id', '=', $id)->where('employee_id', '=', $request->employee_id)->first();

            if (!empty($employeeId) || !empty($check)) {
                $startTime = Utility::getValByName('company_start_time');
                $endTime   = Utility::getValByName('company_end_time');

                $clockIn = $request->clock_in;
                $clockOut = $request->clock_out;
                $checkoutDate = $request->checkout_date ?? $request->date;

                if ($clockIn) {
                    $status = "present";
                } else {
                    $status = "leave";
                }

                $totalLateSeconds = strtotime($clockIn) - strtotime($startTime);

                $hours = floor($totalLateSeconds / 3600);
                $mins  = floor($totalLateSeconds / 60 % 60);
                $secs  = floor($totalLateSeconds % 60);
                $late  = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

                $totalEarlyLeavingSeconds = strtotime($endTime) - strtotime($clockOut);
                $hours                    = floor($totalEarlyLeavingSeconds / 3600);
                $mins                     = floor($totalEarlyLeavingSeconds / 60 % 60);
                $secs                     = floor($totalEarlyLeavingSeconds % 60);
                $earlyLeaving             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

                if (strtotime($clockOut) > strtotime($endTime)) {
                    //Overtime
                    $totalOvertimeSeconds = strtotime($clockOut) - strtotime($endTime);
                    $hours                = floor($totalOvertimeSeconds / 3600);
                    $mins                 = floor($totalOvertimeSeconds / 60 % 60);
                    $secs                 = floor($totalOvertimeSeconds % 60);
                    $overtime             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
                } else {
                    $overtime = '00:00:00';
                }

                

                //if ($check->date == date('Y-m-d')) {
                if ($check->date) {
                    // Get clock-in time for the employee
                    $attendanceRecord = AttendanceEmployee::where('id', $id)->first();
                    $clockInTime = $request->clock_in ? $request->clock_in : null;
                    $clockOutTime = $request->clock_out;

                    $clockInDateTime  = Carbon::parse($request->date . ' ' . $clockIn);
                    $clockOutDateTime = Carbon::parse($checkoutDate . ' ' . $clockOut);


                    if ($clockInTime) {
                        //  Calculate Checkout Time Difference
                        if ($clockOutDateTime->greaterThan($clockInDateTime)) {
                            $checkoutSeconds = $clockInDateTime->diffInSeconds($clockOutDateTime);
                            $checkoutTimeDiff = sprintf('%02d:%02d:%02d',
                                floor($checkoutSeconds / 3600),
                                floor(($checkoutSeconds % 3600) / 60),
                                $checkoutSeconds % 60
                            );
                        } else {
                            $checkoutTimeDiff = '00:00:00';
                        }
                    } else {
                        $checkoutTimeDiff = '00:00:00';
                    }
                    
                    $check->update([
                        'late' => $late,
                        'early_leaving' => ($earlyLeaving > 0) ? $earlyLeaving : '00:00:00',
                        'overtime' => $overtime,
                        'clock_in' => $clockIn,
                        'clock_out' => $clockOut,
                        'checkout_date' => $checkoutDate,
                        'date' => $request->date,
                        'checkout_time_diff' => $checkoutTimeDiff,
                        'work_from_home' => $request->has('work_from_home') ? 1 : 0,
                        'is_leave' => $request->has('is_leave') ? 1 : 0
                    ]);

                    return redirect()->route('attendanceemployee.index')->with('success', __('Employee attendance successfully updated.'));
                } else {
                    return redirect()->route('attendanceemployee.index')->with('error', __('You can only update current day attendance.'));
                }
            } else {
                return redirect()->back()->with('error', __('Employee not avaliable'));
            }
        }

        $employeeId      = !empty(\Auth::user()->employee) ? \Auth::user()->employee->id : 0;
        $todayAttendance = AttendanceEmployee::where('employee_id', '=', $employeeId)->where('date', date('Y-m-d'))->first();

        $startTime = Utility::getValByName('company_start_time');
        $endTime   = Utility::getValByName('company_end_time');
        if (Auth::user()->type == 'employee') {

            $date = date("Y-m-d");
            $time = date("H:i:s");

            //early Leaving
            $totalEarlyLeavingSeconds = strtotime($date . $endTime) - time();
            $hours                    = floor($totalEarlyLeavingSeconds / 3600);
            $mins                     = floor($totalEarlyLeavingSeconds / 60 % 60);
            $secs                     = floor($totalEarlyLeavingSeconds % 60);
            $earlyLeaving             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

            if (time() > strtotime($date . $endTime)) {
                //Overtime
                $totalOvertimeSeconds = time() - strtotime($date . $endTime);
                $hours                = floor($totalOvertimeSeconds / 3600);
                $mins                 = floor($totalOvertimeSeconds / 60 % 60);
                $secs                 = floor($totalOvertimeSeconds % 60);
                $overtime             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
            } else {
                $overtime = '00:00:00';
            }



            // Get clock-in time for the employee
            $attendanceRecord = AttendanceEmployee::where('id', $id)->first();
            $clockInTime = $attendanceRecord ? $attendanceRecord->clock_in : null;
            $clockOutTime = date("H:i:s");

            if ($clockInTime) {
                //  Calculate Checkout Time Difference
                $checkoutTimeDiffSeconds = strtotime($clockOutTime) - strtotime($clockInTime);
                if ($checkoutTimeDiffSeconds > 0) {
                    $checkoutHours = floor($checkoutTimeDiffSeconds / 3600);
                    $checkoutMinutes = floor(($checkoutTimeDiffSeconds % 3600) / 60);
                    $checkoutSeconds = floor($checkoutTimeDiffSeconds % 60);
                    $checkoutTimeDiff = sprintf('%02d:%02d:%02d', $checkoutHours, $checkoutMinutes, $checkoutSeconds);
                } else {
                    $checkoutTimeDiff = '00:00:00';
                }
            } else {
                $checkoutTimeDiff = '00:00:00';
            }


            $attendanceEmployee['clock_out']     = $time;
            $attendanceEmployee['early_leaving'] = $earlyLeaving;
            $attendanceEmployee['overtime']      = $overtime;
            $attendanceEmployee['checkout_date']      = $date;
            $attendanceEmployee['checkout_time_diff']      = $checkoutTimeDiff;

            if (!empty($request->date)) {
                $attendanceEmployee['date']       =  $request->date;
            }
            AttendanceEmployee::where('id', $id)->update($attendanceEmployee);

            return redirect()->route('dashboard')->with('success', __('Employee successfully clock Out.'));
        } else {
            $date = date("Y-m-d");
            $clockout_time = date("H:i:s");
            //late
            $totalLateSeconds = strtotime($clockout_time) - strtotime($date . $startTime);

            $hours            = abs(floor($totalLateSeconds / 3600));
            $mins             = abs(floor($totalLateSeconds / 60 % 60));
            $secs             = abs(floor($totalLateSeconds % 60));

            $late  = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

            //early Leaving
            $totalEarlyLeavingSeconds = strtotime($date . $endTime) - strtotime($clockout_time);
            $hours                    = floor($totalEarlyLeavingSeconds / 3600);
            $mins                     = floor($totalEarlyLeavingSeconds / 60 % 60);
            $secs                     = floor($totalEarlyLeavingSeconds % 60);
            $earlyLeaving             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
            

            if (strtotime($clockout_time) > strtotime($date . $endTime)) {
                //Overtime
                $totalOvertimeSeconds = strtotime($clockout_time) - strtotime($date . $endTime);
                $hours                = floor($totalOvertimeSeconds / 3600);
                $mins                 = floor($totalOvertimeSeconds / 60 % 60);
                $secs                 = floor($totalOvertimeSeconds % 60);
                $overtime             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
            } else {
                $overtime = '00:00:00';
            }

            $attendanceEmployee                = AttendanceEmployee::find($id);
            $attendanceEmployee->clock_out     = $clockout_time;
            $attendanceEmployee->late          = $late;
            $attendanceEmployee->early_leaving = $earlyLeaving;
            $attendanceEmployee->overtime      = $overtime;
            $attendanceEmployee->total_rest    = '00:00:00';
            

            $attendanceEmployee->save();

            return redirect()->back()->with('success', __('Employee attendance successfully updated.'));
        }
    }

    public function destroy($id)
    {
        if (\Auth::user()->can('Delete Attendance')) {
            $attendance = AttendanceEmployee::where('id', $id)->first();

            $attendance->delete();

            return redirect()->route('attendanceemployee.index')->with('success', __('Attendance successfully deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    // public function attendance(Request $request)
    // {
    //     $settings = Utility::settings();

    //     if($settings['ip_restrict'] == 'on')
    //     {
    //         $userIp = request()->ip();
    //         $ip     = IpRestrict::where('created_by', \Auth::user()->creatorId())->whereIn('ip', [$userIp])->first();
    //         if(!empty($ip))
    //         {
    //             return redirect()->back()->with('error', __('this ip is not allowed to clock in & clock out.'));
    //         }
    //     }

    //     $employeeId      = !empty(\Auth::user()->employee) ? \Auth::user()->employee->id : 0;
    //     $todayAttendance = AttendanceEmployee::where('employee_id', '=', $employeeId)->where('date', date('Y-m-d'))->first();
    //     if(empty($todayAttendance))
    //     {

    //         $startTime = Utility::getValByName('company_start_time');
    //         $endTime   = Utility::getValByName('company_end_time');

    //         $attendance = AttendanceEmployee::orderBy('id', 'desc')->where('employee_id', '=', $employeeId)->where('clock_out', '=', '00:00:00')->first();

    //         if($attendance != null)
    //         {
    //             $attendance            = AttendanceEmployee::find($attendance->id);
    //             $attendance->clock_out = $endTime;
    //             $attendance->save();
    //         }

    //         $date = date("Y-m-d");
    //         $time = date("H:i:s");

    //         //late
    //         $totalLateSeconds = time() - strtotime($date . $startTime);
    //         $hours            = floor($totalLateSeconds / 3600);
    //         $mins             = floor($totalLateSeconds / 60 % 60);
    //         $secs             = floor($totalLateSeconds % 60);
    //         $late             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

    //         $checkDb = AttendanceEmployee::where('employee_id', '=', \Auth::user()->id)->get()->toArray();


    //         if(empty($checkDb))
    //         {
    //             $employeeAttendance                = new AttendanceEmployee();
    //             $employeeAttendance->employee_id   = $employeeId;
    //             $employeeAttendance->date          = $date;
    //             $employeeAttendance->status        = 'Present';
    //             $employeeAttendance->clock_in      = $time;
    //             $employeeAttendance->clock_out     = '00:00:00';
    //             $employeeAttendance->late          = $late;
    //             $employeeAttendance->early_leaving = '00:00:00';
    //             $employeeAttendance->overtime      = '00:00:00';
    //             $employeeAttendance->total_rest    = '00:00:00';
    //             $employeeAttendance->created_by    = \Auth::user()->id;

    //             $employeeAttendance->save();

    //             return redirect()->route('home')->with('success', __('Employee Successfully Clock In.'));
    //         }
    //         foreach($checkDb as $check)
    //         {


    //             $employeeAttendance                = new AttendanceEmployee();
    //             $employeeAttendance->employee_id   = $employeeId;
    //             $employeeAttendance->date          = $date;
    //             $employeeAttendance->status        = 'Present';
    //             $employeeAttendance->clock_in      = $time;
    //             $employeeAttendance->clock_out     = '00:00:00';
    //             $employeeAttendance->late          = $late;
    //             $employeeAttendance->early_leaving = '00:00:00';
    //             $employeeAttendance->overtime      = '00:00:00';
    //             $employeeAttendance->total_rest    = '00:00:00';
    //             $employeeAttendance->created_by    = \Auth::user()->id;

    //             $employeeAttendance->save();

    //             return redirect()->route('home')->with('success', __('Employee Successfully Clock In.'));

    //         }
    //     }
    //     else
    //     {
    //         return redirect()->back()->with('error', __('Employee are not allow multiple time clock in & clock for every day.'));
    //     }
    // }


    public function employee_clockout(Request $request, $id)
    {
        $settings = Utility::settings();
        date_default_timezone_set($settings['timezone']); // Set timezone

        $employeeId = !empty(\Auth::user()->employee) ? \Auth::user()->employee->id : 0;
        $todayAttendance = AttendanceEmployee::where('employee_id', '=', $employeeId)
                                              ->where('date', date('Y-m-d'))
                                              ->first();

        if (Auth::user()->type == 'employee') {
            $date = date("Y-m-d");
            $time = date("H:i:s");

            // Early Leaving Calculation
            $startTime = Utility::getValByName('company_start_time');
            $endTime = Utility::getValByName('company_end_time');
            $totalEarlyLeavingSeconds = strtotime($date . $endTime) - time();
            $hours = floor($totalEarlyLeavingSeconds / 3600);
            $mins = floor($totalEarlyLeavingSeconds / 60 % 60);
            $secs = floor($totalEarlyLeavingSeconds % 60);
            $earlyLeaving = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

            // Overtime Calculation
            if (time() > strtotime($date . $endTime)) {
                $totalOvertimeSeconds = time() - strtotime($date . $endTime);
                $hours = floor($totalOvertimeSeconds / 3600);
                $mins = floor($totalOvertimeSeconds / 60 % 60);
                $secs = floor($totalOvertimeSeconds % 60);
                $overtime = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
            } else {
                $overtime = '00:00:00';
            }

            // Get attendance record
            $attendanceRecord = AttendanceEmployee::where('id', $id)->first();
            if (!$attendanceRecord) {
                return response()->json(['error' => 'Attendance record not found'], 404);
            }

            $clockInDateTime = $attendanceRecord->date . ' ' . $attendanceRecord->clock_in; 
            $clockOutTime = $time;  // Current time as clock-out time

            // Check if the checkout time is on the next day
            $checkoutDateTime = $date . ' ' . $clockOutTime; // Combine date and time for checkout

            // Calculate checkout time difference using full datetime values
            $checkoutTimeDiffSeconds = strtotime($checkoutDateTime) - strtotime($clockInDateTime);
            if ($checkoutTimeDiffSeconds > 0) {
                // Valid checkout (same day or next day)
                $checkoutHours = floor($checkoutTimeDiffSeconds / 3600);
                $checkoutMinutes = floor(($checkoutTimeDiffSeconds % 3600) / 60);
                $checkoutSeconds = floor($checkoutTimeDiffSeconds % 60);
                $checkoutTimeDiff = sprintf('%02d:%02d:%02d', $checkoutHours, $checkoutMinutes, $checkoutSeconds);
            } else {
                // If the checkout time is invalid (shouldn't happen)
                $checkoutTimeDiff = '00:00:00';
                $date = date('Y-m-d', strtotime($date . ' +1 day'));  // Set checkout date to next day
            }
            
            // Prepare data to update
            $attendanceEmployee = [
                'clock_out' => $time,
                'early_leaving' => $earlyLeaving,
                'overtime' => $overtime,
                'checkout_date' => $date,  // Use the adjusted date if needed
                'checkout_time_diff' => $checkoutTimeDiff,
                'device_type_clockout' => Get_Device_Type(),
            ];

            // Check if a custom date is provided in the request
            if (!empty($request->date)) {
                $attendanceEmployee['date'] = $request->date;
            }

            // Update the attendance record
            AttendanceEmployee::where('id', $id)->update($attendanceEmployee);

            return redirect()->route('dashboard')->with('success', __('Employee successfully clocked out.'));
        } else {
            return response()->json(['error' => 'Attendance record not found'], 404);
        }
    }

    public function employee_clockoutOld(Request $request, $id)
    {
        $settings = Utility::settings();

        date_default_timezone_set($settings['timezone']); // Set timezone


        $employeeId      = !empty(\Auth::user()->employee) ? \Auth::user()->employee->id : 0;
        $todayAttendance = AttendanceEmployee::where('employee_id', '=', $employeeId)->where('date', date('Y-m-d'))->first();

        $startTime = Utility::getValByName('company_start_time');
        $endTime   = Utility::getValByName('company_end_time');
        if (Auth::user()->type == 'employee') {

            $date = date("Y-m-d");
            $time = date("H:i:s");

            //early Leaving
            $totalEarlyLeavingSeconds = strtotime($date . $endTime) - time();
            $hours                    = floor($totalEarlyLeavingSeconds / 3600);
            $mins                     = floor($totalEarlyLeavingSeconds / 60 % 60);
            $secs                     = floor($totalEarlyLeavingSeconds % 60);
            $earlyLeaving             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

            if (time() > strtotime($date . $endTime)) {
                //Overtime
                $totalOvertimeSeconds = time() - strtotime($date . $endTime);
                $hours                = floor($totalOvertimeSeconds / 3600);
                $mins                 = floor($totalOvertimeSeconds / 60 % 60);
                $secs                 = floor($totalOvertimeSeconds % 60);
                $overtime             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
            } else {
                $overtime = '00:00:00';
            }



            // Get clock-in time for the employee
            $attendanceRecord = AttendanceEmployee::where('id', $id)->first();
            $clockInTime = $attendanceRecord ? $attendanceRecord->clock_in : null;
            $clockOutTime = date("H:i:s");

            if ($clockInTime) {
                //  Calculate Checkout Time Difference
                $checkoutTimeDiffSeconds = strtotime($clockOutTime) - strtotime($clockInTime);
                if ($checkoutTimeDiffSeconds > 0) {
                    $checkoutHours = floor($checkoutTimeDiffSeconds / 3600);
                    $checkoutMinutes = floor(($checkoutTimeDiffSeconds % 3600) / 60);
                    $checkoutSeconds = floor($checkoutTimeDiffSeconds % 60);
                    $checkoutTimeDiff = sprintf('%02d:%02d:%02d', $checkoutHours, $checkoutMinutes, $checkoutSeconds);
                } else {
                    $checkoutTimeDiff = '00:00:00';
                }
            } else {
                $checkoutTimeDiff = '00:00:00';
            }


            $attendanceEmployee['clock_out']     = $time;
            $attendanceEmployee['early_leaving'] = $earlyLeaving;
            $attendanceEmployee['overtime']      = $overtime;
            $attendanceEmployee['checkout_date']      = $date;
            $attendanceEmployee['checkout_time_diff']      = $checkoutTimeDiff;

            if (!empty($request->date)) {
                $attendanceEmployee['date']       =  $request->date;
            }
            AttendanceEmployee::where('id', $id)->update($attendanceEmployee);

            return redirect()->route('dashboard')->with('success', __('Employee successfully clock Out.'));
        }else {
            return response()->json(['error' => 'Attendance record not found'], 404);
        }
    }

    public function startBreak(Request $request)
    {
        $settings = Utility::settings();
        $date = date("Y-m-d");
        $time = date("H:i:s");
        date_default_timezone_set($settings['timezone']); // Set timezone
        $attendance = AttendanceEmployee::where('employee_id', $request->employee_id)
                                        ->where('date', today())
                                        ->first();
        // If no attendance is found for the current date, check for the previous day
        if (!$attendance) {
            $previousDate = date('Y-m-d', strtotime($date . ' -1 day'));
            $currentHour = date("H");
            if ($currentHour < Get_MaxCheckOutTime()) {
                $attendance = AttendanceEmployee::where('employee_id', $request->employee_id)
                                        ->where('date', $previousDate)
                                        ->first();
            }
        }

        if (!$attendance) {
            return response()->json(['error' => 'Attendance record not found'], 404);
        }

        // Check if an active break is already open
        if ($attendance->breaks()->whereNull('break_end')->exists()) {
            return response()->json(['error' => 'A break is already active. End the current break first.'], 400);
        }

        

        $break = AttendanceBreak::create([
            'attendance_id' => $attendance->id,
            'break_start' => $time,
            'break_start_date' => $date,
        ]);

        return response()->json(['message' => 'Break started successfully']);
    }

    public function endBreak(Request $request)
    {
        $settings = Utility::settings();
        $date = date("Y-m-d");
        $time = date("H:i:s");
        date_default_timezone_set($settings['timezone']); // Set timezone
        $attendance = AttendanceEmployee::where('employee_id', $request->employee_id)
                                        ->where('date', today())
                                        ->first();
        // If no attendance is found for the current date, check for the previous day
        if (!$attendance) {
            $previousDate = date('Y-m-d', strtotime($date . ' -1 day'));
            $currentHour = date("H");
            if ($currentHour < Get_MaxCheckOutTime()) {
                $attendance = AttendanceEmployee::where('employee_id', $request->employee_id)
                                        ->where('date', $previousDate)
                                        ->first();
            }
        }

        if (!$attendance) {
            return response()->json(['error' => 'Attendance record not found'], 404);
        }

        $break = AttendanceBreak::where('attendance_id', $attendance->id)
                                ->whereNull('break_end')
                                ->orderBy('id', 'desc')
                                ->first();

        if (!$break) {
            return response()->json(['error' => 'No active break found'], 404);
        }

        

        $break->update(['break_end' => $time,'break_end_date' => $date]);

        return response()->json(['message' => 'Break ended successfully']);
    }

    public function attendance(Request $request)
    {
        $settings = Utility::settings();

        date_default_timezone_set($settings['timezone']); // Set timezone

        if (!empty($settings['ip_restrict']) && $settings['ip_restrict'] == 'on') {
            $userIp = request()->ip();
            $ip     = IpRestrict::where('created_by', Auth::user()->creatorId())->whereIn('ip', [$userIp])->first();
            if (empty($ip)) {
                return redirect()->back()->with('error', __('This IP is not allowed to clock in & clock out.'));
            }
        }

        $employeeId = !empty(\Auth::user()->employee) ? \Auth::user()->employee->id : 0;
        $date = date("Y-m-d");
        $time = date("H:i:s");

        // Check if an attendance record already exists for today
        $todayAttendance = AttendanceEmployee::where('employee_id', '=', $employeeId)
            ->where('date', '=', $date)
            ->first();

        if ($todayAttendance) {
            if ($todayAttendance->clock_out != '00:00:00') {
                return redirect()->back()->with('error', __('You have already checked out today. No further check-in allowed.'));
            } else {
                /*
                $todayAttendance->clock_out = $time;
                
                // Calculate total worked hours
                $clockInTime = strtotime($todayAttendance->clock_in);
                $clockOutTime = strtotime($time);
                $totalWorkSeconds = $clockOutTime - $clockInTime;

                $hours = abs(floor($totalWorkSeconds / 3600));
                $mins = abs(floor(($totalWorkSeconds % 3600) / 60));
                $secs = abs(floor($totalWorkSeconds % 60));
                $totalWorkTime = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

                $todayAttendance->overtime = $totalWorkTime; 
                $todayAttendance->save();

                return redirect()->back()->with('success', __('Employee Successfully Clocked Out.'));
                */
            }
        }

        // If no record exists, proceed with clock-in
        $startTime = Utility::getValByName('company_start_time');

        // Calculate lateness
        $expectedStartTime = strtotime($date . ' ' . $startTime);
        $actualClockInTime = strtotime($date . ' ' . $time);
        $totalLateSeconds = max($actualClockInTime - $expectedStartTime, 0);

        $hours = abs(floor($totalLateSeconds / 3600));
        $mins = abs(floor(($totalLateSeconds % 3600) / 60));
        $secs = abs(floor($totalLateSeconds % 60));
        $late = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

        // Insert new clock-in record
        $employeeAttendance = new AttendanceEmployee();
        $employeeAttendance->employee_id = $employeeId;
        $employeeAttendance->date = $date;
        $employeeAttendance->status = 'Present';
        $employeeAttendance->clock_in = $time;
        $employeeAttendance->clock_out = '00:00:00';
        $employeeAttendance->late = $late;
        $employeeAttendance->early_leaving = '00:00:00';
        $employeeAttendance->checkout_date = null;
        $employeeAttendance->overtime = '00:00:00';
        $employeeAttendance->total_rest = '00:00:00';
        $employeeAttendance->created_by = \Auth::user()->id;
        $employeeAttendance->work_from_home = $request->has('work_from_home') ? 1 : 0;
        $employeeAttendance->device_type_clockin = Get_Device_Type();
        $employeeAttendance->save();

        return redirect()->back()->with('success', __('Employee Successfully Clocked In.'));
    }

    public function bulkAttendance(Request $request)
    {
        if (\Auth::user()->can('Create Attendance')) {

            $branch = Branch::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $branch->prepend('Select Branch', '');

            $department = Department::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $department->prepend('Select Department', '');

            $employees = [];
            if (!empty($request->branch) && !empty($request->department)) {
                $employees = Employee::where('created_by', \Auth::user()->creatorId())->where('branch_id', $request->branch)->where('department_id', $request->department)->get();
            }


            return view('attendance.bulk', compact('employees', 'branch', 'department'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function bulkAttendanceData(Request $request)
    {

        if (\Auth::user()->can('Create Attendance')) {
            if (!empty($request->branch) && !empty($request->department)) {
                $startTime = Utility::getValByName('company_start_time');
                $endTime   = Utility::getValByName('company_end_time');
                $date      = $request->date;

                $employees = $request->employee_id;
                $atte      = [];
                foreach ($employees as $employee) {
                    $present = 'present-' . $employee;
                    $in      = 'in-' . $employee;
                    $out     = 'out-' . $employee;
                    $atte[]  = $present;
                    if ($request->$present == 'on') {

                        $in  = date("H:i:s", strtotime($request->$in));
                        $out = date("H:i:s", strtotime($request->$out));

                        $totalLateSeconds = strtotime($in) - strtotime($startTime);

                        $hours = floor($totalLateSeconds / 3600);
                        $mins  = floor($totalLateSeconds / 60 % 60);
                        $secs  = floor($totalLateSeconds % 60);
                        $late  = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

                        //early Leaving
                        $totalEarlyLeavingSeconds = strtotime($endTime) - strtotime($out);
                        $hours                    = floor($totalEarlyLeavingSeconds / 3600);
                        $mins                     = floor($totalEarlyLeavingSeconds / 60 % 60);
                        $secs                     = floor($totalEarlyLeavingSeconds % 60);
                        $earlyLeaving             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);


                        if (strtotime($out) > strtotime($endTime)) {
                            //Overtime
                            $totalOvertimeSeconds = strtotime($out) - strtotime($endTime);
                            $hours                = floor($totalOvertimeSeconds / 3600);
                            $mins                 = floor($totalOvertimeSeconds / 60 % 60);
                            $secs                 = floor($totalOvertimeSeconds % 60);
                            $overtime             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
                        } else {
                            $overtime = '00:00:00';
                        }


                        $attendance = AttendanceEmployee::where('employee_id', '=', $employee)->where('date', '=', $request->date)->first();

                        if (!empty($attendance)) {
                            $employeeAttendance = $attendance;
                        } else {
                            $employeeAttendance              = new AttendanceEmployee();
                            $employeeAttendance->employee_id = $employee;
                            $employeeAttendance->created_by  = \Auth::user()->creatorId();
                        }


                        $employeeAttendance->date          = $request->date;
                        $employeeAttendance->status        = 'Present';
                        $employeeAttendance->clock_in      = $in;
                        $employeeAttendance->clock_out     = $out;
                        $employeeAttendance->late          = $late;
                        $employeeAttendance->early_leaving = ($earlyLeaving > 0) ? $earlyLeaving : '00:00:00';
                        $employeeAttendance->overtime      = $overtime;
                        $employeeAttendance->total_rest    = '00:00:00';
                        $employeeAttendance->save();
                    } else {
                        $attendance = AttendanceEmployee::where('employee_id', '=', $employee)->where('date', '=', $request->date)->first();

                        if (!empty($attendance)) {
                            $employeeAttendance = $attendance;
                        } else {
                            $employeeAttendance              = new AttendanceEmployee();
                            $employeeAttendance->employee_id = $employee;
                            $employeeAttendance->created_by  = \Auth::user()->creatorId();
                        }

                        $employeeAttendance->status        = 'Leave';
                        $employeeAttendance->date          = $request->date;
                        $employeeAttendance->clock_in      = '00:00:00';
                        $employeeAttendance->clock_out     = '00:00:00';
                        $employeeAttendance->late          = '00:00:00';
                        $employeeAttendance->early_leaving = '00:00:00';
                        $employeeAttendance->overtime      = '00:00:00';
                        $employeeAttendance->total_rest    = '00:00:00';
                        $employeeAttendance->save();
                    }
                }

                return redirect()->back()->with('success', __('Employee attendance successfully created.'));
            } else {
                return redirect()->back()->with('error', __('Branch & department field required.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function importFile()
    {
        return view('attendance.import');
    }

    public function import(Request $request)
    {
        $rules = [
            'file' => 'required|mimes:csv,txt,xlsx',
        ];
        $validator = \Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }

        $attendance = (new AttendanceImport())->toArray(request()->file('file'))[0];

        $email_data = [];
        foreach ($attendance as $key => $employee) {
            if ($key != 0) {
                echo "<pre>";
                if ($employee != null && Employee::where('email', $employee[0])->where('created_by', \Auth::user()->creatorId())->exists()) {
                    $email = $employee[0];
                } else {
                    $email_data[] = $employee[0];
                }
            }
        }
        $totalattendance = count($attendance) - 1;
        $errorArray    = [];

        $startTime = Utility::getValByName('company_start_time');
        $endTime   = Utility::getValByName('company_end_time');

        if (!empty($attendanceData)) {
            $errorArray[] = $attendanceData;
        } else {
            foreach ($attendance as $key => $value) {
                if ($key != 0) {
                    $employeeData = Employee::where('email', $value[0])->where('created_by', \Auth::user()->creatorId())->first();
                    // $employeeId = 0;
                    if (!empty($employeeData)) {
                        $employeeId = $employeeData->id;


                        $clockIn = $value[2];
                        $clockOut = $value[3];

                        if ($clockIn) {
                            $status = "present";
                        } else {
                            $status = "leave";
                        }

                        $totalLateSeconds = strtotime($clockIn) - strtotime($startTime);

                        $hours = floor($totalLateSeconds / 3600);
                        $mins  = floor($totalLateSeconds / 60 % 60);
                        $secs  = floor($totalLateSeconds % 60);
                        $late  = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

                        $totalEarlyLeavingSeconds = strtotime($endTime) - strtotime($clockOut);
                        $hours                    = floor($totalEarlyLeavingSeconds / 3600);
                        $mins                     = floor($totalEarlyLeavingSeconds / 60 % 60);
                        $secs                     = floor($totalEarlyLeavingSeconds % 60);
                        $earlyLeaving             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

                        if (strtotime($clockOut) > strtotime($endTime)) {
                            //Overtime
                            $totalOvertimeSeconds = strtotime($clockOut) - strtotime($endTime);
                            $hours                = floor($totalOvertimeSeconds / 3600);
                            $mins                 = floor($totalOvertimeSeconds / 60 % 60);
                            $secs                 = floor($totalOvertimeSeconds % 60);
                            $overtime             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
                        } else {
                            $overtime = '00:00:00';
                        }

                        $check = AttendanceEmployee::where('employee_id', $employeeId)->where('date', $value[1])->first();
                        if ($check) {
                            $check->update([
                                'late' => $late,
                                'early_leaving' => ($earlyLeaving > 0) ? $earlyLeaving : '00:00:00',
                                'overtime' => $overtime,
                                'clock_in' => $value[2],
                                'clock_out' => $value[3]
                            ]);
                        } else {
                            $time_sheet = AttendanceEmployee::create([
                                'employee_id' => $employeeId,
                                'date' => $value[1],
                                'status' => $status,
                                'late' => $late,
                                'early_leaving' => ($earlyLeaving > 0) ? $earlyLeaving : '00:00:00',
                                'overtime' => $overtime,
                                'clock_in' => $value[2],
                                'clock_out' => $value[3],
                                'created_by' => \Auth::user()->id,
                            ]);
                        }
                    }
                } else {
                    $email_data = implode(' And ', $email_data);
                }
            }
            if (!empty($email_data)) {
                return redirect()->back()->with('status', 'this record is not import. ' . '</br>' . $email_data);
            } else {
                if (empty($errorArray)) {
                    $data['status'] = 'success';
                    $data['msg']    = __('Record successfully imported');
                } else {

                    $data['status'] = 'error';
                    $data['msg']    = count($errorArray) . ' ' . __('Record imported fail out of' . ' ' . $totalattendance . ' ' . 'record');


                    foreach ($errorArray as $errorData) {
                        $errorRecord[] = implode(',', $errorData->toArray());
                    }

                    \Session::put('errorArray', $errorRecord);
                }

                return redirect()->back()->with($data['status'], $data['msg']);
            }
        }
    }
}
