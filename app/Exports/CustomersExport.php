<?php

namespace App\Exports;

use App\Models\Customer;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use App\Services\ItemImportExportService;

class CustomersExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $customers;
    protected $service;

    public function __construct($customers, ItemImportExportService $service)
    {
        $this->customers = $customers;
        $this->service = $service;
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
        ];
    }

    public function map($customer): array
    {
        $generalData = [
            $customer->company_name ?? null,
            $customer->customer_code ?? null,
            $customer->subcategory->name ?? null,
            $customer->currency->short_name ?? null,
            $customer->paymentTerms->name ?? null,
            $customer->customer_type ?? null,
            $customer->erpOrganizationType->name ?? null,
            $customer->sales_person->name ?? null, 
            null, 
            null, 
            null, 
            null,
            null, 
            $customer->email ?? null,
            $customer->phone ?? null,
            $customer->mobile ?? null,
            $customer->whatsapp_number ?? null,
            $customer->notification ?? null,
            $customer->pan_number ?? null,
            $customer->tin_number ?? null,
            $customer->aadhar_number ?? null,
            $customer->ledger->code ?? null,
            $customer->ledgerGroup->name ?? null,
            $customer->credit_limit ?? null,
            $customer->credit_days ?? null,
            ($customer->compliances->gst_applicable == 1) ? 'Yes' : 'No',
            $customer->compliances->gstin_no ?? null,
            $customer->compliances->gst_registered_name ?? null,
            $customer->compliances->gstin_registration_date ?? null,
            ($customer->compliances->tds_applicable == 1) ? 'Yes' : 'No',
            $customer->compliances->wef_date ?? null,
            $customer->compliances->tds_certificate_no ?? null,
            $customer->compliances->tds_tax_percentage ?? null,
            $customer->compliances->tds_category ?? null,
            $customer->compliances->tds_value_cab ?? null,
            $customer->compliances->tan_number ?? null,
            $customer->remarks ?? null,
        ];
    
        $addresses = $customer->addresses;

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
        $yellowColumns = [1, 5, 6, 10, 11, 12,13,14]; 

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
