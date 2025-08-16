<?php

namespace App\Http\Controllers\Plant;

use App\Helpers\ConstantHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Item;   
use App\Models\ItemAttribute;
use App\Models\ErpAttribute;
use App\Helpers\Helper;
use App\Helpers\InventoryHelper;
use App\Models\PlantMaintWo;
use Exception;
use Illuminate\Support\Facades\DB;

class MaintWoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('plant.maint_wo.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $parentURL = "plant_maint-wo";
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

            $locations = InventoryHelper::getAccessibleLocations();
          
        
        if (count($servicesBooks['services']) > 0) {
            $firstService = $servicesBooks['services'][0];
            $series = Helper::getBookSeriesNew($firstService->alias, $parentURL)->get();
        }  
        return view('plant.maint_wo.create', compact('series', 'locations','items'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Base validation rules
        $rules = [
            'book_id' => 'required',
            'document_number' => 'required|string|max:100',
            'document_date' => 'required|date',
            'document_status' => 'required|string',
        ];

        // Add reference type validation only if not saving as draft
        if ($request->document_status !== 'draft') {
            $rules['reference_type'] = 'required|string';
        }

        // Add file validation if file is uploaded
        if ($request->hasFile('upload_file')) {
            $rules['upload_file'] = 'file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240'; // 10MB max
        }

        $request->validate($rules);

        $documentNumber = $request->document_number;
        $existingWo = PlantMaintWo::where('document_number', $documentNumber)->first();
        if ($existingWo) {
            return redirect()
                ->route('maint-wo.create')
                ->withInput()
                ->withErrors("Work Order Number '{$documentNumber}' already exists.");
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
            DB::transaction(function () use ($data, $request) {
                $workOrder = PlantMaintWo::create($data);

                // Handle file upload
                if ($request->hasFile('upload_file')) {
                    $file = $request->file('upload_file');
                    $originalName = $file->getClientOriginalName();
                    $extension = $file->getClientOriginalExtension();
                    $fileName = 'maint_wo_' . $workOrder->id . '_' . time() . '.' . $extension;
                    $path = $file->storeAs('maint_wo_documents', $fileName, 'public');
                    $workOrder->upload_file = $path;
                    $workOrder->save();
                }

                if ($workOrder->document_status != ConstantHelper::DRAFT) {
                    $doc = Helper::approveDocument(
                        $workOrder->book_id,
                        $workOrder->id,
                        $workOrder->revision_number,
                        "",
                        null,
                        1,
                        'submit',
                        0,
                        get_class($workOrder)
                    );

                    $workOrder->document_status = $doc['approvalStatus'] ?? $workOrder->document_status;
                    $workOrder->save();
                }
            });

            return redirect()
                ->route("maint-wo.index")
                ->with('success', 'Maintenance Work Order created!');
        } catch (\Throwable $e) {
            return redirect()
                ->route("maint-wo.create")
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return view('plant.maint_wo.show', compact('id'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        return view('plant.maint_wo.edit', compact('id'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Add your update logic here
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Add your delete logic here
    }
}
