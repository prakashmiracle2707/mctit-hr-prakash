<?php
namespace App\Exports;

use App\Models\TimeSheet;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class TimesheetExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths
{
    public $start_date, $end_date, $employee, $project;
    protected $rowCount = 0;

    public function __construct($start_date = null, $end_date = null, $employee = null, $project = null)
    {
        $this->start_date = $start_date;
        $this->end_date = $end_date;
        $this->employee = $employee;
        $this->project = $project;
    }

    public function collection()
    {
        $query = TimeSheet::query();

        if (!empty($this->employee)) {
            $query->where('employee_id', $this->employee);
        }

        if (!empty($this->project)) {
            $query->where('project_id', $this->project);
        }

        if (!empty($this->start_date) && !empty($this->end_date)) {
            $query->whereBetween('date', [$this->start_date, $this->end_date]);
        }

        if (\Auth::user()->type == 'employee') {
            $query->where('employee_id', \Auth::user()->id);
        } else {
            $query->where('created_by', \Auth::user()->creatorId());
        }

        $data = $query->get();
        $totalMinutes = 0;
        $formattedData = [];

        foreach ($data as $timesheet) {
            $formattedData[] = [
                "date" => \Carbon\Carbon::parse($timesheet->date)->format('d/m/Y'),
                "employee_name" => optional($timesheet->employee)->name,
                "project_name" => optional($timesheet->project)->name,
                "milestone_name" => optional($timesheet->milestone)->name,
                "task_name" => $timesheet->task_name,
                "remark" => $timesheet->remark,
                "hours" => str_pad($timesheet->workhours, 2, '0', STR_PAD_LEFT) . ":" . str_pad($timesheet->workminutes, 2, '0', STR_PAD_LEFT),
            ];
            $totalMinutes += ($timesheet->workhours * 60) + $timesheet->workminutes;
        }

        $totalHours = floor($totalMinutes / 60);
        $remainingMinutes = $totalMinutes % 60;

        $formattedData[] = [
            "date" => '',
            "employee_name" => '',
            "project_name" => '',
            "milestone_name" => '',
            "task_name" => '',
            "remark" => 'Total',
            "hours" => str_pad($totalHours, 2, '0', STR_PAD_LEFT) . ":" . str_pad($remainingMinutes, 2, '0', STR_PAD_LEFT),
        ];

        $this->rowCount = count($formattedData) + 1; // include header row
        return collect($formattedData);
    }

    public function headings(): array
    {
        return [
            "Date",
            "Name",
            "Project",
            "Milestone",
            "Task",
            "Description",
            "Hour",
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = $this->rowCount;

        return [
            // Apply border and wrap to entire data range
            'A1:G' . $lastRow => [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['argb' => '000000'],
                    ],
                ],
                'alignment' => [
                    'wrapText' => true,
                    'vertical' => Alignment::VERTICAL_TOP,
                ],
            ],
            'A1:G1' => [
                'font' => ['bold' => true],
            ],
            'F' . $lastRow => [
                'font' => ['bold' => true],
            ],
            'G' . $lastRow => [
                'font' => ['bold' => true],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 14, // Date
            'B' => 22, // Name
            'C' => 25, // Project
            'D' => 25, // Milestone
            'E' => 25, // Task
            'F' => 60, // Description
            'G' => 10, // Hours
        ];
    }
}

