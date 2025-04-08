<?php

namespace App\Exports;

use App\Models\ProjectEmployee;
use App\Models\Employee;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Exports\TimesheetSummaryExport;
use App\Exports\TimesheetSheetExport;

class TimesheetMultiSheetExport implements WithMultipleSheets
{
    protected $start, $end, $employee_id, $project_id;

    public function __construct($start, $end, $employee_id, $project_id)
    {
        $this->start = $start;
        $this->end = $end;
        $this->employee_id = $employee_id;
        $this->project_id = $project_id;
    }

    public function sheets(): array
    {
        $sheets = [];

        // ⏺ Add Summary Sheet First
        $employeeIds = ProjectEmployee::where('project_id', $this->project_id)->pluck('employee_id');
        $employees = Employee::whereIn('id', $employeeIds)->get();

        // echo "<pre>";print_r($employees);exit;

        

        // ⏺ Add individual sheets
        if (!empty($this->employee_id) && $this->employee_id !== 'all') {

            

            $employee = Employee::where('user_id', $this->employee_id)->first();

            if ($employee) {
                $sheets[] = new TimesheetSheetExport(
                    $this->start,
                    $this->end,
                    $this->employee_id,
                    $this->project_id,
                    $employee->name
                );
            }
        } else {
            $sheets[] = new TimesheetSummaryExport($this->start, $this->end, $employees, $this->project_id);
            foreach ($employees as $employee) {
                $sheets[] = new TimesheetSheetExport(
                    $this->start,
                    $this->end,
                    $employee->user_id,
                    $this->project_id,
                    $employee->name
                );
            }
        }

        return $sheets;
    }
}
