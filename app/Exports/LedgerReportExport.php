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
            ['Date', '', 'Particulars', 'Vch Type', 'Vch No.', 'Debit', 'Credit']  
        ];  
    }  

    public function styles(Worksheet $sheet)  
    {  
        // Apply bold formatting to the first row (ledger name)  
        $sheet->getStyle('A1')->getFont()->setBold(true);  
        $sheet->getStyle('A3')->getFont()->setBold(true); 
        $sheet->getStyle('A6:G6')->getFont()->setBold(true);

        // Apply bold formatting to the last row only  
        $lastRow = $sheet->getHighestRow(); // Get the highest row number with data  
        $sheet->getStyle('A' . ($lastRow - 2) . ':G' . $lastRow)->getFont()->setBold(true); // Last two rows  
    }   

    public function title(): string  
    {  
        return 'Ledger Report';  
    }
}
