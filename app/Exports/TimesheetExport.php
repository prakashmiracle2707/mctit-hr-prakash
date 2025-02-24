<?php

namespace App\Exports;

use App\Models\Employee;
use App\Models\TimeSheet;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TimesheetExport implements FromCollection,WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public $start_date, $end_date, $employee, $project;

    public function __construct($start_date = null, $end_date = null, $employee = null, $project = null)
    {
        $this->start_date = $start_date;
        $this->end_date = $end_date;
        $this->employee = $employee;
        $this->project = $project;
    }

    public function collection()
    {
        // Base Query
        $query = TimeSheet::query();
        
        // Filter by Employee
        if (!empty($this->employee)) {
            $query->where('employee_id', $this->employee);
        }

        // Filter by Project
        if (!empty($this->project)) {
            $query->where('project_id', $this->project);
        }

        // Filter by Date Range
        if (!empty($this->start_date) && !empty($this->end_date)) {
            $query->whereBetween('date', [$this->start_date, $this->end_date]);
        }

        // Apply permissions
        if (\Auth::user()->type == 'employee') {
            $query->where('employee_id', \Auth::user()->id);
        } else {
            $query->where('created_by', \Auth::user()->creatorId());
        }

        // Get Data
        $data = $query->get();
        // echo "<pre>";print_r($this->project);
        // echo "<pre>";print_r(count($data));exit;

        $totalMinutes = 0;
        $formattedData = [];

        // Format Data and Calculate Total Minutes
        foreach ($data as $k => $timesheet) {
            $formattedData[] = [
                "id" => $timesheet->id,
                "employee_name" => !empty($timesheet->employee) ? $timesheet->employee->name : '',
                "project_name" => !empty($timesheet->project) ? $timesheet->project->name : '',
                "milestone_name" => !empty($timesheet->milestone) ? $timesheet->milestone->name : '',
                "task_name" => $timesheet->task_name,
                "date" => \Carbon\Carbon::parse($timesheet->date)->format('d/m/Y'),
                "hours" => $timesheet->hours,
                "remark" => $timesheet->remark,
            ];

            // Split hours and decimal part
            $hours = floor($timesheet->hours); // Get the hour part
            $minutes = ($timesheet->hours - $hours) * 100; // Convert decimal to minutes

            // Total minutes calculation
            $totalMinutes += ($hours * 60) + $minutes;
        }

        // Convert total minutes to hours and minutes
        $totalHours = floor($totalMinutes / 60);
        $remainingMinutes = $totalMinutes % 60;

        // Add Total Hours Row
        $formattedData[] = [
            "id" => '',
            "employee_name" => '',
            "project_name" => '',
            "milestone_name" => '',
            "task_name" => '',
            "date" => 'Total',
            "hours" => $totalHours . "h " . $remainingMinutes . "m",
            "remark" => ''
        ];

        return collect($formattedData);
    }
    public function headings(): array
    {
        return [
            "ID",
            "Name",
            "Project",
            "Milestone",
            "Task",
            "Date",
            "Hour",
            "Work Description",
            // "Created By"
        ];
    }
}
