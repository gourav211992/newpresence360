<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\DefaultGroupCompanyOrg;
use App\Helpers\Helper;
use App\Traits\Deletable;
use Illuminate\Support\Facades\DB;


class FixedAssetMergerHistory extends Model
{
    use HasFactory, SoftDeletes, DefaultGroupCompanyOrg, Deletable;

    protected $table = 'erp_finance_fixed_asset_merger_history';

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
        return $this->belongsTo(Ledger::class, 'ledger_group_id');
    }
    // public function getAssetsAttribute()
    // {
    //     $assetIds = json_decode($this->attributes['assets'], true) ?? [];

    //     return FixedAssetRegistration::whereIn('id', $assetIds)->get();
    // }
    public static function makeRegistration($id){
        try{
        $request = FixedAssetMerger::find($id);
        $user = Helper::getAuthenticatedUser();
        

        $parentURL = "fixed-asset_registration";
        
        
        
        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentURL);
        if (count($servicesBooks['services']) == 0) {
            DB::rollBack();
            return response() -> json([
                'status' => 'exception',
                'data' => array(
                    'status' => false,
                    'message' => 'Service not found',
                    'data' => []
                )
            ]);
       }
       $firstService = $servicesBooks['services'][0];
       $series = Helper::getBookSeriesNew($firstService -> alias, $parentURL)->first();


            if($series!=null){
            $book = Helper::generateDocumentNumberNew($series->id, date('Y-m-d'));
            if($book['document_number']!=null){
            $existingAsset = FixedAssetRegistration::where('asset_code', $request->asset_code)
                ->where('organization_id', $user->organization->id)
                ->where('group_id', $user->organization->group_id)
                ->first();
            
                if($existingAsset){
                    DB::rollBack();
                    return array(
                            'status' => false,
                            'message' => 'Asset Code '.$existingAsset->asset_code . ' already exists.',
                            'data' => []
                    );
                }

            

                
            // Step 1: Create main asset registration (only once per asset_code)
            $data = [
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
                'asset_code' => $request->asset_code,
                'asset_name' => $request->asset_name,
                'quantity' => $request->quantity,
                'catrgory_id'=>$request->category_id,
                'ledger_id' => $request->ledger_id,
                'ledger_group_id' => $request->ledger_group_id,
                'mrn_header_id'=> null,
                'mrn_detail_id'=> null,
                'capitalize_date' => $request->capitalize_date,
                'last_dep_date'=> $request->capitalize_date,
                'vendor_id'=> null,
                'currency_id'=> $user->organization->currency_id,
                'supplier_invoice_no'=> null,
                'supplier_invoice_date'=> null,
                'book_date'=>null,
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
                'document_status' => Helper::checkApprovalRequired($series->id),
                'approval_level' => 1,
                'revision_number' => 0,
                'revision_date' => null,
                'created_by' => $user->auth_user_id,
                'type' => get_class($user),
                'status' => 'active',

            ];

                $asset = FixedAssetRegistration::create($data);
                FixedAssetSub::generateSubAssets($asset->id, $asset->asset_code, $asset->quantity, $asset->current_value, $asset->salvage_value);
                    
                return array(
                    'status' => true,
                    'message' => "Registration Added",
                    'data' => []
            );
            }
            
        }
    }catch (\Exception $e) {
        DB::rollBack();
        return array(
                'status' => false,
                'message' => $e->getMessage(),
                'data' => []
        );
    
       
    }
        

    }
}
