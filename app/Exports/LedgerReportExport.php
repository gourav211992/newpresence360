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
        $totalRow = $lastRow - 1;

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
        $rowStart = 9; // Adjust according to your headings
        $rowEnd = $sheet->getHighestRow();

        $mergeCols = ['A', 'D', 'E', 'F', 'G', 'H']; // Skip B

        $currentStart = $rowStart;
        $lastVchNo = trim((string) $sheet->getCell("F{$rowStart}")->getValue());

        for ($row = $rowStart + 1; $row <= $rowEnd + 1; $row++) {
            $currentVchNo = trim((string) $sheet->getCell("F{$row}")->getValue());

            // Stop and merge block if voucher number changes or we're at the end
            if ($currentVchNo !== $lastVchNo || $row === $rowEnd + 1 || $currentVchNo === '') {
                $mergeEnd = $row - 1;

                if ($mergeEnd > $currentStart && $lastVchNo !== '') {
                    foreach ($mergeCols as $col) {
                        $sheet->mergeCells("{$col}{$currentStart}:{$col}{$mergeEnd}");
                        $alignment = $sheet->getStyle("{$col}{$currentStart}")->getAlignment();
                        $alignment->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

                        // Set horizontal alignment based on column
                        if (in_array($col, ['A', 'D', 'E', 'F'])) {
                            $alignment->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                        } elseif (in_array($col, ['G', 'H'])) {
                            $alignment->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                        }
                    }
                }

                $currentStart = $row;
                $lastVchNo = $currentVchNo;
            }
        }

        return [];
    }

    public function title(): string
    {
        return 'Ledger Report';
    }
}
