<?php

namespace App\Http\Controllers\WHM;

use App\Helpers\CommonHelper;
use App\Helpers\ConstantHelper;
use App\Helpers\Helper;
use App\Helpers\InventoryHelper;
use App\Helpers\StoragePointHelper;
use App\Http\Controllers\Controller;
use App\Models\ErpStore;
use App\Models\ErpSubStore;
use App\Models\ErpSubStoreParent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class IndexController extends Controller
{
    public function stores(){
        $employee = Helper::getAuthenticatedUser();
        $stores = ErpStore::withDefaultGroupCompanyOrg()
            ->select('id','organization_id','group_id','company_id','store_name','store_code')
            ->when(($employee->authenticable_type == "employee"), function ($locationQuery) use($employee) { // Location with same country and state
                $locationQuery->whereHas('employees', function ($employeeQuery) use ($employee) {
                    $employeeQuery->where('employee_id', $employee->id);
                });
            })
            ->get();

        return [
            'data' => $stores,
        ];
    }

    public function subStores(Request $request){

        $validator = Validator::make($request->all(),[
            'store_id' => ['required'],
        ],[
            'store_id.required' => 'Store id is required',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $subStoreIds = ErpSubStoreParent::withDefaultGroupCompanyOrg()
                        ->where('store_id', $request->store_id)
                        ->get() 
                        ->pluck('sub_store_id') 
                        ->toArray();
                        
        $subStores = ErpSubStore::select('id', 'name', 'code','station_wise_consumption','is_warehouse_required') 
                    ->whereIn('id', $subStoreIds) 
                    ->where('status',ConstantHelper::ACTIVE)
                    ->where('type','stock')
                    ->where('is_warehouse_required',1)
                    ->get();

        return [
            'data' => $subStores,
        ];
    }

    public function storagePoints(Request $request){
        $validator = Validator::make($request->all(),[
            'store_id' => ['required'],
        ],[
            'store_id.required' => 'Store id is required',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $storeId = $request->store_id;
        $storagePoints = StoragePointHelper::getStoragePoints("", "", $storeId, "");
        
        return [
            'data' => $storagePoints,
        ];
    }

    // public function storagePointDetail(Request $request){
    //     $validator = Validator::make($request->all(),[
    //         'store_id' => ['required'],
    //         'storage_point_id' => ['required'],
    //     ],[
    //         'store_id.required' => 'Store id is required',
    //         'storage_point_id.required' => 'Storage point id is required',
    //     ]);

    //     if ($validator->fails()) {
    //         throw new ValidationException($validator);
    //     }

    //     $detail = \DB::table('erp_wh_details')->where('id', $request->storage_point_id)->first();
    //     if (!$detail) {
    //         throw ValidationException::withMessages([
    //             'storage_point_id' => ['Invalid storage.'],
    //         ]);
    //     }

    //     $parents = StoragePointHelper::getParentHierarchy($detail->parent_id);
    // }
}
