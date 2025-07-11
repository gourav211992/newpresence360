<?php

namespace App\Http\Controllers\WHM;

use App\Helpers\CommonHelper;
use App\Helpers\StoragePointHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\WHM\PicklistResource;
use App\Models\ErpPlItem;
use App\Models\WHM\ErpWhmJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

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
            'job_id' => ['required']
        ],[
            'job_id.required' => 'Job id is required'
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $job = ErpWhmJob::find($request->job_id);
        $morphableId = $job->morphable_id;

        $items = ErpPlItem::with([
                            'item_attributes' => function($q){
                                $q->select('pl_item_id','attribute_name','attribute_value');
                        }])
                        ->where('pl_header_id', $morphableId)
                        ->select('id','pl_header_id','item_id','item_name','item_code','picked_qty')
                        ->get();
        return [
            'message' => 'Records fetched successfully',
            "data" => [
                'records' => $items
            ],
        ];

    }

    public function itemLocation(Request $request){
        $validator = Validator::make($request->all(),[
            'store_id' => ['required'],
            'item_id' => ['required'],
        ],[
            'store_id.required' => 'Store id is required',
            'item_id.required' => 'Item id is required',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $storeId = $request->store_id;
        $itemId = $request->item_id;
        $response = StoragePointHelper::getStoragePoints($itemId, $storeId);
        
        if($response['code'] == 500){
            throw ValidationException::withMessages([
                'item_id' => [$response['message']],
            ]);
        }

        return [
            'data' => $response['data'],
            'message' => $response['message'],
        ];

    }
}
