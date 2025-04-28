<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemDetailHistory extends Model
{
    protected $table = 'erp_item_details_history';

    use HasFactory;

    protected $fillable = [
        'source_id',
        'voucher_id',
        'ledger_id',
        'debit_amt',
        'credit_amt',
        'cost_center_id',
        'notes',
        'opening',
        'closing',
        'opening_type',
        'closing_type',
        'group_id',
        'company_id',
        'organization_id'
    ];

    public function voucher()
    {
        return $this->belongsTo(VoucherHistory::class,'voucher_id','id');
    }

    public function ledger()
    {
        return $this->belongsTo(Ledger::class);
    }

    public function costCenter()
    {
        return $this->belongsTo(CostCenter::class, 'cost_center_id');
    }
}
