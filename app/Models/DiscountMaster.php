<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\DefaultGroupCompanyOrg;
use App\Helpers\ConstantHelper;
use App\Traits\Deletable;

class DiscountMaster extends Model
{
    use HasFactory, SoftDeletes,Deletable,DefaultGroupCompanyOrg;

    protected $table = 'erp_discount_master';

    protected $fillable = [
        'name', 
        'alias', 
        'percentage', 
        'discount_ledger_id', 
        'discount_ledger_group_id',
        'is_purchase', 
        'is_sale', 
        'group_id',     
        'company_id',       
        'organization_id',
        'status',
    ];

    public function erpLedger()
    {
        return $this->belongsTo(Ledger::class, 'discount_ledger_id');
    }

    public function ledgerGroup()
    {
        return $this->belongsTo(Group::class,'discount_ledger_group_id'); 
    }

}
