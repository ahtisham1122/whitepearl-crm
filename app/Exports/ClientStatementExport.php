<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * The individual client statement doesn't fit GenericReportExport's plain
 * headings-plus-rows shape -- it also has a summary block (owner, plot,
 * price, advance, due, received) printed above the schedule table, so it
 * gets its own small export class instead of stretching the generic one to
 * cover a layout it was never meant for.
 */
class ClientStatementExport implements FromArray, ShouldAutoSize, WithStyles, WithTitle
{
    public function __construct(private readonly array $statement)
    {
    }

    public function array(): array
    {
        $rows = [
            ['WHITE PEARL CITY'],
            ['(Sale Installment Schedule)'],
            [],
        ];

        foreach ($this->statement['summaryBlock'] as $label => $value) {
            $rows[] = [$label, $value];
        }

        $rows[] = [];
        $rows[] = $this->statement['headings'];

        foreach ($this->statement['rows'] as $row) {
            $rows[] = array_values($row);
        }

        return $rows;
    }

    public function title(): string
    {
        return substr($this->statement['filename'], 0, 31);
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            2 => ['font' => ['italic' => true]],
        ];
    }
}
