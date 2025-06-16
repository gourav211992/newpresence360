<?php
namespace App\Exports;  

use Maatwebsite\Excel\Concerns\FromArray;  
use Maatwebsite\Excel\Concerns\WithTitle;  
use Maatwebsite\Excel\Concerns\WithHeadings; 
use Maatwebsite\Excel\Concerns\ShouldAutoSize;  
use Maatwebsite\Excel\Concerns\WithStyles;  
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet; 

class TrialBalanceReportExport implements FromArray, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
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
            ['Trial Balance'],  
            [$this->dateRange], 
            [],  
            ['Particulars','','', 'Opening Balance','Debit','Credit','Closing Balance']  
        ];  
    }  

    public function styles(Worksheet $sheet)  
    {  
        // Apply bold formatting to the first row (ledger name)  
        $sheet->getStyle('A1')->getFont()->setBold(true);  
        $sheet->getStyle('A3')->getFont()->setBold(true); 
        $sheet->getStyle('A5:G5')->getFont()->setBold(true);
        $sheet->getStyle('D')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('E')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('F')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('G')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        // Apply bold formatting to the last row only  
        $lastRow = $sheet->getHighestRow(); // Get the highest row number with data  
        //$sheet->getStyle('A' . $lastRow . ':G' . $lastRow)->getFont()->setBold(true); // Last row only
    }   

    public function title(): string  
    {  
        return 'Trial Balance Report';
    }
}