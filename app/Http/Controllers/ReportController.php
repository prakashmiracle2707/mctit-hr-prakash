<?php

namespace App\Http\Controllers;

use App\Exports\accountstatementExport;
use App\Exports\LeaveReportExport;
use App\Exports\PayrollExport;
use App\Exports\TimesheetReportExport;
use App\Models\AccountList;
use App\Models\AttendanceEmployee;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Deposit;
use App\Models\Employee;
use App\Models\Expense;
use App\Models\Leave;
use App\Models\LeaveType;
use App\Models\PaySlip;
use App\Models\TimeSheet;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Holiday;
use App\Models\Leave as LocalLeave;
use App\Models\FinancialYear;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{

    public function incomeVsExpense(Request $request)
    {
        if (\Auth::user()->can('Manage Report')) {
            $deposit = Deposit::where('created_by', \Auth::user()->creatorId());

            $labels       = $data = [];
            $expenseCount = $incomeCount = 0;
            $incomeData = [];
            $expenseData = [];
            if (!empty($request->start_month) && !empty($request->end_month)) {

                $start = strtotime($request->start_month);
                $end   = strtotime($request->end_month);

                $currentdate = $start;
                $month       = [];
                while ($currentdate <= $end) {
                    $month = date('m', $currentdate);
                    $year  = date('Y', $currentdate);

                    $depositFilter = Deposit::where('created_by', \Auth::user()->creatorId())->whereMonth('date', $month)->whereYear('date', $year)->get();

                    $depositsTotal = 0;
                    foreach ($depositFilter as $deposit) {
                        $depositsTotal += $deposit->amount;
                    }
                    $incomeData[] = $depositsTotal;
                    $incomeCount  += $depositsTotal;

                    $expenseFilter = Expense::where('created_by', \Auth::user()->creatorId())->whereMonth('date', $month)->whereYear('date', $year)->get();
                    $expenseTotal  = 0;
                    foreach ($expenseFilter as $expense) {
                        $expenseTotal += $expense->amount;
                    }
                    $expenseData[] = $expenseTotal;
                    $expenseCount  += $expenseTotal;

                    $labels[]    = date('M Y', $currentdate);
                    $currentdate = strtotime('+1 month', $currentdate);
                }

                $filter['startDateRange'] = date('M-Y', strtotime($request->start_month));
                $filter['endDateRange']   = date('M-Y', strtotime($request->end_month));
            } else {
                for ($i = 0; $i < 6; $i++) {
                    $month = date('m', strtotime("-$i month"));
                    $year  = date('Y', strtotime("-$i month"));

                    $depositFilter = Deposit::where('created_by', \Auth::user()->creatorId())->whereMonth('date', $month)->whereYear('date', $year)->get();

                    $depositTotal = 0;
                    foreach ($depositFilter as $deposit) {
                        $depositTotal += $deposit->amount;
                    }

                    $incomeData[] = $depositTotal;
                    $incomeCount  += $depositTotal;

                    $expenseFilter = Expense::where('created_by', \Auth::user()->creatorId())->whereMonth('date', $month)->whereYear('date', $year)->get();
                    $expenseTotal  = 0;
                    foreach ($expenseFilter as $expense) {
                        $expenseTotal += $expense->amount;
                    }
                    $expenseData[] = $expenseTotal;
                    $expenseCount  += $expenseTotal;

                    $labels[] = date('M Y', strtotime("-$i month"));
                }
                $filter['startDateRange'] = date('M-Y');
                $filter['endDateRange']   = date('M-Y', strtotime("-5 month"));
            }

            $incomeArr['name'] = __('Income');
            $incomeArr['data'] = $incomeData;

            $expenseArr['name'] = __('Expense');
            $expenseArr['data'] = $expenseData;

            $data[] = $incomeArr;
            $data[] = $expenseArr;


            return view('report.income_expense', compact('labels', 'data', 'incomeCount', 'expenseCount', 'filter'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function leave(Request $request)
    {
        if (\Auth::user()->can('Manage Report')) {

            $branch = Branch::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $branch->prepend('All', '');

            $department = Department::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $department->prepend('All', '');

            $filterYear['branch']        = __('All');
            $filterYear['department']    = __('All');
            $filterYear['type']          = __('Monthly');
            $filterYear['dateYearRange'] = date('M-Y');
            $employees                   = Employee::where('created_by', \Auth::user()->creatorId());
            if (!empty($request->branch)) {
                $employees->where('branch_id', $request->branch);
                $filterYear['branch'] = !empty(Branch::find($request->branch)) ? Branch::find($request->branch)->name : '';
            }
            if (!empty($request->department)) {
                $employees->where('department_id', $request->department);
                $filterYear['department'] = !empty(Department::find($request->department)) ? Department::find($request->department)->name : '';
            }


            $employees = $employees->get();

            $leaves        = [];
            $totalApproved = $totalReject = $totalPending = 0;
            foreach ($employees as $employee) {

                $employeeLeave['id']          = $employee->id;
                $employeeLeave['employee_id'] = $employee->employee_id;
                $employeeLeave['employee']    = $employee->name;

                $approved = Leave::where('employee_id', $employee->id)->where('status', 'Approved');
                $reject   = Leave::where('employee_id', $employee->id)->where('status', 'Reject');
                $pending  = Leave::where('employee_id', $employee->id)->where('status', 'Pending');

                if ($request->type == 'monthly' && !empty($request->month)) {
                    $month = date('m', strtotime($request->month));
                    $year  = date('Y', strtotime($request->month));

                    $approved->whereMonth('applied_on', $month)->whereYear('applied_on', $year);
                    $reject->whereMonth('applied_on', $month)->whereYear('applied_on', $year);
                    $pending->whereMonth('applied_on', $month)->whereYear('applied_on', $year);

                    $filterYear['dateYearRange'] = date('M-Y', strtotime($request->month));
                    $filterYear['type']          = __('Monthly');
                } elseif (!isset($request->type)) {
                    $month     = date('m');
                    $year      = date('Y');
                    $monthYear = date('Y-m');

                    $approved->whereMonth('applied_on', $month)->whereYear('applied_on', $year);
                    $reject->whereMonth('applied_on', $month)->whereYear('applied_on', $year);
                    $pending->whereMonth('applied_on', $month)->whereYear('applied_on', $year);

                    $filterYear['dateYearRange'] = date('M-Y', strtotime($monthYear));
                    $filterYear['type']          = __('Monthly');
                }


                if ($request->type == 'yearly' && !empty($request->year)) {
                    $approved->whereYear('applied_on', $request->year);
                    $reject->whereYear('applied_on', $request->year);
                    $pending->whereYear('applied_on', $request->year);


                    $filterYear['dateYearRange'] = $request->year;
                    $filterYear['type']          = __('Yearly');
                }

                $approved = $approved->count();
                $reject   = $reject->count();
                $pending  = $pending->count();

                $totalApproved += $approved;
                $totalReject   += $reject;
                $totalPending  += $pending;

                $employeeLeave['approved'] = $approved;
                $employeeLeave['reject']   = $reject;
                $employeeLeave['pending']  = $pending;


                $leaves[] = $employeeLeave;
            }

            $starting_year = date('Y', strtotime('-5 year'));
            $ending_year   = date('Y', strtotime('+5 year'));

            $filterYear['starting_year'] = $starting_year;
            $filterYear['ending_year']   = $ending_year;

            $filter['totalApproved'] = $totalApproved;
            $filter['totalReject']   = $totalReject;
            $filter['totalPending']  = $totalPending;


            return view('report.leave', compact('department', 'branch', 'leaves', 'filterYear', 'filter'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function employeeLeave(Request $request, $employee_id, $status, $type, $month, $year)
    {
        if (\Auth::user()->can('Manage Report')) {
            $leaveTypes = LeaveType::where('created_by', \Auth::user()->creatorId())->get();
            $leaves     = [];
            foreach ($leaveTypes as $leaveType) {
                $leave        = new Leave();
                $leave->title = $leaveType->title;
                $totalLeave   = Leave::where('employee_id', $employee_id)->where('status', $status)->where('leave_type_id', $leaveType->id);
                if ($type == 'yearly') {
                    $totalLeave->whereYear('applied_on', $year);
                } else {
                    $m = date('m', strtotime($month));
                    $y = date('Y', strtotime($month));

                    $totalLeave->whereMonth('applied_on', $m)->whereYear('applied_on', $y);
                }
                $totalLeave = $totalLeave->count();

                $leave->total = $totalLeave;
                $leaves[]     = $leave;
            }

            $leaveData = Leave::where('employee_id', $employee_id)->where('status', $status);
            if ($type == 'yearly') {
                $leaveData->whereYear('applied_on', $year);
            } else {
                $m = date('m', strtotime($month));
                $y = date('Y', strtotime($month));

                $leaveData->whereMonth('applied_on', $m)->whereYear('applied_on', $y);
            }


            $leaveData = $leaveData->get();


            return view('report.leaveShow', compact('leaves', 'leaveData'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function accountStatement(Request $request)
    {
        if (\Auth::user()->can('Manage Report')) {
            $accountList = AccountList::where('created_by', \Auth::user()->creatorId())->get()->pluck('account_name', 'id');
            $accountList->prepend('All', '');

            $filterYear['account'] = __('All');
            $filterYear['type']    = __('Income');


            if ($request->type == 'expense') {
                $accountData = Expense::orderBy('id');
                $accounts    = Expense::select('account_lists.id', 'account_lists.account_name')->leftjoin('account_lists', 'expenses.account_id', '=', 'account_lists.id')->groupBy('expenses.account_id')->selectRaw('sum(amount) as total');

                if (!empty($request->start_month) && !empty($request->end_month)) {
                    $start = strtotime($request->start_month);
                    $end   = strtotime($request->end_month);
                } else {
                    $start = strtotime(date('Y-m'));
                    $end   = strtotime(date('Y-m', strtotime("-5 month")));
                }

                $currentdate = $start;

                while ($currentdate <= $end) {
                    $data['month'] = date('m', $currentdate);
                    $data['year']  = date('Y', $currentdate);

                    $accountData->Orwhere(
                        function ($query) use ($data) {
                            $query->whereMonth('date', $data['month'])->whereYear('date', $data['year']);
                        }
                    );

                    $accounts->Orwhere(
                        function ($query) use ($data) {
                            $query->whereMonth('date', $data['month'])->whereYear('date', $data['year']);
                        }
                    );

                    $currentdate = strtotime('+1 month', $currentdate);
                }

                $filterYear['startDateRange'] = date('M-Y', $start);
                $filterYear['endDateRange']   = date('M-Y', $end);

                if (!empty($request->account)) {
                    $accountData->where('account_id', $request->account);
                    $accounts->where('account_lists.id', $request->account);

                    $filterYear['account'] = !empty(AccountList::find($request->account)) ? Department::find($request->account)->account_name : '';
                }

                $accounts->where('expenses.created_by', \Auth::user()->creatorId());

                $filterYear['type'] = __('Expense');
            } else {
                $accountData = Deposit::orderBy('id');
                $accounts    = Deposit::select('account_lists.id', 'account_lists.account_name')->leftjoin('account_lists', 'deposits.account_id', '=', 'account_lists.id')->groupBy('deposits.account_id')->selectRaw('sum(amount) as total');

                if (!empty($request->start_month) && !empty($request->end_month)) {

                    $start = strtotime($request->start_month);
                    $end   = strtotime($request->end_month);
                } else {
                    $start = strtotime(date('Y-m'));
                    $end   = strtotime(date('Y-m', strtotime("-5 month")));
                }


                $currentdate = $start;

                while ($currentdate <= $end) {
                    $data['month'] = date('m', $currentdate);
                    $data['year']  = date('Y', $currentdate);

                    $accountData->Orwhere(
                        function ($query) use ($data) {
                            $query->whereMonth('date', $data['month'])->whereYear('date', $data['year']);
                        }
                    );
                    $currentdate = strtotime('+1 month', $currentdate);

                    $accounts->Orwhere(
                        function ($query) use ($data) {
                            $query->whereMonth('date', $data['month'])->whereYear('date', $data['year']);
                        }
                    );
                    $currentdate = strtotime('+1 month', $currentdate);
                }

                $filterYear['startDateRange'] = date('M-Y', $start);
                $filterYear['endDateRange']   = date('M-Y', $end);

                if (!empty($request->account)) {
                    $accountData->where('account_id', $request->account);
                    $accounts->where('account_lists.id', $request->account);

                    $filterYear['account'] = !empty(AccountList::find($request->account)) ? Department::find($request->account)->account_name : '';
                }
                $accounts->where('deposits.created_by', \Auth::user()->creatorId());
            }

            $accountData->where('created_by', \Auth::user()->creatorId());
            $accountData = $accountData->get();

            $accounts = $accounts->get();


            return view('report.account_statement', compact('accountData', 'accountList', 'accounts', 'filterYear'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function payroll(Request $request)
    {
        if (\Auth::user()->can('Manage Report')) {
            $branch = Branch::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $branch->prepend('All', '');

            $department = Department::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $department->prepend('All', '');

            $filterYear['branch']     = __('All');
            $filterYear['department'] = __('All');
            $filterYear['type']       = __('Monthly');

            $payslips = PaySlip::select('pay_slips.*', 'employees.name')->leftjoin('employees', 'pay_slips.employee_id', '=', 'employees.id')->where('pay_slips.created_by', \Auth::user()->creatorId());

            if ($request->type == 'monthly' && !empty($request->month)) {

                $payslips->where('salary_month', $request->month);

                $filterYear['dateYearRange'] = date('M-Y', strtotime($request->month));
                $filterYear['type']          = __('Monthly');
            } elseif (!isset($request->type)) {
                $month = date('Y-m');

                $payslips->where('salary_month', $month);

                $filterYear['dateYearRange'] = date('M-Y', strtotime($month));
                $filterYear['type']          = __('Monthly');
            }


            if ($request->type == 'yearly' && !empty($request->year)) {
                $startMonth = $request->year . '-01';
                $endMonth   = $request->year . '-12';
                $payslips->where('salary_month', '>=', $startMonth)->where('salary_month', '<=', $endMonth);

                $filterYear['dateYearRange'] = $request->year;
                $filterYear['type']          = __('Yearly');
            }


            if (!empty($request->branch)) {
                $payslips->where('employees.branch_id', $request->branch);

                $filterYear['branch'] = !empty(Branch::find($request->branch)) ? Branch::find($request->branch)->name : '';
            }

            if (!empty($request->department)) {
                $payslips->where('employees.department_id', $request->department);

                $filterYear['department'] = !empty(Department::find($request->department)) ? Department::find($request->department)->name : '';
            }

            $payslips = $payslips->get();

            $totalBasicSalary = $totalNetSalary = $totalAllowance = $totalCommision = $totalLoan = $totalSaturationDeduction = $totalOtherPayment = $totalOverTime = 0;

            foreach ($payslips as $payslip) {
                $totalBasicSalary += $payslip->basic_salary;
                $totalNetSalary   += $payslip->net_payble;

                $allowances = json_decode($payslip->allowance);
                foreach ($allowances as $allowance) {
                    $totalAllowance += $allowance->amount;
                }

                $commisions = json_decode($payslip->commission);
                foreach ($commisions as $commision) {
                    $totalCommision += $commision->amount;
                }

                $loans = json_decode($payslip->loan);
                foreach ($loans as $loan) {
                    $totalLoan += $loan->amount;
                }

                $saturationDeductions = json_decode($payslip->saturation_deduction);
                foreach ($saturationDeductions as $saturationDeduction) {
                    $totalSaturationDeduction += $saturationDeduction->amount;
                }

                $otherPayments = json_decode($payslip->other_payment);
                foreach ($otherPayments as $otherPayment) {
                    $totalOtherPayment += $otherPayment->amount;
                }

                $overtimes = json_decode($payslip->overtime);
                foreach ($overtimes as $overtime) {
                    $days  = $overtime->number_of_days;
                    $hours = $overtime->hours;
                    $rate  = $overtime->rate;

                    $totalOverTime += ($rate * $hours) * $days;
                }
            }

            $filterData['totalBasicSalary']         = $totalBasicSalary;
            $filterData['totalNetSalary']           = $totalNetSalary;
            $filterData['totalAllowance']           = $totalAllowance;
            $filterData['totalCommision']           = $totalCommision;
            $filterData['totalLoan']                = $totalLoan;
            $filterData['totalSaturationDeduction'] = $totalSaturationDeduction;
            $filterData['totalOtherPayment']        = $totalOtherPayment;
            $filterData['totalOverTime']            = $totalOverTime;


            $starting_year = date('Y', strtotime('-5 year'));
            $ending_year   = date('Y', strtotime('+5 year'));

            $filterYear['starting_year'] = $starting_year;
            $filterYear['ending_year']   = $ending_year;

            return view('report.payroll', compact('payslips', 'filterData', 'branch', 'department', 'filterYear'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function monthlyAttendance(Request $request)
    {

        if (!\Auth::user()->can('Manage Report')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        /* ************************************************************** */

        $employeeQuery = Employee::select('id', 'name')
            ->where('created_by', \Auth::user()->creatorId());

        $employeeList = $employeeQuery->orderBy('name', 'asc')->pluck('name', 'id');

        if (!empty($request->employee_id)) {
            $employeeQuery->where('id', $request->employee_id);
        }

        $employees = $employeeQuery->pluck('name', 'id');

        $RequestMonth=$request->month;
        // Parse the input month
        $selectedMonth = Carbon::parse($request->month); // 2025-03

        $monthNumber = (int) $selectedMonth->format('m');
        $year = (int) $selectedMonth->format('Y');

        // Determine Financial Year start year
        $fyStartYear = $monthNumber >= 4 ? $year : $year - 1;


        // Get the financial year
        $financialYear = FinancialYear::whereYear('start_date', $fyStartYear)->first();

        if (!$financialYear) {
            return redirect()->back()->with('error', 'Financial year not found for the given month.');
        }

        // Now you have the full Financial Year
        $startDate = Carbon::parse($financialYear->start_date);
        $endDate = Carbon::parse($financialYear->end_date);


        

        if ($endDate->isFuture()) {
            $endDate = Carbon::now();
        }

        $totalLeaveList = [
            'SL' => LeaveType::find(1)->days,
            'CL' => LeaveType::find(2)->days,
            'WFH' => LeaveType::find(3)->days,
            'OH' => LeaveType::find(4)->days,
        ];

        $months = [];
        $cursor = $startDate->copy();
        while ($cursor->startOfMonth() <= $endDate->startOfMonth()) {
            $months[] = [
                'label' => $cursor->format('F-Y'),
                'value' => $cursor->format('Y-m')
            ];
            $cursor->addMonth();
        }

        $leaveUsageTrack = []; // [employeeId => ['SL' => x, 'CL' => y, ...]]
        $data = [];

        foreach ($months as $monthInfo) {
            $request->merge(['month' => $monthInfo['value']]);

            
            // Pass cumulative leave usage to processMonthlyAttendance
            $monthly = $this->processMonthlyAttendance($request, $employees, $totalLeaveList, $leaveUsageTrack);


            // Save the new end balances to be used as "start" for next month
            foreach ($monthly as $attendance) {
                $id = array_search($attendance['name'], $employees->toArray()); // get employee id by name
                $usedSL = ($totalLeaveList['SL'] ?? 0) - ($attendance['leave_balance']['end']['SL'] ?? 0);
                $usedCL = ($totalLeaveList['CL'] ?? 0) - ($attendance['leave_balance']['end']['CL'] ?? 0);
                $usedWFH = ($totalLeaveList['WFH'] ?? 0) - ($attendance['leave_balance']['end']['WFH'] ?? 0);
                $usedOH = ($totalLeaveList['OH'] ?? 0) - ($attendance['leave_balance']['end']['OH'] ?? 0);

                $leaveUsageTrack[$id] = [
                    'SL' => $usedSL,
                    'CL' => $usedCL,
                    'WFH' => $usedWFH,
                    'OH' => $usedOH,
                ];
            }

            $attendanceData[$monthInfo['label']] = $monthly;

            
        }

        $monthKey = Carbon::parse($RequestMonth)->format('F-Y');

        $employeesAttendance = $attendanceData[$monthKey] ?? [];

        // echo "<pre>";print_r($employeesAttendance);exit;


        // Determine selected month or default to now
        

        $selectedDateArray = !empty($RequestMonth) ? Carbon::parse($RequestMonth) : Carbon::now();
        $month = $selectedDateArray->format('m');
        $year = $selectedDateArray->format('Y');
        $curMonth = $selectedDateArray->format('M-Y');

        // Create dates for calendar display
        $num_of_days = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $dates = [];
        for ($i = 1; $i <= $num_of_days; $i++) {
            $dates[] = str_pad($i, 2, '0', STR_PAD_LEFT);
        }

        // Calculate totals
        $data['totalOvertime'] = 0;
        $data['totalEarlyLeave'] = 0;
        $data['totalLate'] = 0;
        $data['totalPresent'] = array_reduce($employeesAttendance, fn($carry, $emp) => $carry + ($emp['summary']['Present'] ?? 0), 0);
        $data['totalLeave'] = array_reduce($employeesAttendance, fn($carry, $emp) => $carry + ($emp['summary']['A'] ?? 0), 0);
        $data['curMonth'] = $curMonth;



        return view('report.monthlyAttendance', compact(
            'employeesAttendance',
            'dates',
            'data',
            'employeeList'
        ));

        /* ************************************************************** */
    }




    // Updated financialYearAttendance
    public function financialYearAttendance(Request $request)
    {
        if (!\Auth::user()->can('Manage Report')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $employeeQuery = Employee::select('id', 'name')
            ->where('created_by', \Auth::user()->creatorId());

        $employeeList = $employeeQuery->orderBy('name', 'asc')->pluck('name', 'id');

        if (!empty($request->employee_id)) {
            $employeeQuery->where('id', $request->employee_id);
        }

        $employees = $employeeQuery->pluck('name', 'id');

        $financialYears = FinancialYear::orderBy('start_date', 'desc')
            ->pluck(DB::raw("CONCAT(YEAR(start_date), '-', YEAR(end_date))"), 'id');

        $selectedFY = $request->financial_year_id ?? FinancialYear::where('is_active', 1)->value('id');
        $financialYear = FinancialYear::find($selectedFY);

        if (!$financialYear) {
            return redirect()->back()->with('error', __('Financial year not found.'));
        }

        $startDate = Carbon::parse($financialYear->start_date);
        $endDate = Carbon::parse($financialYear->end_date);
        if ($endDate->isFuture()) {
            $endDate = Carbon::now();
        }

        $totalLeaveList = [
            'SL' => LeaveType::find(1)->days,
            'CL' => LeaveType::find(2)->days,
            'WFH' => LeaveType::find(3)->days,
            'OH' => LeaveType::find(4)->days,
        ];

        $months = [];
        $cursor = $startDate->copy();
        while ($cursor->startOfMonth() <= $endDate->startOfMonth()) {
            $months[] = [
                'label' => $cursor->format('F-Y'),
                'value' => $cursor->format('Y-m')
            ];
            $cursor->addMonth();
        }

        $leaveUsageTrack = []; // [employeeId => ['SL' => x, 'CL' => y, ...]]

        foreach ($months as $monthInfo) {
            $request->merge(['month' => $monthInfo['value']]);

            // Pass cumulative leave usage to processMonthlyAttendance
            $monthly = $this->processMonthlyAttendance($request, $employees, $totalLeaveList, $leaveUsageTrack);

            // Save the new end balances to be used as "start" for next month
            foreach ($monthly as $attendance) {
                $id = array_search($attendance['name'], $employees->toArray()); // get employee id by name
                $usedSL = ($totalLeaveList['SL'] ?? 0) - ($attendance['leave_balance']['end']['SL'] ?? 0);
                $usedCL = ($totalLeaveList['CL'] ?? 0) - ($attendance['leave_balance']['end']['CL'] ?? 0);
                $usedWFH = ($totalLeaveList['WFH'] ?? 0) - ($attendance['leave_balance']['end']['WFH'] ?? 0);
                $usedOH = ($totalLeaveList['OH'] ?? 0) - ($attendance['leave_balance']['end']['OH'] ?? 0);

                $leaveUsageTrack[$id] = [
                    'SL' => $usedSL,
                    'CL' => $usedCL,
                    'WFH' => $usedWFH,
                    'OH' => $usedOH,
                ];
            }

            $attendanceData[$monthInfo['label']] = $monthly;
        }

        // echo "<pre>";print_r($attendanceData);exit;

        return view('report.financialYearAttendance', compact(
            'attendanceData',
            'months',
            'financialYears',
            'selectedFY',
            'employeeList'
        ));
    }


    public function employeeFinancialYearAttendance(Request $request)
    {
        if (\Auth::user()->type != 'employee') {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $user     = \Auth::user();
        $employee_id = $user->id;

        $employeeQuery = Employee::select('id', 'name')
            ->where('created_by', \Auth::user()->creatorId());

        $employeeList = $employeeQuery->orderBy('name', 'asc')->pluck('name', 'id');
        
        if (!empty($employee_id)) {
            $employeeQuery->where('user_id', $employee_id);
        }

        $employees = $employeeQuery->pluck('name', 'id');

        $financialYears = FinancialYear::orderBy('start_date', 'desc')
            ->pluck(DB::raw("CONCAT(YEAR(start_date), '-', YEAR(end_date))"), 'id');

        $selectedFY = $request->financial_year_id ?? FinancialYear::where('is_active', 1)->value('id');
        $financialYear = FinancialYear::find($selectedFY);

        if (!$financialYear) {
            return redirect()->back()->with('error', __('Financial year not found.'));
        }

        $startDate = Carbon::parse($financialYear->start_date);
        $endDate = Carbon::parse($financialYear->end_date);
        if ($endDate->isFuture()) {
            $endDate = Carbon::now();
        }

        $totalLeaveList = [
            'SL' => LeaveType::find(1)->days,
            'CL' => LeaveType::find(2)->days,
            'WFH' => LeaveType::find(3)->days,
            'OH' => LeaveType::find(4)->days,
        ];

        $months = [];
        $cursor = $startDate->copy();
        while ($cursor->startOfMonth() <= $endDate->startOfMonth()) {
            $months[] = [
                'label' => $cursor->format('F-Y'),
                'value' => $cursor->format('Y-m')
            ];
            $cursor->addMonth();
        }

        $leaveUsageTrack = []; // [employeeId => ['SL' => x, 'CL' => y, ...]]

        foreach ($months as $monthInfo) {
            $request->merge(['month' => $monthInfo['value']]);

            // Pass cumulative leave usage to processMonthlyAttendance
            $monthly = $this->processMonthlyAttendance($request, $employees, $totalLeaveList, $leaveUsageTrack);

            // Save the new end balances to be used as "start" for next month
            foreach ($monthly as $attendance) {
                $id = array_search($attendance['name'], $employees->toArray()); // get employee id by name
                $usedSL = ($totalLeaveList['SL'] ?? 0) - ($attendance['leave_balance']['end']['SL'] ?? 0);
                $usedCL = ($totalLeaveList['CL'] ?? 0) - ($attendance['leave_balance']['end']['CL'] ?? 0);
                $usedWFH = ($totalLeaveList['WFH'] ?? 0) - ($attendance['leave_balance']['end']['WFH'] ?? 0);
                $usedOH = ($totalLeaveList['OH'] ?? 0) - ($attendance['leave_balance']['end']['OH'] ?? 0);

                $leaveUsageTrack[$id] = [
                    'SL' => $usedSL,
                    'CL' => $usedCL,
                    'WFH' => $usedWFH,
                    'OH' => $usedOH,
                ];
            }

            $attendanceData[$monthInfo['label']] = $monthly;
        }

        // echo "<pre>";print_r($attendanceData);exit;

        return view('report.employeeFinancialYearAttendance', compact(
            'attendanceData',
            'months',
            'financialYears',
            'selectedFY',
            'employeeList'
        ));
    }

    public function processMonthlyAttendance($request, $employees, $totalLeaveList, &$leaveUsageTrack)
    {
        $month = date('m', strtotime($request->month));
        $year = date('Y', strtotime($request->month));
        $num_of_days = cal_days_in_month(CAL_GREGORIAN, $month, $year);

        $dates = [];
        for ($i = 1; $i <= $num_of_days; $i++) {
            $dates[] = str_pad($i, 2, '0', STR_PAD_LEFT);
        }

        $employeesAttendance = [];
        foreach ($employees as $id => $employee) {
            $sl = $chl = $cfl = $oh = $WFH = $TotalWeekDay = $Present = $lwp = $a = $h = $WeekdayPresent = $isPresentHoliday = 0;
            $attendanceStatus = [];
            $daysCount = 0;

            foreach ($dates as $date) {
                $dateFormat = "$year-$month-$date";
                $day = Carbon::parse($dateFormat)->format('l');

                if ($dateFormat > now()->format('Y-m-d')) {
                    $attendanceStatus[$date] = '';
                    continue;
                }

                if ($day == 'Saturday' || $day == 'Sunday') {
                    $TotalWeekDay++;
                }

                $daysCount++;
                $employeeAttendance = AttendanceEmployee::where('employee_id', $id)->where('date', $dateFormat)->where('is_leave', 0)->first();

                $isSickLeaveDay = LocalLeave::where('employee_id', $id)->where('status', 'Approved')->where('leave_type_id', 1)->whereDate('start_date', '<=', $dateFormat)->whereDate('end_date', '>=', $dateFormat)->exists();
                $isCasualLeave = LocalLeave::where('employee_id', $id)->where('status', 'Approved')->where('leave_type_id', 2)->whereDate('start_date', '<=', $dateFormat)->whereDate('end_date', '>=', $dateFormat)->first();
                $isOptional = LocalLeave::where('employee_id', $id)->where('status', 'Approved')->where('leave_type_id', 4)->whereDate('start_date', '<=', $dateFormat)->whereDate('end_date', '>=', $dateFormat)->exists();
                $isHoliday = Holiday::where('start_date', $dateFormat)->where('is_optional', 0)->exists();

                if ($employeeAttendance && $employeeAttendance->status === 'Present') {
                    $attendanceStatus[$date] = 'P';
                    if ($day != 'Saturday' && $day != 'Sunday' && !$isSickLeaveDay && !$isCasualLeave) {
                        $Present++;
                    }

                    if ($isCasualLeave && in_array($isCasualLeave->half_day_type, ['morning', 'afternoon'])) {
                        $Present++;
                    }

                    if ($employeeAttendance->work_from_home == 1) $WFH++;
                    if ($isSickLeaveDay) $sl++;
                    if ($isOptional) $oh++;

                    if ($day === 'Saturday' || $day === 'Sunday') {
                        $WeekdayPresent++;
                    } elseif ($isHoliday) {
                        $isPresentHoliday++;
                    } elseif ($isCasualLeave && in_array($isCasualLeave->half_day_type, ['morning', 'afternoon'])) {
                        $chl += 0.5;
                        $Present -= 0.5;
                        $attendanceStatus[$date] = 'H/F';
                    }

                    if($isCasualLeave && $isCasualLeave->half_day_type === 'full_day' && $day != 'Saturday' && $day != 'Sunday'){
                        $attendanceStatus[$date] = 'L';
                        $cfl++;
                    }

                    if ($isSickLeaveDay) {
                        $attendanceStatus[$date] = 'L';
                        $sl++;
                    }
                } elseif ($employeeAttendance && $employeeAttendance->status === 'Leave') {
                    $attendanceStatus[$date] = 'L';
                    $lwp++;
                } else {
                    if ($isSickLeaveDay) {
                        $attendanceStatus[$date] = 'L';
                        $sl++;
                    } elseif ($isCasualLeave && $isCasualLeave->half_day_type === 'full_day' && $day != 'Saturday' && $day != 'Sunday') {
                        $attendanceStatus[$date] = 'L';
                        $cfl++;
                    } elseif ($isCasualLeave && in_array($isCasualLeave->half_day_type, ['morning', 'afternoon'])) {
                        $attendanceStatus[$date] = 'H/F';
                        $chl += 0.5;
                    } elseif ($isOptional) {
                        $attendanceStatus[$date] = 'OL';
                        $oh++;
                    } elseif ($isHoliday) {
                        $attendanceStatus[$date] = 'H';
                        if ($day != 'Saturday' && $day != 'Sunday') {
                            $h++;
                        }
                    } elseif ($day === 'Saturday' || $day === 'Sunday') {
                        $attendanceStatus[$date] = '';
                    } else {
                        $attendanceStatus[$date] = 'A';
                        $a++;
                    }
                }
            }

            $used = [
                'SL' => $sl,
                'CL' => $cfl + $chl,
                'WFH' => $WFH,
                'OH' => $oh,
            ];

            $prevUsage = $leaveUsageTrack[$id] ?? ['SL' => 0, 'CL' => 0, 'WFH' => 0, 'OH' => 0];

            $attendances = [
                'name' => $employee,
                'status' => $attendanceStatus,
                'summary' => [
                    'SL' => $sl,
                    'CFL' => $cfl,
                    'CHL' => $chl,
                    'OH' => $oh,
                    'WFH' => $WFH,
                    'A' => $a,
                    'LWP' => $lwp,
                    'H' => $h,
                    'Present' => $Present,
                    'WeekdayPresent' => $WeekdayPresent,
                    'isPresentHoliday' => $isPresentHoliday,
                    'TotalMonthDays' => $daysCount,
                    'TotalWeekDay' => $TotalWeekDay,
                ],
                'leave_balance' => [
                    'start' => [
                        'SL' => $totalLeaveList['SL'] - $prevUsage['SL'],
                        'CL' => $totalLeaveList['CL'] - $prevUsage['CL'],
                        'WFH' => $totalLeaveList['WFH'] - $prevUsage['WFH'],
                        'OH' => $totalLeaveList['OH'] - $prevUsage['OH'],
                    ],
                    'end' => [
                        'SL' => $totalLeaveList['SL'] - ($prevUsage['SL'] + $used['SL']),
                        'CL' => $totalLeaveList['CL'] - ($prevUsage['CL'] + $used['CL']),
                        'WFH' => $totalLeaveList['WFH'] - ($prevUsage['WFH'] + $used['WFH']),
                        'OH' => $totalLeaveList['OH'] - ($prevUsage['OH'] + $used['OH']),
                    ]
                ]
            ];

            $leaveUsageTrack[$id] = [
                'SL' => $prevUsage['SL'] + $used['SL'],
                'CL' => $prevUsage['CL'] + $used['CL'],
                'WFH' => $prevUsage['WFH'] + $used['WFH'],
                'OH' => $prevUsage['OH'] + $used['OH'],
            ];

            $employeesAttendance[] = $attendances;
        }

        return $employeesAttendance;
    }





    public function timesheet(Request $request)
    {
        if (\Auth::user()->can('Manage Report')) {
            $branch = Branch::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $branch->prepend('All', '');

            $department = Department::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $department->prepend('All', '');

            $filterYear['branch']     = __('All');
            $filterYear['department'] = __('All');

            $timesheets       = TimeSheet::select('time_sheets.*', 'employees.name')->leftjoin('employees', 'time_sheets.employee_id', '=', 'employees.id')->where('time_sheets.created_by', \Auth::user()->creatorId());

            $timesheetFilters = TimeSheet::select('time_sheets.*', 'employees.name')->groupBy('employee_id')->selectRaw('sum(hours) as total')->leftjoin('employees', 'time_sheets.employee_id', '=', 'employees.id')->where('time_sheets.created_by', \Auth::user()->creatorId());




            if (!empty($request->start_date) && !empty($request->end_date)) {
                $timesheets->where('date', '>=', $request->start_date);
                $timesheets->where('date', '<=', $request->end_date);

                $timesheetFilters->where('date', '>=', $request->start_date);
                $timesheetFilters->where('date', '<=', $request->end_date);

                $filterYear['start_date'] = $request->start_date;
                $filterYear['end_date']   = $request->end_date;
            } else {

                $filterYear['start_date'] = date('Y-m-01');
                $filterYear['end_date']   = date('Y-m-t');

                $timesheets->where('date', '>=', $filterYear['start_date']);
                $timesheets->where('date', '<=', $filterYear['end_date']);

                $timesheetFilters->where('date', '>=', $filterYear['start_date']);
                $timesheetFilters->where('date', '<=', $filterYear['end_date']);
            }

            if (!empty($request->branch)) {
                $timesheets->where('branch_id', $request->branch);
                $timesheetFilters->where('branch_id', $request->branch);
                $filterYear['branch'] = !empty(Branch::find($request->branch)) ? Branch::find($request->branch)->name : '';
            }
            if (!empty($request->department)) {
                $timesheets->where('department_id', $request->department);

                $timesheetFilters->where('department_id', $request->department);

                $filterYear['department'] = !empty(Department::find($request->department)) ? Department::find($request->department)->name : '';
            }

            $timesheets = $timesheets->get();

            $timesheetFilters = $timesheetFilters->get();

            $totalHours = 0;
            foreach ($timesheetFilters as $timesheetFilter) {
                $totalHours += $timesheetFilter->hours;
            }
            $filterYear['totalHours']    = $totalHours;
            $filterYear['totalEmployee'] = count($timesheetFilters);


            return view('report.timesheet', compact('timesheets', 'branch', 'department', 'filterYear', 'timesheetFilters'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function LeaveReportExport()
    {
        $name = 'leave_' . date('Y-m-d i:h:s');
        $data = \Excel::download(new LeaveReportExport(), $name . '.xlsx');

        return $data;
    }

    public function AccountStatementReportExport(Request $request)
    {
        $name = 'Account Statement_' . date('Y-m-d i:h:s');
        $data = \Excel::download(new accountstatementExport(), $name . '.xlsx');

        return $data;
    }

    public function PayrollReportExport($month, $branch, $department)
    {
        $data = [];
        $data['branch'] = __('All');
        $data['department'] = __('All');

        if ($branch != 0) {
            $data['branch'] = !empty(Branch::find($branch)) ? Branch::find($branch)->id : '';
        }

        if ($department != 0) {
            $data['department'] = !empty(Department::find($department)) ? Department::find($department)->id : '';
        }
        $data['month'] = $month;
        $name = 'Payroll_' . date('Y-m-d i:h:s');
        $data = \Excel::download(new PayrollExport($data), $name . '.xlsx');

        return $data;
    }

    public function exportTimeshhetReport(Request $request)
    {
        $name = 'Timesheet_' . date('Y-m-d i:h:s');
        $data = \Excel::download(new TimesheetReportExport(), $name . '.xlsx');

        return $data;
    }

    public function exportCsv($filter_month, $branch, $department, $employee)
    {
        $data['branch'] = __('All');
        $data['department'] = __('All');

        $employees = Employee::select('id', 'name')->where('created_by', \Auth::user()->creatorId());
        if ($branch != 0) {
            $employees->where('branch_id', $branch);
            $data['branch'] = !empty(Branch::find($branch)) ? Branch::find($branch)->name : '';
        }

        if ($department != 0) {
            $employees->where('department_id', $department);
            $data['department'] = !empty(Department::find($department)) ? Department::find($department)->name : '';
        }

        if ($employee != 0) {
            $employeeIds = explode(',', $employee);
            $emp = Employee::whereIn('id', $employeeIds);
        } else {
            $emp = Employee::where('created_by', \Auth::user()->creatorId());
        }

        $employees = $emp->get()->pluck('name', 'id');


        $currentdate = strtotime($filter_month);
        $month       = date('m', $currentdate);
        $year        = date('Y', $currentdate);
        $data['curMonth']    = date('M-Y', strtotime($filter_month));


        $fileName = $data['branch'] . ' ' . __('Branch') . ' ' . $data['curMonth'] . ' ' . __('Attendance Report of') . ' ' . $data['department'] . ' ' . __('Department') . ' ' . '.csv';

        $employeesAttendance = [];
        $num_of_days = date('t', mktime(0, 0, 0, $month, 1, $year));
        for ($i = 1; $i <= $num_of_days; $i++) {
            $dates[] = str_pad($i, 2, '0', STR_PAD_LEFT);
        }

        foreach ($employees as $id => $employee) {
            $attendances['name'] = $employee;

            foreach ($dates as $date) {

                $dateFormat = $year . '-' . $month . '-' . $date;

                if ($dateFormat <= date('Y-m-d')) {
                    $employeeAttendance = AttendanceEmployee::where('employee_id', $id)->where('date', $dateFormat)->first();

                    if (!empty($employeeAttendance) && $employeeAttendance->status == 'Present') {
                        $attendanceStatus[$date] = 'P';
                    } elseif (!empty($employeeAttendance) && $employeeAttendance->status == 'Leave') {
                        $attendanceStatus[$date] = 'A';
                    } else {
                        $attendanceStatus[$date] = '-';
                    }
                } else {
                    $attendanceStatus[$date] = '-';
                }
                $attendances[$date] = $attendanceStatus[$date];
            }

            $employeesAttendance[] = $attendances;
        }

        $headers = array(
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0",
        );

        $emp = array(
            'employee',
        );

        $columns = array_merge($emp, $dates);

        $callback = function () use ($employeesAttendance, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            foreach ($employeesAttendance as $attendance) {
                fputcsv($file, str_replace('"', '', array_values($attendance)));
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function getdepartment(Request $request)
    {
        if ($request->branch_id == 0) {
            $departments = Department::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id')->toArray();
        } else {
            $departments = Department::where('created_by', '=', \Auth::user()->creatorId())->where('branch_id', $request->branch_id)->get()->pluck('name', 'id')->toArray();
        }
        return response()->json($departments);
    }

    public function getemployee(Request $request)
    {
        if (!$request->department_id) {
            $employees = Employee::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id')->toArray();
        } else {
            $employees = Employee::where('created_by', '=', \Auth::user()->creatorId())->where('department_id', $request->department_id)->get()->pluck('name', 'id')->toArray();
        }

        return response()->json($employees);
    }
}
