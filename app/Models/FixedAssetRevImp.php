<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\DefaultGroupCompanyOrg;
use App\Helpers\Helper;
use App\Traits\Deletable;
use Illuminate\Support\Facades\DB;


class FixedAssetRevImp extends Model
{
    use HasFactory, SoftDeletes, DefaultGroupCompanyOrg, Deletable;

    protected $table = 'erp_finance_fixed_asset_rev';

    protected $guarded = ['id'];
    public function book(){
       return $this->belongsTo(Book::class, 'book_id');
    }
    public function category()
    {
        return $this->belongsTo(ErpAssetCategory::class, 'category_id');
    }
    public function location()
    {
        return $this->belongsTo(ErpStore::class, 'location_id');
    }
    public function cost_center()
    {
        return $this->belongsTo(CostCenter::class, 'cost_center_id');
    }
    public static function updateRegistration($id){
        $request = FixedAssetRevImp::find($id);
        if (!$request) {
            return array(
                'status' => false,
                'message' => "Document Not Found",
                'data' => []
            );
        }
        
        $sub_assets = json_decode($request->asset_details);
        foreach($sub_assets as $sub_asset){
            $sub = FixedAssetSub::find($sub_asset->sub_asset_id);
            if (!$sub) {
                
                return array(
                    'status' => false,
                    'message' => "Sub Asset Not Found",
                    'data' => []
                );
                
            }
            $sub->current_value = $sub_asset->revaluate;
            $sub->current_value_after_dep = $sub_asset->revaluate;
            $sub->save();
        }

           return array(
                    'status' => true,
                    'message' => "Registration Updated Successfully",
                    'data' => []
            );
        
        } 
    
    
    
}
