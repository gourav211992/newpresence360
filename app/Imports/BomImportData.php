<?php

namespace App\Imports;

use App\Helpers\BookHelper;
use App\Helpers\CurrencyHelper;
use App\Helpers\Helper;
use App\Helpers\ItemHelper;
use App\Models\AttributeGroup;
use App\Models\Attribute;
use App\Models\BomUpload;
use App\Models\Customer;
use App\Models\Item;
use App\Models\ItemAttribute;
use App\Models\ProductionRouteDetail;
use App\Models\ProductionRoute;
use App\Models\ProductSection;
use App\Models\ProductSectionDetail;
use App\Models\Station;
use App\Models\Vendor;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class BomImportData implements ToCollection, WithHeadingRow, SkipsEmptyRows, WithChunkReading
{
    protected $bookId;
    protected $documentDate;
    protected $moduleType;

    public function __construct($bookId, $documentDate, $moduleType)
    {
        $this->bookId = $bookId;
        $this->documentDate = $documentDate;
        $this->moduleType = $moduleType;
    }

    public function collection(Collection $rows)
    {
        $response = BookHelper::fetchBookDocNoAndParameters($this->bookId, $this->documentDate);
        $parameters = json_decode(json_encode($response['data']['parameters']), true) ?? [];
        $consumption_method = isset($parameters['consumption_method']) && $parameters['consumption_method'][0] == 'manual' ? false : true;
        $sectionRequired = isset($parameters['section_required']) && is_array($parameters['section_required']) && in_array('yes', array_map('strtolower', $parameters['section_required']));
        $subSectionRequired = isset($parameters['sub_section_required']) && is_array($parameters['sub_section_required']) && in_array('yes', array_map('strtolower', $parameters['sub_section_required']));
        $trimmedRows = $rows->map(function ($row) {
            $rowArray = $row->toArray();
            while (end($rowArray) === null || end($rowArray) === '') {
                array_pop($rowArray);
            }
            return collect($rowArray);
        });
        $user = Helper::getAuthenticatedUser();
            foreach ($trimmedRows as $index => $row) {
                if($index) {
                    $errors = [];
                    $productItem = Item::where('item_code', @$row['product_code'])->first();
                    if(!$productItem) {
                        $errors[] = "Product not found: ".@$row['product_code'];
                    }
                    $productAttributes = [];
                    # Need to valid item attribute length
                    if($productItem?->itemAttributes?->count()) {
                        $attributesString = $row['product_attributes'] ?? null;
                        if (!$attributesString) {
                            $attrOne = ItemAttribute::where('item_id', $productItem->id)
                                        ->where('all_checked',1)
                                        ->exists();
                            $attrTwo = false;
                            if(!$attrOne) {
                                $attrTwo = ItemAttribute::where('item_id', $productItem->id)
                                ->whereRaw('JSON_LENGTH(attribute_id) > 1')
                                ->exists();
                            }
                            if($attrTwo) {
                                $errors[] = "Product Attributes not specified";
                            } else {
                                foreach($productItem?->itemAttributes as $itemAttrOne) {
                                    $productAttributes[] = [
                                        'item_attribute_id' => $itemAttrOne?->id,
                                        'attribute_name_id' => @$itemAttrOne?->attribute_group_id,
                                        'attribute_value_id' => intval(@$itemAttrOne->attribute_id[0]) ?? 0,
                                    ];
                                }
                            }
                        } else {
                            $prodAttributes = isset($row['product_attributes']) && $row['product_attributes'] ? explode(',' ,$row['product_attributes']) : [];
                            if ($productItem?->itemAttributes?->count() !== count($prodAttributes)) {
                                $errors[] = "All Attributes of product not specified";
                            }
                            foreach($prodAttributes as $i => $prodAttribute) {
                                $result = $this->validateAttribute($productItem, $prodAttribute, $i,'Product');
                                if (!empty($result['error'])) {
                                    $errors[] = $result['error'];
                                }
                                if (!empty($result['attribute'])) {
                                    $productAttributes[] = $result['attribute'];
                                }
                            }
                        }
                    }
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
                    $vendorcode = $row['vendor_code'] ?? ''; 
                    $vendor = Vendor::where('vendor_code', $vendorcode)->first();
                    // if(!$vendor) {
                    //     $errors[] = "Vendor not found";
                    // }
                    $vendorId = $vendor?->id;
                    $customerId = null;
                    $customercode = null;
                    $customer = null;
                    if($this->moduleType == 'qbom') {
                        $customercode = $row['customer_code'] ?? ''; 
                        $customer = Customer::where('customer_code', $customercode)->first();
                        if(!$customer) {
                            $errors[] = "Customer not found";
                        }
                        $customerId = $customer?->id;
                    }
                    $sectionName = $row['section'] ?? ''; 
                    $section = ProductSection::where('name', $sectionName)->first();
                    if(!$section && $sectionRequired) {
                        $errors[] = "Section not found";
                    }
                    $sectionId = $section?->id;
                    $subSectionName = $row['sub_section'] ?? ''; 
                    $subSection = ProductSectionDetail::where('name', $subSectionName)->first();
                    if(!$subSection && $subSectionRequired) {
                        $errors[] = "Sub section not found";
                    }
                    $subSectionId = $subSection?->id;
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
                        $attrString = $row['item_attributes'] ?? null;
                        if (!$attrString) {
                            $attrOne = ItemAttribute::where('item_id', $item->id)
                                        ->where('all_checked',1)
                                        ->exists();
                            $attrTwo = false;
                            if(!$attrOne) {
                                $attrTwo = ItemAttribute::where('item_id', $item->id)
                                ->whereRaw('JSON_LENGTH(attribute_id) > 1')
                                ->exists();
                            }
                            if($attrTwo) {
                                $errors[] = "Item Attributes not specified";
                            } else {
                                foreach($item?->itemAttributes as $itemAttrTwo) {
                                    $attributeIds = is_array($itemAttrTwo->attribute_id)? $itemAttrTwo->attribute_id : json_decode($itemAttrTwo->attribute_id, true);
                                    $attributeValueId = 0;
                                    if ($itemAttrTwo->all_checked == 1) {
                                        if (empty($attributeIds) || !isset($attributeIds[0])) {
                                            $errors[] = "Attribute has no value found for item: {$item?->item_name} and attribute group: {$itemAttrTwo?->attributeGroup?->name}";
                                            continue;
                                        }
                                    }
                                    if (!empty($attributeIds) && isset($attributeIds[0])) {
                                        $attributeValueId = intval($attributeIds[0]);
                                    }
                                    $itemAttributes[] = [
                                        'item_attribute_id' => $itemAttrTwo?->id,
                                        'attribute_name_id' => $itemAttrTwo?->attribute_group_id,
                                        'attribute_value_id' => $attributeValueId,
                                    ];
                                }
                            }
                        } else {
                            $itAttributes = isset($row['item_attributes']) && $row['item_attributes'] ? explode(',' ,$row['item_attributes']) : [];
                            if ($item?->itemAttributes?->count() !== count($itAttributes)) {
                                $errors[] = "All Attributes of product not specified";
                            }
                            foreach($itAttributes as $i => $iAttribute) {
                                $result = $this->validateAttribute($item, $iAttribute, $i,'Item');
                                if (!empty($result['error'])) {
                                    $errors[] = $result['error'];
                                }
                                if (!empty($result['attribute'])) {
                                    $itemAttributes[] = $result['attribute'];
                                }
                            }
                        }
                    }
                    $consumptionQty = $row['consumption_qty'] ?? 0; 
                    if(!$consumption_method) {
                        if (is_null($consumptionQty) || $consumptionQty === '') {
                            $errors[] = "Consumption is required.";
                        } elseif (!is_numeric($consumptionQty)) {
                            $errors[] = "Consumption must be a valid number.";
                        } elseif ($consumptionQty < 0) {
                            $errors[] = "Consumption cannot be negative.";
                        } elseif ($consumptionQty == 0) {
                            $errors[] = "Consumption cannot be zero.";
                        }
                    }
                    $component_per_unit = 0;
                    $pieces = 0;
                    $std_qty = 0;
                    if($consumption_method) {
                        $component_per_unit = $row['component_per_unit'] ?? 0;
                        $pieces = $row['pieces'] ?? 0;
                        $std_qty = $row['std_qty'] ?? 0;
                        if ($component_per_unit !== null && $component_per_unit !== '') {
                            if (!is_numeric($component_per_unit)) {
                                $errors[] = "Norms component per unit must be a valid number.";
                            } elseif ($component_per_unit < 0) {
                                $errors[] = "Norms component per unit cannot be negative.";
                            } elseif ($component_per_unit == 0) {
                                $errors[] = "Norms component per unit cannot be zero.";
                            }
                        }
                        if ($pieces !== null && $pieces !== '') {
                            if (!is_numeric($pieces)) {
                                $errors[] = "Norms pieces must be a valid number.";
                            } elseif ($pieces < 0) {
                                $errors[] = "Norms pieces cannot be negative.";
                            } elseif ($pieces == 0) {
                                $errors[] = "Norms pieces cannot be zero.";
                            }
                        }
                        if ($std_qty !== null && $std_qty !== '') {
                            if (!is_numeric($std_qty)) {
                                $errors[] = "Norms std qty must be a valid number.";
                            } elseif ($std_qty < 0) {
                                $errors[] = "Norms std qty cannot be negative.";
                            } elseif ($std_qty == 0) {
                                $errors[] = "Norms std qty cannot be zero.";
                            }
                        }
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
                    // if(!$costPerUnit) {
                    //     $errors[] = "Item cost not defined";
                    // }
                    BomUpload::create(
                        [
                            'type' => $this->moduleType ?? 'bom',
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
                            'item_id' => $itemId,
                            'item_code' => $itemCode,
                            'item_uom_id' => $itemUomId,
                            'item_uom_code' => $itemUomCode,
                            'item_attributes' => $itemAttributes ?? [],
                            'product_attributes' => $productAttributes ?? [],
                            'reason' => $errors,
                            'consumption_qty' => $consumptionQty,
                            'consumption_per_unit' => $component_per_unit,
                            'pieces' => $pieces,
                            'std_qty' => $std_qty,
                            'cost_per_unit' => $costPerUnit,
                            'station_id' => $stationId,
                            'station_name' => $stationName,
                            'section_id' => $sectionId,
                            'section_name' => $sectionName,
                            'sub_section_id' => $subSectionId,
                            'sub_section_name' => $subSectionName,  
                            'vendor_id' => $vendorId,
                            'vendor_code' => $vendorcode,
                            'vendor_name' => $vendor?->company_name,
                            'customer_id' => $customerId ?? null,
                            'customer_code' => $customercode ?? null,
                            'customer_name' => $customer?->company_name ?? null,
                            'remark' => @$row['remark'],
                            'created_at' => now(),
                            'updated_at' => now()
                        ]
                    );                        
                }
            }
            $groupedItemsTests = BomUpload::where('migrate_status', 0)
                ->where('created_by', $user->auth_user_id)
                ->get();
            foreach($groupedItemsTests as $groupedItemsTest) {
                if (empty($groupedItemsTest->calculated_consumption) && empty($groupedItemsTest->consumption_qty)) {
                    $reasons = $groupedItemsTest->reason ?? [];
                    $reasons[] = 'Either calculated consumption or manual consumption must be defined.';
                    $groupedItemsTest->reason = array_unique($reasons);
                    $groupedItemsTest->save();
                }
                if($groupedItemsTest->calculated_consumption || $groupedItemsTest->consumption_qty) {
                    $reasons = $groupedItemsTest->reason ?? [];
                    $reasons = array_filter($reasons, function ($reason) {
                        return !str_contains(strtolower($reason), 'norms');
                    });
                    $groupedItemsTest->reason = array_unique($reasons);
                    $groupedItemsTest->save();
                }
                if($consumption_method) {

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
    }
    
    public function chunkSize(): int
    {
        return 100; // or 1000 based on system capacity
    }

    private function validateAttribute($item, $prodAttribute, int $index, $label): array
    {
        $attribute = null;
        $prodAttributeArr = explode(':', $prodAttribute) ?? [];
        $groupName = $prodAttributeArr[0] ?? null;
        $valueName = $prodAttributeArr[1] ?? null;
        if (!$groupName) return [];
        $group = AttributeGroup::whereRaw('LOWER(name) = ?', [strtolower(trim($groupName))])->first();
        if (!$group) {
            return ['error' => "{$label} Attribute group {$groupName}  not found"];
        }
        $attr = Attribute::whereRaw('LOWER(value) = ?', [strtolower(trim($valueName))])
                ->where('attribute_group_id', $group->id)
                ->first();
        if (!$attr) {
            return ['error' => "{$label} Attribute  value {$valueName} not found"];
        }
        if ($item && $group) {
            $itemAttr = ItemAttribute::where('item_id', $item->id)->where('attribute_group_id', $group->id)->first();
            if (!$itemAttr) {
                return ['error' => "{$label} Attribute {$groupName} not mapped to item"];
            }
            $attrIds = $itemAttr->all_checked
                ? Attribute::where('attribute_group_id', $group->id)->pluck('id')->toArray()
                : (array) $itemAttr->attribute_id;
            if (!in_array($attr->id, $attrIds)) {
                return ['error' => "{$label} Attribute value {$valueName} not mapped with item"];
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
