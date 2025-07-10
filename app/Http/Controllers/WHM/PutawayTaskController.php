<?php

namespace App\Http\Controllers\WHM;

use App\Exceptions\ApiGenericException;
use App\Helpers\CommonHelper;
use App\Helpers\Helper;
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
        $jobs = ErpWhmJob::with('morphable.book', 'morphable.items', 'itemUniqueCodes')
                    ->where('morphable_type', 'App\Models\MrnHeader')
                    ->whereHasMorph('morphable', ['App\Models\MrnHeader'], function ($q) use ($search, $location) {
                    if ($search) {
                        $q->where(function($q2) use ($search) {
                            $q2->where('document_number', 'like', "%{$search}%")
                            ->orWhere('consignment_no', 'like', "%{$search}%")
                            ->orWhere('supplier_invoice_no', 'like', "%{$search}%")
                            ->orWhereHas('book', function ($bookQuery) use ($search) {
                                $bookQuery->where('book_code', 'like', "%{$search}%");
                            });
                        });
                    }
                    if ($location) {
                        $q->where('store_id', $location);
                    }
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

        $validPackets = ErpItemUniqueCode::where('job_id', $request->job_id)
            ->whereIn('uid', $request->packet_ids)
            ->where('morphable_type', 'App\Models\MrnDetail')
            ->pluck('uid')
            ->toArray();

        $invalidPackets = array_diff($request->packet_ids, $validPackets);

        if (!empty($invalidPackets)) {
            throw ValidationException::withMessages([
                'packet_ids' => ['Invalid or mismatched packet IDs: ' . implode(', ', $invalidPackets)],
            ]);
        }

        // custom validation after
        $alreadyScanned = ErpItemUniqueCode::where('job_id', $request->job_id)
            ->whereIn('uid', $request->packet_ids)
            ->where('status', CommonHelper::SCANNED)
            ->where('morphable_type', 'App\Models\MrnDetail')
            ->pluck('uid')
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
            $job = ErpWhmJob::find($request->job_id);
            if($job->status != CommonHelper::DEVIATION){
                $job->status = CommonHelper::IN_PROGRESS;
                $job->save();
            }
            
            // Update Task Status
            ErpItemUniqueCode::where('job_id',$request->job_id)
            ->where('morphable_type', 'App\Models\MrnDetail')
            ->whereIn('uid',$request->packet_ids)
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
}
