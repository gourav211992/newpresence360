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
use App\Models\WHM\ErpItemUniqueCode;
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
            'job_id' => ['required'],
        ],[
            'store_id.required' => 'Store id is required',
            'job_id.required' => 'Job id is required',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        
        $storeId = $request->store_id;
        $itemIds = ErpItemUniqueCode::where('job_id', $request->job_id)->pluck('item_id')->unique()->values()->toArray();
        $response = StoragePointHelper::getStoragePointsForMultipleItems($itemIds, $storeId);
        
        if($response['code'] == 500){
            throw ValidationException::withMessages([
                'job_id' => [$response['message']],
            ]);
        }

        return [
            'data' => $response['data'],
            'message' => $response['message'],
        ];
    }

    public function storagePointDetail(Request $request){
        $validator = Validator::make($request->all(),[
            'storage_number' => ['required'],
        ],[
            'storage_number.required' => 'Storage number is required',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $storageNumber = $request->input('storage_number');
        $response = StoragePointHelper::getStoragePointDetail($storageNumber);

        if($response['code'] == 500){
            throw ValidationException::withMessages([
                'storage_number' => [$response['message']],
            ]);
        }

        return [
            'data' => $response['data'],
            'message' => $response['message'],
        ];
    }
}
