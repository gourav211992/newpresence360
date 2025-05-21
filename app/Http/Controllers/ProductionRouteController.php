<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

use App\Traits\Deletable;

use App\Helpers\Helper;
use App\Helpers\ConstantHelper;

use App\Models\Station;
use App\Models\StationGroup;
use App\Models\Organization;
use App\Models\UserOrganizationMapping;

use App\Models\ProductionRoute;
use App\Models\ProductionLevel;
use App\Models\ProductionRouteDetail;
use App\Models\ProductionRouteParentDetail;

use Illuminate\Http\Request;
use App\Http\Requests\ProductionRouteRequest;
use App\Models\Attribute;
use App\Models\AttributeGroup;
use App\Models\Item;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth as FacadesAuth;

class ProductionRouteController extends Controller
{

    public function index(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        if ($request->ajax()) {
            $records = ProductionRoute::withDefaultGroupCompanyOrg()
                ->get();

            return DataTables::of($records)
                ->addIndexColumn()
                ->editColumn('name', function ($pr) {
                    return $pr->name ?? '';
                })
                ->editColumn('description', function ($pr) {
                    return $pr->description ?? '';
                })
                ->editColumn('safety_buffer_perc', function ($pr) {
                    return $pr->safety_buffer_perc ?? '';
                })
                ->addColumn('levels', function ($pr) {
                    return $pr->levels ? count($pr->levels) : 0;
                })
                ->addColumn('status', function ($pr) {
                    return '<span class="badge rounded-pill badge-light-' . ($pr->status == 'active' ? 'success' : 'danger') . '">'
                        . ucfirst($pr->status) . '</span>';
                })
                ->addColumn('action', function ($pr) {
                    $editUrl = route('production-route.edit', $pr->id);
                    $deleteUrl = route('production-route.delete', $pr->id);
                    return '<div class="dropdown">
                        <button type="button" class="btn btn-sm dropdown-toggle hide-arrow py-0" data-bs-toggle="dropdown">
                            <i data-feather="more-vertical"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item" href="' . $editUrl . '">
                                <i data-feather="edit-3" class="me-50"></i>
                                <span>Edit</span>
                            </a>
                            <a class="dropdown-item" href="' . $deleteUrl . '">
                                <i data-feather="trash-2" class="me-50"></i>
                                <span>Delete</span>
                            </a>
                        </div>
                    </div>';
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        return view('procurement.production-route.index');
    }

    public function create()
    {
        $user = Helper::getAuthenticatedUser();
        $status = ConstantHelper::STATUS;
        $stations = Station::withDefaultGroupCompanyOrg()
            ->where('status', 'active')
            ->get();
        $items = Item::withDefaultGroupCompanyOrg()
            ->get();

        return view('procurement.production-route.create', [
            'status' => $status,
            'stations' => $stations,
            'items' => $items
        ]);
    }

    public function store(ProductionRouteRequest $request)
    {
        $user = Helper::getAuthenticatedUser();
        DB::beginTransaction();
        try {
            $levels = $request->all()['levels'];
            if(!$levels){
                return response()->json([
                    'message' => "Levels for this production route required.",
                    'error' => "Levels for this production route required.",
                ], 422);
            }
            $lastLevelIndex = array_key_last($levels);

            if (count($levels[$lastLevelIndex]['details']) > 1) {
                return response()->json([
                    'message' => "The last level must have one station.",
                    'error' => "The last level must have one station.",
                ], 422);
            }

            foreach ($levels as $levelIndex => $level) {
                if (!empty($level['details'])) {
                    foreach ($level['details'] as $detail) {
                        // Case 1: Ensure the last level has no parent
                        if ($levelIndex == $lastLevelIndex) {
                            if (!is_null($detail['parent_id']) || !is_null($detail['hidden_parent_id'])) {
                                return response()->json([
                                    'message' => "Last level should not have a parent.",
                                    'error' => "Last level should not have a parent.",
                                ], 422);
                            }
                        } else {
                            // Case 2: All levels except the last must have a parent
                            if (empty($detail['parent_id']) && empty($detail['hidden_parent_id'])) {
                                return response()->json([
                                    'message' => "Parent missing in level " . ($levelIndex),
                                    'error' => "Missing parent for a child in level " . ($levelIndex),
                                ], 422);
                            }
                        }
                    }
                }
            }

            $parent_list = [];
            $station_list = [];
            foreach ($request->all()['levels'] as $level) {
                foreach ($level['details'] as $detail) {
                    if (!is_null(@$detail['parent_id'])) {
                        $parent_list[] = $detail['parent_id'];
                    }
                    if (!is_null(@$detail['hidden_parent_id'])) {
                        $parent_list[] = $detail['hidden_parent_id'];
                    }
                    if (!is_null(@$detail['hidden_station_id'])) {
                        $station_list[] = $detail['hidden_station_id'];
                    }
                    if (!is_null(@$detail['station_id'])) {
                        $station_list[] = $detail['station_id'];
                    }
                }
            }
            $parent_list = array_unique($parent_list);
            $station_list = array_unique($station_list);
            $missing_parents = array_diff($parent_list, $station_list);
            if (!empty($missing_parents)) {
                // $missing_list  = implode(', ', $missing_parents);
                $stationData = Station::whereIn('id', $missing_parents)->pluck('name')->toArray();
                $stationNames = implode(' and ', array_filter(array_merge([implode(', ', array_slice($stationData, 0, -1))], array_slice($stationData, -1))));
                return response()->json([
                    'message' => "Level not defined for parent " . ($stationNames),
                    'error' => "Level not defined for parent " . ($stationNames),
                ], 422);
            }

            $organization = Organization::where('id', $user->organization_id)->first();
            $organizationId = $organization?->id ?? null;
            $groupId = $organization?->group_id ?? null;
            $companyId = $organization?->company_id ?? null;
            // Header Save
            $pRoute = new ProductionRoute();
            $pRoute->fill($request->all());
            $pRoute->organization_id = $organizationId;
            $pRoute->group_id = $groupId;
            $pRoute->company_id = $companyId;
            $pRoute->safety_buffer_perc = $request->safety_buffer_perc ?? '0';
            $pRoute->save();

            // Level Save
            if (isset($request->all()['levels'])) {
                foreach ($request->all()['levels'] as $l_key => $level) {
                    $prLevel = new ProductionLevel();
                    $prLevel->production_route_id = $pRoute->id;
                    $prLevel->level = $level['level'] ?? null;
                    $prLevel->name = $level['name'] ?? null;
                    $prLevel->status = 'active';
                    $prLevel->save();
                    /*Parent Details Save*/
                    if (isset($level['details'])) {
                        foreach ($level['details'] as $detail) {
                            $finalAttrData = [];
                            if (isset($detail['attribute_data']) && $detail['attribute_data']) {
                                foreach ($detail['attribute_data'] as $attribute) {
                                    foreach ($attribute as $groupId => $attributeId) {
                                        // Fetch the corresponding attribute group and attribute value
                                        $attrgroup = AttributeGroup::where('id', $groupId)->first();
                                        $attrvalue = Attribute::where('id', $attributeId)->first();

                                        // Add data entry in the required structure
                                        $data = [
                                            "attr_name" => (string)@$attrgroup->id, // Ensure string type
                                            "attribute_name" => @$attrgroup->name,
                                            "attr_value" => @(string)$attrvalue->id, // Ensure string type
                                            "attribute_value" => @$attrvalue->value
                                        ];
                                        // Append the data to finalAttrData as a flat structure
                                        $finalAttrData[] = $data;
                                    }
                                }
                            }
                            $prDetail = new ProductionRouteDetail();
                            $prDetail->production_route_id = $pRoute->id;
                            $prDetail->production_level_id = $prLevel->id;
                            $prDetail->pr_parent_id = null;
                            $prDetail->level = $prLevel->level;
                            $prDetail->station_id = $detail['hidden_station_id'] ?? null;
                            $prDetail->pr_parent_id = $detail['hidden_parent_id'] ?? null;
                            // $prDetail->item_id = $detail['semi_finished_item_id'] ?? null;
                            $prDetail->consumption = $detail['consumption'] ?? null;
                            $prDetail->qa = $detail['qa'] ?? 'no';
                            $prDetail->status = 'active';
                            // $prDetail->item_attributes = json_encode($finalAttrData);
                            $prDetail->save();
                        }
                    }
                }
            }

            DB::commit();
            return response()->json([
                'message' => 'Record created successfully',
                'data' => $pRoute
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error occurred while creating the record.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function edit($id)
    {
        $user = Helper::getAuthenticatedUser();
        $status = ConstantHelper::STATUS;

        // Fetch the production route with levels and related station details
        $pRoute = ProductionRoute::with(['levels' => function ($e) {
            $e->with(['details.items'])
                ->orderBy('level');
        }])->findOrFail($id);
        // Fetch all active stations for dropdowns
        $stations = Station::withDefaultGroupCompanyOrg()
            ->where('status', 'active')
            ->get();
        return view('procurement.production-route.edit', [
            'status' => $status,
            'pRoute' => $pRoute,
            'stations' => $stations
        ]);
    }

    public function update(ProductionRouteRequest $request, $id)
    {
        $user = Helper::getAuthenticatedUser();
        DB::beginTransaction();
        try {
            $levels = $request->all()['levels'];
            $lastLevelIndex = array_key_last($levels); // Get the last level index

            if (count($levels[$lastLevelIndex]['details']) > 1) {
                return response()->json([
                    'message' => "The last level must have one station.",
                    'error' => "The last level must have one station.",
                ], 422);
            }

            foreach ($levels as $levelIndex => $level) {
                if (!empty($level['details'])) {
                    foreach ($level['details'] as $detail) {
                        // Case 1: Ensure the last level has no parent
                        if ($levelIndex == $lastLevelIndex) {
                            if (!is_null(@$detail['parent_id']) || !is_null(@$detail['hidden_parent_id'])) {
                                return response()->json([
                                    'message' => "Last level should not have a parent.",
                                    'error' => "Last level should not have a parent.",
                                ], 422);
                            }
                        } else {
                            // Case 2: All levels except the last must have a parent
                            if (empty(@$detail['parent_id']) && empty(@$detail['hidden_parent_id'])) {
                                return response()->json([
                                    'message' => "Parent missing in level " . ($levelIndex),
                                    'error' => "Missing parent for a child in level " . ($levelIndex),
                                ], 422);
                            }
                        }
                    }
                }
            }

            $parent_list = [];
            $station_list = [];
            foreach ($request->all()['levels'] as $level) {
                foreach ($level['details'] as $detail) {
                    if (!is_null(@$detail['parent_id'])) {
                        $parent_list[] = $detail['parent_id'];
                    }
                    if (!is_null(@$detail['hidden_parent_id'])) {
                        $parent_list[] = $detail['hidden_parent_id'];
                    }
                    if (!is_null(@$detail['hidden_station_id'])) {
                        $station_list[] = $detail['hidden_station_id'];
                    }
                    if (!is_null(@$detail['station_id'])) {
                        $station_list[] = $detail['station_id'];
                    }
                }
            }
            $parent_list = array_unique($parent_list);
            $station_list = array_unique($station_list);
            $missing_parents = array_diff($parent_list, $station_list);
            if (!empty($missing_parents)) {
                // $missing_list  = implode(', ', $missing_parents);
                $stationData = Station::whereIn('id', $missing_parents)->pluck('name')->toArray();
                $stationNames = implode(' and ', array_filter(array_merge([implode(', ', array_slice($stationData, 0, -1))], array_slice($stationData, -1))));
                return response()->json([
                    'message' => "Level not defined for parent " . ($stationNames),
                    'error' => "Level not defined for parent " . ($stationNames),
                ], 422);
            }

            $deletedData = json_decode($request->input('deleted_data'), true);
            if ($deletedData) {
                foreach ($deletedData as $data) {
                    if (@$data['production_level_id']) {
                        if ($data['type'] === 'parent') {
                            ProductionRouteDetail::where('production_level_id', @$data['production_level_id'])->delete();
                            ProductionLevel::where('id', @$data['production_level_id'])->delete();
                        } elseif ($data['type'] === 'child') {
                            if (!empty(@$data['child'])) {
                                ProductionRouteDetail::whereIn('id', @$data['child'])->delete();
                            }
                        }
                    }
                }
            }
            // Level Update
            $prRouteData = ProductionRoute::find($id);
            if (!$prRouteData) {
                return response()->json([
                    'message' => "No Production Route Data found!",
                    'error' => "No Production Route Data found!",
                ], 422);
            }
            $prRouteData->name = $request->all()['name'];
            $prRouteData->description = $request->all()['description'];
            $prRouteData->safety_buffer_perc = $request->all()['safety_buffer_perc'] ?? '0';
            $prRouteData->status = $request->all()['status'];
            $prRouteData->save();
            if (isset($request->all()['levels'])) {
                foreach ($request->all()['levels'] as $l_key => $level) {
                    if (isset($level['level_id']) && $level['level_id']) {
                        foreach ($level['details'] as $key => $child) {
                            $finalAttrData = [];
                            if (isset($child['attribute_data']) && $child['attribute_data']) {
                                foreach ($child['attribute_data'] as $attribute) {
                                    foreach ($attribute as $groupId => $attributeId) {
                                        // Fetch the corresponding attribute group and attribute value
                                        $attrgroup = AttributeGroup::where('id', $groupId)->first();
                                        $attrvalue = Attribute::where('id', $attributeId)->first();

                                        // Add data entry in the required structure
                                        $data = [
                                            "attr_name" => (string)@$attrgroup->id,
                                            "attribute_name" => @$attrgroup->name,
                                            "attr_value" => (string)@$attrvalue->id,
                                            "attribute_value" => @$attrvalue->value
                                        ];
                                        // Append the data to finalAttrData as a flat structure
                                        $finalAttrData[] = $data;
                                    }
                                }
                            }

                            $prLevel = ProductionLevel::find($level['level_id']);
                            $prLevel->level = $level['level'] ?? null;
                            $prLevel->name = $level['name'] ?? null;
                            $prLevel->save();
                            if (isset($child['child_id']) && $child['child_id']) {
                                $prDetail = ProductionRouteDetail::find($child['child_id']);
                                $prDetail->level = $level['level'];
                                if (isset($child['station_id']) && $child['station_id']) {
                                    $prDetail->station_id = @$child['station_id'];
                                } else {
                                    $prDetail->station_id = @$child['hidden_station_id'] ?? null;
                                }
                                if (isset($child['parent_id']) && $child['parent_id']) {
                                    $prDetail->pr_parent_id = @$child['parent_id'];
                                } else {
                                    $prDetail->pr_parent_id = @$child['hidden_parent_id'] ?? null;
                                }
                                // $prDetail->item_id = $child['semi_finished_item_id'] ?? 'no';
                                $prDetail->consumption = $child['consumption'] ?? 'no';
                                $prDetail->qa = $child['qa'] ?? 'no';
                                // $prDetail->item_attributes = json_encode($finalAttrData);
                                $prDetail->save();
                            }
                            else{
                                $prDetail = new ProductionRouteDetail();
                                $prLevel->production_route_id = $id;
                                $prDetail->production_level_id = $prLevel->id;
                                $prDetail->level = $level['level'];
                                if (isset($child['station_id']) && $child['station_id']) {
                                    $prDetail->station_id = @$child['station_id'];
                                } else {
                                    $prDetail->station_id = @$child['hidden_station_id'] ?? null;
                                }
                                if (isset($child['parent_id']) && $child['parent_id']) {
                                    $prDetail->pr_parent_id = @$child['parent_id'];
                                } else {
                                    $prDetail->pr_parent_id = @$child['hidden_parent_id'] ?? null;
                                }
                                // $prDetail->item_id = $child['semi_finished_item_id'] ?? 'no';
                                $prDetail->consumption = $child['consumption'] ?? 'no';
                                $prDetail->qa = $child['qa'] ?? 'no';
                                // $prDetail->item_attributes = json_encode($finalAttrData);
                                $prDetail->save();
                            }

                        }
                    } else {
                        $prLevel = new ProductionLevel();
                        $prLevel->production_route_id = $id;
                        $prLevel->level = $level['level'] ?? null;
                        $prLevel->name = $level['name'] ?? null;
                        $prLevel->status = 'active';
                        $prLevel->save();
                        foreach ($level['details'] as $key => $child) {
                            $prDetail = new ProductionRouteDetail();
                            $prDetail->production_route_id = $id;
                            $prDetail->production_level_id = $prLevel->id;
                            $prDetail->level = $level['level'];
                            if (isset($child['station_id']) && $child['station_id']) {
                                $prDetail->station_id = $child['station_id'] ?? null;
                            } else {
                                $prDetail->station_id = $child['hidden_station_id'] ?? null;
                            }
                            if (isset($child['parent_id']) && $child['parent_id']) {
                                $prDetail->pr_parent_id = $child['parent_id'] ?? null;
                            } else {
                                $prDetail->pr_parent_id = $child['hidden_parent_id'] ?? null;
                            }
                            // $prDetail->item_id = $child['semi_finished_item_id'] ?? 'no';
                            $prDetail->consumption = $child['consumption'] ?? 'no';
                            $prDetail->qa = $child['qa'] ?? 'no';
                            $prDetail->created_by = $user->id;
                            $prDetail->save();
                        }
                    }
                }
            }
            DB::commit();
            return response()->json([
                'message' => 'Record updated successfully',
                'data' => '$pRoute'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error occurred while creating the record.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function delete($id)
    {
        $pRoute = ProductionRoute::findOrFail($id);
        ProductionRouteDetail::where('production_route_id', $pRoute->id)->delete();
        ProductionLevel::where('production_route_id', $pRoute->id)->delete();
        $pRoute->delete();

        return redirect()->route("production-route.index")->with('success', 'Record deleted successfully.');
    }

    public function getStationData(Request $request)
    {
        $data = Station::where('id', $request->all()['station_id'])->get('is_consumption');

        return response()->json($data);
    }

    public function getItemAttribute(Request $request)
    {
        $item = Item::find($request->item_id);
        if (!$item) {
            return response()->json(['status' => 404, 'message' => 'Item not found.']);
        }

        $selectedAttr = $request->selectedAttr ? json_decode($request->selectedAttr, true) : [];

        // Initialize item attributes collection
        $itemAttributes = collect();

        if (count($selectedAttr)) {
            $itemAttributes = $item->itemAttributes()->whereIn('id', array_column($selectedAttr, 'attr_name'))->get();
        } else {
            $itemAttributes = $item->itemAttributes;
        }

        // Initialize an array to hold the selected attribute data
        $selectedAttributes = [];
        $dropdownCount = count($itemAttributes); // Count total dropdowns

        // Determine container style: Scroll if more than 2 dropdowns
        $scrollStyle = $dropdownCount > 1 ? "max-width: 400px; overflow-x: auto; white-space: nowrap;" : "";

        // Start building the dropdowns inside a flex container
        $html = "<div class='attribute-slider' style='display: flex; gap: 10px; padding: 5px; {$scrollStyle}'>";

        foreach ($itemAttributes as $key => $attribute) {
            if ($attribute->attributeGroup->name) {
                $html .= "<div style='display: flex; align-items: center; gap: 10px; min-width: 180px;'>";
                $html .= "<label style='font-size: 12px; white-space: nowrap;'>{$attribute->attributeGroup->name}:</label>";
                $html .= "<select class='form-select select2' name='levels[$request->levelId][details][$request->detailCount][attribute_data][$key][" . $attribute->attributeGroup->id . "]' data-attr-name='{$attribute->attributeGroup->name}' data-attr-group-id='{$attribute->attributeGroup->id}' data-attr-id='{$attribute->id}'>";
                $html .= "<option value=''>Select {$attribute->attributeGroup->name}</option>";

                foreach ($attribute->attributeGroup->attributes as $attr) {
                    $selected = in_array($attr->id, array_column($selectedAttributes, 'attr_value')) ? 'selected' : '';
                    $html .= "<option value='{$attr->id}' {$selected}>{$attr->value}</option>";

                    if (in_array($attr->id, array_column($selectedAttr, 'attr_value'))) {
                        $selectedAttributes[] = [
                            'attr_name' => $attribute->attributeGroup->id,
                            'attribute_name' => $attribute->attributeGroup->name,
                            'attr_value' => $attr->id,
                            'attribute_value' => $attr->value,
                        ];
                    }
                }
                $html .= "</select>";
                $html .= "</div>";
            }
        }
        $html .= "</div>";

        return response()->json([
            'data' => [
                'attr' => $itemAttributes->count(),
                'html' => $html,
                'selected_attributes' => $selectedAttributes,
            ],
            'status' => 200,
            'message' => 'Attributes fetched successfully.'
        ]);
    }

    public function getItemAttributeEdit(Request $request)
    {
        $item = Item::find($request->item_id);
        $prData = ProductionRouteDetail::where('item_id', $item->id)
            ->where('production_route_id', $request->prouteId)
            ->where('level', $request->levelId)
            ->first();
        $data = $prData->item_attributes;

        if (!$item) {
            return response()->json(['status' => 404, 'message' => 'Item not found.']);
        }

        $selectedAttr = $request->selectedAttr ? json_decode($request->selectedAttr, true) : [];

        // Initialize item attributes collection
        $itemAttributes = collect();

        if (count($selectedAttr)) {
            $itemAttributes = $item->itemAttributes()->whereIn('id', array_column($selectedAttr, 'attr_name'))->get();
        } else {
            $itemAttributes = $item->itemAttributes;
        }

        $selectedAttributes = [];
        $dropdownCount = count($itemAttributes);
        $scrollStyle = $dropdownCount > 1 ? "max-width: 400px; overflow-x: auto; white-space: nowrap;" : "";
        $html = "<div class='attribute-slider' style='display: flex; gap: 10px; padding: 5px; {$scrollStyle}'>";
        foreach ($itemAttributes as $key => $attribute) {
            $matchingData = array_filter(json_decode($data), function ($d) use ($attribute) {
                return $d->attribute_name == $attribute->attributeGroup->name;
            });
            if ($attribute->attributeGroup->name && count($matchingData)) {
                $html .= "<div style='display: flex; align-items: center; gap: 10px; min-width: 180px;'>";
                $html .= "<label style='font-size: 12px; white-space: nowrap;'>{$attribute->attributeGroup->name}:</label>";
                $html .= "<select class='form-select select2' name='levels[$request->levelId][details][$request->detailCount][attribute_data][$key][" . $attribute->attributeGroup->id . "]' data-attr-name='{$attribute->attributeGroup->name}' data-attr-group-id='{$attribute->attributeGroup->id}' data-attr-id='{$attribute->id}'>";
                $html .= "<option value=''>Select {$attribute->attributeGroup->name}</option>";

                foreach ($attribute->attributeGroup->attributes as $attr) {
                    $selected = '';
                    foreach ($matchingData as $match) {
                        if ($match->attr_value == $attr->id) {
                            $selected = 'selected';
                            $selectedAttributes[] = [
                                'attr_name' => $attribute->attributeGroup->id,
                                'attribute_name' => $attribute->attributeGroup->name,
                                'attr_value' => $attr->id,
                                'attribute_value' => $attr->value,
                            ];
                        }
                    }
                    $html .= "<option value='{$attr->id}' {$selected}>{$attr->value}</option>";
                }
                $html .= "</select>";
                $html .= "</div>";
            }
        }
        $html .= "</div>";
        return response()->json([
            'data' => [
                'attr' => $itemAttributes->count(),
                'html' => $html,
                'selected_attributes' => $selectedAttributes,
            ],
            'status' => 200,
            'message' => 'Attributes fetched successfully.'
        ]);
    }
}
