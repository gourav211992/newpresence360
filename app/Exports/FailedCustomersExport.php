<?php

namespace App\Exports;

use App\Models\Customer;  
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class FailedCustomersExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $customers;

    public function __construct($customers)
    {
        $this->customers = $customers;
    }

    public function collection()
    {
        return $this->customers;
    }

    public function headings(): array
    {
        return [
            'Customer Name',       
            'Customer Code',                   
            'Group',          
            'Currency',               
            'Payment Term',           
            'Customer Type',          
            'Organization Type',      
            'Sales Person',           
            'Country',                
            'State',                 
            'City',                 
            'Address',               
            'Pin Code',               
            'Email ID',              
            'Phone No.',              
            'Mobile No.',             
            'WhatsApp No.',           
            'Notification Mode',     
            'PAN No.',             
            'TIN No.',              
            'Adhaar No.',              
            'Ledger Code',            
            'Ledger Group',           
            'Credit Limit',          
            'Credit Days',            
            'GST Registered',          
            'GSTIN No.',              
            'GST Registered Name',    
            'GSTIN Reg. Date',        
            'TDS Applicable',          
            'TDS WEF Date',          
            'TDS Certificate No.',    
            'TDS Tax %',              
            'TDS Category',           
            'TDS Value Cap',          
            'TAN No.',  
            'Remarks',                
        ];
    }

    public function map($customer): array
    {
        return [
            $customer->company_name ?? null,
            $customer->customer_code ?? null,
            $customer->subcategory ?? null,
            $customer->currency ?? null,
            $customer->payment_term ?? null,
            $customer->customer_type ?? null,
            $customer->organization_type ?? null,
            $customer->sales_person ?? null,
            $customer->country ?? null,
            $customer->state ?? null,
            $customer->city ?? null,
            $customer->address ?? null,
            $customer->pin_code ?? null,
            $customer->email ?? null,
            $customer->phone ?? null,
            $customer->mobile ?? null,
            $customer->whatsapp_number ?? null,
            $customer->notification_mode ?? null,
            $customer->pan_number ?? null,
            $customer->tin_number ?? null,
            $customer->aadhar_number ?? null,
            $customer->ledger_code ?? null,
            $customer->ledger_group ?? null,
            $customer->credit_limit ?? null,
            $customer->credit_days ?? null,
            $customer->gst_applicable ?? null,
            $customer->gstin_no ?? null,
            $customer->gst_registered_name ?? null,
            $customer->gstin_registration_date ?? null,
            $customer->tds_applicable ?? null,
            $customer->wef_date ?? null,
            $customer->tds_certificate_no ?? null,
            $customer->tds_tax_percentage ?? null,
            $customer->tds_category ?? null,
            $customer->tds_value_cab ?? null,
            $customer->tan_number ?? null,
            $customer->remarks ?? null,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $styles = [];
        $yellowColumns = [1, 5, 6, 10, 11, 12,13,14]; 
        $totalColumns = count($this->headings());
        $remarksColIndex = $totalColumns; 
        foreach ($yellowColumns as $col) {
            $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
            $styles["{$columnLetter}1"] = [
                'font' => [
                    'color' => ['argb' => 'FF000000'],
                    'bold' => true,
                ],
                'fill' => [
                    'fillType' => 'solid',
                    'startColor' => ['argb' => 'FFFF00'] 
                ],
                'alignment' => [
                   'wrapText' => true, 
                   'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                   'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
               ],
               'borders' => [
                   'allBorders' => [
                       'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                       'color' => ['argb' => 'FF000000'],
                   ],
               ],
            ];
            $sheet->getColumnDimension($columnLetter)->setWidth(15);
            if ($col !== $remarksColIndex) {
                $sheet->getStyle("{$columnLetter}")->getAlignment()->setWrapText(true);
            }
        }

        $totalColumns = count($this->headings());
        for ($col = 1; $col <= $totalColumns; $col++) {
            if (!in_array($col, $yellowColumns)) {
                $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                $styles["{$columnLetter}1"] = [
                    'font' => [
                        'color' => ['argb' => 'FF000000'],
                        'bold' => true,
                    ],
                    'fill' => [
                        'fillType' => 'solid',
                        'startColor' => ['argb' => 'D3D3D3'] 
                    ],
                    'alignment' => [
                       'wrapText' => true, 
                       'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                       'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                   ],
                   'borders' => [
                       'allBorders' => [
                           'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                           'color' => ['argb' => 'FF000000'],
                       ],
                   ],
                ];
                $sheet->getColumnDimension($columnLetter)->setWidth(15);
                if ($col !== $remarksColIndex) {
                    $sheet->getStyle("{$columnLetter}")->getAlignment()->setWrapText(true);
                }
            }
        }

        return $styles;
    }
}
