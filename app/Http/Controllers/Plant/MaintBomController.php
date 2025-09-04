<?php

namespace App\Http\Controllers\Plant;

use App\Helpers\ConstantHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use App\Http\Requests\MaintBOMRequest;
use App\Models\ErpAttribute;
use App\Models\Item;
use App\Models\ItemAttribute;
use App\Models\PlantMaintBom;
use App\Models\StockLedger;
use App\Models\PlantMaintBomHistory;
use Exception;


use Illuminate\Support\Facades\DB;

class MaintBomController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = PlantMaintBom::get();
        return view('plant.maint_bom.index', compact('data'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

        $parentURL = "plant_maint-bom";
        $series = [];
        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentURL);
        if (count($servicesBooks['services']) == 0) {
            return redirect()->route('/');
        }
        $firstService = $servicesBooks['services'][0];
        $series = Helper::getBookSeriesNew($firstService->alias, $parentURL)->get();

        $items = Item::where("type", "goods")
            ->with(["uom", "category", "itemAttributes"])
            ->get();
        foreach ($items as $item) {
            $itemId = $item->id;

            if (isset($itemId)) {
                $itemAttributes = ItemAttribute::where('item_id', $itemId)->get();
            } else {
                $itemAttributes = [];
            }
            $processedData = [];
            foreach ($itemAttributes as $key => $attribute) {
                $attributesArray = array();
                $attribute_group_id = $attribute->attribute_group_id;
                $attribute->group_name = $attribute->group?->name;

                $attributeValueData = ErpAttribute::whereIn('id', $attribute->attribute_id)->select('id', 'value')->where('status', 'active')->get();

                $attribute->values_data = $attributeValueData;
                $attribute = $attribute->only(['id', 'group_name', 'values_data', 'attribute_group_id']);

                array_push($processedData, ['id' => $attribute['id'], 'group_name' => $attribute['group_name'], 'values_data' => $attributeValueData, 'attribute_group_id' => $attribute['attribute_group_id']]);
            }
            $processedData = collect($processedData);

            $item->attributes = $processedData;
        }
       
        $items = $items->map(function ($item) {
            $confirmedStock = StockLedger::query()
                ->selectRaw("
                    SUM(
                        CASE 
                            WHEN document_status IN ('approved', 'approval_not_required', 'posted') 
                            THEN receipt_qty - reserved_qty
                            ELSE 0
                        END
                    ) as confirmed_stock
                ")
                ->where('item_code', $item->item_code) // yaha fix kiya
                ->value('confirmed_stock');
        
            return [
                'id'              => $item->id,
                'item_code'       => $item->item_code,
                'item_name'       => $item->item_name,
                'uom_name'        => optional($item->uom)->name,
                'uom_id'          => optional($item->uom)->id,
                'item_attributes' => $item->attributes,
                'available_stock' => $confirmedStock ?? 0, // agar null ho to 0
            ];
        });
        
        
        return view('plant.maint_bom.create', compact('series', 'items'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(MaintBOMRequest $request)
    {
        // FormRequest handles validation automatically
        $validator = $request->validated();

        if (!$validator) {
            return redirect()
                ->route('maint-bom.create')
                ->withInput()
                ->withErrors($request->errors());
        }

        $name = $request->bom_name;

        // Check for duplicate BOM name
        $existingAsset = PlantMaintBOM::where('bom_name', $name)->first();
        if ($existingAsset) {
            return redirect()
                ->route('maint-bom.create')
                ->withInput()
                ->withErrors("BOM Name '{$name}' already exists.");
        }

        $user = Helper::getAuthenticatedUser();
        $additionalData = [
            'created_by' => $user->auth_user_id,
            'type' => get_class($user),
            'organization_id' => $user->organization->id,
            'group_id' => $user->organization->group_id,
            'company_id' => $user->organization->company_id,
            'approval_level' => 1,
            'revision_number' => 0,
        ];

        $data = array_merge($request->all(), $additionalData);

        try {
            DB::transaction(function () use ($data) {
                $bom = PlantMaintBOM::create($data);

                if ($bom->document_status != ConstantHelper::DRAFT) {
                    $doc = Helper::approveDocument(
                        $bom->book_id,
                        $bom->id,
                        $bom->revision_number,
                        "",
                        null,
                        1,
                        'submit',
                        0,
                        get_class($bom)
                    );

                    $bom->document_status = $doc['approvalStatus'] ?? $bom->document_status;
                    $bom->save();
                }
            });

            return redirect()
                ->route("maint-bom.index")
                ->with('success', 'Maintenance BOM created!');
        } catch (\Throwable $e) {
            return redirect()
                ->route("maint-bom.create")
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(Request $r,string $id)
    {
        
        $data = PlantMaintBom::find($id);
        $currNumber = $r->has('revisionNumber');
        if ($currNumber && $data->revision_number!=$r->revisionNumber) {
            $currNumber = $r->revisionNumber;
            $data = PlantMaintBomHistory::where('source_id', $id)
                ->where('revision_number', $currNumber)->first();
        } else {
            $data = PlantMaintBom::findorFail($id);
        }

        $parentURL = "plant_maint-bom";
        $series = [];
        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentURL);
        if (count($servicesBooks['services']) == 0) {
            return redirect()->route('/');
        }
        $firstService = $servicesBooks['services'][0];
        $series = Helper::getBookSeriesNew($firstService->alias, $parentURL)->get();

        $userType = Helper::userCheck();
        $revision_number = $data->revision_number;

        $buttons = Helper::actionButtonDisplay(
            $data->book_id,
            $data->document_status,
            $id,
            0,
            $data->approval_level,
            $data->created_by ?? 0,
            $userType['type'],
            $revision_number
        );
        $revNo = $data->revision_number;
        if ($r->has('revisionNumber')) {
            $revNo = intval($r->revisionNumber);
        } else {
            $revNo = $data->revision_number;
        }
        $approvalHistory = Helper::getApprovalHistory($data->book_id, $id, $revNo, 0,$data->created_by);

        $docStatusClass = ConstantHelper::DOCUMENT_STATUS_CSS[$data->document_status] ?? '';
        
        $items = Item::where("type", "goods")
            ->with(["uom", "category", "itemAttributes"])
            ->get();
        foreach ($items as $item) {
            $itemId = $item->id;

            if (isset($itemId)) {
                $itemAttributes = ItemAttribute::where('item_id', $itemId)->get();
            } else {
                $itemAttributes = [];
            }
            $processedData = [];
            foreach ($itemAttributes as $key => $attribute) {
                $attributesArray = array();
                $attribute_group_id = $attribute->attribute_group_id;
                $attribute->group_name = $attribute->group?->name;

                $attributeValueData = ErpAttribute::whereIn('id', $attribute->attribute_id)->select('id', 'value')->where('status', 'active')->get();

                $attribute->values_data = $attributeValueData;
                $attribute = $attribute->only(['id', 'group_name', 'values_data', 'attribute_group_id']);

                array_push($processedData, ['id' => $attribute['id'], 'group_name' => $attribute['group_name'], 'values_data' => $attributeValueData, 'attribute_group_id' => $attribute['attribute_group_id']]);
            }
            $processedData = collect($processedData);

            $item->attributes = $processedData;
        }
        $items = $items->map(function ($item) {
            return [
                'id' => $item->id,
                'item_code' => $item->item_code,
                'item_name' => $item->item_name,
                'uom_name' => optional($item->uom)->name,
                'uom_id' => optional($item->uom)->id,
                'item_attributes' => $item->attributes,
            ];
        });
        return view('plant.maint_bom.show', compact('series', 'items','data','buttons', 'docStatusClass', 'revision_number', 'currNumber', 'approvalHistory'));
}

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $bom = PlantMaintBom::find($id);
        $parentURL = "plant_maint-bom";
        $series = [];
        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentURL);
        if (count($servicesBooks['services']) == 0) {
            return redirect()->route('/');
        }
        $firstService = $servicesBooks['services'][0];
        $series = Helper::getBookSeriesNew($firstService->alias, $parentURL)->get();

        $items = Item::where("type", "goods")
            ->with(["uom", "category", "itemAttributes"])
            ->get();
        foreach ($items as $item) {
            $itemId = $item->id;

            if (isset($itemId)) {
                $itemAttributes = ItemAttribute::where('item_id', $itemId)->get();
            } else {
                $itemAttributes = [];
            }
            $processedData = [];
            foreach ($itemAttributes as $key => $attribute) {
                $attributesArray = array();
                $attribute_group_id = $attribute->attribute_group_id;
                $attribute->group_name = $attribute->group?->name;

                $attributeValueData = ErpAttribute::whereIn('id', $attribute->attribute_id)->select('id', 'value')->where('status', 'active')->get();

                $attribute->values_data = $attributeValueData;
                $attribute = $attribute->only(['id', 'group_name', 'values_data', 'attribute_group_id']);

                array_push($processedData, ['id' => $attribute['id'], 'group_name' => $attribute['group_name'], 'values_data' => $attributeValueData, 'attribute_group_id' => $attribute['attribute_group_id']]);
            }
            $processedData = collect($processedData);

            $item->attributes = $processedData;
        }
        $items = $items->map(function ($item) {
            return [
                'id' => $item->id,
                'item_code' => $item->item_code,
                'item_name' => $item->item_name,
                'uom_name' => optional($item->uom)->name,
                'uom_id' => optional($item->uom)->id,
                'item_attributes' => $item->attributes,
            ];
        });
        $data = $bom;
        $userType = Helper::userCheck();
        $revision_number = $data->revision_number;
        $buttons = Helper::actionButtonDisplay(
            $data->book_id,
            $data->document_status,
            $id,
            0,
            $data->approval_level,
            $data->created_by ?? 0,
            $userType['type'],
            $revision_number
        );
        return view('plant.maint_bom.edit', compact('series', 'items', 'bom','buttons'));

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(MaintBOMRequest $request, $id)
    {
        // Validation via FormRequest
        $validator = $request->validated();

        if (!$validator) {
            return redirect()
                ->route('maint-bom.edit', $id)
                ->withInput()
                ->withErrors($request->errors());
        }

        $bom = PlantMaintBOM::findOrFail($id);

        // Check for duplicate BOM Name except current record
        $name = $request->bom_name;
        $existingAsset = PlantMaintBOM::where('bom_name', $name)
            ->where('id', '!=', $id)
            ->first();

        if ($existingAsset) {
            return redirect()
                ->route('maint-bom.edit', $id)
                ->withInput()
                ->withErrors('BOM Name ' . $name . ' already exists.');
        }

        $data = $request->all();

        DB::beginTransaction();

        try {
            if ($request->action_type == "amendment") {
                $revisionData = [
                    [
                        "model_type" => "header",
                        "model_name" => "PlantMaintBOM",
                        "relation_column" => "",
                    ],
                ];
                Helper::documentAmendment($revisionData, $id);
                Helper::approveDocument($bom->book_id, $bom->id, $bom->revision_number, $request->amend_remarks, $request->file('amend_attachment'), $bom->approval_level, 'amendment', 0, get_class($bom));
                $data['revision_number'] = $bom->revision_number + 1;
                $data['revision_date']=now();
            }
            $bom->update($data);

            // Approval handling if not draft
            if ($bom->document_status != ConstantHelper::DRAFT) {
                $doc = Helper::approveDocument(
                    $bom->book_id,
                    $bom->id,
                    $bom->revision_number,
                    "",
                    null,
                    1,
                    'submit',
          
                    0,
                    get_class($bom)
                );

                $bom->document_status = $doc['approvalStatus'] ?? $bom->document_status;
                $bom->save();
            }

            DB::commit();
            return redirect()->route("maint-bom.index")->with('success', 'Maintenance BOM updated!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route("maint-bom.edit", $id)->with('error', $e->getMessage());
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
    public function documentApproval(Request $request)
    {
        $request->validate([
            'remarks' => 'nullable|string|max:255',
            'attachment' => 'nullable'
        ]);
        DB::beginTransaction();
        try {
            $doc = PlantMaintBom::find($request->id);
            $bookId = $doc->book_id;
            $docId = $doc->id;
            $docValue = 0;
            $remarks = $request->remarks;
            $attachments = $request->file('attachment');
            $currentLevel = $doc->approval_level;
            $revisionNumber = $doc->revision_number ?? 0;
            $actionType = $request->action_type; // Approve or reject
            $modelName = get_class($doc);
            $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber, $remarks, $attachments, $currentLevel, $actionType, $docValue, $modelName);
            $doc->approval_level = $approveDocument['nextLevel'];
            $doc->document_status = $approveDocument['approvalStatus'];
            $doc->save();

            DB::commit();
            return response()->json([
                'message' => "Maint BOM $actionType successfully!",
                'data' => $doc,
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => "Error occurred while $actionType",
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
