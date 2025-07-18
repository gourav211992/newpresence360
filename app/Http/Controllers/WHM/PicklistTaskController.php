<?php

namespace App\Http\Controllers\WHM;

use App\Exceptions\ApiGenericException;
use App\Helpers\CommonHelper;
use App\Helpers\Helper;
use App\Helpers\StoragePointHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\WHM\PicklistResource;
use App\Lib\Services\WHM\WhmJob;
use App\Models\ErpPlHeader;
use App\Models\ErpPlItem;
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
        $jobs = ErpWhmJob::with(['morphable.store' => function($q){
                        $q->select('id','store_name');
                    }, 'morphable.items' => function($q){
                        $q->select('id','pl_header_id','picked_qty');
                    }])
                    ->where('morphable_type', 'App\Models\ErpPlHeader')
                    ->when($search, function ($query) use ($search) {
                        $query->whereHasMorph('morphable', ['App\Models\ErpPlHeader'], function ($q) use ($search) {
                             $q->where(function($q2) use ($search) {
                                $q2->where('document_number', 'like', "%{$search}%")
                                    ->orWhere('book_code', 'like', "%{$search}%");
                            });
                        });
                    })
                    ->when($location, function ($query) use ($location) {
                        $query->whereHasMorph('morphable', ['App\Models\ErpPlHeader'], function ($q) use ($location) {
                            $q->where('store_id', $location);
                        });
                    })
                    ->whereIn('status',[CommonHelper::PENDING,CommonHelper::IN_PROGRESS, CommonHelper::DEVIATION])
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

        // $job = ErpWhmJob::find($request->job_id);
        $job = ErpWhmJob::where('morphable_type','App\Models\ErpPlHeader')->where('id',$request->job_id)->first();
        if (!$job) {
            throw ValidationException::withMessages([
                'job_id' => ['Job not found.'],
            ]);
        }

        $morphableId = $job->morphable_id;
        $storeId = $request->store_id;

        $items = ErpPlItem::with([
                            'item_attributes' => function($q){
                                $q->select('pl_item_id','attribute_name','attribute_value');
                        }])
                        ->where('pl_header_id', $morphableId)
                        ->whereHas('header', function($q) use($storeId){
                            $q->where('store_id',$storeId);
                        })
                        ->select('id','pl_header_id','item_id','item_name','item_code','picked_qty')
                        ->get();

        foreach ($items as $item) {
            $item->storage_points = [];

            $storageData = ErpItemUniqueCode::select('storage_point_id', \DB::raw('COUNT(*) as quantity'))
                ->where('item_id', $item->item_id)
                ->where('store_id', $request->store_id)
                ->where('morphable_type', 'App\Models\MrnDetail')
                ->where('doc_type', CommonHelper::RECEIPT)
                ->whereNull('utilized_id') // available packets only
                ->whereNotNull('storage_point_id')
                ->groupBy('storage_point_id')
                ->get();
                
            // STEP 2: Map storage point detail with quantity
            $item->storage_points = $storageData->map(function ($record) {
                $detailsResponse = StoragePointHelper::getStoragePointDetailById($record->storage_point_id);

                return [
                    'quantity' => $record->quantity,
                    'details' => $detailsResponse['data'] ?? null,
                ];
            })->values();
        }

        return [
            'message' => 'Records fetched successfully',
            "data" => $items,
        ];

    }

    public function itemDetail(Request $request){
        $validator = Validator::make($request->all(),[
            'store_id' => ['required'],
            'id' => ['required'],
        ],[
            'store_id.required' => 'Store id is required',
            'id.required' => 'id is required',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $storeId = $request->store_id;
        $plItem = ErpPlItem::with([
                            'item_attributes' => function($q){
                                $q->select('pl_item_id','attribute_name','attribute_value');
                        }])
                        ->whereHas('header', function($q) use($storeId){
                            $q->where('store_id',$storeId);
                        })
                        ->where('id', $request->id)
                        ->select('id','pl_header_id','item_id','item_name','item_code','picked_qty')
                        ->first();

        if($plItem){
            $itemId = $plItem->item_id;
            // STEP 1: Fetch quantities grouped by storage_point_id
            $storageData = ErpItemUniqueCode::where('item_id', $itemId)
                ->where('store_id', $storeId)
                ->where('morphable_type', 'App\Models\MrnDetail')
                ->where('doc_type', CommonHelper::RECEIPT)
                ->select('storage_point_id', DB::raw('COUNT(*) as quantity'))
                ->groupBy('storage_point_id')
                ->get();

            // STEP 2: Map storage point detail with quantity
            $plItem->storage_points = $storageData->map(function ($record) {
                $detailsResponse = StoragePointHelper::getStoragePointDetailById($record->storage_point_id);

                return [
                    'quantity' => $record->quantity,
                    'details' => $detailsResponse['data'] ?? null,
                ];
            });

        }

        return [
            'data' => $plItem,
            'message' => "Record fetched successfully.",
        ];

    }

     public function saveAsDraft(Request $request){
        $validator = Validator::make($request->all(),[
            'job_id' => ['required'],
            'id' => ['required'],
            'packet_ids' => ['required', 'array'],
            'storage_point_id' => ['required']
        ],[
            'job_id.required' => 'Job id is required',
            'id.required' => 'Id is required',
            'packet_ids.required' => 'Packet ids are required',
            'storage_point_id.required' => 'Storage point id is required',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $job = ErpWhmJob::where('id',$request->job_id)
            ->where('morphable_type','App\Models\ErpPlHeader')
            ->first();

        if(!$job){
            throw ValidationException::withMessages([
                'job_id' => ['Job not found.'],
            ]);
        }

        $packets = ErpItemUniqueCode::whereIn('item_uid', $request->packet_ids)
            ->where('storage_point_id',$request->storage_point_id)
            ->whereNull('utilized_id')
            ->where('morphable_type', 'App\Models\MrnDetail')
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
            ->where('morphable_type', 'App\Models\ErpPlItem')
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

            $header = $job->morphable;
            $detail = ErpPlItem::find($request->id);

            (new WhmJob())->copyQRCodesForPickList($detail, $header, $job->id, $request->packet_ids, $request->storage_point_id, $user);


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
            'storage_point_id' => ['required'],
        ],[
            'packet_id.required' => 'Packet id is required',
            'job_id.required' => 'Job id is required',
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

        $uniqueCode = ErpItemUniqueCode::where('item_uid', $request->packet_id)
                        ->where('job_id',$request->job_id)
                        ->where('storage_point_id',$request->storage_point_id)
                        ->where('status', CommonHelper::SCANNED)
                        ->where('morphable_type', 'App\Models\ErpPlItem')
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
            // $uniqueCode->status = CommonHelper::PENDING;
            // $uniqueCode->storage_point_id = Null;
            $uniqueCode->delete();

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
}
