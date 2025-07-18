<?php

namespace App\Http\Controllers\WHM;

use App\Exceptions\ApiGenericException;
use App\Helpers\CommonHelper;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Resources\WHM\UnloadingResource;
use App\Lib\Services\WHM\WhmJob;
use App\Models\GateEntryAttribute;
use App\Models\ItemAttribute;
use App\Models\WHM\ErpItemUniqueCode;
use App\Models\WHM\ErpWhmJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class UnloadingTaskController extends Controller
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
                    ->where('morphable_type', 'App\Models\GateEntryHeader')
                    ->when($search, function ($query) use ($search) {
                        $query->whereHasMorph('morphable', ['App\Models\GateEntryHeader'], function ($q) use ($search) {
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
                        $query->whereHasMorph('morphable', ['App\Models\GateEntryHeader'], function ($q) use ($location) {
                            $q->where('store_id', $location);
                        });
                    })
                    ->whereIn('status',[CommonHelper::PENDING,CommonHelper::IN_PROGRESS, CommonHelper::DEVIATION])
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

    public function pendingTasks(Request $request){
        $validator = Validator::make($request->all(),[
            'job_id' => ['required']
        ],[
            'job_id.required' => 'Job id is required'
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $pendingTasks = ErpItemUniqueCode::with(['vendor' => function ($q) {
            $q->select('id', 'vendor_code', 'company_name');
        }])
        ->where('job_id',$request->job_id)
        // ->where('status',CommonHelper::PENDING)
        ->select('uid','job_id','group_id','company_id','organization_id','book_code','doc_no','doc_date','status','item_id','item_uid','item_name','item_code','item_attributes','status','vendor_id')
        ->get();

        return [
            'message' => 'Records fetched successfully',
            "data" => $pendingTasks,
        ];

    }

    public function scannedPackets(Request $request){
        $validator = Validator::make($request->all(),[
            'id' => ['required'],
        ],[
            'id.required' => 'Id is required',
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
            ->where('job_id',$request->id)
            ->where('status',CommonHelper::SCANNED)
            ->select('uid','job_id','group_id','company_id','organization_id','book_code','doc_no','doc_date','status','item_id','item_name','item_code','item_attributes','status','vendor_id','storage_point_id')
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
            'id' => ['required'],
            'packet_ids' => ['required', 'array'],
        ],[
            'id.required' => 'Id is required',
            'packet_ids.required' => 'Packet IDs are required',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $job = ErpWhmJob::find($request->id);
        if(!$job){
            throw ValidationException::withMessages([
                'id' => ['Job no found.'],
            ]);
        }

        $packets = ErpItemUniqueCode::where('job_id', $request->id)
            ->whereIn('item_uid', $request->packet_ids)
            ->where('morphable_type', 'App\Models\GateEntryDetail')
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
            ErpItemUniqueCode::where('job_id',$request->id)
            ->whereIn('item_uid',$request->packet_ids)
            ->where('morphable_type', 'App\Models\GateEntryDetail')
            ->update([
                'status' => CommonHelper::SCANNED,
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

            \DB::commit();
            return [
                'message' => $message
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

        $uniqueCode = ErpItemUniqueCode::where('item_uid', $request->packet_id)->first();
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
}
