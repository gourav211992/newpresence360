<?php

namespace App\Exports;

use App\Models\UploadVendorMaster; 
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class FailedVendorsExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $vendors;

    public function __construct($vendors)
    {
        $this->vendors = $vendors;
    
      
    }

    public function collection()
    {
        return $this->vendors;
    }

    public function headings(): array
    {
        return [
            'Vendor Name',            
            'Vendor Code',                      
            'Group',          
            'Currency',               
            'Payment Term',          
            'Vendor Type',            
            'Sub Type',              
            'Organization Type',     
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
            'MSME Registered',       
            'MSME No.',              
            'MSME Type',   
            'Remarks',                   
        ];
    }

    public function map($vendor): array
    {
        return [
            $vendor->company_name ?? null,
            $vendor->vendor_code ?? null,
            $vendor->subcategory ?? null,
            $vendor->currency ?? null,
            $vendor->payment_term ?? null,
            $vendor->vendor_type ?? null,
            $vendor->vendor_sub_type ?? null,
            $vendor->organization_type ?? null,
            $vendor->country ?? null,
            $vendor->state ?? null,
            $vendor->city ?? null,
            $vendor->address ?? null,
            $vendor->pin_code ?? null,
            $vendor->email ?? null,
            $vendor->phone ?? null,
            $vendor->mobile ?? null,
            $vendor->whatsapp_number ?? null,
            $vendor->notification_mode ?? null,
            $vendor->pan_number ?? null,
            $vendor->tin_number ?? null,
            $vendor->aadhar_number ?? null,
            $vendor->ledger_code ?? null,
            $vendor->ledger_group ?? null,
            $vendor->credit_limit ?? null,
            $vendor->credit_days ?? null,
            $vendor->gst_applicable ?? null,
            $vendor->gstin_no ?? null,
            $vendor->gst_registered_name ?? null,
            $vendor->gstin_registration_date ?? null,
            $vendor->tds_applicable ?? null,
            $vendor->wef_date ?? null,
            $vendor->tds_certificate_no ?? null,
            $vendor->tds_tax_percentage ?? null,
            $vendor->tds_category ?? null,
            $vendor->tds_value_cab ?? null,
            $vendor->tan_number ?? null,
            $vendor->msme_registered ?? null,
            $vendor->msme_no ?? null,
            $vendor->msme_type ?? null,
            $vendor->remarks ?? null,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $styles = [];
        
        $yellowColumns = [1, 5, 6, 10, 11, 12, 13, 14]; 
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