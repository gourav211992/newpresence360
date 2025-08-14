<?php

namespace App\Exports\finance;

use App\Helpers\ConstantHelper;
use App\Helpers\GeneralHelper;
use App\Models\ErpGstInvoiceType;
use App\Models\Finance\GstrCompiledData;
use Carbon\Carbon;

class GstrDetailExport
{
    /**
    * @return \Illuminate\Support\Collection
    */

    public function export($fileName,$request,$id, $invoiceTypeName, $supplierGstin)
    {
        $startDate = Carbon::now()->startOfMonth(); // Start of the current month
        $endDate = Carbon::now()->endOfMonth(); 

        // Check if there's an applied date filter
        if ($request->has('date_range') && $request->date_range != '') {
            $dates = explode(' to ', $request->date_range);
            $startDate = $dates[0] ? Carbon::parse($dates[0])->startOfDay() : null;
            $endDate = isset($dates[1]) ? Carbon::parse($dates[1])->startOfDay():  Carbon::parse($dates[0])->startOfDay();
        }

        $masterConnection = config('database.connections.mysql_master.database');
        
        $gstrData = GstrCompiledData::where(function($q) use($request){
                if($request->search){
                    $q->where(function($query) use($request){
                        $query->where('erp_gstr_compiled_data.party_name', 'like', '%' . $request->search . '%')
                        ->orWhere('erp_gstr_compiled_data.party_gstin', 'like', '%' . $request->search . '%');
                    });
                }

                if($request->group_id){
                    $q->where('erp_gstr_compiled_data.group_id', 'like', '%' . $request->group_id . '%');
                }
                
                if($request->company_id){
                    $q->where('erp_gstr_compiled_data.company_id', 'like', '%' . $request->company_id . '%');
                }

                if($request->organization_id){
                    $q->where('erp_gstr_compiled_data.organization_id', 'like', '%' . $request->organization_id . '%');
                }
            })
        ->whereBetween('erp_gstr_compiled_data.invoice_date', [$startDate, $endDate])
        ->where('invoice_type_id',$id)
        ->where('supplier_gstin',$supplierGstin)
        ->chunk(1000, function ($gstrData) use ($fileName, $invoiceTypeName) {
            $this->writeToCsv($gstrData, $fileName, $invoiceTypeName);
        });
    }

    /**
     * Write data to CSV file
     * 
     * @param $gstrInvoiceTypes
     * @param string $fileName
     */
    private function writeToCsv($data, $fileName, $invoiceTypeName)
    {   
        switch ($invoiceTypeName) {
            case 'b2b':
                $csvData = self::prepareB2bData($data);
                break;
            case 'b2ba':
                $csvData = self::prepareB2baData($data);
                break;
            case 'b2cl':
                $csvData = self::prepareB2clData($data);
                break;
            case 'b2cla':
                $csvData = self::prepareB2claData($data);
                break;
            case 'b2cs':
                $csvData = self::prepareB2csData($data);
                break;
            case 'b2csa':
                $csvData = self::prepareB2csaData($data);
                break;
            case 'cdnr':
                $csvData = self::prepareCdnrData($data);
                break;
            case 'cdnra':
                $csvData = self::prepareCdnraData($data);
                break;
            case 'cdnur':
                $csvData = self::prepareCdnurData($data);
                break;
            case 'cdnura':
                $csvData = self::prepareCdnuraData($data);
                break;
            case 'supeco':
                $csvData = self::prepareSupecoData($data);
                break;
            case 'supecoa':
                $csvData = self::prepareSupecoaData($data);
                break;
            case 'ecob2b':
                $csvData = self::prepareEcob2bData($data);
                break;
            case 'ecob2c':
                $csvData = self::prepareEcob2cData($data);
                break;
            case 'ecourp2b':
                $csvData = self::prepareEcourp2bData($data);
                break;
            case 'ecourp2c':
                $csvData = self::prepareEcourp2cData($data);
                break;
            case 'ecoab2b':
                $csvData = self::prepareEcoab2bData($data);
                break;
            case 'ecoab2c':
                $csvData = self::prepareEcoab2cData($data);
                break;
            case 'ecoaurp2b':
                $csvData = self::prepareEcoaurp2bData($data);
                break;
            case 'ecoaurp2c':
                $csvData = self::prepareEcoaurp2cData($data);
                break;
            case 'doc_issue':
                $csvData = self::prepareDocIssueData($data);
                break;
            case 'at':
                $csvData = self::prepareAtData($data);
                break;
            case 'ata':
                $csvData = self::prepareAtaData($data);
                break;
            case 'txpd':
                $csvData = self::prepareTxpdData($data);
                break;
            case 'txpda':
                $csvData = self::prepareTxpdaData($data);
                break;
            case 'nil':
                $csvData = self::prepareNilData($data);
                break;
            case 'exp':
                $csvData = self::prepareExpData($data);
                break;
            case 'expa':
                $csvData = self::prepareExpaData($data);
                break;
            case 'hsn':
                $csvData = self::prepareHsnData($data);
                break;
            default:
                $csvData = [
                    'header' => [],
                    'rows' => []
                ];
        }

        $filePath = public_path($fileName);
        $directoryPath = dirname($filePath);

        // Check if the directory exists, and create it if not
        if (!is_dir($directoryPath)) {
            mkdir($directoryPath, 0777, true);  // 0777 is the permission, true makes it recursive
        }

        $handle = fopen($filePath, 'w');

        // Write header
        fputcsv($handle, $csvData['header']);

        // Write rows
        foreach ($csvData['rows'] as $row) {
            fputcsv($handle, $row);
        }

        fclose($handle); // Close the file after all chunks are processed
    }

    public static function prepareB2bData($data){
        $header = [
            'GSTIN/UIN of Recipient',
            'Receiver Name',
            'Invoice Number',
            'Invoice date', 
            'Invoice Value',
            'Place Of Supply', 
            'Reverse Charge', 
            'Applicable % of Tax Rate', 
            'Invoice Type', 
            'E-Commerce GSTIN', 
            'Rate', 
            'Taxable Value', 
            'Cess Amount'
        ];

        $rows = [];

        foreach ($data as $item) {
            $rows[] = [
                $item->party_gstin,
                $item->party_name ? $item->party_name : '', 
                $item->invoice_no ? $item->invoice_no : '', 
                $item->invoice_date ? GeneralHelper::dateFormat3($item->invoice_date) : '', 
                $item->invoice_amt ?  $item->invoice_amt : '',
                $item->place_of_supply ? $item->pos.''.$item->place_of_supply : '',
                $item->reverse_charge ? $item->reverse_charge : 0, 
                $item->applicable_tax_rate ? $item->applicable_tax_rate : 0, 
                $item->invoice_type ? $item->invoice_type : '', 
                $item->e_commerce_gstin ? $item->e_commerce_gstin : '', 
                $item->rate ? $item->rate.'%' : 0, 
                $item->taxable_amt ? $item->taxable_amt : 0, 
                $item->cess ? $item->cess : 0 
            ];
        }
        return [
            'header' => $header,
            'rows' => $rows
        ];
    }
 
    public static function prepareB2baData($data){
        $header = [
            'GSTIN/UIN of Recipient',	
            'Receiver Name',	
            'Original Invoice Number',	
            'Original Invoice date',	
            'Revised Invoice Number',	
            'Revised Invoice date',	
            'Invoice Value',	
            'Place Of Supply',	
            'Reverse Charge',	
            'Applicable % of Tax Rate',	
            'Invoice Type',	
            'E-Commerce GSTIN',	
            'Rate',	
            'Taxable Value',	
            'Cess Amount'
        ];

        $rows = [];

        foreach ($data as $item) {
            $rows[] = [
                $item->party_gstin,
                $item->party_name ? $item->party_name : '', 
                $item->invoice_no ? $item->invoice_no : '', 
                $item->invoice_date ? GeneralHelper::dateFormat3($item->invoice_date) : '', 
                $item->revised_invoice_no ? $item->revised_invoice_no : '', 
                $item->revised_invoice_date ? GeneralHelper::dateFormat3($item->revised_invoice_date) : '', 
                $item->invoice_amt ?  $item->invoice_amt : '',
                $item->place_of_supply ?  $item->pos.''.$item->place_of_supply : '',
                $item->reverse_charge ? $item->reverse_charge : 0, 
                $item->applicable_tax_rate ? $item->applicable_tax_rate : 0, 
                $item->invoice_type ? $item->invoice_type : '', 
                $item->e_commerce_gstin ? $item->e_commerce_gstin : '', 
                $item->rate ? $item->rate.'%' : 0, 
                $item->taxable_amt ? $item->taxable_amt : 0, 
                $item->cess ? $item->cess : 0,
            ];
        }
        return [
            'header' => $header,
            'rows' => $rows
        ];
    }

    public static function prepareB2clData($data){
        $header = [
            'Invoice Number',
            'Invoice date', 
            'Invoice Value',
            'Place Of Supply', 
            'Applicable % of Tax Rate', 
            'Rate', 
            'Taxable Value', 
            'Cess Amount', 
            'E-Commerce GSTIN'
        ];

        $rows = [];

        foreach ($data as $item) {
            $rows[] = [
                $item->invoice_no ? $item->invoice_no : '', 
                $item->invoice_date ? GeneralHelper::dateFormat3($item->invoice_date) : '', 
                $item->invoice_amt ?  $item->invoice_amt : '',
                $item->place_of_supply ? $item->pos.''.$item->place_of_supply : '',
                $item->applicable_tax_rate ? $item->applicable_tax_rate : 0, 
                $item->rate ? $item->rate.'%' : 0, 
                $item->taxable_amt ? $item->taxable_amt : 0, 
                $item->cess ? $item->cess : 0,  
                $item->e_commerce_gstin ? $item->e_commerce_gstin : '' 
            ];
        }
        return [
            'header' => $header,
            'rows' => $rows
        ];
    }

    public static function prepareB2claData($data){
        $header = [
            'Original Invoice Number',
            'Original Invoice date', 
            'Original Place Of Supply',
            'Revised Invoice Number',
            'Revised Invoice date',  
            'Invoice Value',
            'Applicable % of Tax Rate', 
            'Rate', 
            'Taxable Value', 
            'Cess Amount', 
            'E-Commerce GSTIN'
        ];

        $rows = [];

        foreach ($data as $item) {
            $rows[] = [
                $item->invoice_no ? $item->invoice_no : '', 
                $item->invoice_date ? GeneralHelper::dateFormat3($item->invoice_date) : '', 
                $item->place_of_supply ? $item->pos.''.$item->place_of_supply : '',
                $item->revised_invoice_no ? $item->revised_invoice_no : '', 
                $item->revised_invoice_date ? GeneralHelper::dateFormat3($item->revised_invoice_date) : '',
                $item->invoice_amt ?  $item->invoice_amt : '',
                $item->applicable_tax_rate ? $item->applicable_tax_rate : 0, 
                $item->rate ? $item->rate.'%' : 0, 
                $item->taxable_amt ? $item->taxable_amt : 0, 
                $item->cess ? $item->cess : 0,  
                $item->e_commerce_gstin ? $item->e_commerce_gstin : '' 
            ];
        }
        return [
            'header' => $header,
            'rows' => $rows
        ];
    }

    public static function prepareB2csData($data){
        $header = [
            'Type',
            'Place Of Supply',
            'Rate', 
            'Applicable % of Tax Rate', 
            'Taxable Value', 
            'Cess Amount', 
            'E-Commerce GSTIN'
        ];

        $rows = [];

        foreach ($data as $item) {
            $rows[] = [
                $item->invoice_type ? $item->invoice_type : '', 
                $item->place_of_supply ? $item->pos.''.$item->place_of_supply : '',
                $item->rate ? $item->rate.'%' : 0, 
                $item->applicable_tax_rate ? $item->applicable_tax_rate : 0, 
                $item->taxable_amt ? $item->taxable_amt : 0, 
                $item->cess ? $item->cess : 0,  
                $item->e_commerce_gstin ? $item->e_commerce_gstin : '' 
            ];
        }
        return [
            'header' => $header,
            'rows' => $rows
        ];
    }

    public static function prepareB2csaData($data){
        $header = [
            'Financial Year',
            'Original Month',
            'Place Of Supply',
            'Type',
            'Rate', 
            'Applicable % of Tax Rate', 
            'Taxable Value', 
            'Cess Amount', 
            'E-Commerce GSTIN' 
        ];

        $rows = [];

        foreach ($data as $item) {
            $rows[] = [
                $item->year ?  $item->year : '',
                $item->month ?  \DateTime::createFromFormat('!m', $item->month)->format('F') : '',
                $item->place_of_supply ? $item->pos.''.$item->place_of_supply : '',
                $item->invoice_type ? $item->invoice_type : '', 
                $item->rate ? $item->rate.'%' : 0, 
                $item->applicable_tax_rate ? $item->applicable_tax_rate : 0, 
                $item->taxable_amt ? $item->taxable_amt : 0, 
                $item->cess ? $item->cess : 0,  
                $item->e_commerce_gstin ? $item->e_commerce_gstin : '' 
            ];
        }
        return [
            'header' => $header,
            'rows' => $rows
        ];
    }

    public static function prepareCdnrData($data){
        $header = [
            'GSTIN/UIN of Recipient',
            'Receiver Name',
            'Note Number',
            'Note date', 
            'Note Type', 
            'Place Of Supply', 
            'Note Value', 
            'Applicable % of Tax Rate', 
            'Rate', 
            'Taxable Value', 
            'Cess Amount', 
        ];

        $rows = [];

        foreach ($data as $item) {
            $rows[] = [
                $item->party_gstin,
                $item->party_name ? $item->party_name : '', 
                $item->note_number ? $item->note_number : '', 
                $item->note_date ? GeneralHelper::dateFormat3($item->note_date) : '', 
                $item->note_type ? $item->note_type : '', 
                $item->place_of_supply ? $item->pos.''.$item->place_of_supply : '',
                $item->note_value ? $item->note_value : '', 
                $item->applicable_tax_rate ? $item->applicable_tax_rate : 0, 
                $item->rate ? $item->rate.'%' : 0, 
                $item->taxable_amt ? $item->taxable_amt : 0, 
                $item->cess ? $item->cess : 0,  
            ];
        }
        return [
            'header' => $header,
            'rows' => $rows
        ];
    }

    public static function prepareCdnraData($data){
        $header = [
            'GSTIN/UIN of Recipient',
            'Receiver Name',
            'Original Note Number',
            'Original Note date', 
            'Revised Note Number',
            'Revised Note date', 
            'Note Type', 
            'Place Of Supply', 
            'Reverse Charge', 
            'Note Supply Type', 
            'Note Value', 
            'Applicable % of Tax Rate', 
            'Rate', 
            'Taxable Value', 
            'Cess Amount', 
        ];

        $rows = [];

        foreach ($data as $item) {
            $rows[] = [
                $item->party_gstin,
                $item->party_name ? $item->party_name : '', 
                $item->note_number ? $item->note_number : '', 
                $item->note_date ? GeneralHelper::dateFormat3($item->note_date) : '', 
                $item->revised_note_no ? $item->revised_note_no : '', 
                $item->revised_note_date ? GeneralHelper::dateFormat3($item->revised_note_date) : '', 
                $item->note_type ? $item->note_type : '', 
                $item->place_of_supply ? $item->pos.''.$item->place_of_supply : '',
                $item->reverse_charge ? $item->reverse_charge : '', 
                $item->note_type ? $item->note_type : '', 
                $item->note_value ? $item->note_value : '', 
                $item->applicable_tax_rate ? $item->applicable_tax_rate : 0, 
                $item->rate ? $item->rate.'%' : 0, 
                $item->taxable_amt ? $item->taxable_amt : 0, 
                $item->cess ? $item->cess : 0,  
            ];
        }
        return [
            'header' => $header,
            'rows' => $rows
        ];
    }

    public static function prepareCdnurData($data){
        $header = [
            'UR Type',
            'Note Number',
            'Note date', 
            'Note Type', 
            'Place Of Supply', 
            'Note Value', 
            'Applicable % of Tax Rate', 
            'Rate', 
            'Taxable Value', 
            'Cess Amount', 
        ];

        $rows = [];

        foreach ($data as $item) {
            $rows[] = [
                $item->ur_type,
                $item->note_number ? $item->note_number : '', 
                $item->note_date ? GeneralHelper::dateFormat3($item->note_date) : '', 
                $item->note_type ? $item->note_type : '', 
                $item->place_of_supply ? $item->pos.''.$item->place_of_supply : '',
                $item->note_value ? $item->note_value : '', 
                $item->applicable_tax_rate ? $item->applicable_tax_rate : 0, 
                $item->rate ? $item->rate.'%' : 0, 
                $item->taxable_amt ? $item->taxable_amt : 0, 
                $item->cess ? $item->cess : 0,  
            ];
        }
        return [
            'header' => $header,
            'rows' => $rows
        ];
    }

    public static function prepareCdnuraData($data){
        $header = [
            'UR Type',
            'Original Note Number',
            'Original Note date', 
            'Revised Note Number',
            'Revised Note date', 
            'Note Type', 
            'Place Of Supply', 
            'Note Value', 
            'Applicable % of Tax Rate', 
            'Rate', 
            'Taxable Value', 
            'Cess Amount', 
        ];

        $rows = [];

        foreach ($data as $item) {
            $rows[] = [
                $item->ur_type,
                $item->note_number ? $item->note_number : '', 
                $item->note_date ? GeneralHelper::dateFormat3($item->note_date) : '', 
                $item->revised_note_no ? $item->revised_note_no : '', 
                $item->revised_note_date ? GeneralHelper::dateFormat3($item->revised_note_date) : '', 
                $item->note_type ? $item->note_type : '', 
                $item->place_of_supply ? $item->pos.''.$item->place_of_supply : '',
                $item->note_value ? $item->note_value : '', 
                $item->applicable_tax_rate ? $item->applicable_tax_rate : 0, 
                $item->rate ? $item->rate.'%' : 0, 
                $item->taxable_amt ? $item->taxable_amt : 0, 
                $item->cess ? $item->cess : 0,  
            ];
        }
        return [
            'header' => $header,
            'rows' => $rows
        ];
    }

    public static function prepareSupecoData($data){
        $header = [
            'Nature of Supply',	
            'GSTIN of E-Commerce Operator',
            'E-Commerce Operator Name',	
            'Net value of supplies',	
            'Integrated tax',	
            'Central tax',	
            'State/UT tax',	
            'Cess',
        ];

        $rows = [];

        foreach ($data as $item) {
            $rows[] = [
                $item->nature_of_document ? ConstantHelper::NATURE_OF_DOCUMENT[$item->nature_of_document] : '',
                $item->e_commerce_gstin,
                $item->ecom_operator_name, 
                $item->net_value_of_supplies, 
                $item->igst, 
                $item->cgst, 
                $item->sgst, 
                $item->cess,  
            ];
        }
        return [
            'header' => $header,
            'rows' => $rows
        ];
    }

    public static function prepareSupecoaData($data){
        $header = [
            'Nature of Supply',	
            'Financial Year',	
            'Original Month/Quarter',	
            'Original GSTIN of E-Commerce Operator',
            'Revised GSTIN of E-Commerce Operator',
            'E-Commerce Operator Name',	
            'Revised Net value of supplies',	
            'Integrated tax',	
            'Central tax',	
            'State/UT tax',	
            'Cess',
        ];

        $rows = [];

        foreach ($data as $item) {
            $rows[] = [
                $item->nature_of_document ? ConstantHelper::NATURE_OF_DOCUMENT[$item->nature_of_document] : '',
                $item->year ?  $item->year : '',
                $item->month ?  \DateTime::createFromFormat('!m', $item->month)->format('F') : '',
                $item->e_commerce_gstin,
                $item->revised_ecom_gstin,
                $item->ecom_operator_name, 
                $item->net_value_of_supplies, 
                $item->igst, 
                $item->cgst, 
                $item->sgst, 
                $item->cess, 
            ];
        }
        return [
            'header' => $header,
            'rows' => $rows
        ];
    }

    public static function prepareEcob2bData($data){
        $header = [
            'Supplier GSTIN/UIN',	
            'Supplier Name',	
            'Recipient GSTIN/UIN',	
            'Recipient Name',	
            'Document Number',	
            'Document Date',	
            'Value of supplies made',	
            'Place Of Supply',	
            'Document type',	
            'Rate',	
            'Taxable Value',	
            'Cess Amount',
        ];

        $rows = [];

        foreach ($data as $item) {
            $rows[] = [
                $item->supplier_gstin ? $item->supplier_gstin : '',
                $item->supplier_name ? $item->supplier_name : '',
                $item->party_gstin ? $item->party_gstin : '',
                $item->party_name ? $item->party_name : '',
                $item->doc_no ? $item->doc_no : '',
                $item->doc_date ? GeneralHelper::dateFormat3($item->doc_date) : '',
                $item->value_of_supplies_made, 
                $item->place_of_supply ? $item->pos.''.$item->place_of_supply : '', 
                $item->doc_type, 
                $item->rate, 
                $item->taxable_amt, 
                $item->cess, 
            ];
        }
        return [
            'header' => $header,
            'rows' => $rows
        ];
    }

    public static function prepareEcob2cData($data){
        $header = [
            'Supplier GSTIN/UIN',	
            'Supplier Name',	
            'Place Of Supply',	
            'Taxable Value',	
            'Cess Amount',
        ];

        $rows = [];

        foreach ($data as $item) {
            $rows[] = [
                $item->supplier_gstin ? $item->supplier_gstin : '',
                $item->supplier_name ? $item->supplier_name : '',
                $item->place_of_supply ? $item->pos.''.$item->place_of_supply : '', 
                $item->taxable_amt, 
                $item->cess, 
            ];
        }
        return [
            'header' => $header,
            'rows' => $rows
        ];
    }

    public static function prepareEcourp2bData($data){
        $header = [
            'Recipient GSTIN/UIN',	
            'Recipient Name',	
            'Document Number',	
            'Document Date',	
            'Value of supplies made',	
            'Place Of Supply',	
            'Document type',	
            'Rate',	
            'Taxable Value',	
            'Cess Amount',
        ];

        $rows = [];

        foreach ($data as $item) {
            $rows[] = [
                $item->party_gstin ? $item->party_gstin : '',
                $item->party_name ? $item->party_name : '',
                $item->doc_no ? $item->doc_no : '',
                $item->doc_date ? GeneralHelper::dateFormat3($item->doc_date) : '',
                $item->value_of_supplies_made, 
                $item->place_of_supply ? $item->pos.''.$item->place_of_supply : '', 
                $item->doc_type, 
                $item->rate, 
                $item->taxable_amt, 
                $item->cess, 
            ];
        }
        return [
            'header' => $header,
            'rows' => $rows
        ];
    }

    public static function prepareEcourp2cData($data){
        $header = [
            'Place Of Supply',	
            'Taxable Value',	
            'Cess Amount',
        ];

        $rows = [];

        foreach ($data as $item) {
            $rows[] = [
                $item->place_of_supply ? $item->pos.''.$item->place_of_supply : '', 
                $item->taxable_amt, 
                $item->cess, 
            ];
        }
        return [
            'header' => $header,
            'rows' => $rows
        ];
    }

    public static function prepareEcoab2bData($data){
        $header = [
            'Supplier GSTIN/UIN',	
            'Supplier Name',	
            'Recipient GSTIN/UIN',	
            'Recipient Name',	
            'Original Document Number',	
            'Original Document Date',	
            'Revised Document Number',	
            'Revised Document Date',	
            'Value of supplies made',	
            'Place Of Supply',	
            'Document type',	
            'Rate',	
            'Taxable Value',	
            'Cess Amount',
        ];

        $rows = [];

        foreach ($data as $item) {
            $rows[] = [
                $item->supplier_gstin ? $item->supplier_gstin : '',
                $item->supplier_name ? $item->supplier_name : '',
                $item->party_gstin ? $item->party_gstin : '',
                $item->party_name ? $item->party_name : '',
                $item->doc_no ? $item->doc_no : '',
                $item->doc_date ? GeneralHelper::dateFormat3($item->doc_date) : '',
                $item->revised_doc_no ? $item->revised_doc_no : '',
                $item->revised_doc_date ? GeneralHelper::dateFormat3($item->revised_doc_date) : '',
                $item->value_of_supplies_made, 
                $item->place_of_supply ? $item->pos.''.$item->place_of_supply : '', 
                $item->doc_type, 
                $item->rate, 
                $item->taxable_amt, 
                $item->cess, 
            ];
        }
        return [
            'header' => $header,
            'rows' => $rows
        ];
    }

    public static function prepareEcoab2cData($data){
        $header = [
            'Financial Year',	
            'Original Month',	
            'Supplier GSTIN/UIN',	
            'Supplier Name',	
            'Place Of Supply',	
            'Taxable Value',	
            'Cess Amount',
        ];

        $rows = [];

        foreach ($data as $item) {
            $rows[] = [
                $item->year ?  $item->year : '',
                $item->month ?  \DateTime::createFromFormat('!m', $item->month)->format('F') : '', 
                $item->supplier_gstin ? $item->supplier_gstin : '',
                $item->supplier_name ? $item->supplier_name : '',
                $item->place_of_supply ? $item->pos.''.$item->place_of_supply : '', 
                $item->taxable_amt, 
                $item->cess, 
            ];
        }
        return [
            'header' => $header,
            'rows' => $rows
        ];
    }

    public static function prepareEcoaurp2bData($data){
        $header = [
            'Recipient GSTIN/UIN',	
            'Recipient Name',	
            'Original Document Number',	
            'Original Document Date',	
            'Revised Document Number',	
            'Revised Document Date',	
            'Value of supplies made',	
            'Place Of Supply',	
            'Document type',	
            'Rate',	
            'Taxable Value',	
            'Cess Amount',
        ];

        $rows = [];

        foreach ($data as $item) {
            $rows[] = [
                $item->party_gstin ? $item->party_gstin : '',
                $item->party_name ? $item->party_name : '',
                $item->doc_no ? $item->doc_no : '',
                $item->doc_date ? GeneralHelper::dateFormat3($item->doc_date) : '',
                $item->revised_doc_no ? $item->revised_doc_no : '',
                $item->revised_doc_date ? GeneralHelper::dateFormat3($item->revised_doc_date) : '',
                $item->value_of_supplies_made, 
                $item->place_of_supply ? $item->pos.''.$item->place_of_supply : '', 
                $item->doc_type, 
                $item->rate, 
                $item->taxable_amt, 
                $item->cess, 
            ];
        }
        return [
            'header' => $header,
            'rows' => $rows
        ];
    }

    public static function prepareEcoaurp2cData($data){
        $header = [
            'Financial Year',	
            'Original Month',	
            'Place Of Supply',	
            'Rate',	
            'Taxable Value',	
            'Cess Amount',
        ];

        $rows = [];

        foreach ($data as $item) {
            $rows[] = [
                $item->year ?  $item->year : '',
                $item->month ?  \DateTime::createFromFormat('!m', $item->month)->format('F') : '', 
                $item->place_of_supply ? $item->pos.''.$item->place_of_supply : '', 
                $item->rate ? $item->rate.'%' : 0, 
                $item->taxable_amt, 
                $item->cess, 
            ];
        }
        return [
            'header' => $header,
            'rows' => $rows
        ];
    }

    public static function prepareDocIssueData($data){
        $header = [
            'Nature of Document',	
            'Sr. No. From',	
            'Sr. No. To',	
            'Total Number',	
            'Cancelled',
        ];

        $rows = [];

        foreach ($data as $item) {
            $rows[] = [
                $item->nature_of_document,
                $item->sr_no_from ? $item->sr_no_from : '', 
                $item->sr_no_to ? $item->sr_no_to : '', 
                $item->total_number ? $item->total_number : '', 
                $item->cancelled ? $item->cancelled : '', 
            ];
        }
        return [
            'header' => $header,
            'rows' => $rows
        ];
    }

    public static function prepareAtData($data){
        $header = [
            'Place Of Supply',	
            'Applicable % of Tax Rate',	
            'Rate',	
            'Gross Advance Received',	
            'Cess Amount',
        ];

        $rows = [];

        foreach ($data as $item) {
            $rows[] = [
                $item->place_of_supply ?  $item->pos.''.$item->place_of_supply : '',
                $item->applicable_tax_rate ? $item->applicable_tax_rate : 0, 
                $item->rate ? $item->rate.'%' : 0, 
                $item->taxable_amt ? $item->taxable_amt : 0, 
                $item->cess ? $item->cess : 0,  
            ];
        }
        return [
            'header' => $header,
            'rows' => $rows
        ];
    }

    public static function prepareAtaData($data){
        $header = [
            'Financial Year',
            'Original Month',
            'Original Place Of Supply',	
            'Applicable % of Tax Rate',	
            'Rate',	
            'Gross Advance Received',	
            'Cess Amount',
        ];

        $rows = [];

        foreach ($data as $item) {
            $rows[] = [
                $item->year ?  $item->year : '',
                $item->month ?  \DateTime::createFromFormat('!m', $item->month)->format('F') : '',
                $item->place_of_supply ?  $item->pos.''.$item->place_of_supply : '',
                $item->applicable_tax_rate ? $item->applicable_tax_rate : 0, 
                $item->rate ? $item->rate.'%' : 0, 
                $item->taxable_amt ? $item->taxable_amt : 0, 
                $item->cess ? $item->cess : 0,   
            ];
        }
        return [
            'header' => $header,
            'rows' => $rows
        ];
    }

    public static function prepareTxpdData($data){
        $header = [
            'Place Of Supply',	
            'Applicable % of Tax Rate',	
            'Rate',	
            'Gross Advance Adjusted',	
            'Cess Amount',
        ];

        $rows = [];

        foreach ($data as $item) {
            $rows[] = [
                $item->place_of_supply ?  $item->pos.''.$item->place_of_supply : '',
                $item->applicable_tax_rate ? $item->applicable_tax_rate : 0, 
                $item->rate ? $item->rate.'%' : 0, 
                $item->taxable_amt ? $item->taxable_amt : 0, 
                $item->cess ? $item->cess : 0,    
            ];
        }
        return [
            'header' => $header,
            'rows' => $rows
        ];
    }

    public static function prepareTxpdaData($data){
        $header = [
            'Financial Year',
            'Original Month',
            'Original Place Of Supply',	
            'Applicable % of Tax Rate',	
            'Rate',	
            'Gross Advance Adjusted',	
            'Cess Amount',
        ];

        $rows = [];

        foreach ($data as $item) {
            $rows[] = [
                $item->year ?  $item->year : '',
                $item->month ?  \DateTime::createFromFormat('!m', $item->month)->format('F') : '',
                $item->place_of_supply ?  $item->pos.''.$item->place_of_supply : '',
                $item->applicable_tax_rate ? $item->applicable_tax_rate : 0, 
                $item->rate ? $item->rate.'%' : 0, 
                $item->taxable_amt ? $item->taxable_amt : 0, 
                $item->cess ? $item->cess : 0,      
            ];
        }
        return [
            'header' => $header,
            'rows' => $rows
        ];
    }

    public static function prepareNilData($data){
        $header = [
            'Description',
            'Nil Rated Supplies',
            'Exempted(other than nil rated/non GST supply)',
            'Non-GST Supplies',
        ];

        $rows = [];

        foreach ($data as $item) {
            $rows[] = [
                $item->description ? $item->description : '',
                $item->nil_amt ? $item->nil_amt : '',
                $item->expt_amt ? $item->expt_amt : '',
                $item->non_gst_amt ? $item->non_gst_amt : '', 
            ];
        }
        return [
            'header' => $header,
            'rows' => $rows
        ];
    }

    public static function prepareExpData($data){
        $header = [
            'Export Type',	
            'Invoice Number',	
            'Invoice date',	
            'Invoice Value',	
            'Port Code',	
            'Shipping Bill Number',	
            'Shipping Bill Date',	
            'Rate',	
            'Taxable Value',	
            'Cess Amount',
        ];

        $rows = [];

        foreach ($data as $item) {
            $rows[] = [
                $item->exp_type,
                $item->invoice_no ? $item->invoice_no : '', 
                $item->invoice_date ? GeneralHelper::dateFormat3($item->invoice_date) : '', 
                $item->invoice_amt ?  $item->invoice_amt : '',
                $item->port_code ? $item->port_code : '',
                $item->shipping_bill_no ? $item->shipping_bill_no : 0, 
                $item->shipping_bill_date ? GeneralHelper::dateFormat3($item->shipping_bill_date) : 0, 
                $item->rate ? $item->rate.'%' : 0, 
                $item->taxable_amt ? $item->taxable_amt : 0, 
                $item->cess ? $item->cess : 0,  
            ];
        }
        return [
            'header' => $header,
            'rows' => $rows
        ];
    }

    public static function prepareExpaData($data){
        $header = [
            'Export Type',	
            'Original Invoice Number',	
            'Original Invoice date',	
            'Revised Invoice Number',	
            'Revised Invoice date',	
            'Invoice Value',	
            'Port Code',	
            'Shipping Bill Number',	
            'Shipping Bill Date',	
            'Rate',	
            'Taxable Value',	
            'Cess Amount',
        ];

        $rows = [];

        foreach ($data as $item) {
            $rows[] = [
                $item->exp_type,
                $item->invoice_no ? $item->invoice_no : '', 
                $item->invoice_date ? GeneralHelper::dateFormat3($item->invoice_date) : '', 
                $item->revised_invoice_no ? $item->revised_invoice_no : '', 
                $item->revised_invoice_date ? GeneralHelper::dateFormat3($item->revised_invoice_date) : '', 
                $item->invoice_amt ?  $item->invoice_amt : '',
                $item->port_code ? $item->port_code : '',
                $item->shipping_bill_no ? $item->shipping_bill_no : 0, 
                $item->shipping_bill_date ? GeneralHelper::dateFormat3($item->shipping_bill_date) : 0, 
                $item->rate ? $item->rate.'%' : 0, 
                $item->taxable_amt ? $item->taxable_amt : 0, 
                $item->cess ? $item->cess : 0,   
            ];
        }
        return [
            'header' => $header,
            'rows' => $rows
        ];
    }

    public static function prepareHsnData($data){
        $header = [
            'HSN',	
            'Description',	
            'UQC',	
            'Total Quantity',	
            'Taxable Value',	
            'Integrated Tax Amount',	
            'Central Tax Amount',	
            'State/UT Tax Amount',	
            'Cess Amount',
            'Rate',	
        ];

        $rows = [];

        foreach ($data as $item) {
            $rows[] = [
                $item->hsn_code,
                $item->description ? $item->description : '', 
                $item->uqc ? $item->uqc : '', 
                $item->qty ? $item->qty : '', 
                $item->taxable_amt ? $item->taxable_amt : '',
                $item->igst ? $item->igst : 0, 
                $item->cgst ? $item->cgst : 0, 
                $item->sgst ? $item->sgst : 0, 
                $item->cess ? $item->cess : 0,  
                $item->rate ? $item->rate.'%' : 0,  
            ];
        }
        return [
            'header' => $header,
            'rows' => $rows
        ];
    }

}
