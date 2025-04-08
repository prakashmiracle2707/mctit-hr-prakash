<?php

namespace App\Exports;

use App\Models\TimeSheet;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class TimesheetSheetExport implements FromCollection, WithHeadings, WithTitle
{
    protected $start, $end, $employee_id, $project_id, $title;

    public function __construct($start, $end, $employee_id, $project_id, $title)
    {
        $this->start = $start;
        $this->end = $end;
        $this->employee_id = $employee_id;
        $this->project_id = $project_id;
        $this->title = $title;
    }

    public function collection(): Collection
    {
        $query = TimeSheet::where('employee_id', $this->employee_id)
            ->where('project_id', $this->project_id)
            ->whereBetween('date', [$this->start, $this->end]);

        if (\Auth::user()->type !== 'employee') {
            $query->where('created_by', \Auth::user()->creatorId());
        }

        $data = $query->get();

        $formatted = [];
        $total = 0;

        foreach ($data as $row) {
            $formatted[] = [
                'Date' => Carbon::parse($row->date)->format('d/m/Y'),
                'Project' => optional($row->project)->name,
                'Milestone' => optional($row->milestone)->name,
                'Task' => $row->task_name,
                'Hours' => str_pad($row->workhours, 2, '0', STR_PAD_LEFT).":".str_pad($row->workminutes, 2, '0', STR_PAD_LEFT),
            ];

            $total += ($row->workhours * 60) + $row->workminutes;
        }

        $totalHours = floor($total / 60);
        $remainingMinutes = $total % 60;

        $formatted[] = [
            'Date' => '',
            'Project' => '',
            'Milestone' => '',
            'Task' => 'Total',
            'Hours' => str_pad($totalHours, 2, '0', STR_PAD_LEFT) . ":" . str_pad($remainingMinutes, 2, '0', STR_PAD_LEFT),
        ];

        return collect($formatted);
    }

    public function headings(): array
    {
        return ['Date', 'Project', 'Milestone', 'Task', 'Hours'];
    }

    public function title(): string
    {
        return $this->title;
    }
}
