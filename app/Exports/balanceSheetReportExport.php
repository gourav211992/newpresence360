<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class balanceSheetReportExport implements FromArray, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
{
    protected $data;
    protected $organizationName;
    protected $dateRange;

    public function __construct(string $organizationName, string $dateRange, array $data)
    {
        $this->organizationName = $organizationName;
        $this->dateRange = $dateRange;
        $this->data = $data;
    }

    public function array(): array
    {
        return $this->data;
    }

    public function headings(): array
    {
        return [
            [$this->organizationName],
            ['Balance Sheet'],
            [$this->dateRange],
            [],
            ['Liabilities','','','Amount', 'Assets','','', 'Amount']
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1')->getFont()->setBold(true);
        $sheet->getStyle('A3')->getFont()->setBold(true);
        $sheet->getStyle('A5:H5')->getFont()->setBold(true);
        foreach (['C', 'D', 'G', 'H'] as $col) {
            $sheet->getStyle($col)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        }
        // Apply bold formatting to the last row only
        $lastRow = $sheet->getHighestRow(); // Get the highest row number with data
        $sheet->getStyle('A' . $lastRow . ':H' . $lastRow)->getFont()->setBold(true); // Last row only
    }

    public function title(): string
    {
        return 'Balance Sheet Report';
    }
}
