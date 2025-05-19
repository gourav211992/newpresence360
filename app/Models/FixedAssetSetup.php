<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\DefaultGroupCompanyOrg;
use App\Traits\Deletable;
use Illuminate\Database\Eloquent\SoftDeletes;


class FixedAssetSetup extends Model
{
    use HasFactory, DefaultGroupCompanyOrg, Deletable,softDeletes;

    protected $table = 'erp_finance_fixed_asset_setup';
     protected $guarded = ['id'];
     public function assetCategory()
    {
        return $this->belongsTo(ErpAssetCategory::class, 'asset_category_id');
    }

    public function ledger()
    {
        return $this->belongsTo(Ledger::class, 'ledger_id');
    }

    public function ledgerGroup()
    {
        return $this->belongsTo(Group::class, 'ledger_group_id');
    }
}
