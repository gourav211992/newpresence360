<?php

namespace App\Http\Controllers\WHM;

use App\Exceptions\ApiGenericException;
use App\Helpers\CommonHelper;
use App\Helpers\ConstantHelper;
use App\Helpers\Helper;
use App\Helpers\Inventory\StockReservation;
use App\Helpers\StoragePointHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\WHM\PicklistItemResource;
use App\Http\Resources\WHM\PicklistResource;
use App\Lib\Services\WHM\MaterialIssueWhmJob;
use App\Lib\Services\WHM\WhmJob;
use App\Models\ErpMaterialIssueHeader;
use App\Models\ErpMiItem;
use App\Lib\Services\WHM\PickingJob;
use App\Models\ErpPlHeader;
use App\Models\ErpPlItem;
use App\Models\StockLedgerReservation;
use App\Models\WHM\ErpItemUniqueCode;
use App\Models\WHM\ErpWhmJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use DB;

class PicklistTaskController extends Controller
{
    public function index(Request $request){
        $search = $request->input('search');
        $location = $request->input('store_id');
        $subLocation = $request->input('sub_store_id');
        $jobs = ErpWhmJob::with(['store' => function($q){
                        $q->select('id','store_name');
                    },'subStore' => function($q){
                        $q->select('id','name','is_warehouse_required');
                    }, 'morphable' => function ($q) {
                        $q->with('pickingItems');
                    }])
                    ->where('type', CommonHelper::PICKING)
                    ->when($search, function ($query) use ($search) {
                        $query->whereHasMorph('morphable', ['App\Models\ErpPlHeader', 'App\Models\ErpMaterialIssueHeader'], function ($q) use ($search) {
                             $q->where(function($q2) use ($search) {
                                $q2->where('document_number', 'like', "%{$search}%")
                                    ->orWhere('book_code', 'like', "%{$search}%");
                            });
                        });
                    })
                    ->when($location, function ($query) use ($location) {
                        $query->where('store_id', $location);
                    })
                    ->when($subLocation, function ($query) use ($subLocation) {
                        $query->where('sub_store_id', $subLocation);
                    })
                    ->whereIn('status',[CommonHelper::PENDING,CommonHelper::IN_PROGRESS, CommonHelper::DEVIATION])
                    ->orderBy('id','desc')
                    ->paginate(CommonHelper::PAGE_LENGTH_10);

        $jobResources = PicklistResource::collection($jobs->getCollection());

        return [
            'message' => 'Records fetched successfully',
            "data" => [
                'records' => $jobResources,
                'pagination' => [
                    'current_page' => $jobs->currentPage(),
                    'last_page' => $jobs->lastPage(),
                    'per_page' => $jobs->perPage(),
                    'total' => $jobs->total(),
                    'from' => $jobs->firstItem(),
                    'to' => $jobs->lastItem(),
                ],
            ],
        ];
    }

    public function items(Request $request){
        $validator = Validator::make($request->all(),[
            'job_id' => ['required'],
            'store_id' => ['required'],
        ],[
            'job_id.required' => 'Job id is required',
            'store_id.required' => 'Store id is required',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $job = ErpWhmJob::where('type', CommonHelper::PICKING)->where('id',$request->job_id)->first();
        if (!$job) {
            throw ValidationException::withMessages([
                'job_id' => ['Job not found.'],
            ]);
        }

        $morphableId = $job->morphable_id;
        $storeId = $request->store_id;
        
        $items = $this -> getItemDetailsData($job, $storeId);
        return [
            'message' => 'Records fetched successfully',
            "data" => $items,
        ];

    }

    public function itemDetail(Request $request){
        $validator = Validator::make($request->all(),[
            'store_id' => ['required'],
            'pl_item_id' => ['required'],
            'job_id' => ['required'],
        ],[
            'store_id.required' => 'Store id is required',
            'pl_item_id.required' => 'Pl item id is required',
            'job_id.required' => 'Job id is required',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $storeId = $request->store_id;
        $job = ErpWhmJob::find($request -> job_id);
        if (!$job) {
            throw ValidationException::withMessages([
                'job_id' => ['Job not found.'],
            ]);
        }
        if ($job -> trns_type === ConstantHelper::MATERIAL_ISSUE_SERVICE_ALIAS_NAME) {
            $plItem = ErpMiItem::whereHas('header', function($q) use($storeId){
                    $q->where('from_store_id',$storeId);
                })
                ->where('id', $request->pl_item_id)
                ->with('attributes')
                ->select('id','material_issue_id AS pl_header_id','item_id','item_name','item_code',
                    // DB::raw('CAST(inventory_uom_qty AS UNSIGNED) as quanity')
                    DB::raw("(
                        CAST(inventory_uom_qty AS UNSIGNED) * 
                        (
                            SELECT IFNULL(storage_uom_count, 1)
                            FROM erp_items 
                            WHERE erp_items.id = erp_mi_items.item_id
                        )
                    ) as quantity")
                )
                ->first();
        } else if ($job -> trns_type === ConstantHelper::PL_SERVICE_ALIAS) {
            $plItem = ErpPlItem::whereHas('header', function($q) use($storeId){
                    $q->where('store_id',$storeId);
                })
                ->where('id', $request->pl_item_id)
                ->select('id','pl_header_id','item_id','item_name','item_code',
                    DB::raw("(
                        CAST(inventory_uom_qty AS UNSIGNED) * 
                        (
                            SELECT IFNULL(storage_uom_count, 1)
                            FROM erp_items 
                            WHERE erp_items.id = erp_pl_items.item_id
                        )
                    ) as quantity"),
                    'attributes'
                )
                ->first();
        }

        if (!$plItem) {
            throw ValidationException::withMessages([
                'pl_item_id' => ['Item not found.'],
            ]);
        }

        $plScannedItemUids = ErpItemUniqueCode::where('job_id',$request->job_id)->pluck('uid')->toArray(); 
        
        $plItemId = $plItem->id;
 
        if($plItem){
            $reservedStock = $plItem->stockReservation()
                ->where('issue_book_type',$job -> trns_type)
                ->where('issue_header_id',$plItem->pl_header_id);

            $transType = $reservedStock->pluck('receipt_book_type')
                ->unique()
                ->toArray();

            $mrnIds = $reservedStock->pluck('receipt_detail_id')
                ->toArray();

            $itemId = $plItem->item_id;
            
            // STEP 1: Fetch quantities grouped by storage_point_id
            $storageData = ErpItemUniqueCode::where('item_id', $itemId)
                ->where('store_id', $storeId)
                ->whereIn('trns_type', $transType)
                ->where('doc_type', CommonHelper::RECEIPT)
                ->where(function($q) use($plScannedItemUids){
                    $q->whereIn('utilized_id',$plScannedItemUids)
                    ->orWhereNull('utilized_id');
                })
                ->whereIn('morphable_id',$mrnIds)
                ->select('storage_point_id', DB::raw('COUNT(*) as quantity'))
                ->groupBy('storage_point_id')
                ->get();
            // dd($storageData,$plScannedItemUids,$itemId,$storeId,$mrnIds,$transType);

            // STEP 2: Map storage point detail with quantity
            $plItem->storage_points = $storageData->map(function ($record) use($storeId, $itemId, $plItemId){
                $detailsResponse = StoragePointHelper::getStoragePointDetailById($record->storage_point_id);
                $scannedPackets = self::scannedPackets($storeId, $itemId, $record->storage_point_id, $plItemId);

                return [
                    'quantity' => $record->quantity,
                    'details' => $detailsResponse['data'] ?? null,
                    'scannedPacketsCount' => $scannedPackets ? $scannedPackets->count() : null,
                    'scannedPackets' => $scannedPackets ?? null,
                ];
            });

        } else {
            $plItem->storage_points = null;
            $plItem->scannedPacketsCount = 0;
            $plItem->scannedPackets = null;
        }

        return [
            'data' => new PicklistItemResource($plItem),
            'message' => "Record fetched successfully.",
        ];

    }

    private function scannedPackets($storeId, $itemId, $storagePointId, $plItemId){
        $scannedPacketsUids = ErpItemUniqueCode::where('item_id', $itemId)
            ->where('store_id', $storeId)
            ->where('morphable_type', 'App\Models\ErpPlItem')
            ->where('morphable_id', $plItemId)
            ->where('doc_type', CommonHelper::RECEIPT)
            ->where('status',CommonHelper::SCANNED)
            ->get()
            ->pluck('uid')
            ->toArray();

        // Fetch the original MRN packets and their storage_point_id
        $packets = ErpItemUniqueCode::whereIn('utilized_id', $scannedPacketsUids)
            ->where('storage_point_id', $storagePointId)
            ->select('uid','item_uid', 'storage_point_id')
            ->get();


        return $packets;
    }

    public function saveAsDraft(Request $request){
        $validator = Validator::make($request->all(),[
            'job_id' => ['required'],
            'pl_item_id' => ['required'],
            'packet_ids' => ['required', 'array'],
            'storage_point_id' => ['nullable']
        ],[
            'job_id.required' => 'Job id is required',
            'pl_item_id.required' => 'Picklist item id is required',
            'packet_ids.required' => 'Scan a packet to draft the form',
            'storage_point_id.required' => 'Storage point id is required',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $job = ErpWhmJob::where('id',$request->job_id)
            ->where('type',CommonHelper::PICKING)
            ->first();

        if(!$job){
            throw ValidationException::withMessages([
                'job_id' => ['Job not found.'],
            ]);
        }

        $subStore = $job->subStore;
        if ($subStore && $subStore->is_warehouse_required) {
            if (!$request->filled('storage_point_id')) {
                throw ValidationException::withMessages([
                    'storage_point_id' => ['Storage point is required.'],
                ]);
            }
        }

        if ($job -> trns_type === ConstantHelper::MATERIAL_ISSUE_SERVICE_ALIAS_NAME) {
            $detail = ErpMiItem::find($request->pl_item_id);
        } else if ($job -> trns_type === ConstantHelper::PL_SERVICE_ALIAS) {        
            $detail = ErpPlItem::find($request->pl_item_id);
        }

        if(!$detail){
            throw ValidationException::withMessages([
                'pl_item_id' => ['Item not found.'],
            ]);
        }

        $plHeaderId = $job->morphable_id;
        $reservedStock = $detail->stockReservation()
            ->where('issue_book_type',$job->trns_type)
            ->where('issue_header_id',$plHeaderId)
            ->where('issue_detail_id',$request->pl_item_id);

        $transType = $reservedStock->pluck('receipt_book_type')
            ->toArray();

        $mrnIds = $reservedStock->pluck('receipt_detail_id')
            ->toArray();

        $storagePointId = $request->storage_point_id ?? NULL;
        $packets = ErpItemUniqueCode::whereIn('item_uid', $request->packet_ids)
            // ->where('storage_point_id',$request->storage_point_id)
            ->when($storagePointId, function ($query) use ($storagePointId) {
                $query->where('storage_point_id', $storagePointId);
            })
            ->where('item_id',$detail->item_id)
            // ->whereNull('utilized_id')
            ->whereIn('morphable_id',$mrnIds)
            ->whereIn('trns_type', $transType)
            ->pluck('item_uid')
            ->toArray();

        $invalidPackets = array_diff($request->packet_ids, $packets);

        if (!empty($invalidPackets)) {
            throw ValidationException::withMessages([
                'packet_ids' => ['Invalid or mismatched packet IDs: ' . implode(', ', $invalidPackets)],
            ]);
        }

        // custom validation after
        $alreadyScanned = ErpItemUniqueCode::where('job_id', $request->job_id)
            ->whereIn('item_uid', $request->packet_ids)
            ->where('status', CommonHelper::SCANNED)
            ->where('job_type', CommonHelper::PICKING)
            ->pluck('item_uid')
            ->toArray();

        if (!empty($alreadyScanned)) {
            throw ValidationException::withMessages([
                'packet_ids' => ['Some packets are already scanned: ' . implode(', ', $alreadyScanned)],
            ]);
        }

        // $count = count($request->packet_ids);
        // $stockRes = StockReservation::validateReservedStock($job->trns_type,$job->morphable_id,$request->pl_item_id,$count);
        // if($stockRes['status'] == 'error'){
        //     throw ValidationException::withMessages([
        //         'pl_item_id' => [$stockRes['message']],
        //     ]);
        // }
       

        \DB::beginTransaction();
        try {
            // Get Login User
            $user = Helper::getAuthenticatedUser();
            
            // Update Job Status
            if($job->status != CommonHelper::DEVIATION){
                $job->status = CommonHelper::IN_PROGRESS;
                $job->save();
            }

            $header = $job->morphable;
            (new PickingJob())->scanQRCodes($detail, $header, $job, $request->packet_ids, $storagePointId, $user, CommonHelper::PICKING, $transType);

            $scannedPackets = ErpItemUniqueCode::where('job_id', $request->job_id)
                ->where('status', CommonHelper::SCANNED)
                ->where('job_type', CommonHelper::PICKING)
                ->get();

            $count = count($scannedPackets);
            $noOfPackets = optional($detail->item)->storage_uom_count ?? 1;
            $inventoryQty = (int) $detail->inventory_uom_qty;

            $qty = $count/$noOfPackets;
            $stockRes = StockReservation::validateReservedStock($job->trns_type,$job->morphable_id,$request->pl_item_id,$qty);
            if($stockRes['status'] == 'error'){
                throw ValidationException::withMessages([
                    'pl_item_id' => [$stockRes['message']],
                ]);
            }
                                
            foreach ($scannedPackets->groupBy('packet_no') as $packetNo => $qrs) {
                if ($qrs->count() > $inventoryQty) {
                    throw ValidationException::withMessages([
                        'packet_data.' . $packetNo => "You can only scan $inventoryQty quantity per packet. Already scanned: " . $qrs->count(),
                    ]);
                }
            }

            \DB::commit();
            return [
                'message' => 'Task saved in draft'
            ];
        } catch (\Exception $e) {
            \DB::rollback();
            throw new ApiGenericException($e->getMessage());
        }
    }

    public function updateStatus(Request $request){
        $validator = Validator::make($request->all(),[
            'packet_id' => ['required'],
            'job_id' => ['required'],
            'storage_point_id' => ['nullable']
        ],[
            'packet_id.required' => 'Packet id is required',
            'job_id.required' => 'Job id is required',
            'storage_point_id.required' => 'Storage point id is required',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        // custom validation after
        $job = ErpWhmJob::where('id',$request->job_id)->where('type',CommonHelper::PICKING)->first();

        if (!$job) {
            throw ValidationException::withMessages([
                'job_id' => ['Job not found.'],
            ]);
        }

        $subStore = $job->subStore;
        if ($subStore && $subStore->is_warehouse_required) {
            if (!$request->filled('storage_point_id')) {
                throw ValidationException::withMessages([
                    'storage_point_id' => ['Storage point is required.'],
                ]);
            }
        }

        $morphableType = "";
        if ($job -> trns_type === ConstantHelper::MATERIAL_ISSUE_SERVICE_ALIAS_NAME) {
            $morphableType = "App\Models\ErpMiItem";
        } else if ($job -> trns_type === ConstantHelper::PL_SERVICE_ALIAS) {
            $morphableType = "App\Models\ErpPlItem";
        }

        $uniqueCode = ErpItemUniqueCode::where('item_uid', $request->packet_id)
                        ->where('job_id',$request->job_id)
                        // ->where('storage_point_id',$request->storage_point_id)
                        ->where('morphable_type', $morphableType)
                        ->where('status',CommonHelper::SCANNED)
                        ->first();
        if (!$uniqueCode) {
            throw ValidationException::withMessages([
                'packet_id' => ['Packet ID not found.'],
            ]);
        }

        if ($job->status == CommonHelper::DEVIATION) {
            throw ValidationException::withMessages([
                'job_id' => ['The job status is deviation.'],
            ]);
        }

        \DB::beginTransaction();
        try {
            $storagePointId = $request->storage_point_id ?? NULL;
            $mrnDetail = ErpItemUniqueCode::where('item_uid', $request->packet_id)
                // ->where('storage_point_id',$request->storage_point_id)
                ->when($storagePointId, function ($query) use ($storagePointId) {
                    $query->where('storage_point_id', $storagePointId);
                })
                ->where('job_type', CommonHelper::PUTAWAY)
                ->where('status',CommonHelper::SCANNED)
                ->where('utilized_id',$uniqueCode->uid)
                ->first();

            if($mrnDetail){
                $mrnDetail->utilized_id = NULL;
                $mrnDetail->save();
                $uniqueCode->delete();
            }

            \DB::commit();
            return [
                'data' => $request->packet_id,
                'message' => 'Packet deleted successfully.'
            ];
        } catch (\Exception $e) {
            \DB::rollback();
            throw new ApiGenericException($e->getMessage());
        }

    }

    public function closeJob(Request $request){
        $validator = Validator::make($request->all(),[
            'job_id' => ['required'],
            'deviation' => ['required'],
        ],[
            'job_id.required' => 'Job id is required'
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        // custom validation after
        $job = ErpWhmJob::find($request->job_id);
        if (!$job) {
            throw ValidationException::withMessages([
                'job_id' => ['Job not found.'],
            ]);
        }

        // Check if job is already closed with deviation=0 and incoming deviation=0
        if ($job->job_closed_at !== null ) {
            if ($job->deviation_qty == $request->deviation) {
                throw ValidationException::withMessages([
                    'job_id' => ['Job already closed.'],
                ]);
            }
        }


        \DB::beginTransaction();
        try {

            $job = ErpWhmJob::find($request->job_id);
            $job->status = CommonHelper::CLOSED;
            $job->job_closed_at = now();
            $job->deviation_qty = $request->deviation;
            $message = 'Job closed successfully.';

            // Update status based on deviation
            if($request->deviation > 0){
                $job->status = CommonHelper::DEVIATION;
                $message = 'Job closed with deviation '.$request->deviation.'.';
            }

            $job->save();

            $actionType = $job->status == CommonHelper::DEVIATION ? CommonHelper::DEVIATION : CommonHelper::getJobType($job->morphable_type) .' completed';
            $header = $job->morphable;
            $bookId = $header->book_id;
            $docId = $header->id;
            $revisionNumber = $header->revision_number ?? 0;
            $modelName = $job->morphable_type;
            $remarks = NULL;
            CommonHelper::approveDocument($bookId, $docId, $revisionNumber, $remarks, $actionType, $modelName);

            if ($job -> trns_type === ConstantHelper::MATERIAL_ISSUE_SERVICE_ALIAS_NAME) {
                $mi = ErpMaterialIssueHeader::find($job -> morphable_id);
                if ($mi && $job -> status === CommonHelper::CLOSED) {
                    //Check Recieve job
                    if ($mi -> to_sub_store ?-> is_warehouse_required) {
                        //Only Issue and Recieve Job
                        (new MaterialIssueWhmJob)->createJob($mi->id,'App\Models\ErpMaterialIssueHeader', CommonHelper::PUTAWAY);
                        foreach ($mi->items as $miItem) {
                            $status = StockReservation::settlementOfReservedStocks(ConstantHelper::MATERIAL_ISSUE_SERVICE_ALIAS_NAME, $mi->id, $miItem->id, $miItem->inventory_uom_qty, false);
                            if ($status['status'] == 'error') {
                                throw new ApiGenericException($status['message']);
                            }
                        }
                    } else {
                        //Direct Issue and Recieve
                        foreach ($mi->items as $miItem) {
                            $status = StockReservation::settlementOfReservedStocks(ConstantHelper::MATERIAL_ISSUE_SERVICE_ALIAS_NAME, $mi->id, $miItem->id, $miItem->inventory_uom_qty, true);
                            if ($status['status'] == 'error') {
                                throw new ApiGenericException($status['message']);
                            }
                        }
                        $subStoreId = $header->to_sub_store_id ?? NULL;
                        $storeId = $header->to_store_id ?? NULL;
                        (new PickingJob())->generateQRCodes($subStoreId,$job,$storeId);
                    }
                }
            } else if ($job -> trns_type === ConstantHelper::PL_SERVICE_ALIAS) {
                $pickList = ErpPlHeader::find($job->morphable_id);
                if($pickList && $job -> status == CommonHelper::CLOSED){
                    foreach ($pickList->inv_items as $plItem) {
                        $status = StockReservation::settlementOfReservedStocks(ConstantHelper::PL_SERVICE_ALIAS, $pickList->id, $plItem->id, $plItem->inventory_uom_qty, true);
                        if ($status['status'] == 'error') {
                            throw new ApiGenericException($status['message']);
                        }
                    }
                }
                $subStoreId = $header->staging_sub_store_id ?? NULL;
                (new PickingJob())->generateQRCodes($subStoreId,$job,$header->store_id);
            }

            \DB::commit();
            return [
                'message' => $message
            ];
        } catch (\Exception $e) {
            \DB::rollback();
            throw new ApiGenericException($e->getMessage());
        }
    }

    public function pendingTasks(Request $request){
        $validator = Validator::make($request->all(),[
            'job_id' => ['required'],
            'pl_item_id' => ['required'],
            'status' => ['nullable'],
        ],[
            'job_id.required' => 'Job id is required',
            'pl_item_id.required' => 'Picklist item id is required',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $job = ErpWhmJob::find($request->job_id);
        if (!$job) {
            throw ValidationException::withMessages([
                'job_id' => ['Job not found.'],
            ]);
        }

        $status = $request->status;
        $plHeaderId = $job->morphable_id;
        $storagePointId = $request->storage_point_id;

        $reservedStock = StockLedgerReservation::where('issue_book_type', $job->trns_type)
            ->where('issue_header_id',$plHeaderId)
            ->where('issue_detail_id',$request->pl_item_id);

        $transType = $reservedStock->pluck('receipt_book_type')
            ->toArray();

        $mrnIds = $reservedStock->pluck('receipt_detail_id')
            ->toArray();

        $scannedPacketsUids = ErpItemUniqueCode::where('job_id', $request->job_id)
                ->where('morphable_id',$request->pl_item_id)
                ->where('job_type',CommonHelper::PICKING)
                ->get()
                ->pluck('uid')
                ->toArray();
        // dd($scannedPacketsUids);

        $pendingTasksQuery = ErpItemUniqueCode::with(['storagePoint' => function($q){
                $q->select('id', 'storage_number');
            },'vendor' => function ($q) {
                $q->select('id', 'vendor_code', 'company_name');
            }])
            ->whereIn('morphable_id',$mrnIds)
            ->whereIn('trns_type',$transType)
            // ->whereNull('utilized_id')
            ->when($storagePointId,function($q) use($storagePointId){
                $q->where('storage_point_id',$storagePointId);
            });

            if($status == CommonHelper::PENDING){
                $pendingTasksQuery->whereNull('utilized_id');
            }elseif($status == CommonHelper::SCANNED){
                $pendingTasksQuery->whereIn('utilized_id',$scannedPacketsUids);
            }else{
                $pendingTasksQuery->where(function($q) use($scannedPacketsUids){
                    $q->whereNull('utilized_id')
                    ->orWhereIn('utilized_id',$scannedPacketsUids);
                });
            }

        $pendingTasks = $pendingTasksQuery->select('uid','job_id','group_id','company_id','organization_id','book_code','doc_no','doc_date','status','item_id','item_uid','item_name','item_code','item_attributes','status','utilized_id','storage_point_id','packet_no','total_packets','vendor_id')
            ->get();

        return [
            'message' => 'Records fetched successfully',
            "data" => $pendingTasks,
        ];

    }

    public function scanStorage(Request $request){
        $validator = Validator::make($request->all(),[
            'storage_number' => ['required'],
            'job_id' => ['required'],
            'pl_item_id' => ['required'],
        ],[
            'storage_number.required' => 'Storage number is required',
            'job_id.required' => 'Job id is required',
            'pl_item_id.required' => 'Picklist item id is required',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }


        $job = ErpWhmJob::where('id',$request->job_id)
            ->where('type',CommonHelper::PICKING)
            ->first();

        if(!$job){
            throw ValidationException::withMessages([
                'job_id' => ['Job not found.'],
            ]);
        }
        if ($job->trns_type === ConstantHelper::MATERIAL_RETURN_SERVICE_ALIAS_NAME) {
            $detail = ErpMiItem::find($request->pl_item_id);
        } else if ($job->trns_type === ConstantHelper::PL_SERVICE_ALIAS) {
            $detail = ErpPlItem::find($request->pl_item_id);
        }
        if(!$detail){
            throw ValidationException::withMessages([
                'pl_item_id' => ['Item not found.'],
            ]);
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

        $plHeaderId = $job->morphable_id;
        $reservedStock = $detail->stockReservation()
            ->where('issue_book_type',$job->trns_type)
            ->where('issue_header_id',$plHeaderId)
            ->where('issue_detail_id',$request->pl_item_id);

        $transType = $reservedStock->pluck('receipt_book_type')
            ->toArray();

        $mrnIds = $reservedStock->pluck('receipt_detail_id')
            ->toArray();

        $packets = ErpItemUniqueCode::where('storage_point_id',$storagePointId)
            ->where('item_id',$detail->item_id)
            ->whereNull('utilized_id')
            ->whereIn('morphable_id',$mrnIds)
            ->whereIn('trns_type', $transType)
            ->pluck('item_uid')
            ->toArray();

        if (empty($packets)) {
            throw ValidationException::withMessages([
                'packet_ids' => ['No packets found in storage point: ' . $request->storage_number],
            ]);
        }

        return [
            'data' => $response['data'],
            'message' => $response['message'],
        ];

    }

    private function getItemDetailsData(ErpWhmJob $job, int $storeId)
    {
        $morphableId = $job -> morphable_id;
        $trnsType = $job -> trns_type;
        if ($trnsType === ConstantHelper::PL_SERVICE_ALIAS) {
            return ErpPlItem::where('pl_header_id', $morphableId)
                ->whereHas('header', function($q) use($storeId){
                    $q->where('store_id',$storeId);
                })
                ->select(
                    'id as pl_item_id',
                    'pl_header_id',
                    'item_id',
                    'item_name',
                    'item_code',
                    // DB::raw('CAST(inventory_uom_qty AS UNSIGNED) as quanity'),
                    DB::raw("(
                        CAST(inventory_uom_qty AS UNSIGNED) * 
                        (
                            SELECT IFNULL(storage_uom_count, 1)
                            FROM erp_items 
                            WHERE erp_items.id = erp_pl_items.item_id
                        )
                    ) as quantity"),
                    'attributes',
                    DB::raw("(
                        SELECT COUNT(*)
                        FROM erp_item_unique_codes
                        WHERE morphable_id = erp_pl_items.id
                        AND morphable_type = '" . addslashes(ErpPlItem::class) . "'
                        AND status = '" . CommonHelper::SCANNED . "'
                    ) as scanned_count"),
                )->paginate(CommonHelper::PAGE_LENGTH_10);
        } else if ($trnsType === ConstantHelper::MATERIAL_ISSUE_SERVICE_ALIAS_NAME) {
            return ErpMiItem::where('material_issue_id', $morphableId)
                ->whereHas('header', function($q) use($storeId){
                    $q->where('from_store_id',$storeId);
                })
                ->with('attributes')
                ->select(
                    'id as pl_item_id',
                    'material_issue_id AS pl_header_id',
                    'item_id',
                    'item_name',
                    'item_code',
                    // DB::raw('CAST(inventory_uom_qty AS UNSIGNED) as quanity'),
                    DB::raw("(
                        CAST(inventory_uom_qty AS UNSIGNED) * 
                        (
                            SELECT IFNULL(storage_uom_count, 1)
                            FROM erp_items 
                            WHERE erp_items.id = erp_mi_items.item_id
                        )
                    ) as quantity"),
                    DB::raw("(
                        SELECT COUNT(*)
                        FROM erp_item_unique_codes
                        WHERE morphable_id = erp_mi_items.id
                        AND morphable_type = '" . addslashes(ErpMiItem::class) . "'
                        AND status = '" . CommonHelper::SCANNED . "'
                    ) as scanned_count")
                )->paginate(CommonHelper::PAGE_LENGTH_10);
        } else {
            return [];
        }
    }

    public function scannedItemQrs(Request $request){
        $validator = Validator::make($request->all(),[
            'job_id' => ['required'],
            'pl_item_id' => ['required'],
        ],[
            'job_id.required' => 'Job id is required',
            'pl_item_id.required' => 'Pl item id is required',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        \DB::beginTransaction();
        try {
            $scannedPackets = ErpItemUniqueCode::where('job_type', CommonHelper::PICKING)
                ->where('morphable_id', $request->pl_item_id)
                ->where('job_id',$request->job_id)
                ->where('status',CommonHelper::SCANNED)
                ->where('doc_type',CommonHelper::ISSUE)
                ->select('uid','job_id','group_id','company_id','organization_id','book_code','doc_no','doc_date','status','item_id','item_uid','item_name','item_code','item_attributes','status','vendor_id','storage_point_id')
                ->get();


        \DB::commit();
            return [
                'data' => $scannedPackets
            ];
        } catch (\Exception $e) {
            \DB::rollback();
            throw new ApiGenericException($e->getMessage());
        }
    }
}
