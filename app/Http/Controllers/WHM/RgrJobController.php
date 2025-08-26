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

class RgrJobController extends Controller
{
    public function getRgrDetails($store_id)
    {
        if (!is_numeric($store_id)) {
            return response()->json(['error' => 'Invalid store_id provided.'], 400);
        }

        $storeExists = ErpStore::where('id', $store_id)->exists();
        if (!$storeExists) {
            return response()->json(['error' => 'Store does not exist.'], 404);
        }

        $rgrs = ErpRgr::with(['items', 'job.itemUniqueCodes'])
            ->whereHas('job', function ($query) use ($store_id) {
                $query->where('store_id', $store_id);
            })
            ->orderBy('id','desc')
            ->paginate(CommonHelper::PAGE_LENGTH_10);

        $result = $rgrs->map(function ($rgr) {
            $job = $rgr->job;

            return [
                'document_no' => $rgr->document_number ?? null,
                'trip_no'     => $rgr->trip_no ?? null,
                'vehicle_no'  => $rgr->vehicle_no ?? null,
                'store_name'  => $rgr->store_name ?? null,
                'total_items' => $rgr->items->count(),
                'job' => $job ? [
                    'total_packets' => $job->itemUniqueCodes->count(),
                    'job_status'    => $job->status,
                    'created_at'    => optional($job->created_at)->format('Y-m-d'),
                ] : null,
            ];
        });

        return response()->json([
            'message' => 'Data retrieved successfully.',
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

   public function getRgrByJob($job_id)
    {
        if (!is_numeric($job_id)) {
            return response()->json(['error' => 'Invalid job_id provided.'], 400);
        }

        $job = ErpWhmJob::with('morphable')->find($job_id);

        if (!$job || !$job->morphable) {
            return response()->json([
                'message' => 'No RGR found for this job.',
                'data' => []
            ], 200);
        }

        if (!$job->morphable instanceof ErpRgr) {
            return response()->json([
                'message' => 'The related record is not an RGR.',
                'data' => []
            ], 200);
        }

        $rgr = $job->morphable;

        $data = [
            'document_no' => $rgr->document_number,
            'trip_no'     => $rgr->trip_no,
            'vehicle_no'  => $rgr->vehicle_no,
            'total_item'  => $job->itemUniqueCodes->count(),
        ];


        $scannedItems = ErpItemUniqueCode::where('job_id', $job_id)
            ->where('status', 'scanned')
            ->orderBy('id', 'desc')
            ->paginate(CommonHelper::PAGE_LENGTH_10);

        $formattedScannedItems = $scannedItems->map(function ($uniqueCode) {
            $attributes = [];

            if ($uniqueCode->item_attributes) {
                if (is_string($uniqueCode->item_attributes)) {
                    $attributes = json_decode($uniqueCode->item_attributes, true);
                } elseif (is_array($uniqueCode->item_attributes)) {
                    $attributes = $uniqueCode->item_attributes;
                }
            }

            return [
                'item_id'    => $uniqueCode->item_id,
                'item_code'  => $uniqueCode->item_code,
                'item_name'  => $uniqueCode->item_name,
                'attributes' => $attributes,
                'uid'        => $uniqueCode->uid,
                'status'     => $uniqueCode->status,
            ];
        });

        $responseData = [
            'rgr' => $data,
            'scanned_items' => $formattedScannedItems,
            'scanned_item_count' => $scannedItems->total(),
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
            'data' => ConstantHelper::DEFECT_SEVERITY_LEVELS
        ], 200);
    }

    //Damage nature list
    public function getDamageNatureOptions()
    {
        return response()->json([
            'data' => ConstantHelper::DAMAGE_NATURES
        ], 200);
    }

   public function getDefectTypes(string $severity, int $itemId)
    {
        $severity = ucfirst(strtolower($severity)); 

        $item = Item::find($itemId);
        if (!$item) {
            return response()->json([
                'error' => 'Invalid item ID',
                'message' => 'The provided item ID does not exist.'
            ], 400);
        }

        $subcategory_id = $item->subcategory_id;

        $defectType = ErpRgrDefectType::where('category_id', $subcategory_id)
            ->where('defect_severity', $severity)
            ->first();

        if (!$defectType) {
            return response()->json([
                'data' => [],
                'message' => 'No matching defect type found for this category and severity.',
            ]);
        }

        $reasons = ErpRgrDefectTypeDetail::select('id', 'type')->where('header_id', $defectType->id)
            ->get();

        return response()->json([
            'message' => 'Successfully retrieved defect reasons.',
            'data' => $reasons,
        ]);
    }

   public function getItems(Request $request)
    {
        $searchTerm = $request->query('search');

        $query = Item::where('status', ConstantHelper::ACTIVE);

        if ($searchTerm) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('item_code', 'like', '%' . $searchTerm . '%')
                ->orWhere('item_name', 'like', '%' . $searchTerm . '%');
            });
        }

        $items = $query->orderBy('id', 'desc')
                    ->paginate(CommonHelper::PAGE_LENGTH_10, ['id', 'item_code', 'item_name']);

        if ($items->isEmpty()) {
            return response()->json([
                'message' => 'No items found for the given search term.',
                'data' => []
            ], 404);
        }

        return response()->json([
            'message' => 'Data retrieved successfully.',
            'data' => [
                'records' => $items->items(),
                'pagination' => [
                    'current_page' => $items->currentPage(),
                    'last_page'    => $items->lastPage(),
                    'per_page'     => $items->perPage(),
                    'total'        => $items->total(),
                    'from'         => $items->firstItem(),
                    'to'           => $items->lastItem(),
                ],
            ]
        ]);
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
  public function scanItem(Request $request, $item_id)
    {
        $validator = Validator::make(['item_id' => $item_id], [
            'item_id' => 'required|exists:erp_item_unique_codes,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $uniqueItem = ErpItemUniqueCode::find($item_id);

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
            'message' => 'Item retrieved successfully.',
            'data' => [
                'id'         => $uniqueItem->id,
                'item_code'  => $uniqueItem->item_code,
                'item_name'  => $uniqueItem->item_name,
                'uid'        => $uniqueItem->uid,
                'status'     => $uniqueItem->status,
                'attributes' => $attributes,
            ],
        ]);
 }

  public function createSegregation(Request $request, $item_id)
    {
        $request->validate([
            'label_status' => 'nullable|boolean',
            'delivery_cancel' => 'nullable|boolean',
            'packing_status' => 'nullable|boolean',
            'defect_severity' => 'nullable|string|in:minor,major,scrap',
            'defect_type' => 'nullable|string',
            'damage_nature' => 'nullable|string|in:no_damage,customer_damage,transit_handling_damage,wear_tear_damage',
            'remarks' => 'nullable|string',
            'new_item_id' => 'nullable|exists:erp_items,id',
            'new_item_code' => 'nullable|string',
            'new_item_name' => 'nullable|string',
            'new_item_attributes' => 'nullable|array'
        ]);

        $uniqueItem = ErpItemUniqueCode::find($item_id);

      
        if (!$uniqueItem) {
            return response()->json(['message' => 'Item not found'], 404);
        }

        if ($uniqueItem->status === 'scanned') {
            return response()->json(['error' => 'Item already scanned'], 409);
        }

        $existingSegregation = ErpRgrItemSegregation::where('job_item_id', $item_id)->first();
        if ($existingSegregation) {
            return response()->json(['message' => 'Segregation already exists for this item'], 409);
        }

        $job = ErpWhmJob::find($uniqueItem->job_id);

        if (!$job) {
            return response()->json(['message' => 'Job not found for this item'], 404);
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

            'new_item_id' => $request->input('new_item_id'),
            'new_item_code' => $request->input('new_item_code'),
            'new_item_name' => $request->input('new_item_name'),
            'new_item_attributes' => $request->input('new_item_attributes') 
                ? json_encode($request->input('new_item_attributes')) 
                : null,
        ]);

        $uniqueItem->status = 'scanned';
        $uniqueItem->save();

        return response()->json([
            'message' => 'Segregation created successfully.',
            'data' => $segregation
        ], 201);
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
}
