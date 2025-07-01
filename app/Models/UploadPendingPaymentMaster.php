<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UploadPendingPaymentMaster extends Model
{
    use HasFactory;
    protected $table = 'upload_pending_payment_masters';


    protected $fillable = [
        'user_id',
        'ledger_name',
        'ledger_group',
        'voucher_no',
        'amount',
        'balance',
        'settle_amount',
        'group_id',
        'company_id',
        'organization_id',
        'import_remarks',
        'status',
        'import_status',
    ];
}
