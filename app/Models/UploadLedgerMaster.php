<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UploadLedgerMaster extends Model
{
    use HasFactory;
    protected $table = 'upload_ledger_masters';


    protected $fillable = [
        'name',
        'user_id',
        'code',
        'cost_center_id',
        'ledger_groups',
        'tax_percentage',
        'tax_type',
        'tds_section',
        'tds_percentage',
        'tcs_section',
        'tcs_percentage',
        'group_id',
        'company_id',
        'organization_id',
        'import_remarks',
        'status',
        'import_status',
    ];
}
