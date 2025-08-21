<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\DefaultGroupCompanyOrg;
use App\Helpers\ConstantHelper;
use App\Traits\Deletable;


class ExpenseMaster extends Model
{
    use HasFactory, SoftDeletes, Deletable, DefaultGroupCompanyOrg;

    protected $table = 'erp_expense_master';

    protected $fillable = [
        'hsn_id',
        'name',
        'alias',
        'percentage',
        'is_purchase',
        'is_sale',
        'expense_ledger_id',
        'expense_ledger_group_id',
        'service_provider_ledger_id',
        'service_provider_ledger_group_id',
        'status',
        'group_id',
        'company_id',
        'organization_id'
    ];

    public $referencingRelationships = [
        'hsn' => 'hsn_id',
    ];

    public function hsn()
    {
        return $this->belongsTo(Hsn::class);
    }

    public function expenseLedger()
    {
        return $this->belongsTo(Ledger::class, 'expense_ledger_id');
    }

    public function expenseLedgerGroup()
    {
        return $this->belongsTo(Group::class, 'expense_ledger_group_id');
    }

    public function serviceProviderLedger()
    {
        return $this->belongsTo(Ledger::class, 'service_provider_ledger_id');
    }

    public function serviceProviderLedgerGroup()
    {
        return $this->belongsTo(Group::class, 'service_provider_ledger_group_id');
    }
}
