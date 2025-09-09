<?php

namespace App\Http\Controllers\WHM;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Helpers\CommonHelper;
use App\Models\ErpStore;
use App\Models\ErpRgr;
use App\Models\WHM\ErpWhmJob;
use App\Models\WHM\ErpItemUniqueCode;
use App\Models\ErpRgrItem;
use App\Models\Item;
use App\Models\ItemAttribute;
use App\Models\Attribute;
use App\Models\ErpRgrItemSegregation;
use App\Models\ErpRgrDefectType;
use App\Models\ErpRgrDefectTypeDetail;
use App\Helpers\ConstantHelper;
use App\Helpers\RGR\Constants as RGRConstants;
use App\Helpers\ReManufacturing\RepairOrder\Helper as RepHelper;
use App\Helpers\ServiceParametersHelper;
use Illuminate\Validation\ValidationException;
use App\Lib\Services\WHM\WhmJob;
use App\Helpers\Helper;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class RgrJobController extends Controller
{

   public function getRgr(Request $request, $store_id)
    {
        try {
            if (!is_numeric($store_id)) {
                throw ValidationException::withMessages(['store_id' => ['Invalid store_id provided.']]);
            }

            $storeExists = ErpStore::where('id', $store_id)->exists();
            if (!$storeExists) {
                throw ValidationException::withMessages(['store_id' => ['Store does not exist.']]);
            }

            $search = $request->get('search'); 

            $rgrs = ErpRgr::with(['items', 'job.itemUniqueCodes'])
                ->whereHas('job', function ($query) use ($store_id) {
                    $query->where('store_id', $store_id)
                        ->where('status', '!=', 'closed'); 
                })
                ->when($search, function ($query) use ($search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('book_code', 'like', "%{$search}%")
                        ->orWhere('document_number', 'like', "%{$search}%")
                        ->orWhere('trip_no', 'like', "%{$search}%")
                        ->orWhere('vehicle_no', 'like', "%{$search}%")
                        ->orWhere('store_name', 'like', "%{$search}%");
                    });
                })
                ->orderBy('id','desc')
                ->paginate(CommonHelper::PAGE_LENGTH_10);

            if ($rgrs->isEmpty()) {
                throw ValidationException::withMessages(['rgrs' => ['No records found.']]);
            }

            $result = $rgrs->map(function ($rgr) {
                $job = $rgr->job;

                return [
                    'id'          => $rgr?->job?->id,
                    'document_no' => ($rgr->book_code ?? '') . '-' . ($rgr->document_number ?? ''),
                    'trip_no'     => $rgr->trip_no ?? "",
                    'vehicle_no'  => $rgr->vehicle_no ?? "",
                    'store_name'  => $rgr->store_name ?? "",
                    'total_items' => $rgr->items->count(),
                    'job' => $job ? [
                        'total_packets' => $job->itemUniqueCodes->count(),
                        'job_status'    => $job->status ?? "",
                        'created_at'    => $job->created_at ? $job->created_at->format('Y-m-d') : "",
                    ] : [],
                ];
            });

            return [
                'message' => 'Data retrieved successfully.',
                'data' => [
                    'records' => $result,
                    'pagination' => [
                        'current_page' => $rgrs->currentPage(),
                        'last_page'    => $rgrs->lastPage(),
                        'per_page'     => $rgrs->perPage(),
                        'total'        => $rgrs->total(),
                        'from'         => $rgrs->firstItem(),
                        'to'           => $rgrs->lastItem(),
                    ]
                ]
            ];

        } catch (\Throwable $e) {
            throw new ApiGenericException($e->getMessage());
        }
    }


   public function getRgrDetails($job_id)
    {
        try {
            if (!is_numeric($job_id)) {
                throw ValidationException::withMessages(['job_id' => ['Invalid job_id provided.']]);
            }

            $job = ErpWhmJob::where('id', $job_id)
                ->where('morphable_type', ErpRgr::class)
                ->with('morphable', 'itemUniqueCodes')
                ->first();

            if (!$job || !$job->morphable) {
                throw ValidationException::withMessages(['job_id' => ['No RGR found for this job.']]);
            }

            if ($job->status === 'closed') {  
                throw ValidationException::withMessages(['job_id' => ['This job is closed.']]);
            }

            $rgr = $job->morphable;

            $scannedItems = ErpItemUniqueCode::where('job_id', $job_id)
                ->where('status', 'scanned')
                ->orderBy('id', 'desc')
                ->paginate(CommonHelper::PAGE_LENGTH_10);

            $formattedScannedItems = $scannedItems->map(function ($uniqueCode) {
                $attributes = [];

                if ($uniqueCode->item_attributes) {
                    if (is_string($uniqueCode->item_attributes)) {
                        $attributes = json_decode($uniqueCode->item_attributes, true) ?? [];
                    } elseif (is_array($uniqueCode->item_attributes)) {
                        $attributes = $uniqueCode->item_attributes;
                    }
                }

                return [
                    'id'          => $uniqueCode->id ?? "",
                    'item_id'     => $uniqueCode->item_id ?? "",
                    'item_code'   => $uniqueCode->item_code ?? "",
                    'item_name'   => $uniqueCode->item_name ?? "",
                    'attributes'  => $attributes,
                    'uid'         => $uniqueCode->uid ?? "",
                    'item_uid'    => $uniqueCode->item_uid ?? "",
                    'status'      => $uniqueCode->status ?? "",
                ];
            });

            if ($formattedScannedItems->isEmpty()) {
                throw ValidationException::withMessages(['scanned_items' => ['No scanned items found.']]);
            }

            $data = [
                'id'                 => $rgr?->job?->id,
                'document_no'        => ($rgr->book_code ?? '') . '-' . ($rgr->document_number ?? ''),
                'trip_no'            => $rgr->trip_no ?? "",
                'vehicle_no'         => $rgr->vehicle_no ?? "",
                'total_item'         => $job->itemUniqueCodes->count(),
                'scanned_items'      => $formattedScannedItems,
                'scanned_item_count' => $scannedItems->total(),
            ];

            $responseData = [
                'rgr' => $data,
                'pagination' => [
                    'current_page' => $scannedItems->currentPage(),
                    'last_page'    => $scannedItems->lastPage(),
                    'per_page'     => $scannedItems->perPage(),
                    'total'        => $scannedItems->total(),
                    'from'         => $scannedItems->firstItem(),
                    'to'           => $scannedItems->lastItem(),
                ]
            ];

            return [
                'message' => 'Data retrieved successfully.',
                'data' => $responseData
            ];

        } catch (\Throwable $e) {
            throw new ApiGenericException($e->getMessage());
        }
    }

   
  public function getDefectSeverity()
    {
        return response()->json([
            'message' => 'Data retrieved successfully.',
            'data'    => ConstantHelper::DEFECT_SEVERITY_LEVELS
        ], 200);
    }

   public function getDamageNatureOptions()
    {
        return response()->json([
            'message' => 'Data retrieved successfully.',
            'data'    => ConstantHelper::DAMAGE_NATURES
        ], 200);
    }

  public function getDefectTypes(string $severity, int $itemId)
    {
        try {
            $severity = ucfirst(strtolower($severity));

            $item = Item::find($itemId);
            if (!$item) {
                throw ValidationException::withMessages(['item_id' => ['The provided item ID does not exist.']]);
            }

            $subcategory_id = $item->subcategory_id;

            $defectType = ErpRgrDefectType::where('category_id', $subcategory_id)
                ->where('defect_severity', $severity)
                ->first();

            if (!$defectType) {
                $defectType = ErpRgrDefectType::whereNull('category_id')
                    ->where('defect_severity', $severity)
                    ->first();
            }

            if (!$defectType) {
                throw ValidationException::withMessages(['defect_type' => ['No matching defect type found for this category and severity.']]);
            }

            $reasons = ErpRgrDefectTypeDetail::select('id', 'reason')
                ->where('header_id', $defectType->id)
                ->get();

            return [
                'message' => 'Successfully retrieved defect reasons.',
                'data'    => $reasons,
            ];

        } catch (\Throwable $e) {
            throw new ApiGenericException($e->getMessage());
        }
    }

    public function getItems(Request $request)
    {
        try {
            $searchTerm = $request->query('search');

            $query = Item::where('status', ConstantHelper::ACTIVE)
                        ->where('type', 'Goods');

            if ($searchTerm) {
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('item_code', 'like', '%' . $searchTerm . '%')
                    ->orWhere('item_name', 'like', '%' . $searchTerm . '%');
                });
            }

            $items = $query->orderBy('id', 'desc')
                        ->select('id','item_code','item_name')
                        ->limit(CommonHelper::PAGE_LENGTH_10)
                        ->get();

            if ($items->isEmpty()) {
                throw ValidationException::withMessages(['items' => ['No Goods items found.']]);
            }

            $records = $items->map(fn($item) => [
                'id'        => $item->id,
                'item_code' => $item->item_code,
                'item_name' => $item->item_name,
            ]);

            return [
                'message' => 'Data retrieved successfully.',
                'data' => [
                    'records' => $records,
                    'total'   => $records->count(),
                ]
            ];

        } catch (\Throwable $e) {
            throw new ApiGenericException($e->getMessage());
        }
    }

  public function getAttributesByItemId($itemId)
    {
        try {
            $item = Item::where('id', $itemId)
                        ->where('status', ConstantHelper::ACTIVE)
                        ->first();

            if (!$item) {
                throw ValidationException::withMessages(['item' => ['Item not found or not active.']]);
            }

            $itemAttributes = ItemAttribute::with('attributeGroup')
                ->where('item_id', $itemId)
                ->orderBy('id', 'asc')
                ->get();

            if ($itemAttributes->isEmpty()) {
                throw ValidationException::withMessages(['attributes' => ['No attributes found for this item.']]);
            }

            $attributesByGroup = [];

            foreach ($itemAttributes as $itemAttribute) {
                $attributeGroup = $itemAttribute->attributeGroup;
                if (!$attributeGroup) continue;

                $attributeGroupId = $attributeGroup->id;
                $attributeGroupName = $attributeGroup->name;
                $attributeIds = is_array($itemAttribute->attribute_id)
                    ? $itemAttribute->attribute_id
                    : json_decode($itemAttribute->attribute_id, true);

                $attributes = Attribute::whereIn('id', $attributeIds)
                    ->get(['id', 'value']);

                if (!isset($attributesByGroup[$attributeGroupId])) {
                    $attributesByGroup[$attributeGroupId] = [
                        'attr_name' => $attributeGroupId,
                        'attribute_name' => $attributeGroupName,
                        'options' => [],
                    ];
                }

                foreach ($attributes as $attribute) {
                    $attributesByGroup[$attributeGroupId]['options'][] = [
                        'attr_value' => $attribute->id,
                        'attribute_value' => $attribute->value,
                    ];
                }
            }

            $response = array_values($attributesByGroup);

            return [
                'message' => 'Data retrieved successfully.',
                'data' => $response,
            ];

        } catch (\Throwable $e) {
            throw new ApiGenericException($e->getMessage());
        }
    }


  public function scanItem($item_uid)
    {
        try {
            $uniqueItem = ErpItemUniqueCode::where('uid', $item_uid)->first();

            if (!$uniqueItem) {
                throw ValidationException::withMessages(['item' => ['Item not found.']]);
            }

            if ($uniqueItem->status === 'scanned') {
                throw ValidationException::withMessages(['item' => ['Item already scanned.']]);
            }

            $attributes = [];
            if ($uniqueItem->item_attributes) {
                if (is_string($uniqueItem->item_attributes)) {
                    $attributes = json_decode($uniqueItem->item_attributes, true) ?? [];
                } elseif (is_array($uniqueItem->item_attributes)) {
                    $attributes = $uniqueItem->item_attributes;
                }
            }

            return [
                'message' => 'Data retrieved successfully.',
                'data' => [
                    'id'              => $uniqueItem->id,
                    'item_id'         => $uniqueItem->item_id,
                    'item_code'       => $uniqueItem->item_code,
                    'item_name'       => $uniqueItem->item_name,
                    'item_uid'        => $uniqueItem->item_uid,
                    'uid'             => $uniqueItem->uid,
                    'status'          => $uniqueItem->status,
                    'attributes'      => $attributes,
                    'label_status'    => true,
                    'delivery_cancel' => false,
                    'packing_status'  => true,
                ],
            ];

        } catch (\Throwable $e) {
            throw new ApiGenericException($e->getMessage());
        }
    }


  public function createSegregation(Request $request)
    {
        DB::beginTransaction();
        try {

            $validated = $request->validate([
                'id' => 'nullable|exists:erp_rgr_item_segregations,id',
                'unique_item_id' => 'required|exists:erp_item_unique_codes,id',
                'label_status' => 'nullable|boolean',
                'delivery_cancel' => 'nullable|boolean',
                'packing_status' => 'nullable|boolean',
                'defect_severity' => 'nullable|string|in:minor,major,scrap',
                'defect_type' => 'nullable|string',
                'damage_nature' => 'nullable|string|in:no_damage,customer_damage,transit_handling_damage,wear_tear_damage',
                'remarks' => 'nullable|string',
                'new_item_id' => 'nullable|exists:erp_items,id',
                'new_item_attributes' => 'nullable|array',
                'files' => 'nullable|array|max:5',
                'files.*' => 'file|mimes:png,jpeg,jpg,svg,webp|max:2048',
            ]);

            $uniqueItem = ErpItemUniqueCode::find($validated['unique_item_id']);
            if (!$uniqueItem) {
                throw ValidationException::withMessages(['unique_item_id' => ['Item not found.']]);
            }

            if (!$request->id && $uniqueItem->status === 'scanned') {
                throw ValidationException::withMessages(['unique_item_id' => ['Item has already been scanned.']]);
            }

            $job = ErpWhmJob::where('id', $uniqueItem->job_id)
                ->where('morphable_type', ErpRgr::class)
                ->first();

            if (!$job) {
                throw ValidationException::withMessages(['job' => ['Job not found for this item.']]);
            }

            $newItem = $request->new_item_id ? Item::find($request->new_item_id) : null;

            // --- UPDATE existing segregation ---
            if ($request->id) {
                $segregation = ErpRgrItemSegregation::find($request->id);
                if (!$segregation) {
                    throw ValidationException::withMessages(['id' => ['Segregation not found.']]);
                }

                $segregation->update([
                    'label_status' => $request->input('label_status', $segregation->label_status),
                    'delivery_cancel' => $request->input('delivery_cancel', $segregation->delivery_cancel),
                    'packing_status' => $request->input('packing_status', $segregation->packing_status),
                    'defect_severity' => $request->input('defect_severity', $segregation->defect_severity),
                    'defect_type' => $request->input('defect_type', $segregation->defect_type),
                    'damage_nature' => $request->input('damage_nature', $segregation->damage_nature),
                    'remarks' => $request->input('remarks', $segregation->remarks),
                    'new_item_id' => $newItem?->id,
                    'new_item_code' => $newItem?->item_code,
                    'new_item_name' => $newItem?->item_name,
                    'new_item_attributes' => $request->input('new_item_attributes') 
                        ? json_encode($request->input('new_item_attributes')) 
                        : $segregation->new_item_attributes,
                ]);

                $message = 'Segregation updated successfully.';
            }
            // --- CREATE new segregation ---
            else {
                $existingSegregation = ErpRgrItemSegregation::where('job_item_id', $uniqueItem->id)->first();
                if ($existingSegregation) {
                    throw ValidationException::withMessages(['unique_item_id' => ['Segregation for this item already exists.']]);
                }

                $segregation = ErpRgrItemSegregation::create([
                    'rgr_id' => $job->morphable_id,
                    'rgr_item_id' => $uniqueItem->morphable_id,
                    'job_item_id' => $uniqueItem->id,
                    'item_id' => $uniqueItem->item_id,
                    'label_status' => $request->input('label_status', 0),
                    'delivery_cancel' => $request->input('delivery_cancel', 0),
                    'packing_status' => $request->input('packing_status', 0),
                    'defect_severity' => $request->input('defect_severity', 'minor'),
                    'defect_type' => $request->input('defect_type', 'component_missing'),
                    'damage_nature' => $request->input('damage_nature', 'no_damage'),
                    'remarks' => $request->input('remarks'),
                    'new_item_id' => $newItem?->id,
                    'new_item_code' => $newItem?->item_code,
                    'new_item_name' => $newItem?->item_name,
                    'new_item_attributes' => $request->input('new_item_attributes') 
                        ? json_encode($request->input('new_item_attributes')) 
                        : null,
                ]);

                $message = 'Segregation created successfully.';
            }

            if ($request->hasFile('files')) {
                $segregation->uploadDocuments($request->file('files'), 'images');
            }

            $uniqueItem->status = 'scanned';
            $uniqueItem->save();

            DB::commit();

            return [
                'message' => $message,
            ];

        } catch (\Throwable $e) {
            DB::rollBack();
            throw new ApiGenericException($e->getMessage());
        }
    }


   public function storeUniqueItem(Request $request)
    {
        DB::beginTransaction();
        try {
            $user = Helper::getAuthenticatedUser();

            $validated = $request->validate([
                'job_id'          => 'required|exists:erp_whm_jobs,id',
                'item_id'         => 'required|exists:erp_items,id',
                'item_code'       => 'required|string|max:50',
                'item_name'       => 'required|string|max:199',
                'item_attributes' => 'nullable|array',
            ]);

            $job = ErpWhmJob::find($validated['job_id']);
            if (!$job) {
                throw ValidationException::withMessages(['job_id' => ['Job not found.']]);
            }

            $rgr = ErpRgr::find($job->morphable_id);
            if (!$rgr) {
                throw ValidationException::withMessages(['job_id' => ['RGR not found for this job.']]);
            }

            $item = Item::find($validated['item_id']);
            if (!$item) {
                throw ValidationException::withMessages(['item_id' => ['Item not found.']]);
            }

            $uniqueItem = new ErpItemUniqueCode();
            $uniqueItem->job_id          = $job->id;
            $uniqueItem->item_id         = $item->id;
            $uniqueItem->item_code       = $item->item_code;
            $uniqueItem->item_name       = $item->item_name;
            $uniqueItem->item_attributes = $validated['item_attributes'] ? json_encode($validated['item_attributes'], JSON_THROW_ON_ERROR) : null;
            $uniqueItem->status          = 'pending';

            $uniqueItem->store_id        = $rgr->store_id;
            $uniqueItem->book_id         = $rgr->book_id;
            $uniqueItem->book_code       = $rgr->book_code;
            $uniqueItem->group_id        = $rgr->group_id;
            $uniqueItem->company_id      = $rgr->company_id;
            $uniqueItem->organization_id = $rgr->organization_id;
            $uniqueItem->doc_no          = $rgr->document_number;
            $uniqueItem->doc_date        = $rgr->document_date;

            $uniqueItem->trns_type  = $job->trns_type;
            $uniqueItem->job_type   = $job->type;
            $uniqueItem->doc_type   = 'receipt';
            $uniqueItem->type       = 'qr';
            $uniqueItem->uid        = (new WhmJob())->generateUniqueUid();

            $uniqueItem->save();

            $attributes = [];
            if ($uniqueItem->item_attributes) {
                if (is_string($uniqueItem->item_attributes)) {
                    $attributes = json_decode($uniqueItem->item_attributes, true);
                } elseif (is_array($uniqueItem->item_attributes)) {
                    $attributes = $uniqueItem->item_attributes;
                }
            }

            DB::commit();

            return [
                'message' => 'Unique item created successfully.',
                'data' => [
                    'id'             => $uniqueItem->id,
                    'item_id'        => $uniqueItem->item_id,
                    'item_code'      => $uniqueItem->item_code,
                    'item_name'      => $uniqueItem->item_name,
                    'item_uid'       => $uniqueItem->item_uid,
                    'uid'            => $uniqueItem->uid,
                    'status'         => $uniqueItem->status,
                    'attributes'     => $attributes,
                    'label_status'   => false,
                    'delivery_cancel'=> false,
                    'packing_status' => true,
                ],
            ];

        } catch (\Throwable $e) {
            DB::rollBack();
            throw new ApiGenericException($e->getMessage());
        }
    }


    public function getJobItemStatus($jobId)
    {
        try {
            $job = ErpWhmJob::with('itemUniqueCodes')->find($jobId);

            if (!$job) {
                return response()->json([
                    'message' => 'Job not found',
                    'data' => []
                ], 404);
            }

            $items = $job->itemUniqueCodes;

            $data = [
                'total_packets'   => $items->count(),
                'ok_to_receive'   => $items->where('status', 'ok_to_receive')->count(),
                'package_missing' => $items->where('status', 'package_missing')->count(),
                'wrong_product'   => $items->where('status', 'wrong_product')->count(),
                'missing_item'    => $items->where('status', 'missing_item')->count(),
                'extra_item'      => $items->where('status', 'extra_item')->count(),
                'transit_damage'  => $items->where('status', 'transit_damage')->count(),
            ];

            return response()->json([
                'message' => 'Data retrieved successfully.',
                'data' => $data
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function closeJob($jobId)
    {
        try {
            $authUser = Helper::getAuthenticatedUser();
            $job = ErpWhmJob::where('morphable_type', ErpRgr::class)->with('itemUniqueCodes')->find($jobId);

            if (!$job) {
                return response()->json([
                    'message' => 'Job not found',
                    'data' => []
                ], 404);
            }

            $items = $job->itemUniqueCodes;

            foreach ($items as $item) {
                $segregation = ErpRgrItemSegregation::where('rgr_item_id', $item -> morphable_id) -> first();
                if ($segregation) {
                    $isOkToReceive = in_array(RGRConstants::RGR_SEGREGATION_OK_TO_RECIEVE, $segregation -> segregation_status);
                    if ($isOkToReceive) {
                        //Generate Repair Orders
                        $status = RepHelper::generateRepFromRgrItem($item, ServiceParametersHelper::OK_TO_RECIEVE_BOOK_PARAM, $authUser, true);
                        if ($status['status'] == 'error') {
                            return response()->json([
                                'message' => $status['message'],
                                'error' => $status['message']
                            ], 500);
                        }
                    }
                }
            }

            return response()->json([
                'message' => 'Data retrieved successfully.',
                'data' => []
                ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
    
    public function getSegregationByUniqueItemId($uniqueItemId)
    {
        $uniqueItem = ErpItemUniqueCode::find($uniqueItemId);

        if (!$uniqueItem) {
            throw ValidationException::withMessages([
                'unique_item_id' => ['Unique item not found.']
            ]);
        }

        $segregation = ErpRgrItemSegregation::where('job_item_id', $uniqueItem->id)->first();

        if (!$segregation) {
            throw ValidationException::withMessages([
                'segregation' => ['No segregation found for this item.']
            ]);
        }

        $newItemAttributes = $segregation->new_item_attributes 
            ? json_decode($segregation->new_item_attributes, true) 
            : null;

        return [
            'message' => 'Segregation details retrieved successfully',
            'data' => [
                'segregation_id'      => $segregation->id,
                'job_item_id'         => $segregation->job_item_id,
                'rgr_id'              => $segregation->rgr_id,
                'rgr_item_id'         => $segregation->rgr_item_id,
                'original_item_id'    => $segregation->item_id,
                'label_status'        => $segregation->label_status,
                'delivery_cancel'     => $segregation->delivery_cancel,
                'packing_status'      => $segregation->packing_status,
                'defect_severity'     => $segregation->defect_severity,
                'defect_type'         => $segregation->defect_type,
                'damage_nature'       => $segregation->damage_nature,
                'remarks'             => $segregation->remarks,
                'new_item_id'         => $segregation->new_item_id,
                'new_item_code'       => $segregation->new_item_code,
                'new_item_name'       => $segregation->new_item_name,
                'new_item_attributes' => $newItemAttributes,
            ]
        ];
    }

  public function deleteScannedItem($uniqueItemId)
    {
        if (!is_numeric($uniqueItemId)) {
            throw ValidationException::withMessages([
                'unique_item_id' => ['The provided ID is invalid.']
            ]);
        }

        try {
            $uniqueItem = ErpItemUniqueCode::find($uniqueItemId);

            if (!$uniqueItem) {
                throw ValidationException::withMessages([
                    'unique_item_id' => ['Unique item not found.']
                ]);
            }

            $segregation = ErpRgrItemSegregation::where('job_item_id', $uniqueItem->id)->first();
            if ($segregation) {
                $segregation->delete();
            }

            $uniqueItem->status = 'pending';
            $uniqueItem->save();

            return response()->json([
                'message' => 'Record deleted successfully.',
            ], 200);

        } catch (\Throwable $e) {
            throw new ApiGenericException($e->getMessage());
        }
    }

}
