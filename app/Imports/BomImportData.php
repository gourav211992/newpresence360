<?php

namespace App\Imports;

use App\Helpers\CurrencyHelper;
use App\Helpers\Helper;
use App\Helpers\ItemHelper;
use App\Models\AttributeGroup;
use App\Models\Attribute;
use App\Models\Bom;
use App\Models\BomUpload;
use App\Models\Item;
use App\Models\ItemAttribute;
use App\Models\ProductionRouteDetail;
use App\Models\ProductionRoute;
use App\Models\Station;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\DB;

class BomImportData implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        $user = Helper::getAuthenticatedUser();
        $filteredRows = $rows->filter(fn($row) => collect($row)->filter()->isNotEmpty());
        // DB::beginTransaction(); // Start Transaction
        // try {
            foreach ($filteredRows as $index => $row) {
                if($index) {
                    $errors = [];
                    $productItem = Item::where('item_code', @$row['product_code'])->first();
                    if(!$productItem) {
                        $errors[] = "Product not found: {$row['product_code']}";
                    }
                    $itemAttributes = [];
                    $productCode = $productItem?->item_code; 
                    $productName = $productItem?->item_name;
                    $productId = $productItem?->id;
                    $productUomId = $productItem?->uom?->id;
                    $productUomCode = $productItem?->uom?->name;
                    $productionType =  $row['production_type'] ?? 'In-house';
                    if(!in_array(strtolower($productionType), ['in-house','job work'])) {
                        $errors[] = "Invalid Production Type: {$row['production_type']}";
                    }
                    $productionRoute = ProductionRoute::where('name', @$row['production_route'])->first();
                    if(!$productionRoute) {
                        $errors[] = "Production route not found";
                    }
                    $productionRouteId = $productionRoute?->id;

                    $stationName = $row['station'] ?? ''; 
                    $station = Station::where('name', $stationName)->first();
                    if(!$station) {
                        $errors[] = "Station not found";
                    }
                    $stationId = $station?->id;
                    $checkStationMapped = null;
                    if($productionRouteId && $stationId) {
                       $checkStationMapped = $productionRoute->details()->where('station_id',$stationId)->first();
                    } 
                    if(!$checkStationMapped) {
                        $errors[] = "Station not mapped with Production route";
                    }
                    $customizable = $row['customizable'] ?? 'no';
                    if(!in_array(strtolower($customizable), ['yes','no'])) {
                        $errors[] = "Invalid customizable: {$row['customizable']}";
                    }
                    $item = Item::where('item_code', @$row['item_code'])->first();
                    if(!$item) {
                        $errors[] = "Item not found: {$row['item_code']}";
                    }
                    $itemId = $item?->id; 
                    $itemCode = $item?->item_code; 
                    $itemUomId = $item?->uom?->id;
                    $itemUomCode = $item?->uom?->name;

                    $itemAttributes = [];
                    # Need to valid item attribute length
                    if($item?->itemAttributes?->count()) {
                        for ($i = 1; $i <= 5; $i++) {
                            $result = $this->validateAttribute($item, $row, $i);
                            if (!empty($result['error'])) {
                                $errors[] = $result['error'];
                            }
                            if (!empty($result['attribute'])) {
                                $itemAttributes[] = $result['attribute'];
                            }
                        }
                        if($item?->itemAttributes?->count() != count($itemAttributes)) {
                            $errors[] = "Item Attribute length not match";
                        }
                    }

                    $consumptionQty = $row['consumption_qty'] ?? 0; 
                    if(!$consumptionQty) {
                        $errors[] = "Consumption not defined";
                    }
                    $costPerUnit = $row['cost_per_unit'] ?? 0;
                    $currency =  CurrencyHelper::getOrganizationCurrency();
                    $currencyId = $currency?->id ?? null; 
                    $transactionDate = date('Y-m-d');
                    $itemCost = 0;
                    if($itemId) {
                        $itemCost = ItemHelper::getItemCostPrice($itemId, [], $itemUomId, $currencyId, $transactionDate);
                    }
                    if(!floatval($costPerUnit)) {
                        $costPerUnit = $itemCost; 
                    }
                    if(!$costPerUnit) {
                        $errors[] = "Item cost not defined";
                    }
                    
                    BomUpload::create(
                        [
                            'type' => 'bom',
                            'production_route_id' => $productionRouteId,
                            'production_route_name' => @$row['production_route'],
                            'product_item_id' => $productId,
                            'product_item_code' => $productCode,
                            'product_item_name' => $productName,
                            'uom_id' => $productUomId,
                            'uom_code' => $productUomCode,
                            'customizable' => $customizable,
                            'bom_type' => 'fixed',
                            'production_type' => $productionType,
                            'product_attributes' => [],
                            'item_id' => $itemId,
                            'item_code' => $itemCode,
                            'item_uom_id' => $itemUomId,
                            'item_uom_code' => $itemUomCode,
                            'item_attributes' => $itemAttributes,
                            'reason' => $errors,
                            'attribute_name_1' => @$row['attribute_name_1'],
                            'attribute_value_1' => @$row['attribute_value_1'],
                            'attribute_name_2' => @$row['attribute_name_2'],
                            'attribute_value_2' => @$row['attribute_value_2'],
                            'attribute_name_3' => @$row['attribute_name_3'],
                            'attribute_value_3' => @$row['attribute_value_3'],
                            'attribute_name_4' => @$row['attribute_name_4'],
                            'attribute_value_4' => @$row['attribute_value_4'],
                            'attribute_name_5' => @$row['attribute_name_5'],
                            'attribute_value_5' => @$row['attribute_value_5'],
                            'consumption_qty' => $consumptionQty,
                            'cost_per_unit' => $costPerUnit,
                            'station_id' => $stationId,
                            'station_name' => $stationName,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]
                    );                        
                }
            }
            $groupedItems = BomUpload::select('product_item_id', DB::raw('COUNT(DISTINCT production_route_id) as route_count'))
                ->where('migrate_status', 0)
                ->where('created_by', $user->auth_user_id)
                ->groupBy('product_item_id')
                ->get();
            foreach ($groupedItems as $item) {
                if ($item->route_count > 1) {
                    BomUpload::where('migrate_status', 0)
                        ->where('created_by', $user->auth_user_id)
                        ->where('product_item_id', $item->product_item_id)
                        ->get()
                        ->each(function ($row) {
                            $reasons = $row->reason ?? [];
                            $reasons[] = 'Production route multiple';
                            $row->reason = array_unique($reasons);
                            $row->save();
                        });
                } else {
                    
                    $allData = BomUpload::where('migrate_status', 0)
                    ->where('product_item_id', $item->product_item_id)
                    ->where('created_by', $user->auth_user_id)
                    ->first();

                    $prDetailStationIds = ProductionRouteDetail::where('production_route_id',$allData?->production_route_id)
                                    ->where('consumption', 'yes')
                                    ->pluck('station_id')
                                    ->toArray();

                    $bomStationIds = BomUpload::where('migrate_status', 0)
                                    ->where('product_item_id', $item->product_item_id)
                                    ->where('created_by', $user->auth_user_id)
                                    ->pluck('station_id')
                                    ->toArray();
                    $newDiffArr = array_diff($prDetailStationIds, $bomStationIds);
                    if(count($newDiffArr)) {
                        $reasons = $allData->reason ?? [];
                        $reasons[] = "All station of production route not defined in Bom";
                        $allData->reason = array_unique($reasons);
                        $allData->save();
                    }
                }
            }

        //     DB::commit();
        // } catch (\Exception $e) {
        //     DB::rollBack();
        //     \Log::error('BOM Import Error: ' . $e->getMessage());
        //     throw $e;
        // }
    }

    private function validateAttribute($item, $row, int $index): array
    {
        $attribute = null;
        $groupName = $row["attribute_name_{$index}"] ?? null;
        $valueName = $row["attribute_value_{$index}"] ?? null;
        if (!$groupName) return [];
        $group = AttributeGroup::withDefaultGroupCompanyOrg()->where('name', $groupName)->first();
        if (!$group) {
            return ['error' => "Attr {$index} group not found"];
        }
        $attr = Attribute::where('value', $valueName)->where('attribute_group_id', $group->id)->first();
        if (!$attr) {
            return ['error' => "Attr {$index} value not found"];
        }
        if ($item && $group) {
            $itemAttr = ItemAttribute::where('item_id', $item->id)->where('attribute_group_id', $group->id)->first();
            if (!$itemAttr) {
                return ['error' => "Attr {$index} not mapped to item"];
            }
            $attrIds = $itemAttr->all_checked
                ? Attribute::where('attribute_group_id', $group->id)->pluck('id')->toArray()
                : (array) $itemAttr->attribute_id;
            if (!in_array($attr->id, $attrIds)) {
                return ['error' => "Attr {$index} value not mapped with item"];
            }
            $attribute = [
                'item_attribute_id' => $itemAttr->id,
                'attribute_name_id' => $group->id,
                'attribute_value_id' => $attr->id,
            ];
        }
        return ['attribute' => $attribute];
    }
}
