<?php

namespace App\Exports;

use App\Models\TimeSheet;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class TimesheetSummaryExport implements FromCollection, WithHeadings, WithTitle
{
    protected $start, $end, $employees, $project_id;

    public function __construct($start, $end, $employees, $project_id)
    {
        $this->start = $start;
        $this->end = $end;
        $this->employees = $employees;
        $this->project_id = $project_id;
    }

    public function collection(): Collection
    {
        $summary = [];
        $counter = 1;
        $total = 0;

        foreach ($this->employees as $employee) {
            $workhoursTot = TimeSheet::where('employee_id', $employee->user_id)
                ->where('project_id', $this->project_id)
                ->whereBetween('date', [$this->start, $this->end])
                ->sum('workhours');

            $workminutesTot = TimeSheet::where('employee_id', $employee->user_id)
                ->where('project_id', $this->project_id)
                ->whereBetween('date', [$this->start, $this->end])
                ->sum('workminutes');

            $EmpTotalHrs = ($workhoursTot * 60) + $workminutesTot;
            $RowtotalHours = floor($EmpTotalHrs / 60);
            $RowremainingMinutes = $EmpTotalHrs % 60;

            $summary[] = [
                '#'=> $counter++,
                'Name' => $employee->name,
                'Hours' => str_pad($RowtotalHours, 2, '0', STR_PAD_LEFT) . ":" . str_pad($RowremainingMinutes, 2, '0', STR_PAD_LEFT)
            ];

            $total += $EmpTotalHrs;
        }

        $totalHours = floor($total / 60);
        $remainingMinutes = $total % 60;
        // ➤ Add Total Row
        $summary[] = [
            '#'=> '',
            'Name' => 'Total',
            'Hours' => str_pad($totalHours, 2, '0', STR_PAD_LEFT) . ":" . str_pad($remainingMinutes, 2, '0', STR_PAD_LEFT)
        ];

        return collect($summary);
    }

    public function headings(): array
    {
        return ['#', 'Name', 'Hours'];
    }

    public function title(): string
    {
        return 'Summary';
    }
}
