<?php

namespace App\Http\Controllers\WHM;

use App\Exceptions\ApiGenericException;
use App\Helpers\CommonHelper;
use App\Helpers\Helper;
use App\Helpers\StoragePointHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\WHM\UnloadingResource;
use App\Models\WHM\ErpItemUniqueCode;
use App\Models\WHM\ErpWhmJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class PutawayTaskController extends Controller
{
    public function index(Request $request){
        $search = $request->input('search');
        $location = $request->input('store_id');
        $jobs = ErpWhmJob::with(['morphable.book' => function($q){
                        $q->select('id','book_code');
                    }, 'morphable.erpStore' => function($q){
                        $q->select('id','store_name');
                    }, 'itemUniqueCodes' => function($q){
                        $q->select('id','job_id','item_id');
                    }])
                    ->where('type', CommonHelper::PUTAWAY)
                    ->when($search, function ($query) use ($search) {
                        $query->whereHasMorph('morphable', ['App\Models\MrnHeader','App\Models\InspectionHeader'], function ($q) use ($search) {
                             $q->where(function($q2) use ($search) {
                                $q2->where('document_number', 'like', "%{$search}%")
                                ->orWhere('consignment_no', 'like', "%{$search}%")
                                ->orWhere('supplier_invoice_no', 'like', "%{$search}%")
                                ->orWhereHas('book', function ($bookQuery) use ($search) {
                                    $bookQuery->where('book_code', 'like', "%{$search}%");
                                });
                            });
                        });
                    })
                    ->when($location, function ($query) use ($location) {
                        $query->whereHasMorph('morphable', ['App\Models\MrnHeader','App\Models\InspectionHeader'], function ($q) use ($location) {
                            $q->where('store_id', $location);
                        });
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

        $items = ErpItemUniqueCode::select('job_id','group_id','morphable_id as putaway_item_id','company_id','organization_id','book_code','doc_no','doc_date','item_id','item_name','item_code','item_attributes', \DB::raw('COUNT(*) as quantity'))
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
                $response = StoragePointHelper::getStoragePoints(
                    $item->item_id,
                    null,
                    $request->store_id,
                    null
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

        $item = ErpItemUniqueCode::select('job_id','group_id','morphable_id as putaway_item_id','company_id','organization_id','book_code','doc_no','doc_date','item_id','item_name','item_code','item_attributes', \DB::raw('COUNT(*) as quantity'))
                ->where('store_id', $request->store_id)
                ->where('job_id',$request->job_id)
                ->where('morphable_id',$request->putaway_item_id)
                ->where('job_type', CommonHelper::PUTAWAY)
                ->where('doc_type', CommonHelper::RECEIPT)
                ->groupBy('morphable_id')
                ->first();

        if($item){

            $item->storage_points = [];

            // Get storage points
            $response = StoragePointHelper::getStoragePoints(
                $item->item_id,
                null,
                $request->store_id,
                null
            );

            if (!empty($response['status']) && $response['status'] === 'success') {
                $item->storage_points = $response['data'];

                $item->storage_points = collect($item->storage_points)->map(function($storageData) use($request, $item) {
                    $scannedPackets = self::scannedPackets(
                        $request->store_id,
                        $item->item_id,
                        $storageData->id,
                        $request->job_id,
                        $request->putaway_item_id
                    );
                    $storageData->scannedPacketCount = count($scannedPackets);
                    $storageData->scannedPackets = $scannedPackets;

                    return $storageData;
                });
            }

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
            ->select('uid','item_uid')
            ->get();

        return $packets;
    }

    public function pendingTasks(Request $request){
        $validator = Validator::make($request->all(),[
            'job_id' => ['required'],
            'putaway_item_id' => ['nullable'],
        ],[
            'job_id.required' => 'Job id is required',
            'putaway_item_id.required' => 'Putaway item id is required',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

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
        ->where('job_type', CommonHelper::PUTAWAY)
        ->whereIn('status',[CommonHelper::PENDING,CommonHelper::SCANNED])
        ->select('uid','job_id','morphable_id as putaway_item_id','group_id','company_id','organization_id','book_code','doc_no','doc_date','status','item_id','item_uid','item_name','item_code','item_attributes','status','vendor_id')
        ->get();

        return [
            'message' => 'Records fetched successfully',
            "data" => $pendingTasks,
        ];

    }

    public function saveAsDraft(Request $request){
        $validator = Validator::make($request->all(),[
            'job_id' => ['required'],
            'packet_ids' => ['required', 'array'],
            'storage_point_id' => ['required']
        ],[
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

        $packets = ErpItemUniqueCode::where('job_id', $request->job_id)
            ->whereIn('item_uid', $request->packet_ids)
            ->where('job_type', CommonHelper::PUTAWAY)
            ->get();

        $validPackets = $packets->pluck('item_uid')->toArray();
        $invalidPackets = array_diff($request->packet_ids, $validPackets);

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

        \DB::beginTransaction();
        try {
            // Get Login User
            $user = Helper::getAuthenticatedUser();
            
            // Update Job Status
            if($job->status != CommonHelper::DEVIATION){
                $job->status = CommonHelper::IN_PROGRESS;
                $job->save();
            }
            
            // Update Task Status
            ErpItemUniqueCode::where('job_id',$request->job_id)
            ->where('job_type', CommonHelper::PUTAWAY)
            ->whereIn('item_uid',$request->packet_ids)
            ->update([
                'status' => CommonHelper::SCANNED,
                'storage_point_id' => $request->storage_point_id,
                'action_by' => $user->id,
                'action_at' => now()
            ]);

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

    // public function scannedPackets(Request $request){
    //     $validator = Validator::make($request->all(),[
    //         'job_id' => ['required'],
    //     ],[
    //         'job_id.required' => 'Job id is required',
    //     ]);

    //     if ($validator->fails()) {
    //         throw new ValidationException($validator);
    //     }

    //     \DB::beginTransaction();
    //     try {
    //         // Fetch Scanned Packets
    //         $scannedPackets = ErpItemUniqueCode::with(['vendor' => function ($q) {
    //             $q->select('id', 'vendor_code', 'company_name');
    //         },'storagePoint' => function($q){
    //             $q->select('id', 'storage_number');
    //         }])
    //         ->where('job_id',$request->job_id)
    //         ->where('status',CommonHelper::SCANNED)
    //         ->select('uid','job_id','group_id','company_id','organization_id','book_code','doc_no','doc_date','status','item_id','item_name','item_code','item_attributes','status','vendor_id','storage_point_id')
    //         ->get();

    //         \DB::commit();
    //         return [
    //             'data' => $scannedPackets
    //         ];
    //     } catch (\Exception $e) {
    //         \DB::rollback();
    //         throw new ApiGenericException($e->getMessage());
    //     }
    // }

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
                StoragePointHelper::saveStoragePoints($header, $detailIds, $job->trns_type, NULL, NULL, NULL);
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
