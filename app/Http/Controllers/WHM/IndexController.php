<?php

namespace App\Http\Controllers\WHM;

use App\Helpers\CommonHelper;
use App\Helpers\ConstantHelper;
use App\Helpers\Helper;
use App\Helpers\StoragePointHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\WHM\ItemAttributeResource;
use App\Http\Resources\WHM\TrackingResource;
use App\Models\Configuration;
use App\Models\ErpItemAttribute;
use App\Models\ErpStore;
use App\Models\ErpSubStore;
use App\Models\ErpSubStoreParent;
use App\Models\Item;
use App\Models\WhLevel;
use App\Models\WHM\ErpItemUniqueCode;
use App\Models\WHM\ErpWhmJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class IndexController extends Controller
{
    public function userDashboard(Request $request) {
        $location = $request->input('store_id');
        $subLocation = $request->input('sub_store_id');
        $employee = Helper::getAuthenticatedUser();

        $pendingUnloadingCounts = ErpWhmJob::where('type', CommonHelper::UNLOADING)
                ->whereIn('status',[CommonHelper::PENDING,CommonHelper::DEVIATION, CommonHelper::IN_PROGRESS])
                // ->where('organization_id', @$employee->organization_id)
                ->when($location, function ($q) use ($location) {
                    $q->where('store_id', $location);
                })
                ->count();
         
        $pendingPutawayCounts = ErpWhmJob::where('type', CommonHelper::PUTAWAY)
                ->whereIn('status',[CommonHelper::PENDING,CommonHelper::DEVIATION, CommonHelper::IN_PROGRESS])
                // ->where('organization_id', @$employee->organization_id)
                ->when($location, function ($q) use ($location) {
                    $q->where('store_id', $location);
                })
                ->when($subLocation, function ($q) use ($subLocation) {
                    $q->where('sub_store_id', $subLocation);
                })
                ->count();    

        $pendingPickingCounts = ErpWhmJob::where('type', CommonHelper::PICKING)
                ->whereIn('status',[CommonHelper::PENDING,CommonHelper::DEVIATION, CommonHelper::IN_PROGRESS])
                // ->where('organization_id', @$employee->organization_id)
                ->when($location, function ($q) use ($location) {
                        $q->where('store_id', $location);
                })
                ->when($subLocation, function ($q) use ($subLocation) {
                    $q->where('sub_store_id', $subLocation);
                })
                ->count();    

        $pendingDispatchCounts = ErpWhmJob::where('type', CommonHelper::DISPATCH)
                ->whereIn('status',[CommonHelper::PENDING,CommonHelper::DEVIATION, CommonHelper::IN_PROGRESS])
                // ->where('organization_id', @$employee->organization_id)
                ->when($location, function ($q) use ($location) {
                    $q->where('store_id', $location);
                })
                ->when($subLocation, function ($q) use ($subLocation) {
                    $q->where('sub_store_id', $subLocation);
                })
                ->count();        

        return [
            'message' => 'Data fetched successfully.',
            'data' => [
                'total_unloadings' => $pendingUnloadingCounts,
                'total_putways' => $pendingPutawayCounts,
                'total_pickings' => $pendingPickingCounts,
                'total_dispatches' => $pendingDispatchCounts
            ],
        ];        

    }

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
                    // ->where('is_warehouse_required',1)
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
            ->whereNull('utilized_id')
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
            'job_id' => ['nullable'],
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
        ->when($request->job_id, function($q) use($request){
            $q->where('job_id',$request->job_id);
        })
        ->where('storage_point_id', $storagePointId)
        ->where('status',CommonHelper::SCANNED)
        ->whereNull('utilized_id')
        ->select('uid','job_id','group_id','company_id','organization_id','book_code','doc_no','doc_date','status','item_id','item_uid','item_name','item_code','item_attributes','vendor_id','storage_point_id')
        ->get();

        $storagePoint->quantity = count($scannedPackets);
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

        $item = ErpItemUniqueCode::select('item_name','item_code','item_attributes')->where('item_uid',$request->packet_id)->first();

        $unicodes = ErpItemUniqueCode::with(['actionBy' => function($q){
                $q->select('id','name');
            },'storagePoint' => function($q){
                $q->select('id', 'storage_number', 'heirarchy_name', 'name');
            },'store' => function($q){
                $q->select('id', 'store_name');
            }])
            ->where('item_uid',$request->packet_id)
            ->select('uid','item_uid', 'action_at','action_by','job_type','status','storage_point_id','book_code','doc_no','created_at','store_id')
            ->get();

        $trackingResources = TrackingResource::collection($unicodes);

        return [
            'data' => [
                'item' => $item,
                'tracking' => $trackingResources
            ],
            'message' => 'Data fetched successfully.',
        ];
    }

    public function getStoragePointPackets(Request $request){
        $validator = Validator::make($request->all(),[
            'storage_number' => ['required'],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $unicodes = ErpItemUniqueCode::whereHas('storagePoint', function($q) use($request) {
                $q->whereStorageNumber($request->storage_number);
            })
            ->whereNull('utilized_id')
            ->select('uid','item_id','item_name','item_code','item_attributes','status','vendor_id','storage_point_id')
            ->get();


        return [
            'data' => $unicodes,
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

    public function items(Request $request){
        $search = $request->search;
        $items = Item::orderBy('id', 'ASC')
            ->withDefaultGroupCompanyOrg()
            ->when($search, function($query) use($search){
                $query->where('item_name','like','%'.$search.'%')
                    ->orWhere('item_code','like','%'.$search.'%');
            })
            ->select('id','item_name','item_code')
            ->get();

        return [
            'data' => $items,
            'message' => 'Data fetched successfully.',
        ];   
    }

    public function getItemAttributes(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'item_id' => ['required'],
        ],[
            'item_id.required' => 'Item id is required',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $itemAttributes = ErpItemAttribute::with([
                    'group' => function($q){
                        $q->select('id','name');
                    }
                ])
                ->where('item_id',$request->item_id)
                ->select('id','item_id','attribute_group_id','attribute_id')
                ->get();
        
        return [
            'data' => ItemAttributeResource::collection($itemAttributes),
            'message' => 'Data fetched successfully.',
        ];   
    }

    public function getStructureMapping(Request $request){
        $validator = Validator::make($request->all(),[
            'store_id' => ['required'],
            'sub_store_id' => ['required'],
        ],[
            'store_id.required' => 'Store id is required',
            'sub_store_id.required' => 'Sub store id is required',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        
        $structures = WhLevel::with([
                'storagePointDetails' => function($q){
                    $q->select('id','name','wh_level_id','store_id','sub_store_id','storage_number');
                }
            ])
            ->where('store_id', $request->store_id)
            ->where('sub_store_id', $request->sub_store_id)
            ->select('id','name')
            ->get();


        return [
            "data" => $structures
        ];
    }

    public function getItemStorage(Request $request){
        $validator = Validator::make($request->all(),[
            'item_id' => ['required'],
            'store_id' => ['required'],
            'sub_store_id' => ['required'],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $response = StoragePointHelper::getStoragePoints(
                $request->item_id,
                null,
                $request->store_id,
                $request->sub_store_id
            );

        return [
            'data' => $response,
            'message' => 'Data fetched successfully.',
        ];
    }
}
