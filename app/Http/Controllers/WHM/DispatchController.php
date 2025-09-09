<?php

namespace App\Http\Controllers\WHM;

use App\Exceptions\ApiGenericException;
use App\Helpers\CommonHelper;
use App\Helpers\Helper;
use App\Helpers\Inventory\StockReservation;
use App\Http\Controllers\Controller;
use App\Http\Resources\WHM\DispatchResource;
use App\Http\Resources\WHM\UnloadingResource;
use App\Lib\Services\WHM\DispatchJob;
use App\Models\ErpInvoiceItem;
use App\Models\ErpSaleInvoice;
use App\Models\WHM\ErpItemUniqueCode;
use App\Models\WHM\ErpWhmJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use DB;

class DispatchController extends Controller
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
                            $q->select('id','name');
                    }])
                    ->where('type', CommonHelper::DISPATCH)
                    ->when($search, function ($query) use ($search) {
                        $query->whereHasMorph('morphable', ['App\Models\ErpSaleInvoice'], function ($q) use ($search) {
                             $q->where(function($q2) use ($search) {
                                $q2->where('document_number', 'like', "%{$search}%")
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
        $jobResources = DispatchResource::collection($jobs->getCollection());

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

        $job = ErpWhmJob::find($request->job_id);
        if (!$job) {
            throw ValidationException::withMessages([
                'job_id' => ['Job not found.'],
            ]);
        }

        $morphableId = $job->morphable_id;
        $storeId = $request->store_id;

        $items = ErpInvoiceItem::where('sale_invoice_id', $morphableId)
                        ->where('store_id',$storeId)
                        ->with('attributes')
                        ->select(
                            'id as sale_invoice_item_id',
                            'sale_invoice_id',
                            'item_id',
                            'item_name',
                            'item_code',
                            DB::raw('CAST(inventory_uom_qty AS UNSIGNED) as quanity'),
                            DB::raw("(
                                SELECT COUNT(*)
                                FROM erp_item_unique_codes
                                WHERE morphable_id = erp_invoice_items.id
                                AND morphable_type = '" . addslashes(ErpInvoiceItem::class) . "'
                                AND status = '" . CommonHelper::SCANNED . "'
                            ) as scanned_count")
                        )
                        ->paginate(CommonHelper::PAGE_LENGTH_10);
        return [
            'message' => 'Records fetched successfully',
            "data" => $items,
        ];

    }

    public function pendingTasks(Request $request){
        $validator = Validator::make($request->all(),[
            'job_id' => ['required'],
            'status' => ['nullable'],
        ],[
            'job_id.required' => 'Job id is required'
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $status = $request->status;
        $job = ErpWhmJob::find($request->job_id);
        $plitemIds = ErpInvoiceItem::where('sale_invoice_id',$job->morphable_id)->pluck('pl_item_detail_id')->toArray();

        $scannedPacketsUids = ErpItemUniqueCode::where('job_id', $request->job_id)
                ->where('job_type',CommonHelper::DISPATCH)
                ->where('status',CommonHelper::SCANNED)
                ->get()
                ->pluck('uid')
                ->toArray();
        
        $pendingTasksQuery = ErpItemUniqueCode::with(['vendor' => function ($q) {
                $q->select('id', 'vendor_code', 'company_name');
            }])
        ->whereIn('morphable_id',$plitemIds)
        ->where('job_type',CommonHelper::PICKING)
        ->where('doc_type',CommonHelper::RECEIPT);

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

        $pendingTasks = $pendingTasksQuery->select('uid','job_id','group_id','company_id','organization_id','book_code','doc_no','doc_date','status','item_id','item_uid','item_name','item_code','item_attributes','status','vendor_id')
        ->get();

        return [
            'message' => 'Records fetched successfully',
            "data" => $pendingTasks,
        ];

    }

    public function scannedPackets(Request $request){
        $validator = Validator::make($request->all(),[
            'job_id' => ['required'],
        ],[
            'job_id.required' => 'Job id is required',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        \DB::beginTransaction();
        try {
            // Fetch Scanned Packets
            $scannedPackets = ErpItemUniqueCode::where('job_id',$request->job_id)
            ->where('status',CommonHelper::SCANNED)
            ->select('uid','job_id','group_id','company_id','organization_id','book_code','doc_no','doc_date','status','item_id','item_uid','item_name','item_code','item_attributes','status')
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

    public function saveAsDraft(Request $request){
        $validator = Validator::make($request->all(),[
            'job_id' => ['required'],
            'packet_ids' => ['required', 'array'],
        ],[
            'job_id.required' => 'Job id is required',
            'packet_ids.required' => 'Scan a packet to draft the form',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $job = ErpWhmJob::find($request->job_id);
        if(!$job){
            throw ValidationException::withMessages([
                'job_id' => ['Job no found.'],
            ]);
        }

        $plitemIds = ErpInvoiceItem::where('sale_invoice_id',$job->morphable_id)->pluck('pl_item_detail_id')->toArray();

        $packets = ErpItemUniqueCode::whereIn('morphable_id',$plitemIds)
            ->whereIn('item_uid', $request->packet_ids)
            ->where('job_type', CommonHelper::PICKING)
            ->whereNull('utilized_id')
            ->get();

        // Check invalid packets
        $validPackets = $packets->pluck('item_uid')->toArray();
        $invalidPackets = array_diff($request->packet_ids, $validPackets);

        if (!empty($invalidPackets)) {
            throw ValidationException::withMessages([
                'packet_ids' => ['Invalid or mismatched packet IDs: ' . implode(', ', $invalidPackets)],
            ]);
        }

        // Filter already scanned packets from the result set
        $alreadyScanned = ErpItemUniqueCode::where('job_id', $request->job_id)
            ->whereIn('item_uid', $request->packet_ids)
            ->where('status', CommonHelper::SCANNED)
            ->where('job_type', CommonHelper::DISPATCH)
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
            $invoiceItemIds = ErpInvoiceItem::where('sale_invoice_id', $job->morphable_id)
                ->pluck('id', 'pl_item_detail_id')
                ->toArray();

            (new DispatchJob())->scanQRCodes($header, $job->id, $packets, $user->id, $invoiceItemIds, 'App\Models\ErpInvoiceItem');

            \DB::commit();
            return [
                'message' => 'Task saved in draft'
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

            $saleInvoice = ErpSaleInvoice::find($job->morphable_id);
            if($saleInvoice && $job -> status == CommonHelper::CLOSED){
                foreach ($saleInvoice->items as $invItem) {
                    $status = StockReservation::settlementOfReservedStocks($saleInvoice -> document_type, $saleInvoice->id, $invItem->id, $invItem->inventory_uom_qty);
                    if ($status['status'] == 'error') {
                        throw new ApiGenericException($status['message']);
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

    public function removePacket(Request $request){
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

        $uniqueCode = ErpItemUniqueCode::where('job_id',$request->job_id)->where('item_uid', $request->packet_id)->first();
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

            $plDetail = ErpItemUniqueCode::where('item_uid', $request->packet_id)
                ->where('job_type', CommonHelper::PICKING)
                ->where('status',CommonHelper::SCANNED)
                ->where('utilized_id',$uniqueCode->uid)
                ->first();

            if($plDetail){
                $plDetail->utilized_id = NULL;
                $plDetail->save();
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
}
