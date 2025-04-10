<?php

namespace App\Exports;

use App\Models\TimeSheet;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class TimesheetSummaryExport implements FromCollection, WithHeadings, WithTitle, WithStyles, WithColumnWidths
{
    protected $start, $end, $employees, $project_id;
    protected $rowCount = 0;

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
                '#' => $counter++,
                'Name' => $employee->name,
                'Hours' => str_pad($RowtotalHours, 2, '0', STR_PAD_LEFT) . ":" . str_pad($RowremainingMinutes, 2, '0', STR_PAD_LEFT)
            ];

            $total += $EmpTotalHrs;
        }

        $totalHours = floor($total / 60);
        $remainingMinutes = $total % 60;

        // ➤ Add Total Row
        $summary[] = [
            '#' => '',
            'Name' => 'Total',
            'Hours' => str_pad($totalHours, 2, '0', STR_PAD_LEFT) . ":" . str_pad($remainingMinutes, 2, '0', STR_PAD_LEFT)
        ];

        $this->rowCount = count($summary) + 1; // for styles

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

    public function styles(Worksheet $sheet)
    {
        $lastRow = $this->rowCount;
        return [
            // Borders for the whole table
            'A1:C' . $lastRow => [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['argb' => '000000'],
                    ],
                ],
                'alignment' => [
                    'wrapText' => true,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
            'A1:C1' => [ // Bold headings
                'font' => ['bold' => true],
            ],
            'B' . $lastRow => [ // Bold Total
                'font' => ['bold' => true],
            ],
            'C' . $lastRow => [ // Bold Total Hours
                'font' => ['bold' => true],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 6,
            'B' => 30,
            'C' => 12,
        ];
    }
}

