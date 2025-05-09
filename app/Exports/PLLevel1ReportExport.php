<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;  
use Maatwebsite\Excel\Concerns\WithTitle;  
use Maatwebsite\Excel\Concerns\WithHeadings; 
use Maatwebsite\Excel\Concerns\ShouldAutoSize;  
use Maatwebsite\Excel\Concerns\WithStyles;  
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;  
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class PLLevel1ReportExport implements FromArray, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
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
            ['Profit & Loss A/c'],  
            [$this->dateRange], 
            [],  
            ['Particulars', 'Amount','', 'Particulars', 'Amount']  
        ];  
    }  

    public function styles(Worksheet $sheet)  
    {  
        // Apply bold formatting to the first row (ledger name)  
        $sheet->getStyle('A1')->getFont()->setBold(true);  
        $sheet->getStyle('A3')->getFont()->setBold(true); 
        $sheet->getStyle('A5:E5')->getFont()->setBold(true);
        $borderStyle = [
            'borders' => [
                'top' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                ],
                'bottom' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                ],
            ],
        ];
    
        $sheet->getStyle('B10')->applyFromArray($borderStyle);
        $sheet->getStyle('E10')->applyFromArray($borderStyle);
    
        // Apply bold formatting to the last row only  
        $lastRow = $sheet->getHighestRow(); // Get the highest row number with data  
        $sheet->getStyle('A' . $lastRow . ':G' . $lastRow)->getFont()->setBold(true); // Last row only
        $sheet->getStyle('B:B')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('E:E')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        
    // Set border top and bottom for B10 and E10
    }   

    public function title(): string  
    {  
        return 'Profit Loss Report';  
    }
}
