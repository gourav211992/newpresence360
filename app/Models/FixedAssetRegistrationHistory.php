<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\DefaultGroupCompanyOrg;
use App\Traits\Deletable;
use Illuminate\Support\Facades\DB;

class FixedAssetRegistrationHistory extends Model
{
    use HasFactory, SoftDeletes, DefaultGroupCompanyOrg, Deletable;

    protected $table = 'erp_finance_fixed_asset_registration_history';

    /**
     * Guarded attributes for mass assignment protection.
     */
    protected $guarded = ['id'];

    protected $dates = [
        'document_date',
        'capitalize_date',
        'supplier_invoice_date',
        'book_date',
        'revision_date',
        'deleted_at',
    ];

    /**
     * Relationships
     */

    // Relation to Organization
    public function book()
    {
        return $this->belongsTo(Book::class, 'book_id');
    }
    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }

    // Relation to Group
    public function group()
    {
        return $this->belongsTo(OrganizationGroup::class, 'group_id');
    }

    // Relation to Company
    public function company()
    {
        return $this->belongsTo(OrganizationCompany::class, 'company_id');
    }
    public function category()
    {
        return $this->belongsTo(ErpAssetCategory::class, 'category_id');
    }
    public function mrnHeader()
    {
        return $this->belongsTo(MrnHeader::class, 'mrn_header_id');
    }

    public function mrnDetail()
    {
        return $this->belongsTo(MrnDetail::class, 'mrn_detail_id');
    }
    public function ledger()
    {
        return $this->belongsTo(Ledger::class, 'ledger_id');
    }

    public function ledgerGroup()
    {
        return $this->belongsTo(Group::class, 'ledger_group_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Relation to Vendor
    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }
    
    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }
    public function issue_transfer(){
        return $this->hasOne(FixedAssetIssueTransfer::class, 'asset_id', 'id');
    }
    public function subAsset()
    {
        return $this->hasMany(FixedAssetSub::class, 'parent_id');
    }

    public function insurance(){
        return $this->hasMany(FixedAssetInsurance::class, 'asset_id', 'id');
    }

    public function getDepreciationsAttribute()
    {
        return FixedAssetDepreciation::whereRaw(
            "JSON_CONTAINS(assets, ?)", 
            [json_encode((string) $this->id)]
        )->get();
    }
    
}
