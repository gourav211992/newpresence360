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
use Illuminate\Validation\ValidationException;
use App\Lib\Services\WHM\WhmJob;
use App\Helpers\Helper;
use Illuminate\Support\Str;

class RgrJobController extends Controller
{

   public function getRgr(Request $request, $store_id)
    {
        if (!is_numeric($store_id)) {
            return response()->json(['message' => 'Invalid store_id provided.'], 400);
        }

        $storeExists = ErpStore::where('id', $store_id)->exists();
        if (!$storeExists) {
            return response()->json(['message' => 'Store does not exist.'], 404);
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

        return response()->json([
            'message' => $result->isEmpty() ? 'No records found.' : 'Data retrieved successfully.',
            "data" => [
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
        ], 200);
    }

   public function getRgrDetails($job_id)
    {
        if (!is_numeric($job_id)) {
            return response()->json(['message' => 'Invalid job_id provided.'], 400);
        }

         $job = ErpWhmJob::where('id', $job_id)
            ->where('morphable_type', ErpRgr::class)
            ->with('morphable', 'itemUniqueCodes')
            ->first();

        if (!$job || !$job->morphable) {
            return response()->json([
                'message' => 'No RGR found for this job.',
                'data' => []
            ], 200);
        }

        if ($job->status === 'closed') {  
            return response()->json([
                'message' => 'This job is closed.',
                'data' => []
            ], 200);
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
                'id'    => $uniqueCode->id ?? "",
                'item_id'    => $uniqueCode->item_id ?? "",
                'item_code'  => $uniqueCode->item_code ?? "",
                'item_name'  => $uniqueCode->item_name ?? "",
                'attributes' => $attributes,
                'uid'        => $uniqueCode->uid ?? "",
                'item_uid'   => $uniqueCode->item_uid ?? "",
                'status'     => $uniqueCode->status ?? "",
            ];
        });

        $data = [
            'id'          => $rgr?->job?->id,
            'document_no' => ($rgr->book_code ?? '') . '-' . ($rgr->document_number ?? ''),
            'trip_no'     => $rgr->trip_no ?? "",
            'vehicle_no'  => $rgr->vehicle_no ?? "",
            'total_item'  => $job->itemUniqueCodes->count(),
            'scanned_items' => $formattedScannedItems,
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

        return response()->json([
            'message' => 'Data retrieved successfully.',
            'data' => $responseData
        ], 200);
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
        $severity = ucfirst(strtolower($severity)); 

        $item = Item::find($itemId);
        if (!$item) {
            return response()->json([
                'error'   => 'Invalid item ID',
                'message' => 'The provided item ID does not exist.'
            ], 400);
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
            return response()->json([
                'data'    => [],
                'message' => 'No matching defect type found for this category and severity.',
            ]);
        }

        $reasons = ErpRgrDefectTypeDetail::select('id', 'reason')
            ->where('header_id', $defectType->id)
            ->get();

        return response()->json([
            'message' => 'Successfully retrieved defect reasons.',
            'data'    => $reasons,
        ]);
    }

    public function getItems(Request $request)
    {
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

        $records = $items->map(fn($item) => [
            'id'        => $item->id,
            'item_code' => $item->item_code,
            'item_name' => $item->item_name,
        ]);

        return response()->json([
            'message' => $records->isEmpty() ? 'No Goods items found.' : 'Data retrieved successfully.',
            'data' => [
                'records' => $records,
                'total'   => $records->count(),
            ]
        ], 200);
  }
   public function getAttributesByItemId($itemId)
    {
        $item = Item::where('id', $itemId)
                    ->where('status', ConstantHelper::ACTIVE)
                    ->first();

        if (!$item) {
            return response()->json(['error' => 'Item not found or not active'], 404);
        }

        $query = ItemAttribute::with('attributeGroup')
            ->where('item_id', $itemId)
            ->orderBy('id', 'asc');

        $itemAttributes = $query->get();

        $attributesByGroup = [];

        foreach ($itemAttributes as $itemAttribute) {
            $attributeGroupId = $itemAttribute->attributeGroup->id;
            $attributeGroupName = $itemAttribute->attributeGroup->name;
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

        return response()->json([
            'message' => 'Data retrieved successfully.',
            'data' => $response,
        ]);
    }

   public function scanItem($item_uid)
    {
    
        $uniqueItem = ErpItemUniqueCode::where('uid', $item_uid) -> first();

        if (!$uniqueItem) {
            return response()->json(['error' => 'Item not found'], 404);
        }

        if ($uniqueItem->status === 'scanned') {
            return response()->json(['error' => 'Item already scanned'], 409);
        }

        $attributes = [];
        if ($uniqueItem->item_attributes) {
            if (is_string($uniqueItem->item_attributes)) {
                $attributes = json_decode($uniqueItem->item_attributes, true);
            } elseif (is_array($uniqueItem->item_attributes)) {
                $attributes = $uniqueItem->item_attributes;
            }
        }

        return response()->json([
            'message' => 'Data retrieved successfully.',
            'data' => [
                'id'         => $uniqueItem->id,
                'item_id'    => $uniqueItem->item_id,
                'item_code'  => $uniqueItem->item_code,
                'item_name'  => $uniqueItem->item_name,
                'item_uid'   => $uniqueItem->item_uid,
                'uid'        => $uniqueItem->uid,
                'status'     => $uniqueItem->status,
                'attributes' => $attributes,
                'label_status' => true,
                'delivery_cancel' =>false ,
                'packing_status' =>true ,
            ],
        ]);
 }

  public function createSegregation(Request $request)
    {
        try {

            $request->validate([
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
            ]);

            $uniqueItem = ErpItemUniqueCode::find($request->unique_item_id);
            if (!$uniqueItem) {
                return response()->json(['message' => 'Item not found'], 404);
            }

            if (!$request->id && $uniqueItem->status === 'scanned') {
                return response()->json(['message' => 'Item has already been scanned.'], 409);
            }

            $job = ErpWhmJob::where('id', $uniqueItem->job_id)
                ->where('morphable_type', ErpRgr::class)
                ->first();
            if (!$job) {
                return response()->json(['message' => 'Job not found for this item'], 404);
            }

            $newItem = $request->new_item_id ? Item::find($request->new_item_id) : null;

            // --- UPDATE existing segregation ---
            if ($request->id) {
                $segregation = ErpRgrItemSegregation::find($request->id);
                if (!$segregation) {
                    return response()->json(['message' => 'Segregation not found'], 404);
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
                    return response()->json(['message' => 'Segregation for this item already exists.'], 409);
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

            $uniqueItem->status = 'scanned';
            $uniqueItem->save();

            return response()->json([
                'message' => $message,
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Something went wrong.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

   public function storeUniqueItem(Request $request)
    {
        try {
            $user = Helper::getAuthenticatedUser();
            $request->validate([
                'job_id'          => 'required|exists:erp_whm_jobs,id',
                'item_id'         => 'required|exists:erp_items,id',
                'item_code'       => 'required|string|max:50',
                'item_name'       => 'required|string|max:199',
                'item_attributes' => 'nullable|array',
            ]);

            $job = ErpWhmJob::find($request->job_id);
            if (!$job) {
                return response()->json(['message' => 'Job not found'], 404);
            }

            // Fetch RGR
            $rgr = ErpRgr::find($job->morphable_id);
            if (!$rgr) {
                return response()->json(['message' => 'RGR not found for this job'], 404);
            }

             $item = Item::find($request->item_id);
                if (!$item) {
                    return response()->json(['message' => 'Item not found'], 404);
                }

            $uniqueItem = new ErpItemUniqueCode();
            $uniqueItem->job_id          = $request->job_id;
            $uniqueItem->item_id         = $item->id;
            $uniqueItem->item_code       = $item->item_code;
            $uniqueItem->item_name       = $item->item_name;
            $uniqueItem->item_attributes = $request->item_attributes ? json_encode($request->item_attributes, JSON_THROW_ON_ERROR): null;
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

            return response()->json([
                'message' => 'Unique item created successfully.',
                'data' => [
                    'id'         => $uniqueItem->id,
                    'item_id'    => $uniqueItem->item_id,
                    'item_code'  => $uniqueItem->item_code,
                    'item_name'  => $uniqueItem->item_name,
                    'item_uid'   => $uniqueItem->item_uid,
                    'uid'        => $uniqueItem->uid,
                    'status'     => $uniqueItem->status,
                    'attributes' => $attributes,
                    'label_status' => false,
                    'delivery_cancel' =>false ,
                    'packing_status' =>true ,
                ],
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors'  => $e->errors()
            ], 422);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Something went wrong.',
                'error'   => $e->getMessage()
            ], 500);
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

    public function getSegregationByUniqueItemId($uniqueItemId)
    {
        $uniqueItem = ErpItemUniqueCode::find($uniqueItemId);

        if (!$uniqueItem) {
            return response()->json([
                'message' => 'Unique item not found',
            ], 404);
        }

        $segregation = ErpRgrItemSegregation::where('job_item_id', $uniqueItem->id)->first();

        if (!$segregation) {
            return response()->json([
                'message' => 'No segregation found for this item',
                'data' => []
            ], 200);
        }

        $newItemAttributes = $segregation->new_item_attributes 
            ? json_decode($segregation->new_item_attributes, true) 
            : null;

        return response()->json([
            'message' => 'Segregation details retrieved successfully',
            'data' => [
                'segregation_id'   => $segregation->id,
                'job_item_id'      => $segregation->job_item_id,
                'rgr_id'           => $segregation->rgr_id,
                'rgr_item_id'      => $segregation->rgr_item_id,
                'original_item_id' => $segregation->item_id,
                'label_status'     => $segregation->label_status,
                'delivery_cancel'  => $segregation->delivery_cancel,
                'packing_status'   => $segregation->packing_status,
                'defect_severity'  => $segregation->defect_severity,
                'defect_type'      => $segregation->defect_type,
                'damage_nature'    => $segregation->damage_nature,
                'remarks'          => $segregation->remarks,
                'new_item_id'      => $segregation->new_item_id,
                'new_item_code'    => $segregation->new_item_code,
                'new_item_name'    => $segregation->new_item_name,
                'new_item_attributes' => $newItemAttributes,
            ]
        ], 200);
    }

    public function deleteScannedItem($uniqueItemId)
    {
        try {
            
            $uniqueItem = ErpItemUniqueCode::find($uniqueItemId);

            if (!$uniqueItem) {
                return response()->json([
                    'message' => 'Unique item not found',
                ], 404);
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

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
