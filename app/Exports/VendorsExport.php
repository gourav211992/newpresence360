<?php

namespace App\Exports;

use App\Models\Vendor;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use App\Services\ItemImportExportService;

class VendorsExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $vendors;
    protected $service;

    public function __construct($vendors, ItemImportExportService $service)
    {
        $this->vendors = $vendors;
        $this->service = $service;
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
        ];
    }

    public function map($vendor): array
    {
    
        $generalData = [
            $vendor->company_name ?? null,                   
            $vendor->vendor_code ?? null,                            
            $vendor->subcategory->name ?? null,              
            $vendor->currency->short_name ?? null,               
            $vendor->paymentTerms->name ?? null,              
            $vendor->vendor_type ?? null,                      
            $vendor->vendor_sub_type ?? null,                
            $vendor->erpOrganizationType->name ?? null,      
            null,
            null, 
            null, 
            null, 
            null, 
            $vendor->email ?? null,                        
            $vendor->phone ?? null,                            
            $vendor->mobile ?? null,                         
            $vendor->whatsapp_number ?? null,                
            $vendor->notification ?? null,                     
            $vendor->pan_number ?? null,                     
            $vendor->tin_number ?? null,                    
            $vendor->aadhar_number ?? null,                   
            $vendor->ledger->code ?? null,                     
            $vendor->ledgerGroup->name ?? null,             
            $vendor->credit_limit ?? null,                   
            $vendor->credit_days ?? null,                     
            ($vendor->compliances->gst_applicable == 1) ? 'Yes' : 'No', 
            $vendor->compliances->gstin_no ?? null,          
            $vendor->compliances->gst_registered_name ?? null, 
            $vendor->compliances->gstin_registration_date ?? null, 
            ($vendor->compliances->tds_applicable == 1) ? 'Yes' : 'No', 
            $vendor->compliances->wef_date ?? null,            
            $vendor->compliances->tds_certificate_no ?? null,  
            $vendor->compliances->tds_tax_percentage ?? null,  
            $vendor->compliances->tds_category ?? null,        
            $vendor->compliances->tds_value_cab ?? null,     
            $vendor->compliances->tan_number ?? null,         
            ($vendor->compliances->msme_registered == 1) ? 'Yes' : 'No', 
            $vendor->compliances->msme_no ?? null,          
            $vendor->compliances->msme_type ?? null,                                                      
        ];

        $addresses = $vendor->addresses;

        if ($addresses && $addresses->count() > 0) {
            $address = $addresses->first();
            $generalData[9] = $address->country->name ?? null; 
            $generalData[10] = $address->state->name ?? null;  
            $generalData[11] = $address->city->name ?? null;   
            $generalData[12] = $address->address ?? null;        
            $generalData[13] = $address->pincode ?? null;      
        } else {
            $generalData[9] = null;  
            $generalData[10] = null; 
            $generalData[11] = null; 
            $generalData[12] = null; 
            $generalData[13] = null; 
        }

        return $generalData;
    }
    
    public function styles(Worksheet $sheet)
    {
        $styles = [];
        
        $yellowColumns = [1, 5, 6, 10, 11, 12, 13, 14]; 
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
            $sheet->getStyle("{$columnLetter}")->getAlignment()->setWrapText(true);
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
                $sheet->getStyle("{$columnLetter}")->getAlignment()->setWrapText(true);
               
            }
        }
    
        return $styles;
    }
}
