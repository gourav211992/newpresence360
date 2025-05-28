<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;


class DebitorCreditoExcelExport implements FromView, WithStyles, WithColumnFormatting
{
    public function __construct(
        public $data,
    ) {}

    public function view(): View
    {
        return view('exports.report-debitor-creditor-export', [
            'entities' => $this->data['entities'],
            'group' => $this->data['group_name'] ?? null,
            'date' => $this->data['date']  ?? null,
            'date2' => $this->data['date2']  ?? null,
            'type' => $this->data['type'],
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Header
            'A1:I1' => ['font' => ['bold' => true]],
            // Apply borders to all
            // 'A1:I100' => ['borders' => ['allBorders' => ['borderStyle' => 'thin']]],
            // Grey background for A5 to AI5
            'A5:J5' => [
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'D9D9D9'], // light grey
                ],
                'font' => ['bold' => true],
            ],

        ];
    }

     public function columnFormats(): array
        {
            return [
                'I' => NumberFormat::FORMAT_NUMBER_00, // force 2 decimal places in column I
                'H' => NumberFormat::FORMAT_NUMBER_00, // force 2 decimal places in column I
            ];
        }
}
