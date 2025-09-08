<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\DefaultGroupCompanyOrg;
use App\Traits\Deletable;
use Illuminate\Support\Facades\DB;

class FixedAssetRegistration extends Model
{
    use HasFactory, SoftDeletes, DefaultGroupCompanyOrg, Deletable;

    protected $table = 'erp_finance_fixed_asset_registration';

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
    public function it_category()
    {
        return $this->belongsTo(ErpAssetCategory::class, 'it_category_id');
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
    public function location()
    {
        return $this->belongsTo(ErpStore::class, 'location_id');
    }
    public function cost_center()
    {
        return $this->belongsTo(CostCenter::class, 'cost_center_id');
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
    public function getCgstValueAttribute()
    {
        $tedRecords = MrnExtraAmount::where('mrn_detail_id', $this->mrn_detail_id)
            ->where('mrn_header_id', $this->mrn_header_id)
            ->where('ted_type', '=', 'Tax')
            ->where('ted_level', '=', 'D')
            ->where('ted_code', '=', 'CGST')
            ->sum('ted_amount');

        $tedRecord = MrnExtraAmount::with(['taxDetail'])
            ->where('mrn_detail_id', $this->mrn_detail_id)
            ->where('mrn_header_id', $this->mrn_header_id)
            ->where('ted_type', '=', 'Tax')
            ->where('ted_level', '=', 'D')
            ->where('ted_code', '=', 'CGST')
            ->first();


        return [
            'rate' => @$tedRecord->taxDetail->tax_percentage,
            'ledger' => @$tedRecord->taxDetail->ledger->id,
            'ledger_group' => @$tedRecord->taxDetail->ledgerGroup->id,
            'value' => $tedRecords ?? 0.00
        ];
    }

    public function getSgstValueAttribute()
    {
        $tedRecords = MrnExtraAmount::where('mrn_detail_id', $this->mrn_detail_id)
            ->where('ted_type', '=', 'Tax')
            ->where('ted_level', '=', 'D')
            ->where('ted_code', '=', 'SGST')
            ->sum('ted_amount');

        $tedRecord = MrnExtraAmount::with(['taxDetail'])
            ->where('mrn_detail_id', $this->mrn_detail_id)
            ->where('mrn_header_id', $this->mrn_header_id)
            ->where('ted_type', '=', 'Tax')
            ->where('ted_level', '=', 'D')
            ->where('ted_code', '=', 'SGST')
            ->first();


        return [
            'rate' => @$tedRecord->taxDetail->tax_percentage,
            'ledger' => @$tedRecord->taxDetail->ledger->id,
            'ledger_group' => @$tedRecord->taxDetail->ledgerGroup->id,
            'value' => $tedRecords ?? 0.00
        ];
    }

    public function getIgstValueAttribute()
    {
        $tedRecords = MrnExtraAmount::where('mrn_detail_id', $this->mrn_detail_id)
            ->where('ted_type', '=', 'Tax')
            ->where('ted_level', '=', 'D')
            ->where('ted_code', '=', 'IGST')
            ->sum('ted_amount');

        $tedRecord = MrnExtraAmount::with(['taxDetail'])
            ->where('mrn_detail_id', $this->mrn_detail_id)
            ->where('mrn_header_id', $this->mrn_header_id)
            ->where('ted_type', '=', 'Tax')
            ->where('ted_level', '=', 'D')
            ->where('ted_code', '=', 'IGST')
            ->first();


        return [
            'rate' => @$tedRecord->taxDetail->tax_percentage,
            'ledger' => @$tedRecord->taxDetail->ledger->id,
            'ledger_group' => @$tedRecord->taxDetail->ledgerGroup->id,
            'value' => $tedRecords ?? 0.00
        ];
    }
    public function updateTotalDep()
    {
        $total = $this->subAsset()->sum('total_depreciation'); // Soft-deleted are excluded automatically
        $this->total_depreciation = $total;
        $this->save();
        return $total;
    }
    public function updateUniqueCodes()
    {
        $sub_assets = $this->subAsset()->orderBy('id')->get();
       
        $detail = $this->mrnDetail;
      
        if ($detail && $detail->uniqueCodes && $detail->uniqueCodes->isNotEmpty()) {
            $codes = $detail->uniqueCodes->values(); // reset keys
            DB::transaction(function () use ($codes, $sub_assets) {
                foreach ($codes as $index => $code) {
                    if (isset($sub_assets[$index])) {
                        $sub_assets[$index]->update([
                            'uid' => $code->uid ?? null,
                            'batch_number' => $code->batch_number ?? null,
                            'manufacturing_year' => $code->manufacturing_year ?? null,
                        ]);
                        $code->sub_asset_id = $sub_assets[$index]->id;
                        $code->save();
                    }
                }
            });
        }else if ($detail && $detail->batches && $detail->batches->isNotEmpty()) {
                $batches = $detail->batches->values(); // reset keys
                DB::transaction(function () use ($batches, $sub_assets) {
                $i = 0;
                 foreach($batches as $batch){
                    for($j=0;$j<(int)$batch->inventory_uom_qty;$j++){
                         $sub_assets[$i]->update([
                            'batch_number' => $batch->batch_number ?? null,
                            'manufacturing_year' => $batch->manufacturing_year ?? null,
                        ]);
                    $i++;
                    }
                }
                });
            }
        
    }
    public function batchupdateUniqueCodes($uniqueCodes, $batch,$offset)
{
    // Fetch all sub-assets for this asset and order by ID
    $sub_assets = $this->subAsset()->orderBy('id')->get();

    // Reset keys in case $uniqueCodes is a collection
    $codes = $uniqueCodes->values();

    DB::transaction(function () use ($codes, $batch, $sub_assets,$offset) {
        $batchQty = (int)$batch->inventory_uom_qty;

        for ($j = 0; $j < $batchQty; $j++) {
            if (isset($sub_assets[$j])) {
                $sub_assets[$j]->update([
                    // Assign unique code if available, otherwise null
                    'uid' => $codes[$offset + $j]->item_uid ?? null,
                    'batch_number' => $batch->batch_number ?? null,
                    'manufacturing_year' => $batch->manufacturing_year ?? null,
                ]);
            }
        }

    });
}




    
}
