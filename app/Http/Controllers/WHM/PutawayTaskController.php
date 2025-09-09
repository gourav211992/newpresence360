<?php

namespace App\Http\Controllers\WHM;

use App\Exceptions\ApiGenericException;
use App\Helpers\CommonHelper;
use App\Helpers\ConstantHelper;
use App\Helpers\Helper;
use App\Helpers\InventoryHelper;
use App\Helpers\StoragePointHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\WHM\UnloadingResource;
use App\Models\ErpMaterialIssueHeader;
use App\Models\Item;
use App\Models\MrnBatchDetail;
use App\Models\MrnHeader;
use App\Models\WHM\ErpItemUniqueCode;
use App\Models\WHM\ErpWhmJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use DB;

class PutawayTaskController extends Controller
{
    public function index(Request $request){
        $search = $request->input('search');
        $location = $request->input('store_id');
        $subLocation = $request->input('sub_store_id');
        $jobs = ErpWhmJob::with(['morphable.book' => function($q){
                        $q->select('id','book_code');
                    }, 'itemUniqueCodes' => function($q){
                        $q->select('id','job_id','item_id');
                    },'store' => function($q){
                        $q->select('id','store_name');
                    },'subStore' => function($q){
                        $q->select('id','name','is_warehouse_required');
                    }])
                    ->where('type', CommonHelper::PUTAWAY)
                    ->when($search, function ($query) use ($search) {
                        $query->whereHasMorph('morphable', ['App\Models\MrnHeader','App\Models\InspectionHeader', 'App\Models\ErpMaterialIssueHeader'], function ($q) use ($search) {
                             $q->where(function($q2) use ($search) {
                                $q2->where('document_number', 'like', "%{$search}%")
                                // ->orWhere('consignment_no', 'like', "%{$search}%")
                                // ->orWhere('supplier_invoice_no', 'like', "%{$search}%")
                                ->orWhereHas('book', function ($bookQuery) use ($search) {
                                    $bookQuery->where('book_code', 'like', "%{$search}%");
                                });
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
        $jobResources = UnloadingResource::collection($jobs->getCollection());

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

        $job = ErpWhmJob::where('type', CommonHelper::PUTAWAY)->where('id',$request->job_id)->first();
        if (!$job) {
            throw ValidationException::withMessages([
                'job_id' => ['Job not found.'],
            ]);
        }

        $items = ErpItemUniqueCode::with(['item' => function($q){
                    $q->select('id','is_serial_no','is_asset');
                }])
                ->select('job_id','sub_store_id','group_id','morphable_id as putaway_item_id','company_id','organization_id','book_code','doc_no','doc_date','item_id','item_name','item_code','item_attributes','batch_number','manufacturing_year','expiry_date','serial_no', \DB::raw('COUNT(*) as quantity'))
                ->where('store_id', $request->store_id)
                ->where('job_id',$request->job_id)
                ->where('job_type', CommonHelper::PUTAWAY)
                ->where('doc_type', CommonHelper::RECEIPT)
                ->groupBy('morphable_id')
                ->paginate(CommonHelper::PAGE_LENGTH_10);

        // Get all morphable_ids from paginated items
        $morphableIds = $items->pluck('putaway_item_id')->toArray();

        // Get scanned quantities grouped by morphable_id
        $scannedQuantities = ErpItemUniqueCode::select(
                'morphable_id',
                \DB::raw('COUNT(*) as scanned_quantity')
            )
            ->whereIn('morphable_id', $morphableIds)
            ->where('job_id',$request->job_id)
            ->where('status', CommonHelper::SCANNED)
            ->groupBy('morphable_id')
            ->pluck('scanned_quantity', 'morphable_id')
            ->toArray();

        foreach ($items as $item) {
            $item->scanned_quantity = $scannedQuantities[$item->putaway_item_id] ?? 0;

            $item->storage_points = [];
            if ($item->item_id) {
                $subStoreId = $item->sub_store_id;
                $response = StoragePointHelper::getStoragePoints(
                    $item->item_id,
                    null,
                    $request->store_id,
                    $subStoreId
                );

                if (!empty($response['status']) && $response['status'] === 'success') {
                    $item->storage_points = $response['data'];
                }
            }

        }

        return [
            'message' => 'Records fetched successfully',
            "data" => [
                'records' => $items->items(), // only the current page's items
                'pagination' => [
                    'current_page' => $items->currentPage(),
                    'last_page' => $items->lastPage(),
                    'per_page' => $items->perPage(),
                    'total' => $items->total(),
                    'from' => $items->firstItem(),
                    'to' => $items->lastItem(),
                ],
            ],
        ];

    }

    public function itemDetail(Request $request){
        $validator = Validator::make($request->all(),[
            'putaway_item_id' => ['required'],
            'job_id' => ['required'],
            'store_id' => ['required'],
        ],[
            'putaway_item_id.required' => 'Putaway item id is required',
            'job_id.required' => 'Job id is required',
            'store_id.required' => 'Store id is required',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $item = ErpItemUniqueCode::select('job_id','group_id','sub_store_id','morphable_id as putaway_item_id','company_id','organization_id','book_code','doc_no','doc_date','item_id','item_name','item_code','item_attributes', \DB::raw('COUNT(*) as quantity'))
                ->where('store_id', $request->store_id)
                ->where('job_id',$request->job_id)
                ->where('morphable_id',$request->putaway_item_id)
                ->where('job_type', CommonHelper::PUTAWAY)
                ->where('doc_type', CommonHelper::RECEIPT)
                ->groupBy('morphable_id')
                ->first();

        if($item){

            $item->storage_points = [];
            $itemId = $item->item_id;

            $storageData = ErpItemUniqueCode::where('store_id', $request->store_id)
                ->where('job_id',$request->job_id)
                ->where('morphable_id',$request->putaway_item_id)
                ->where('job_type', CommonHelper::PUTAWAY)
                ->where('doc_type', CommonHelper::RECEIPT)
                ->where('status', CommonHelper::SCANNED)
                ->select('storage_point_id', DB::raw('COUNT(*) as quantity'))
                ->whereNotNull('storage_point_id')
                ->groupBy('storage_point_id')
                ->get();

             // STEP 2: Map storage point detail with quantity
            $item->storage_points = $storageData->map(function ($record) use($request, $itemId){
                $detailsResponse = StoragePointHelper::getStoragePointDetailById($record->storage_point_id);
                $scannedPackets = self::scannedPackets(
                        $request->store_id,
                        $itemId,
                        $record->storage_point_id,
                        $request->job_id,
                        $request->putaway_item_id
                );

                return [
                    'quantity' => $record->quantity,
                    'details' => $detailsResponse['data'] ?? null,
                    'scannedPacketCount' => $scannedPackets ? $scannedPackets->count() : null,
                    'scannedPackets' => $scannedPackets ?? null,
                ];
            });

            // Get storage points
            // $response = StoragePointHelper::getStoragePoints(
            //     $item->item_id,
            //     null,
            //     $request->store_id,
            //     $subStoreId
            // );

            // if (!empty($response['status']) && $response['status'] === 'success') {
            //     $item->storage_points = $response['data'];

            //     $item->storage_points = collect($item->storage_points)->map(function($storageData) use($request, $item) {
            //         $scannedPackets = self::scannedPackets(
            //             $request->store_id,
            //             $item->item_id,
            //             $storageData->id,
            //             $request->job_id,
            //             $request->putaway_item_id
            //         );
            //         $storageData->scannedPacketCount = count($scannedPackets);
            //         $storageData->scannedPackets = $scannedPackets;

            //         return $storageData;
            //     });
            // }

        }else {
            $item->storage_points = null;
            $item->scannedPacketCount = 0;
            $item->scannedPackets = null;
        }

        return [
            'data' => $item,
            'message' => "Record fetched successfully.",
        ];

    }

    private function scannedPackets($storeId, $itemId, $storagePointId, $jobId, $putawayItemId){
        $packets = ErpItemUniqueCode::where('item_id', $itemId)
            ->where('store_id', $storeId)
            ->where('storage_point_id', $storagePointId)
            ->where('job_id', $jobId)
            ->where('morphable_id', $putawayItemId)
            ->where('job_type',CommonHelper::PUTAWAY)
            ->where('doc_type', CommonHelper::RECEIPT)
            ->where('status',CommonHelper::SCANNED)
            ->select('uid','item_uid','batch_number','manufacturing_year','expiry_date','serial_no')
            ->get();

        return $packets;
    }

    public function pendingTasks(Request $request){
        $validator = Validator::make($request->all(),[
            'job_id' => ['required'],
            'putaway_item_id' => ['nullable'],
            'status' => ['nullable'],
        ],[
            'job_id.required' => 'Job id is required',
            'putaway_item_id.required' => 'Putaway item id is required',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $status = $request->status;

        // custom validation after
        $job = ErpWhmJob::where('type', CommonHelper::PUTAWAY)->where('id',$request->job_id)->first();

        if (!$job) {
            throw ValidationException::withMessages([
                'job_id' => ['Job not found.'],
            ]);
        }

        $putawayItemId = $request->putaway_item_id;

        $pendingTasks = ErpItemUniqueCode::with(['vendor' => function ($q) {
            $q->select('id', 'vendor_code', 'company_name');
        }])
        ->where('job_id',$request->job_id)
        ->when($putawayItemId, function ($q) use ($putawayItemId) {
            $q->where('morphable_id', $putawayItemId);
        })
        ->when($status, function ($query) use ($status) {
            $query->where('status', $status);
        })
        ->where('job_type', CommonHelper::PUTAWAY)
        // ->whereIn('status',[CommonHelper::PENDING,CommonHelper::SCANNED])
        ->select('uid','job_id','morphable_id as putaway_item_id','group_id','company_id','organization_id','book_code','doc_no','doc_date','status','item_id','item_uid','item_name','item_code','item_attributes','status','vendor_id','batch_number','manufacturing_year','expiry_date','serial_no','packet_no','total_packets')
        ->get();

        return [
            'message' => 'Records fetched successfully',
            "data" => $pendingTasks,
        ];

    }

    public function saveAsDraft(Request $request){
        $validator = Validator::make($request->all(),[
            'job_id' => ['required'],
            'packets' => ['required', 'array'],
            'packets.*.packet_id' => ['required', 'string'],
            'packets.*.serial_no' => ['nullable', 'string'],
            'packets.*.manufacturing_year' => ['nullable', 'integer'],
            'storage_point_id' => ['nullable']
        ],[
            'packets.required' => 'Packets are required',
            'packets.*.packet_id.required' => 'Each packet must have an packet_id',
            'job_id.required' => 'Job id is required',
            'packet_ids.required' => 'Packet ids are required',
            'storage_point_id.required' => 'Storage point id is required',
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
        
        $subStore = $job->subStore;
        if ($subStore && $subStore->is_warehouse_required) {
            if (!$request->filled('storage_point_id')) {
                throw ValidationException::withMessages([
                    'storage_point_id' => ['Storage point is required.'],
                ]);
            }
        }

        $packetIds = collect($request->packets)->pluck('packet_id')->toArray();
        $packets = ErpItemUniqueCode::with(['item' => function($q){
                    $q->select('id','is_serial_no','is_asset');
            }])
            ->where('job_id', $request->job_id)
            ->whereIn('item_uid', $packetIds)
            ->where('job_type', CommonHelper::PUTAWAY)
            ->get();

        $validPackets = $packets->pluck('item_uid')->toArray();
        $invalidPackets = array_diff($packetIds, $validPackets);

        if (!empty($invalidPackets)) {
            throw ValidationException::withMessages([
                'packet_ids' => ['Invalid or mismatched packet IDs: ' . implode(', ', $invalidPackets)],
            ]);
        }

        // custom validation after
        $alreadyScanned = $packets->where('status', CommonHelper::SCANNED)
            ->pluck('item_uid')
            ->toArray();

        if (!empty($alreadyScanned)) {
            throw ValidationException::withMessages([
                'packet_ids' => ['Some packets are already scanned: ' . implode(', ', $alreadyScanned)],
            ]);
        }

        // Build map of packets by item_uid
        $requestPackets = collect($request->packets)->keyBy('packet_id');
        // Track serials to detect duplicates
        $seenSerials = [];

        foreach ($packets as $packet) {
            $packetId = $packet->item_uid;
            $item = $packet->item;
            $requestData = $requestPackets[$packetId] ?? null;

            if (!$requestData) {
                continue; // skip if packet not found in request (shouldn't happen)
            }

            $serialNo = $requestData['serial_no'] ?? null;
            $manufacturingYear = $requestData['manufacturing_year'] ?? null;

            // If is_serial_no == 1, then serial_no is required
            if ($item && $item->is_serial_no == 1 && empty($serialNo)) {
                throw ValidationException::withMessages([
                    "packets" => ["Serial number is required for packet ID: {$packetId}"],
                ]);
            }

            // ✅ Validate manufacturing_year if item is an asset
            if ($item && $item->is_asset == 1 && empty($manufacturingYear)) {
                throw ValidationException::withMessages([
                    "packets" => ["Manufacturing year is required for asset-type packet ID: {$packetId}"],
                ]);
            }

            // Optional: Check for duplicate serial numbers (if not null)
            if (!empty($serialNo)) {
                if (in_array($serialNo, $seenSerials)) {
                    throw ValidationException::withMessages([
                        "packets" => ["Duplicate serial number found: {$serialNo}"],
                    ]);
                }
                $seenSerials[] = $serialNo;
            }
        }

        \DB::beginTransaction();
        try {
            // Get Login User
            $user = Helper::getAuthenticatedUser();
            
            // Update Job Status
            if($job->status != CommonHelper::DEVIATION){
                $job->status = CommonHelper::IN_PROGRESS;
                $job->save();
            }

            // Update packets
            foreach ($packets as $packet) {
                $meta = $requestPackets[$packet->item_uid];

                // Update packet
                $packet->status = CommonHelper::SCANNED;
                $packet->storage_point_id = $request->storage_point_id;
                $packet->manufacturing_year = $meta['manufacturing_year'];
                $packet->serial_no = $meta['serial_no'];
                $packet->action_by = $user->id;
                $packet->action_at = now();
                $packet->save();
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
        ],[
            'packet_id.required' => 'Packet id is required',
            'job_id.required' => 'Job id is required',
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

        $uniqueCode = ErpItemUniqueCode::where('item_uid', $request->packet_id)
                        ->where('job_id',$request->job_id)
                        ->where('job_type', CommonHelper::PUTAWAY)
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
            $uniqueCode->status = CommonHelper::PENDING;
            $uniqueCode->storage_point_id = Null;
            $uniqueCode->save();

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

    public function scannedItemQrs(Request $request){
        $validator = Validator::make($request->all(),[
            'job_id' => ['required'],
            'putaway_item_id' => ['required'],
        ],[
            'job_id.required' => 'Job id is required',
            'putaway_item_id.required' => 'Putaway item id is required',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        \DB::beginTransaction();
        try {
            // Fetch Scanned Packets
            $scannedPackets = ErpItemUniqueCode::with(['vendor' => function ($q) {
                $q->select('id', 'vendor_code', 'company_name');
            },'storagePoint' => function($q){
                $q->select('id', 'storage_number');
            }])
            ->where('job_id',$request->job_id)
            ->where('morphable_id', $request->putaway_item_id)
            ->where('job_type', CommonHelper::PUTAWAY)
            ->where('status',CommonHelper::SCANNED)
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
        // $alreadyClosed = ErpWhmJob::where('id',$request->job_id)->where('job_closed_at')->first();
        // if (!empty($alreadyClosed)) {
        //     throw ValidationException::withMessages([
        //         'job_id' => ['Job already closed.'],
        //     ]);
        // }


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

            $bookId = $header->series_id;
            $docId = $header->id;
            $revisionNumber = $header->revision_number ?? 0;
            $modelName = $job->morphable_type;
            $remarks = NULL;
            CommonHelper::approveDocument($bookId, $docId, $revisionNumber, $remarks, $actionType, $modelName);

            // Update stock ledger qty
            if($job->status == CommonHelper::CLOSED){
                $detailIds = $job->itemUniqueCodes()->pluck('morphable_id')->unique()->toArray();
                $subStoreId = $job->sub_store_id;

                //Stock Ledger
                if ($job -> trns_type === ConstantHelper::MATERIAL_ISSUE_SERVICE_ALIAS_NAME) {
                    $mi = ErpMaterialIssueHeader::find($job->morphable_id);
                    if ($mi) {
                        $miItemIds = $mi -> items -> pluck('id') -> toArray();
                        $receiveRecords = InventoryHelper::settlementOfInventoryAndStock($mi->id, $miItemIds, ConstantHelper::MATERIAL_ISSUE_SERVICE_ALIAS_NAME, $mi->document_status, 'receipt');
                        if ($receiveRecords['status'] == 'error') {
                            return $receiveRecords['message'];
                        } else {
                            return "";
                        }
                    }
                } else if ($job -> trns_type === ConstantHelper::MRN_SERVICE_ALIAS || $jpb -> trns_type === ConstantHelper::INSPECTION_SERVICE_ALIAS) {
                    $res = StoragePointHelper::saveStoragePoints($header, $detailIds, $job->trns_type, NULL, NULL, NULL, $subStoreId);
                    if($res['status'] == 'error'){
                        \DB::rollback();
                        return[
                            'message' => $res['message']
                        ];
                    }
                }
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
}
