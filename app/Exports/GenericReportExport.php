<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * One reusable Excel export for every report. It doesn't know anything
 * about clients, plots, or payments -- it just turns the report array
 * (see ReportService) into rows of a spreadsheet. Every report reuses
 * this same class instead of each needing its own Export class.
 */
class GenericReportExport implements FromArray, ShouldAutoSize, WithHeadings, WithStyles, WithTitle
{
    public function __construct(private readonly array $report)
    {
    }

    public function array(): array
    {
        $rows = $this->report['rows']->map(fn (array $row) => array_values($row))->all();

        if ($this->report['totalsRow'] ?? null) {
            $rows[] = array_values($this->report['totalsRow']);
        }

        return $rows;
    }

    public function headings(): array
    {
        return $this->report['headings'];
    }

    public function title(): string
    {
        return substr($this->report['title'], 0, 31);
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
