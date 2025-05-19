<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\DefaultGroupCompanyOrg;
use App\Traits\Deletable;

class FixedAssetIssueTransfer extends Model
{
    use HasFactory, DefaultGroupCompanyOrg, Deletable;

    protected $table = 'erp_finance_fixed_asset_issue_transfer';
     protected $guarded = ['id'];

    public function authorizedPerson()
    {
        return $this->belongsTo(Employee::class, 'authorized_person');
    }
    public function asset()
    {
        return $this->belongsTo(FixedAssetRegistration::class, 'asset_id','id');
    }
}
