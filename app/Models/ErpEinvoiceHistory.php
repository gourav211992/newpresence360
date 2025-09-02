<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ErpEinvoiceHistory extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'erp_einvoices_history';

    protected $fillable = [
        'source_id',
        'organization_id',
        'group_id',
        'company_id',
        'ack_no',
        'ack_date',
        'irn_number',
        'signed_invoice',
        'signed_qr_code',
        'ewb_no',
        'ewb_date',
        'ewb_valid_till',
        'status',
        'ewb_status',
        'cancel_date',
        'remarks',
        'created_by',
        'updated_by',
        'deleted_by'
    ];
}
