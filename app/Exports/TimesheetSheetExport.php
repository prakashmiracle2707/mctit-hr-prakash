<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use App\Models\TimeSheet;

class TimesheetSheetExport implements FromCollection, WithHeadings, WithTitle, WithStyles, WithColumnWidths
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
                'Description' => $row->remark,
                'Hours' => str_pad($row->workhours, 2, '0', STR_PAD_LEFT) . ":" . str_pad($row->workminutes, 2, '0', STR_PAD_LEFT),
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
            'Description' => '',
            'Hours' => str_pad($totalHours, 2, '0', STR_PAD_LEFT) . ":" . str_pad($remainingMinutes, 2, '0', STR_PAD_LEFT),
        ];

        return collect($formatted);
    }

    public function headings(): array
    {
        return ['Date', 'Project', 'Milestone', 'Task', 'Description', 'Hours'];
    }

    public function title(): string
    {
        return $this->title;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 14, // Date
            'B' => 20, // Project
            'C' => 20, // Milestone
            'D' => 30, // Task
            'E' => 100, // Description
            'F' => 10, // Hours
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $rowCount = count($this->collection()) + 1; // +1 for headings
        $range = 'A1:F' . $rowCount;

        return [
            $range => [
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
            'A1:F1' => [ // Header row bold
                'font' => ['bold' => true],
            ],
            'D' . $rowCount => [ // "Total" text in bold
                'font' => ['bold' => true],
            ],
            'F' . $rowCount => [ // Total Hours in bold
                'font' => ['bold' => true],
            ],
        ];
    }
}
