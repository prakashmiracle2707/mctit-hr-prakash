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

class LeaveEmployeeController extends Controller
{
    public function index()
    {

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


            $employee = Employee::where('user_id', '=',  \Auth::user()->id)->first();

            $leaveIds = LeaveManager::where('manager_id', $employee->id)
                        ->pluck('leave_id')
                        ->toArray();

            $leaves = $leavesQuery->whereIn('id', $leaveIds) // Add this line
                ->with(['employees', 'leaveType'])
                ->orderByRaw("FIELD(status, 'Pending') DESC")
                ->orderBy('applied_on', 'desc')
                ->get();

            $employee = Employee::where('user_id', '=',  \Auth::user()->id)->first();
            $managerId = $employee->id;

            foreach ($leaves as $leave) {
                // Set manager_status for the current manager
                $managerStatus = $leave->leaveManagers->where('manager_id', $managerId)->first();
                $leave->manager_status = $managerStatus ? $managerStatus->status : 'Pending';
            }

            return view('leaveEmployee.index', compact(
                'leaves',
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

    public function action($id)
    {
        $leave     = LocalLeave::find($id);
        $employee  = Employee::find($leave->employee_id);
        $leavetype = LeaveType::find($leave->leave_type_id);

        // ✅ Fetch leave manager list for this leave
        $leaveManagers = LeaveManager::where('leave_id', $leave->id)
                        ->with('manager') // eager load manager info
                        ->get();

        return view('leaveEmployee.action', compact('employee', 'leavetype', 'leave','leaveManagers'));
    }

    public function quickApproveForm($id)
    {
        $leave = LocalLeave::findOrFail($id);

        $employee = Employee::where('user_id', '=',  \Auth::user()->id)->first();
        $managerId = $employee->id;

        $managerEntry = LeaveManager::where('leave_id', $id)
                        ->where('manager_id', $managerId)
                        ->first();
        return view('leaveEmployee.quick_approve', compact('leave','managerEntry'));
    }

    public function quickApproveAction(Request $request, $id)
    {
        $action = $request->input('action');
        $remark = $request->input('manager_remark');

        if (!in_array($action, ['approve', 'reject'])) {
            return redirect()->back()->with('error', 'Invalid action.');
        }

        $leave = LocalLeave::findOrFail($id);

        // Get the logged-in manager's user ID
        $employee = Employee::where('user_id', \Auth::id())->first();
        $managerId = $employee->id;

        // Update the corresponding record in leave_managers
        $leaveManager = LeaveManager::where('leave_id', $id)
            ->where('manager_id', $managerId)
            ->first();

        if (!$leaveManager) {
            return redirect()->back()->with('error', 'Leave manager record not found.');
        }

        $leaveManager->status = $action === 'approve' ? 'Approved' : 'Reject';
        $leaveManager->remark = $remark;
        $leaveManager->action_date = now();
        $leaveManager->save();

        // After saving, check all managers' statuses
        
        $allManagers = LeaveManager::where('leave_id', $id)->get();

        $PendingCount = $allManagers->where('status', 'Pending')->count();
        $approvedCount = $allManagers->where('status', 'Approved')->count();
        $rejectedCount = $allManagers->where('status', 'Reject')->count();
        $totalManagers = $allManagers->count();

        if ($approvedCount === $totalManagers && $PendingCount === 0) {
            $leave->status = 'Manager_Approved';
        } elseif ($rejectedCount === $totalManagers && $PendingCount === 0) {
            $leave->status = 'Manager_Rejected';
        } elseif ($approvedCount > 0 && $PendingCount === 0) {
            $leave->status = 'Partially_Approved';
        } elseif ($PendingCount > 0) {
            $leave->status = 'In_Process';
        }

        $leave->save();
        

        return redirect()->route('leave-employee.index')->with('success', __('Leave has been ' . $leaveManager->status . ' successfully.'));
    }
}
