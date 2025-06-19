<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithEvents;

class CashflowExport implements FromView,WithEvents
{
    public function __construct(
        public $data,
    ) {}

    public function view(): View
    {
        return view('exports.cashflow-export', [
            'opening_balance' => $this->data['opening'],
            'payment_made' => $this->data['payment_made'],
            'payment_made_t' => $this->data['payment_made_t'],
            'payment_received' => $this->data['payment_received'],
            'payment_received_t' => $this->data['payment_received_t'],
            'closing_balance' => $this->data['closing'],
            'organization_id' => $this->data['organization_id'],
            'createdBy' => $this->data['createdBy'],
            'currency' => $this->data['currency'],
            'in_words' => $this->data['in_words'],
        ]);
    }
    public function registerEvents(): array
    {
        // Updated for Sr. No column
              
        return [
            AfterSheet::class => function (AfterSheet $event) {
                 $totalColumns = 7;
                $sheet = $event->sheet->getDelegate();
            for ($col = 0; $col < $totalColumns; $col++) {
                    $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col + 1);
                    $sheet->getColumnDimension($columnLetter)->setAutoSize(true);
                }
            }
        ];
        
    }

    // public function styles(Worksheet $sheet)
    // {
    //     return [
    //         // Header
    //         'A1:G1' => ['font' => ['bold' => true], 'borders' => ['allBorders' => ['borderStyle' => 'thin']]],
    //         // Apply borders to all
    //         'A1:G100' => ['borders' => ['allBorders' => ['borderStyle' => 'thin']]],
    //     ];
    // }
}
