<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Helpers\Helper;
use App\Traits\DefaultGroupCompanyOrg;
use App\Traits\Deletable;
use Illuminate\Support\Facades\DB;

class FixedAssetSplit extends Model
{
    use HasFactory, SoftDeletes, DefaultGroupCompanyOrg, Deletable;

    protected $table = 'erp_finance_fixed_asset_split';

    protected $guarded = ['id'];
    public function book()
    {
        return $this->belongsTo(Book::class, 'book_id');
    }
    public function asset()
    {
        return $this->belongsTo(FixedAssetRegistration::class, 'asset_id');
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
    public function location()
    {
        return $this->belongsTo(ErpStore::class, 'location_id');
    }
    public function cost_center()
    {
        return $this->belongsTo(CostCenter::class, 'cost_center_id');
    }
    // public function getAssetsAttribute()
    // {
    //     $assetIds = json_decode($this->attributes['assets'], true) ?? [];

    //     return FixedAssetRegistration::whereIn('id', $assetIds)->get();
    // }
    public static function makeRegistration($id)
    {
        $request = FixedAssetSplit::find($id);
        $grouped = collect(json_decode($request->sub_assets))->groupBy('asset_code');
        $parentURL = "fixed-asset_registration";



        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentURL);
        if (count($servicesBooks['services']) == 0) {
            return response()->json([
                'status' => 'exception',
                'data' => array(
                    'status' => false,
                    'message' => 'Service not found',
                    'data' => []
                )
            ]);
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


        foreach ($grouped as $assetCode => $items) {
            $firstItem = $items->first();
            $book = Helper::generateDocumentNumberNew($series->id, date('Y-m-d'));
            if ($book['document_number'] == null) {
                return array(
                    'status' => false,
                    'message' => 'Document Number Not Found',
                    'data' => []
                );
            }
            $existingAsset = FixedAssetRegistration::withDefaultGroupCompanyOrg()->where('asset_code', $assetCode)->first();

            if ($existingAsset) {
                return array(
                    'status' => false,
                    'message' => 'Asset Code ' . $existingAsset->asset_code . ' already exists.',
                    'data' => []
                );
            }

            $asset = FixedAssetRegistration::find($request->asset_id);
            if (!$asset) {
                return array(
                    'status' => false,
                    'message' => 'Asset not found.',
                    'data' => []
                );
            }



            // Step 1: Create main asset registration (only once per asset_code)
            $mainAsset = FixedAssetRegistration::create([
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
                'asset_code' => $assetCode,
                'asset_name' => $firstItem->asset_name,
                'quantity' => $items->sum('quantity'),
                'reference_doc_id' => $request->id,
                'reference_series' => 'fixed-asset-split',
                'category_id' => $request->category_id,
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
                'dep_type' => $asset->dep_type,
                'current_value' => $items->sum('current_value'),
                'current_value_after_dep' => $items->sum('current_value'),
                'document_status' => 'approved',
                'approval_level' => 1,
                'revision_number' => 0,
                'revision_date' => null,
                'status' => 'active',

            ]);


            // Step 2: Create sub-assets under main asset
            foreach ($items as $subAsset) {
                FixedAssetSub::create([
                    'parent_id' => $mainAsset->id,
                    'sub_asset_code' => $subAsset->sub_asset_id,
                    'quantity' => $subAsset->quantity,
                    'current_value' => $subAsset->current_value,
                    'current_value_after_dep' => $subAsset->current_value,
                    'location_id' => $request->location_id,
                    'cost_center_id' => $request->cost_center_id,
                ]);
            }
        }

        //delete_old
        $old = FixedAssetSub::find((int)$request->sub_asset_id);
        if ($old)
            FixedAssetSub::find((int)$request->sub_asset_id)->delete();
        return array(
            'status' => true,
            'message' => "Registration Added",
            'data' => []
        );
    }
}
