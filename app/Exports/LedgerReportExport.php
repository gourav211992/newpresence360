<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LedgerReportExport implements FromArray, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
{
    protected $data;
    protected $organizationName;
    protected $ledgerName;
    protected $dateRange;

    public function __construct(string $organizationName, string $ledgerName, string $dateRange, array $data)
    {
        $this->organizationName = $organizationName;
        $this->ledgerName = $ledgerName;
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
            [],
            [$this->ledgerName],
            [$this->dateRange],
            [],
            ['Date', 'Particulars','Amount','Series', 'Vch Type', 'Vch No.', 'Debit', 'Credit']
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Apply bold formatting to the first row (ledger name)
        $sheet->getStyle('A1')->getFont()->setBold(true);
        $sheet->getStyle('A3')->getFont()->setBold(true);
        $sheet->getStyle('F8:G8')->getFont()->setBold(true);
        $sheet->getStyle('A6:H6')->getFont()->setBold(true);
        // Align Amount (C), Debit (G), Credit (H) to left
        $sheet->getStyle('C')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('G')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('H')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

        // Apply bold formatting to the last row only
        $lastRow = $sheet->getHighestRow(); // Get the highest row number with data
        $sheet->getStyle('A' . ($lastRow - 2) . ':H' . $lastRow)->getFont()->setBold(true); // Last two rows
         $lastRow = $sheet->getHighestRow();

    // "Total" is the second-last row before "Closing Balance"
    $totalRow = $lastRow - 2;

    $styleArray = [
        'borders' => [
            'top' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
            'bottom' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
        ],
        'font' => [
            'bold' => true,
        ],
    ];

    // Apply the style only to the "Total" row (columns F to H)
    $sheet->getStyle("F{$totalRow}:H{$totalRow}")->applyFromArray($styleArray);

    return [];
    }

    public function title(): string
    {
        return 'Ledger Report';
    }
}
