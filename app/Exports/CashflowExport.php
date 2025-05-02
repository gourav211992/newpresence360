<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CashflowExport implements FromView, WithStyles
{
    public function __construct(
        public $data,
    ) {}

    public function view(): View
    {
        return view('exports.cashflow-export', [
            'opening_balance' => $this->data['opening'],
            'payment_made' => $this->data['payment_made'],
            'payment_received' => $this->data['payment_received'],
            'closing_balance' => $this->data['closing'],
            'organization_id' => $this->data['organization_id'],
            'createdBy' => $this->data['createdBy'],
            'currency' => $this->data['currency'],
            'in_words' => $this->data['in_words'],
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Header
            'A1:G1' => ['font' => ['bold' => true], 'borders' => ['allBorders' => ['borderStyle' => 'thin']]],
            // Apply borders to all
            'A1:G100' => ['borders' => ['allBorders' => ['borderStyle' => 'thin']]],
        ];
    }
}
