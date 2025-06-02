<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\DefaultGroupCompanyOrg;
use App\Helpers\Helper;
use App\Traits\Deletable;
use Illuminate\Support\Facades\DB;


class FixedAssetMerger extends Model
{
    use HasFactory, SoftDeletes, DefaultGroupCompanyOrg, Deletable;

    protected $table = 'erp_finance_fixed_asset_merger';

    protected $guarded = ['id'];
    public function book()
    {
        return $this->belongsTo(Book::class, 'book_id');
    }
    public function asset()
    {
        return $this->belongsTo(FixedAssetRegistration::class, 'asset_id');
    }
    public function location()
    {
        return $this->belongsTo(ErpStore::class, 'location_id');
    }
    public function cost_center()
    {
        return $this->belongsTo(CostCenter::class, 'cost_center_id');
    }
    public function subAsset()
    {
        return $this->belongsTo(FixedAssetSub::class, 'sub_asset_id');
    }
    public function category()
    {
        return $this->belongsTo(ErpAssetCategory::class, 'category_id');
    }
    public function ledger()
    {
        return $this->belongsTo(Ledger::class, 'ledger_id');
    }
    public function ledgerGroup()
    {
        return $this->belongsTo(Group::class, 'ledger_group_id');
    }
    // public function getAssetsAttribute()
    // {
    //     $assetIds = json_decode($this->attributes['assets'], true) ?? [];

    //     return FixedAssetRegistration::whereIn('id', $assetIds)->get();
    // }
    public static function makeRegistration($id)
    {
        $request = FixedAssetMerger::find($id);


        $parentURL = "fixed-asset_registration";



        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentURL);
        if (count($servicesBooks['services']) == 0) {
            return array(
                'status' => false,
                'message' => 'Service Not Found',
                'data' => []
            );
        }
        $firstService = $servicesBooks['services'][0];
        $series = Helper::getBookSeriesNew($firstService->alias, $parentURL)->first();
        if (!$series) {
            return array(
                'status' => false,
                'message' => 'Series Not Found',
                'data' => []
            );
        }


        $book = Helper::generateDocumentNumberNew($series->id, date('Y-m-d'));
        if ($book['document_number'] == null) {
            return array(
                'status' => false,
                'message' => 'Document Number Not Found',
                'data' => []
            );
        }

        $existingAsset = FixedAssetRegistration::withDefaultGroupCompanyOrg()->where('asset_code', $request->asset_code)->first();

        if ($existingAsset) {
            return array(
                'status' => false,
                'message' => 'Asset Code ' . $existingAsset->asset_code . ' already exists.',
                'data' => []
            );
        }




        // Step 1: Create main asset registration (only once per asset_code)
        $data = [
            'organization_id' => $request->organization_id,
            'group_id' => $request->group_id,
            'company_id' => $request->company_id,
            'created_by' => $request->created_by,
            'type' => $request->type,
            'book_id' => $series->id,
            'document_number' => $book['document_number'],
            'document_date' => $request->document_date,
            'doc_number_type' => $book['type'],
            'doc_reset_pattern' => $book['reset_pattern'],
            'doc_prefix' => $book['prefix'],
            'doc_suffix' => $book['suffix'],
            'doc_no' => $book['doc_no'],
            'asset_code' => $request->asset_code,
            'asset_name' => $request->asset_name,
            'quantity' => $request->quantity,
            'category_id' => $request->category_id,
            'reference_doc_id' => $request->id,
            'reference_series' => 'fixed-asset-merger',
            'ledger_id' => $request->ledger_id,
            'ledger_group_id' => $request->ledger_group_id,
            'capitalize_date' => $request->capitalize_date,
            'last_dep_date' => $request->capitalize_date,
            'currency_id' => $request->currency_id,
            'location_id' => $request->location_id,
            'cost_center_id' => $request->cost_center_id,
            'maintenance_schedule' => $request->maintenance_schedule,
            'depreciation_method' => $request->depreciation_method,
            'useful_life' => $request->useful_life,
            'salvage_value' => $request->salvage_value,
            'depreciation_percentage' => $request->depreciation_percentage,
            'depreciation_percentage_year' => $request->depreciation_percentage,
            'total_depreciation' => $request->total_depreciation,
            'dep_type' => $request->dep_type,
            'current_value' => $request->current_value,
            'current_value_after_dep' => $request->current_value,
            'document_status' => 'approved',
            'approval_level' => 1,
            'revision_number' => 0,
            'revision_date' => null,
            'status' => 'active',

        ];

        $asset = FixedAssetRegistration::create($data);
        FixedAssetSub::generateSubAssets($asset->id, $asset->asset_code, $asset->quantity, $asset->current_value, $asset->salvage_value);
        //delete old assets
        foreach (json_decode($request->asset_details) as $item) {
            foreach ($item->sub_asset_id as $sub) {
                $old = FixedAssetSub::find($sub);
                if ($old) {
                    if ($old->last_dep_date != $old->capitalize_date) {
                        $old->expiry_date = $item->last_dep_date;
                        $old->save();
                    } else {
                        $old->expiry_date = $old->last_dep_date;
                        $old->save();
                    }
                }
            }
        }
        return array(
            'status' => true,
            'message' => "Registration Added",
            'data' => []
        );
    }
}
