<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GstrCompiledData extends Model
{
    use HasFactory;

    protected $table = 'erp_gstr_compiled_data';

    protected $fillable = [
        'voucher_id', //CONFIRMED - ALL
        'invoice_id', //CONFIRMED - ALL
        'invoice_no', //(NEED TO DISCUSS - document_number or book_code + document_number) - ALL
        'invoice_date', //CONFIRMED - ALL
        'revised_invoice_no', // (IF AMENDEMENT IS MADE) - ALL
        'revised_invoice_date', // (IF AMENDEMENT IS MADE) - ALL
        'supply_type',
        'invoice_type',
        'invoice_type_id',
        'group_id',
        'company_id',
        'organization_id',
        'party_name',//CONFIRMED - B2B, SEZ, DE
        'party_gstin',//CONFIRMED - B2B, SE
        'voucher_type',
        'voucher_no',
        'taxable_amt',
        'pos',
        'place_of_supply',
        'reverse_charge', // ?? - B2B
        'hsn_code',
        'uqc',
        'e_commerce_gstin',
        'revised_ecom_gstin',
        'ecom_operator_name',
        'rate',
        'sgst',
        'cgst',
        'igst',
        'cess',
        'invoice_amt',
        'applicable_tax_rate', //CONFIRMED - Percent of Tax Applicable - B2B
        'is_conflict',
        'conflict_msg',
        'note_date',
        'note_type',
        'note_value',
        'note_number',
        'revised_note_no',
        'revised_note_date',
        'ur_type',
        'exp_type',
        'port_code',
        'shipping_bill_no',
        'shipping_bill_date',
        'description',
        'expt_amt',
        'non_gst_amt',
        'nil_amt',
        'qty',
        'nature_of_document',
        'sr_no_from',
        'sr_no_to',
        'total_number',
        'cancelled',
        'net_value_of_supplies',
        'supplier_gstin',
        'supplier_name',
        'doc_no',
        'doc_date',
        'revised_doc_no',
        'revised_doc_date',
        'doc_type',
        'value_of_supplies_made',
        'year',
        'month',
    ];
}
