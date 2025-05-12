<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Helpers\Helper;
use App\Traits\DefaultGroupCompanyOrg;
use App\Traits\Deletable;
use Illuminate\Support\Facades\DB;

class FixedAssetSplitHistory extends Model
{
    use HasFactory, SoftDeletes, DefaultGroupCompanyOrg, Deletable;

    protected $table = 'erp_finance_fixed_asset_split_history';

    protected $guarded = ['id'];
    public function book(){
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
    // public function getAssetsAttribute()
    // {
    //     $assetIds = json_decode($this->attributes['assets'], true) ?? [];

    //     return FixedAssetRegistration::whereIn('id', $assetIds)->get();
    // }
    public function makeRegistration($id){
        DB::beginTransaction();
        try{
        $request = FixedAssetSplit::find($id);
        $user = Helper::getAuthenticatedUser();
        $grouped = collect(json_decode($request->sub_assets))->groupBy('asset_code');
        $parentURL = "fixed-asset_registration";
        
        
        
         $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentURL);
         if (count($servicesBooks['services']) == 0) {
            return redirect()
            ->route('finance.fixed-asset.split.show',$id)
            ->withInput()
            ->withErrors('Series not set.');
        
        }
        $firstService = $servicesBooks['services'][0];
        $series = Helper::getBookSeriesNew($firstService -> alias, $parentURL)->first();
        
            foreach ($grouped as $assetCode => $items) {
                $firstItem = $items->first();
                if($series!=null){
                $book = Helper::generateDocumentNumberNew($series->id, date('Y-m-d'));
                if($book['document_number']!=null){
                $existingAsset = FixedAssetRegistration::where('asset_code', $assetCode)
                    ->where('organization_id', $user->organization->id)
                    ->where('group_id', $user->organization->group_id)
                    ->first();
                
                if($existingAsset){
                    return redirect()
                    ->route('finance.fixed-asset.split.show',$id)
                    ->withInput()
                    ->withErrors('Asset Code '.$existingAsset->asset_code . ' already exists.');
                }

                $asset = FixedAssetRegistration::find($request->asset_id);
                

                    
                // Step 1: Create main asset registration (only once per asset_code)
                $mainAsset = FixedAssetRegistration::create([
                    'organization_id' => $user->organization->id,
                    'group_id' => $user->organization->group_id,
                    'company_id' => $user->organization->company_id,
                    'book_id' => $series->id,
                    'document_number'=>$book['document_number'],
                    'document_date' => date('Y-m-d'),
                    'doc_number_type' => $book['type'],
                    'doc_reset_pattern' => $book['reset_pattern'],
                    'doc_prefix' => $book['prefix'],
                    'doc_suffix' => $book['suffix'],
                    'doc_no' => $book['doc_no'],
                    'asset_code' => $assetCode,
                    'asset_name' => $firstItem->asset_name,
                    'quantity' => $items->sum('quantity'),
                    'catrgory_id'=>$request->category_id,
                    'ledger_id' => $request->ledger_id,
                    'ledger_group_id' => $request->ledger_group_id,
                    'mrn_header_id'=> $asset->mrn_header_id,
                    'mrn_detail_id'=> $asset->mrn_detail_id,
                    'capitalize_date' => $request->capitalize_date,
                    'last_dep_date'=> $request->capitalize_date,
                    'vendor_id'=> $asset->vendor_id,
                    'currency_id'=> $asset->currency_id,
                    'supplier_invoice_no'=> $asset->supplier_invoice_no,
                    'supplier_invoice_date'=> $asset->supplier_invoice_date,
                    'book_date'=>$asset->book_date,
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
                    'document_status' => Helper::checkApprovalRequired($series->id),
                    'approval_level' => 1,
                    'revision_number' => 0,
                    'revision_date' => null,
                    'created_by' => $user->auth_user_id,
                    'type' => get_class($user),
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
                    ]);
                }
            }
        }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
        }
    }

    
}
