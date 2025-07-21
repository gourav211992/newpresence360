<?php

namespace App\Http\Controllers\WHM;

use App\Helpers\CommonHelper;
use App\Helpers\ConstantHelper;
use App\Helpers\Helper;
use App\Helpers\InventoryHelper;
use App\Helpers\StoragePointHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\WHM\StoragePointResource;
use App\Http\Resources\WHM\TrackingResource;
use App\Models\Configuration;
use App\Models\ErpStore;
use App\Models\ErpSubStore;
use App\Models\ErpSubStoreParent;
use App\Models\WHM\ErpItemUniqueCode;
use App\Models\WHM\ErpWhmJob;
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
                'message' => [$response['message']],
            ]);
        }

        $storagePoints = $response['data'];
        $storagePointIds = $storagePoints->pluck('id')->toArray();

        // Fetch scanned packets grouped by storage_point_id
        $scannedPacketsGrouped = ErpItemUniqueCode::with(['vendor' => function ($q) {
                $q->select('id', 'vendor_code', 'company_name');
            },'storagePoint' => function($q){
                $q->select('id', 'storage_number');
            }])
            ->where('job_id', $request->job_id)
            ->whereIn('storage_point_id', $storagePointIds)
            ->where('status', CommonHelper::SCANNED)
            ->select('uid','job_id','group_id','company_id','organization_id','book_code','doc_no','doc_date','status','item_id','item_name','item_code','item_attributes','vendor_id','storage_point_id')
            ->get()
            ->groupBy('storage_point_id');

        // Pass scanned packets grouped data to resource collection
        $storagePoints = $storagePoints->map(function($storagePoint) use ($scannedPacketsGrouped) {
            $storagePoint->scanned_packets = $scannedPacketsGrouped->get($storagePoint->id, collect());
            return $storagePoint;
        });

        return [
            'data' => $storagePoints,
            'message' => $response['message'],
        ];
    }

    public function storagePointDetail(Request $request){
        $validator = Validator::make($request->all(),[
            'storage_number' => ['required'],
            'job_id' => ['required'],
        ],[
            'storage_number.required' => 'Storage number is required',
            'job_id.required' => 'Job id is required',
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

        if (empty($response['data'])) {
            throw ValidationException::withMessages([
                'storage_number' => ['Storage point data not found.'],
            ]);
        }

        $storagePoint = $response['data'];
        $storagePointId = $storagePoint->id;

        $scannedPackets = ErpItemUniqueCode::with(['vendor' => function ($q) {
                $q->select('id', 'vendor_code', 'company_name');
            },'storagePoint' => function($q){
                $q->select('id', 'storage_number');
            }])
        ->where('job_id',$request->job_id)
        ->where('storage_point_id', $storagePointId)
        ->where('status',CommonHelper::SCANNED)
        ->select('uid','job_id','group_id','company_id','organization_id','book_code','doc_no','doc_date','status','item_id','item_name','item_code','item_attributes','vendor_id','storage_point_id')
        ->get();

        $storagePoint->scanned_packets = $scannedPackets;

        return [
            'data' => $response['data'],
            'message' => $response['message'],
        ];
    }

    public function getJobs(Request $request){
        $search = $request->input('search');
        $jobs = ErpWhmJob::when($search, function ($query) use ($search) {
                        $query->where('type', $search);
                        
                    })
                    ->orderBy('id','desc')
                    ->get();
        return [
            'data' => $jobs,
        ];

    }

    public function getUniqueCodes(Request $request){
        $search = $request->input('search');
        $jobId = $request->input('job_id');
        $jobs = ErpItemUniqueCode::when($search, function ($query) use ($search) {
                        $query->where('job_type', $search);
                    })
                    ->when($jobId, function ($query) use ($jobId) {
                        $query->where('job_id', $jobId);
                    })
                    ->orderBy('id','desc')
                    ->get();
        return [
            'data' => $jobs,
        ];

    }

    public function trackPacket(Request $request){
        $validator = Validator::make($request->all(),[
            'packet_id' => ['required'],
        ],[
            'packet_id.required' => 'Packet Id is required'
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $unicodes = ErpItemUniqueCode::with(['actionBy' => function($q){
                $q->select('id','name');
            }])
            ->where('item_uid',$request->packet_id)
            ->select('uid','item_uid', 'action_at','action_by','job_type')
            ->get();

        $trackingResources = TrackingResource::collection($unicodes);

        return [
            'data' => $trackingResources,
            'message' => 'Data fetched successfully.',
        ];
    }

    public function getConfiguration(Request $request){
        $validator = Validator::make($request->all(),[
            'organization_id' => ['required'],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $configurations = Configuration::where('type','organization')
            ->where('type_id', $request->organization_id)
            ->get();

        return [
            'data' => $configurations,
            'message' => 'Data fetched successfully.',
        ];
    }
}
