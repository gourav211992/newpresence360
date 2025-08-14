<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;
use App\Helpers\Helper;
use App\Traits\DefaultGroupCompanyOrg;
use App\Traits\Deletable;
use App\Helpers\ServiceParametersHelper;
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
        $old = FixedAssetSub::find((int)$request->sub_asset_id);
        $book = Book::find($request->book_id);
        $glPostingBookParam = OrganizationBookParameter::where('book_id', $book->id)->where('parameter_name', ServiceParametersHelper::GL_POSTING_SERIES_PARAM)->first();
        if (isset($glPostingBookParam) && isset($glPostingBookParam->parameter_value[0])) {
            $glPostingBookId = $glPostingBookParam->parameter_value[0];
        } else {
            return array(
                'status' => false,
                'message' => 'Financial Book Code is not specified',
                'data' => []
            );
        }
        $exitingReg = FixedAssetRegistration::where('reference_series',$request->book_id)
        ->where('reference_doc_id',$request->id)->first();
            if ($exitingReg) {
                return array(
                    'message' => 'Registration already posted',
                    'status' => false
                );
            }
            
            $exitingVoucher = FixedAssetRegistration::where('document_number',$request->document_number)
            ->where('book_id',$request->book_id)->first();

            if ($exitingVoucher) {
                return array(
                    'message' => 'Registration already posted with same Doc No# '.$request->document_number,
                    'status' => false
                );
            }

        $grouped = collect(json_decode($request->sub_assets))->groupBy('asset_code');


        foreach ($grouped as $assetCode => $items) {
            $firstItem = $items->first();
            $existingAsset = FixedAssetRegistration::where('asset_code', $assetCode)->first();

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
                'book_id' => $glPostingBookId,
                'document_number' => $request->document_number,
                'document_date' => $request->document_date,
                'asset_code' => $assetCode,
                'asset_name' => $firstItem->asset_name,
                'quantity' => $items->sum('quantity'),
                'reference_doc_id' => $request->id,
                'reference_series' => 'fixed-asset-split',
                'category_id' => $firstItem->category,
                'ledger_id' => $firstItem->ledger,
                'ledger_group_id' => $firstItem->ledger_group,
                'capitalize_date' => $firstItem->capitalize_date,
                'last_dep_date' => $firstItem->capitalize_date,
                'currency_id' => $request->currency_id,
                'it_category_id'=>$asset->it_category_id??null,
                'location_id' => $request->location_id,
                'cost_center_id' => $request->cost_center_id,
                'maintenance_schedule' => $request->maintenance_schedule,
                'depreciation_method' => $request->depreciation_method,
                'useful_life' => $firstItem->life,
                'salvage_value' => $items->sum('salvage_value'),
                'depreciation_percentage' => $firstItem->dep_per,
                'depreciation_percentage_year' => $firstItem->dep_per,
                'total_depreciation' => 0,
                'dep_type' => $asset->dep_type,
                'current_value' => $items->sum('current_value'),
                'current_value_after_dep' => $items->sum('current_value'),
                'document_status' => 'approved',
                'approval_lesvel' => 1,
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
                    'salvage_value' => $subAsset->salvage_value,
                    'current_value' => $subAsset->current_value,
                    'current_value_after_dep' => $subAsset->current_value,
                    'location_id' => $request->location_id,
                    'cost_center_id' => $request->cost_center_id,
                    'capitalize_date' => $subAsset->capitalize_date,
                    'last_dep_date' => $subAsset->capitalize_date,
                    'expiry_date' => $old->expiry_date?? null,
                ]);
            }
        }

        
        //delete_old
       
        if ($old){
            if($old->last_dep_date!=$old->capitalize_date){
                $old->expiry_date = Carbon::parse($request->capitalize_date)->subDay()->format('Y-m-d');
                $old->save();
            }else{
                $old->expiry_date = $old->last_dep_date;
                $old->save();

            }
        }
           
           
        return array(
            'status' => true,
            'message' => "Registration Added",
            'data' => []
        );
    }
}
