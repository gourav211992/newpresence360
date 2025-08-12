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
use Illuminate\Support\Facades\DB;

class MaintBomController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = PlantMaintBom::get();
        return view('plant.maint_bom.index',compact('data'));
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
            ->with(["uom", "category","itemAttributes"])
            ->get();
       foreach($items as $item)
       {
            $itemId = $item->id;

            if (isset($itemId)) {
                $itemAttributes = ItemAttribute::where('item_id', $itemId) -> get();
            } else {
                $itemAttributes = [];
            }
            $processedData = [];
            foreach ($itemAttributes as $key => $attribute) {
                $attributesArray = array();
                $attribute_group_id = $attribute->attribute_group_id;
                $attribute->group_name = $attribute->group?-> name;

                $attributeValueData = ErpAttribute::whereIn('id', $attribute->attribute_id) -> select('id', 'value') -> where('status', 'active') -> get();

            $attribute->values_data = $attributeValueData;
            $attribute = $attribute -> only(['id','group_name', 'values_data', 'attribute_group_id']);

            array_push($processedData, ['id' => $attribute['id'], 'group_name' => $attribute['group_name'], 'values_data' => $attributeValueData, 'attribute_group_id' => $attribute['attribute_group_id']]);
            }
            $processedData = collect($processedData);

            $item->attributes = $processedData;
        }
        $items = $items->map(function($item) {
        return [
            'id' => $item->id,
            'item_code' => $item->item_code,
            'item_name' => $item->item_name,
            'uom_name' => optional($item->uom)->name,
            'uom_id' => optional($item->uom)->id,
            'item_attributes' => $item->attributes,
        ];
    });
   
        return view('plant.maint_bom.create', compact('series','items'));
    }

    /**
     * Store a newly created resource in storage.
     */
   public function store(MaintBOMRequest $request)
    {
        // Validation is automatically handled by the FormRequest
        $validator = $request->validated();

        if (!$validator) {
            return redirect()
                ->route('maint-bom.create')
                ->withInput()
                ->withErrors($request->errors());
        }
        $name = $request->bom_name;
        $existingAsset = PlantMaintBOM::where('bom_name', $name)->first();

        if ($existingAsset) {
            return redirect()
                ->route('maint-bom.create')
                ->withInput()
                ->withErrors('BOM Name ' . $name . ' already exists.');
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

        DB::beginTransaction();

        try {
            $bom = PlantMaintBOM::create($data);
            
            if ($bom->document_status != ConstantHelper::DRAFT) {
                $doc = Helper::approveDocument($bom->book_id, $bom->id, $bom->revision_number, "", null, 1, 'submit', 0, get_class($bom));
                $bom->document_status = $doc['approvalStatus'] ?? $bom->document_status;
                $bom->save();
            }
            
            DB::commit();
            return redirect()->route("maint-bom.index")->with('success', 'Maintenance BOM created!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route("maint-bom.create")->with('error', $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return view('plant.maint_bom.show');
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
            ->with(["uom", "category","itemAttributes"])
            ->get();
       foreach($items as $item)
       {
            $itemId = $item->id;

            if (isset($itemId)) {
                $itemAttributes = ItemAttribute::where('item_id', $itemId) -> get();
            } else {
                $itemAttributes = [];
            }
            $processedData = [];
            foreach ($itemAttributes as $key => $attribute) {
                $attributesArray = array();
                $attribute_group_id = $attribute->attribute_group_id;
                $attribute->group_name = $attribute->group?-> name;

                $attributeValueData = ErpAttribute::whereIn('id', $attribute->attribute_id) -> select('id', 'value') -> where('status', 'active') -> get();

            $attribute->values_data = $attributeValueData;
            $attribute = $attribute -> only(['id','group_name', 'values_data', 'attribute_group_id']);

            array_push($processedData, ['id' => $attribute['id'], 'group_name' => $attribute['group_name'], 'values_data' => $attributeValueData, 'attribute_group_id' => $attribute['attribute_group_id']]);
            }
            $processedData = collect($processedData);

            $item->attributes = $processedData;
        }
        $items = $items->map(function($item) {
        return [
            'id' => $item->id,
            'item_code' => $item->item_code,
            'item_name' => $item->item_name,
            'uom_name' => optional($item->uom)->name,
            'uom_id' => optional($item->uom)->id,
            'item_attributes' => $item->attributes,
        ];
    });
     return view('plant.maint_bom.edit', compact('series','items','bom'));
   
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
    }
